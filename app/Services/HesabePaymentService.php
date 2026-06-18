<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HesabePaymentService
{
    private string $baseUrl;
    private ?string $merchantCode;
    private ?string $accessCode;
    private ?string $secretKey;
    private ?string $ivKey;
    private string $checkoutEndpoint;
    private string $paymentEndpoint;
    private string $reviewKitsEndpoint;
    private ?string $returnUrl;
    private ?string $failureUrl;

    public function __construct()
    {
        $config = config('services.hesabe', []);

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://sandbox.hesabe.com', '/');
        $this->merchantCode = $config['merchant_code'] ?? null;
        $this->accessCode = $config['access_code'] ?? null;
        // Trim keys to avoid whitespace issues
        $this->secretKey = isset($config['secret_key']) ? trim($config['secret_key']) : null;
        $this->ivKey = isset($config['iv_key']) ? trim($config['iv_key']) : null;
        $this->checkoutEndpoint = $config['checkout_endpoint'] ?? '/checkout';
        $this->paymentEndpoint = $config['payment_endpoint'] ?? '/payment';
        $this->reviewKitsEndpoint = $config['review_kits_endpoint'] ?? '/api/integration-kits/review';
        $this->returnUrl = $config['return_url'] ?? null;
        $this->failureUrl = $config['failure_url'] ?? null;
    }

    public function reviewKits(): array
    {
        $this->assertConfigured();

        $response = Http::withOptions(['verify' => false])
            ->acceptJson()
            ->get($this->baseUrl . $this->reviewKitsEndpoint, [
                'merchantCode' => $this->merchantCode,
                'accessCode' => $this->accessCode,
            ]);

        if ($response->failed()) {
            throw new PaymentException('Unable to fetch Hesabe review kits.');
        }

        return $response->json();
    }

    public function checkout(array $payload): array
    {
        $this->assertConfigured();

        // Hesabe may require responseUrl and failureUrl - add defaults if not provided
        if (empty($payload['responseUrl'])) {
            $payload['responseUrl'] = $this->returnUrl ?? 'https://sandbox.hesabe.com/success';
        }

        if (empty($payload['failureUrl'])) {
            $payload['failureUrl'] = $this->failureUrl ?? 'https://sandbox.hesabe.com/failure';
        }

        // Log payload structure before encryption (without sensitive data)
        $payloadForLog = $payload;
        if (isset($payloadForLog['cardNumber'])) {
            $payloadForLog['cardNumber'] = substr($payloadForLog['cardNumber'], 0, 4) . '****';
        }
        if (isset($payloadForLog['cardSecurityCode'])) {
            $payloadForLog['cardSecurityCode'] = '***';
        }
        Log::debug('Hesabe checkout payload structure', [
            'keys' => array_keys($payload),
            'has_responseUrl' => !empty($payload['responseUrl']),
            'has_failureUrl' => !empty($payload['failureUrl']),
            'payload_preview' => $payloadForLog,
        ]);

        $encryptedPayload = $this->encryptPayload($payload);

        $requestPayload = [
            'merchantCode' => $this->merchantCode,
            'data' => $encryptedPayload,
        ];

        // Log request for debugging (without sensitive data)
        Log::debug('Hesabe checkout request', [
            'endpoint' => $this->baseUrl . $this->checkoutEndpoint,
            'merchant_code' => $this->merchantCode ? substr($this->merchantCode, 0, 4) . '****' : null,
            'merchant_code_length' => $this->merchantCode ? strlen($this->merchantCode) : 0,
            'has_access_code' => !empty($this->accessCode),
            'access_code_length' => $this->accessCode ? strlen($this->accessCode) : 0,
            'has_data' => !empty($encryptedPayload),
            'data_length' => strlen($encryptedPayload),
            'secret_key_length' => $this->secretKey ? strlen($this->secretKey) : 0,
            'iv_key_length' => $this->ivKey ? strlen($this->ivKey) : 0,
            'secret_is_hex' => $this->secretKey ? ctype_xdigit($this->secretKey) : false,
            'iv_is_hex' => $this->ivKey ? ctype_xdigit($this->ivKey) : false,
        ]);

        // Hesabe requires accessCode in headers, not in POST body
        $response = Http::withOptions(['verify' => false])
            ->acceptJson()
            ->withHeaders([
                'accessCode' => $this->accessCode,
            ])
            ->post($this->baseUrl . $this->checkoutEndpoint, $requestPayload);

        $responseStatus = $response->status();
        $responseBody = $response->body();

        // Log response immediately
        Log::debug('Hesabe checkout response received', [
            'status_code' => $responseStatus,
            'response_length' => strlen($responseBody),
            'response_preview' => substr($responseBody, 0, 100),
            'is_successful' => $response->successful(),
            'is_failed' => $response->failed(),
            'is_hex' => ctype_xdigit($responseBody),
        ]);

        // Hesabe may return 401/422 with encrypted error response - try to decrypt it
        if (in_array($responseStatus, [401, 422]) || ($response->failed() && ctype_xdigit($responseBody))) {
            Log::warning('Hesabe returned HTTP error with encrypted response, attempting to decrypt', [
                'status_code' => $responseStatus,
                'response_length' => strlen($responseBody),
            ]);

            // Try to decrypt the error response
            try {
                if (ctype_xdigit($responseBody)) {
                    $hexDecoded = hex2bin($responseBody);
                    if ($hexDecoded !== false) {
                        $decrypted = $this->decryptRawPayload($hexDecoded);

                        if (isset($decrypted['status']) && $decrypted['status'] === false) {
                            $errorMessage = $decrypted['message'] ?? 'Authentication failed';
                            $errorCode = $decrypted['code'] ?? null;

                            Log::error('Hesabe authentication error (decrypted)', [
                                'http_status' => $responseStatus,
                                'code' => $errorCode,
                                'message' => $errorMessage,
                                'full_response' => $decrypted,
                            ]);

                            $errorDetails = $errorMessage;
                            if ($errorCode) {
                                $errorDetails .= " (Code: {$errorCode})";
                            }

                            throw new PaymentException('Unable to initiate Hesabe checkout. Error: ' . $errorDetails);
                        }
                    }
                }
            } catch (PaymentException $e) {
                // Re-throw decrypted errors
                throw $e;
            } catch (PaymentException $e) {
                // If it's a decryption error, provide more context
                if (strpos($e->getMessage(), 'decrypt') !== false) {
                    Log::error('Failed to decrypt Hesabe error response', [
                        'error' => $e->getMessage(),
                        'status_code' => $responseStatus,
                        'response_length' => strlen($responseBody),
                        'response_preview' => substr($responseBody, 0, 100),
                        'note' => 'Response may be encrypted with different keys or format',
                    ]);
                    // Don't throw here - let it fall through to generic error
                } else {
                    // Re-throw non-decryption PaymentExceptions
                    throw $e;
                }
            } catch (\Throwable $e) {
                Log::error('Unexpected error decrypting Hesabe error response', [
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                    'status_code' => $responseStatus,
                ]);
            }

            // If decryption failed or didn't yield error details, provide helpful error based on status code
            $errorMessage = 'Unable to initiate Hesabe checkout. HTTP Error: ' . $responseStatus;

            if ($responseStatus === 422) {
                $errorMessage .= ' - Unprocessable Entity. The request format may be incorrect or validation failed.';
            } elseif ($responseStatus === 401) {
                $errorMessage .= ' - Unauthorized. Please verify your HESABE_MERCHANT_CODE, HESABE_ACCESS_CODE, HESABE_SECRET_KEY, and HESABE_IV_KEY are correct.';
            } else {
                $errorMessage .= ' - Request failed. Please check your credentials and request format.';
            }

            throw new PaymentException($errorMessage);
        }

        if ($response->failed()) {
            Log::error('Hesabe checkout HTTP error', [
                'status_code' => $responseStatus,
                'response_preview' => substr($responseBody, 0, 200),
            ]);
            throw new PaymentException('Unable to initiate Hesabe checkout. HTTP Error: ' . $responseStatus);
        }

        // Log response for debugging
        Log::debug('Hesabe checkout response', [
            'status_code' => $response->status(),
            'response_length' => strlen($responseBody),
            'is_json' => $this->isJson($responseBody),
            'is_hex' => ctype_xdigit($responseBody),
        ]);

        // Hesabe may return encrypted data directly (as hex string) or as JSON with encrypted data field
        $decrypted = null;
        $body = null;

        // Try to parse as JSON first
        $body = json_decode($responseBody, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($body)) {
            // Response is JSON - check for errors
            if (isset($body['status']) && $body['status'] === false) {
                $errorMessage = $body['message'] ?? 'Unknown error from Hesabe';
                $errorCode = $body['code'] ?? null;

                // Log full error for debugging
                Log::error('Hesabe checkout error', [
                    'code' => $errorCode,
                    'message' => $errorMessage,
                    'full_response' => $body,
                ]);

                // Format error message more clearly
                $errorDetails = $errorMessage;
                if ($errorCode) {
                    $errorDetails .= " (Code: {$errorCode})";
                }

                // Provide helpful message for authentication errors
                if ($errorCode == 501) {
                    $errorDetails .= '. Please verify your HESABE_MERCHANT_CODE, HESABE_ACCESS_CODE, HESABE_SECRET_KEY, and HESABE_IV_KEY in your .env file.';
                }

                throw new PaymentException('Unable to initiate Hesabe checkout. Error: ' . $errorDetails);
            }

            // Try to decrypt data field if it exists
            if (isset($body['data']) && !empty($body['data'])) {
                try {
                    $decrypted = $this->decryptPayload($body['data']);
                } catch (PaymentException $e) {
                    Log::warning('Failed to decrypt Hesabe response data field', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } else {
            // Response is not JSON - might be encrypted hex string or base64
            // Try to decrypt the entire response body
            try {
                // Check if it's hex-encoded (like the error we're seeing)
                if (ctype_xdigit($responseBody)) {
                    // Convert hex to binary and decrypt directly
                    $hexDecoded = hex2bin($responseBody);
                    if ($hexDecoded === false) {
                        throw new PaymentException('Failed to convert hex-encoded response to binary.');
                    }

                    Log::debug('Decrypting hex-encoded Hesabe response', [
                        'hex_length' => strlen($responseBody),
                        'binary_length' => strlen($hexDecoded),
                    ]);

                    $decrypted = $this->decryptRawPayload($hexDecoded);
                } else {
                    // Try as base64 directly
                    Log::debug('Attempting to decrypt as base64');
                    $decrypted = $this->decryptPayload($responseBody);
                }

                // If decryption succeeded, check for errors in decrypted data
                if ($decrypted && isset($decrypted['status']) && $decrypted['status'] === false) {
                    $errorMessage = $decrypted['message'] ?? 'Unknown error from Hesabe';
                    $errorCode = $decrypted['code'] ?? null;

                    Log::error('Hesabe checkout error (decrypted)', [
                        'code' => $errorCode,
                        'message' => $errorMessage,
                        'full_response' => $decrypted,
                    ]);

                    $errorDetails = $errorMessage;
                    if ($errorCode) {
                        $errorDetails .= " (Code: {$errorCode})";
                    }

                    if ($errorCode == 501) {
                        $errorDetails .= '. Please verify your HESABE_MERCHANT_CODE, HESABE_ACCESS_CODE, HESABE_SECRET_KEY, and HESABE_IV_KEY in your .env file.';
                    }

                    throw new PaymentException('Unable to initiate Hesabe checkout. Error: ' . $errorDetails);
                }
            } catch (PaymentException $e) {
                // If decryption fails, log detailed error and throw with helpful message
                Log::error('Failed to decrypt Hesabe response', [
                    'error' => $e->getMessage(),
                    'response_length' => strlen($responseBody),
                    'response_preview' => substr($responseBody, 0, 100),
                    'is_hex' => ctype_xdigit($responseBody),
                    'response_type' => ctype_xdigit($responseBody) ? 'hex' : (base64_encode(base64_decode($responseBody, true)) === $responseBody ? 'base64' : 'unknown'),
                ]);

                // Check if it's a decryption error vs other errors
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'decrypt') !== false || strpos($errorMsg, 'decode') !== false) {
                    throw new PaymentException('Unable to initiate Hesabe checkout. Failed to decrypt response: ' . $errorMsg . '. Please verify your HESABE_SECRET_KEY and HESABE_IV_KEY are correct and match your Hesabe account. The response appears to be encrypted but cannot be decrypted with the provided keys.');
                }

                // Don't expose raw encrypted data in error message
                throw new PaymentException('Unable to initiate Hesabe checkout. ' . $errorMsg);
            } catch (\Throwable $e) {
                // Catch any other unexpected errors
                Log::error('Unexpected error decrypting Hesabe response', [
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                    'response_length' => strlen($responseBody),
                ]);
                throw new PaymentException('Unable to initiate Hesabe checkout. Unexpected error during decryption: ' . $e->getMessage());
            }
        }

        return [
            'raw' => $body ?? $responseBody,
            'data' => $decrypted,
        ];
    }

    /**
     * Initiate a hosted/redirect payment (KNET flow).
     * Posts to /payment endpoint and returns paymentToken + redirect URL.
     */
    public function initiatePayment(array $payload): array
    {
        $this->assertConfigured();

        if (empty($payload['responseUrl'])) {
            $payload['responseUrl'] = $this->returnUrl ?? url('/api/v1/payment/callback');
        }
        if (empty($payload['failureUrl'])) {
            $payload['failureUrl'] = $this->failureUrl ?? url('/api/v1/payment/callback/failure');
        }

        $encryptedPayload = $this->encryptPayload($payload);

        $requestPayload = [
            'merchantCode' => $this->merchantCode,
            'data'         => $encryptedPayload,
        ];

        Log::debug('Hesabe initiatePayment request', [
            'endpoint'    => $this->baseUrl . $this->paymentEndpoint,
            'reference'   => $payload['merchantOrderReferenceNumber'] ?? null,
            'amount'      => $payload['amount'] ?? null,
            'paymentType' => $payload['paymentType'] ?? null,
        ]);

        $response     = Http::withOptions(['verify' => false])
            ->acceptJson()
            ->withHeaders(['accessCode' => $this->accessCode])
            ->post($this->baseUrl . $this->paymentEndpoint, $requestPayload);

        $responseStatus = $response->status();
        $responseBody   = $response->body();

        Log::debug('Hesabe initiatePayment response', [
            'status_code'      => $responseStatus,
            'response_length'  => strlen($responseBody),
            'response_preview' => substr($responseBody, 0, 200),
        ]);

        if ($response->failed()) {
            // Try to decrypt an error response
            if (ctype_xdigit($responseBody)) {
                try {
                    $hexDecoded = hex2bin($responseBody);
                    if ($hexDecoded !== false) {
                        $decrypted = $this->decryptRawPayload($hexDecoded);
                        if (isset($decrypted['status']) && $decrypted['status'] === false) {
                            $msg  = $decrypted['message'] ?? 'Hesabe error';
                            $code = $decrypted['code'] ?? null;
                            throw new PaymentException('Unable to initiate KNET payment. ' . $msg . ($code ? " (Code: {$code})" : ''));
                        }
                    }
                } catch (PaymentException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    Log::warning('Could not decrypt Hesabe initiate error response: ' . $e->getMessage());
                }
            }
            throw new PaymentException('Unable to initiate KNET payment. HTTP Error: ' . $responseStatus);
        }

        // Parse response
        $body      = json_decode($responseBody, true);
        $decrypted = null;

        if (json_last_error() === JSON_ERROR_NONE && is_array($body)) {
            if (isset($body['status']) && $body['status'] === false) {
                $msg  = $body['message'] ?? 'Unknown Hesabe error';
                $code = $body['code'] ?? null;
                throw new PaymentException('Unable to initiate KNET payment. ' . $msg . ($code ? " (Code: {$code})" : ''));
            }
            if (!empty($body['data'])) {
                try {
                    $decrypted = $this->decryptPayload($body['data']);
                } catch (\Throwable $e) {
                    Log::warning('Failed to decrypt Hesabe initiatePayment data field: ' . $e->getMessage());
                }
            }
        } elseif (ctype_xdigit($responseBody)) {
            $hexDecoded = hex2bin($responseBody);
            if ($hexDecoded !== false) {
                $decrypted = $this->decryptRawPayload($hexDecoded);
            }
        } else {
            try {
                $decrypted = $this->decryptPayload($responseBody);
            } catch (\Throwable $e) {
                Log::warning('Failed to decrypt Hesabe initiatePayment response: ' . $e->getMessage());
            }
        }

        if ($decrypted && isset($decrypted['status']) && $decrypted['status'] === false) {
            $msg  = $decrypted['message'] ?? 'Payment initiation failed';
            $code = $decrypted['code'] ?? null;
            throw new PaymentException('Unable to initiate KNET payment. ' . $msg . ($code ? " (Code: {$code})" : ''));
        }

        Log::info('Hesabe initiatePayment success', [
            'has_token'    => !empty($decrypted['paymentToken'] ?? $decrypted['data']['paymentToken'] ?? null),
        ]);

        return [
            'raw'  => $body ?? $responseBody,
            'data' => $decrypted,
        ];
    }

    public function initiateHostedPayment(array $payload): array
    {
        $this->assertConfigured();

        $encryptedPayload = $this->encryptPayload($payload);

        $requestPayload = [
            'merchantCode' => $this->merchantCode,
            'data'         => $encryptedPayload,
        ];

        $endpoint = config('services.hesabe.payment_endpoint', '/payment');

        Log::debug('Hesabe initiateHostedPayment request', [
            'endpoint'  => $this->baseUrl . $endpoint,
            'reference' => $payload['merchantOrderReferenceNumber'] ?? null,
            'amount'    => $payload['amount'] ?? null,
        ]);

        $response = Http::withOptions(['verify' => false])
            ->acceptJson()
            ->withHeaders(['accessCode' => $this->accessCode])
            ->post($this->baseUrl . $endpoint, $requestPayload);

        $responseStatus = $response->status();
        $responseBody   = $response->body();

        Log::debug('Hesabe initiateHostedPayment response', [
            'status'  => $responseStatus,
            'is_hex'  => ctype_xdigit($responseBody),
            'preview' => substr($responseBody, 0, 100),
        ]);

        if ($response->failed()) {
            if (ctype_xdigit($responseBody)) {
                try {
                    $decrypted = $this->decryptRawPayload(hex2bin($responseBody));
                    throw new PaymentException('Hesabe error: ' . ($decrypted['message'] ?? 'Payment initiation failed'));
                } catch (PaymentException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    // fall through
                }
            }
            throw new PaymentException('Unable to initiate Hesabe payment. HTTP ' . $responseStatus);
        }

        $decrypted = $this->parseAndDecryptResponse($responseBody);

        // Hesabe response structure:
        // { status: true, token: "...", response: { data: "<encrypted>" } }
        // The outer `token` is the payment token for the redirect URL.
        // `response.data` is a nested encrypted blob with full payment details.

        // Try to decrypt response.data for extra payment details (best-effort)
        $innerData = [];
        if (!empty($decrypted['response']['data'])) {
            try {
                $innerData = $this->decryptCallbackData($decrypted['response']['data']);
                Log::debug('Hesabe initiateHostedPayment: inner response decrypted', ['keys' => array_keys($innerData)]);
            } catch (\Throwable $e) {
                Log::debug('Hesabe initiateHostedPayment: inner response decrypt skipped', ['reason' => $e->getMessage()]);
            }
        }

        // Resolve payment token — check all known field names Hesabe may use
        $paymentToken = $decrypted['paymentToken']
            ?? $decrypted['token']
            ?? $innerData['paymentToken']
            ?? $innerData['token']
            ?? null;

        if (empty($paymentToken)) {
            Log::error('Hesabe initiateHostedPayment: no token in response', [
                'decrypted'  => $decrypted,
                'inner_data' => $innerData,
            ]);
            throw new PaymentException('Hesabe did not return a payment token. Check credentials and request format.');
        }

        // Build the redirect URL — user opens this in WebView/browser to complete payment.
        // NOTE: $this->paymentEndpoint (/payment) is the user-facing GET page.
        //       $this->checkoutEndpoint (/checkout) is the backend API POST endpoint — do NOT send users there (405).
        $checkoutUrl = $this->baseUrl . $this->paymentEndpoint . '?data=' . urlencode($paymentToken);

        Log::info('Hesabe initiateHostedPayment: success', [
            'token'       => $paymentToken,
            'payment_url' => $checkoutUrl,
        ]);

        return [
            'payment_token' => $paymentToken,
            'payment_url'   => $checkoutUrl,
            'raw'           => $decrypted,
        ];
    }

    private function parseAndDecryptResponse(string $responseBody): array
    {
        $body = json_decode($responseBody, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($body)) {
            if (isset($body['status']) && $body['status'] === false) {
                throw new PaymentException($body['message'] ?? 'Hesabe returned an error');
            }
            if (!empty($body['data'])) {
                return $this->decryptPayload($body['data']);
            }
            return $body;
        }

        if (ctype_xdigit($responseBody)) {
            return $this->decryptRawPayload(hex2bin($responseBody));
        }

        return $this->decryptPayload($responseBody);
    }

    /**
     * Decrypt the raw `data` parameter received in Hesabe's callback POST.
     * Handles both hex-encoded and base64-encoded payloads.
     */
    public function decryptCallbackData(string $encryptedData): array
    {
        if (ctype_xdigit($encryptedData)) {
            $binary = hex2bin($encryptedData);
            if ($binary === false) {
                throw new PaymentException('Failed to hex-decode Hesabe callback data.');
            }
            return $this->decryptRawPayload($binary);
        }

        return $this->decryptPayload($encryptedData);
    }

    protected function encryptPayload(array $payload): string
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $cipher = 'AES-256-CBC';
        $secret = $this->normalizeKey($this->secretKey);
        $iv = $this->normalizeKey($this->ivKey);

        Log::debug('Encrypting Hesabe payload', [
            'payload_keys' => array_keys($payload),
            'json_length' => strlen($jsonPayload),
            'secret_length' => strlen($secret),
            'iv_length' => strlen($iv),
        ]);

        $encrypted = openssl_encrypt($jsonPayload, $cipher, $secret, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            $error = openssl_error_string();
            throw new PaymentException('Failed to encrypt Hesabe payload. OpenSSL error: ' . ($error ?: 'Unknown error'));
        }

        // Hesabe expects hex-encoded encrypted data (not base64)
        // Since they return hex-encoded responses, they likely expect hex-encoded requests too
        return bin2hex($encrypted);
    }

    protected function decryptPayload(string $payload): array
    {
        $cipher = 'AES-256-CBC';
        $secret = $this->normalizeKey($this->secretKey);
        $iv = $this->normalizeKey($this->ivKey);

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw new PaymentException('Failed to decode Hesabe response payload.');
        }

        return $this->decryptRawPayload($decoded);
    }

    protected function decryptRawPayload(string $encryptedData): array
    {
        $cipher = 'AES-256-CBC';
        $secret = $this->normalizeKey($this->secretKey);
        $iv = $this->normalizeKey($this->ivKey);

        Log::debug('Decrypting raw payload', [
            'data_length' => strlen($encryptedData),
            'secret_length' => strlen($secret),
            'iv_length' => strlen($iv),
        ]);

        $decrypted = openssl_decrypt($encryptedData, $cipher, $secret, OPENSSL_RAW_DATA, $iv);

        // If default decryption fails, try with NO_PADDING (Hesabe sometimes uses it)
        if ($decrypted === false) {
            $decrypted = openssl_decrypt($encryptedData, $cipher, $secret, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
            
            if ($decrypted !== false) {
                // Trim null bytes or other padding characters
                $decrypted = rtrim($decrypted, "\0..\x1F");
            }
        }

        if ($decrypted === false) {
            $error = openssl_error_string();
            Log::error('OpenSSL decryption failed', [
                'openssl_error' => $error,
                'data_length' => strlen($encryptedData),
                'full_hex' => bin2hex($encryptedData),
            ]);
            throw new PaymentException('Failed to decrypt Hesabe response payload. OpenSSL error: ' . ($error ?: 'Unknown error') . '. likely due to incorrect HESABE_SECRET_KEY or HESABE_IV_KEY.');
        }

        $decoded = json_decode($decrypted, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Decrypted data is not valid JSON', [
                'json_error' => json_last_error_msg(),
                'decrypted_preview' => substr($decrypted, 0, 200),
            ]);
        }

        return $decoded ?? [];
    }

    protected function normalizeKey(?string $key): string
    {
        if (! $key) {
            throw new PaymentException('Hesabe encryption keys are not configured.');
        }

        // If key is hex-encoded (all hex digits and even length), convert to binary
        if (ctype_xdigit($key) && strlen($key) % 2 === 0) {
            $decoded = hex2bin($key);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        // Return key as-is for plain text keys
        return $key;
    }

    protected function assertConfigured(): void
    {
        if (! $this->merchantCode || ! $this->accessCode || ! $this->secretKey || ! $this->ivKey) {
            throw new PaymentException('Hesabe service credentials are missing.');
        }
    }

    protected function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

