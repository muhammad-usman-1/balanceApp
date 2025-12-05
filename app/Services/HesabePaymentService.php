<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;

class HesabePaymentService
{
    private string $baseUrl;
    private ?string $merchantCode;
    private ?string $accessCode;
    private ?string $secretKey;
    private ?string $ivKey;
    private string $checkoutEndpoint;
    private string $reviewKitsEndpoint;
    private ?string $returnUrl;
    private ?string $failureUrl;

    public function __construct()
    {
        $config = config('services.hesabe', []);

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://sandbox.hesabe.com', '/');
        $this->merchantCode = $config['merchant_code'] ?? null;
        $this->accessCode = $config['access_code'] ?? null;
        $this->secretKey = $config['secret_key'] ?? null;
        $this->ivKey = $config['iv_key'] ?? null;
        $this->checkoutEndpoint = $config['checkout_endpoint'] ?? '/api/encryption/checkout';
        $this->reviewKitsEndpoint = $config['review_kits_endpoint'] ?? '/api/integration-kits/review';
        $this->returnUrl = $config['return_url'] ?? null;
        $this->failureUrl = $config['failure_url'] ?? null;
    }

    public function reviewKits(): array
    {
        $this->assertConfigured();

        $response = Http::acceptJson()
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

        if ($this->returnUrl && empty($payload['responseUrl'])) {
            $payload['responseUrl'] = $this->returnUrl;
        }

        if ($this->failureUrl && empty($payload['failureUrl'])) {
            $payload['failureUrl'] = $this->failureUrl;
        }

        $encryptedPayload = $this->encryptPayload($payload);

        $response = Http::acceptJson()
            ->post($this->baseUrl . $this->checkoutEndpoint, [
                'merchantCode' => $this->merchantCode,
                'accessCode' => $this->accessCode,
                'data' => $encryptedPayload,
            ]);

        if ($response->failed()) {
            throw new PaymentException('Unable to initiate Hesabe checkout.');
        }

        $body = $response->json();
        $decrypted = isset($body['data']) ? $this->decryptPayload($body['data']) : null;

        return [
            'raw' => $body,
            'data' => $decrypted,
        ];
    }

    protected function encryptPayload(array $payload): string
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $cipher = 'AES-256-CBC';
        $secret = $this->normalizeKey($this->secretKey);
        $iv = $this->normalizeKey($this->ivKey);

        $encrypted = openssl_encrypt($jsonPayload, $cipher, $secret, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new PaymentException('Failed to encrypt Hesabe payload.');
        }

        return base64_encode($encrypted);
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

        $decrypted = openssl_decrypt($decoded, $cipher, $secret, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new PaymentException('Failed to decrypt Hesabe response payload.');
        }

        return json_decode($decrypted, true) ?? [];
    }

    protected function normalizeKey(?string $key): string
    {
        if (! $key) {
            throw new PaymentException('Hesabe encryption keys are not configured.');
        }

        return ctype_xdigit($key) && strlen($key) % 2 === 0 ? hex2bin($key) : $key;
    }

    protected function assertConfigured(): void
    {
        if (! $this->merchantCode || ! $this->accessCode || ! $this->secretKey || ! $this->ivKey) {
            throw new PaymentException('Hesabe service credentials are missing.');
        }
    }
}

