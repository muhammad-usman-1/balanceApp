<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Requests\ProcessHesabePaymentRequest;
use App\Models\PaymentOrder;
use App\Models\Setting;
use App\Models\SubcrptionPlan;
use App\Models\User;
use App\Models\UserPaymentMethod;
use App\Models\UserSubcrption;
use App\Services\HesabePaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HesabePaymentController extends Controller
{
    public function __construct(
        private readonly HesabePaymentService $hesabePaymentService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    // =========================================================================
    // GET /v1/payment/kits — fetch available integration kits from Hesabe
    // =========================================================================
    public function reviewKits(): JsonResponse
    {
        try {
            $kits = $this->hesabePaymentService->reviewKits();

            return response()->json([
                'success' => true,
                'message' => 'Payment kits fetched successfully.',
                'data'    => $kits,
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        } catch (\Throwable $e) {
            Log::error('Hesabe reviewKits error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch payment methods.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // POST /v1/payment/checkout — cash or direct card payment
    // =========================================================================
    public function checkout(ProcessHesabePaymentRequest $request): JsonResponse
    {
        $paymentMethod = $request->payment_method; // cash | debit_card | credit_card

        // Check if this payment method is enabled in admin settings
        $settings = Setting::firstOrCreateDefault();
        if ($paymentMethod === 'cash' && ! $settings->payment_cash) {
            return response()->json([
                'success' => false,
                'message' => 'Cash on delivery is currently disabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (in_array($paymentMethod, ['credit_card', 'debit_card'], true) && ! $settings->payment_credit_card) {
            return response()->json([
                'success' => false,
                'message' => 'Card payments are currently disabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Log::info('CHECKOUT: request received', [
            'payment_method'  => $paymentMethod,
            'user_id'         => $request->user_id,
            'plan_id'         => $request->subcrption_plans_id,
            'area_id'         => $request->area_id,
            'start_date'      => $request->start_date,
            'selected_days'   => $request->selected_days,
            'is_personalized' => $request->is_personalized,
            'meals_count'     => count($request->meals ?? []),
            'has_address'     => ! empty($request->address),
        ]);

        return $paymentMethod === 'cash'
            ? $this->handleCashCheckout($request)
            : $this->handleCardCheckout($request, $paymentMethod);
    }

    // =========================================================================
    // POST /v1/payment/initiate — Hosted payment (KNET, credit_card, debit_card)
    // Returns a Hesabe payment URL; the app opens it in a WebView/browser.
    // =========================================================================
    public function initiateKnetPayment(InitiatePaymentRequest $request): JsonResponse
    {
        $paymentMethod = $request->payment_method;
        $settings      = Setting::firstOrCreateDefault();

        // Check the correct toggle per payment method
        if ($paymentMethod === 'knet' && ! $settings->payment_knet) {
            return response()->json([
                'success' => false,
                'message' => 'KNET payments are currently disabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (in_array($paymentMethod, ['credit_card', 'debit_card']) && ! $settings->payment_credit_card) {
            return response()->json([
                'success' => false,
                'message' => 'Card payments are currently disabled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $plan      = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            $user      = User::findOrFail($request->user_id);
            $amount    = $request->amount ?? $plan->price;
            $currency  = strtoupper($request->currency ?? 'KWD');

            $prefix         = strtoupper(str_replace('_', '', $paymentMethod)); // KNET / CREDITCARD / DEBITCARD
            $orderToken     = (string) Str::uuid();
            $orderReference = $prefix . '-' . now()->timestamp . '-' . strtoupper(Str::random(6));

            $subscriptionData = $request->safe()->except(['payment_method', 'amount', 'currency']);

            $paymentOrder = PaymentOrder::create([
                'order_token'            => $orderToken,
                'user_id'                => $request->user_id,
                'subscription_data'      => $subscriptionData,
                'amount'                 => $amount,
                'currency'               => $currency,
                'payment_method'         => $paymentMethod,
                'status'                 => 'pending',
                'hesabe_order_reference' => $orderReference,
            ]);

            $responseUrl = config('services.hesabe.return_url') ?: url('/api/v1/payment/callback');
            $failureUrl  = config('services.hesabe.failure_url') ?: url('/api/v1/payment/callback/failure');

            Log::info('HOSTED INITIATE: PaymentOrder created', [
                'payment_method' => $paymentMethod,
                'order_token'    => $orderToken,
                'reference'      => $orderReference,
                'amount'         => $amount,
                'user_id'        => $request->user_id,
                'response_url'   => $responseUrl,
                'failure_url'    => $failureUrl,
            ]);

            $hesabePayload = [
                'amount'                       => number_format((float) $amount, 3, '.', ''),
                'currencyCode'                 => $currency,
                'merchantOrderReferenceNumber' => $orderReference,
                'customerEmail'                => $user->email ?? '',
                'customerMobileNumber'         => $user->mobile,
                'responseUrl'                  => $responseUrl,
                'failureUrl'                   => $failureUrl,
                'paymentType'                  => '0',
                'version'                      => '2.0',
                'language'                     => 'en',
                'variable1'                    => $orderToken,
                'variable2'                    => $paymentMethod,
                'variable3'                    => '',
            ];

            $paymentResponse = $this->hesabePaymentService->initiateHostedPayment($hesabePayload);

            $paymentToken = $paymentResponse['payment_token'] ?? null;
            $paymentUrl   = $paymentResponse['payment_url'] ?? null;

            if (! $paymentToken || ! $paymentUrl) {
                Log::error('HOSTED INITIATE: no paymentToken in response', ['response' => $paymentResponse]);
                $paymentOrder->update(['status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initiate payment. Please try again.',
                ], Response::HTTP_BAD_GATEWAY);
            }

            $paymentOrder->update(['hesabe_payment_token' => $paymentToken]);

            Log::info('HOSTED INITIATE: success', [
                'payment_method' => $paymentMethod,
                'order_token'    => $orderToken,
                'payment_url'    => $paymentUrl,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated. Please complete payment via the provided URL.',
                'data'    => [
                    'order_token'    => $orderToken,
                    'payment_url'    => $paymentUrl,
                    'payment_method' => $paymentMethod,
                    'amount'         => $amount,
                    'currency'       => $currency,
                ],
            ]);

        } catch (PaymentException $e) {
            Log::error('HOSTED INITIATE: PaymentException', [
                'payment_method' => $paymentMethod ?? null,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);

        } catch (\Throwable $e) {
            Log::error('HOSTED INITIATE: FAILED', [
                'payment_method' => $paymentMethod ?? null,
                'error'          => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // POST|GET /v1/payment/callback — Hesabe posts here after KNET payment
    // Must always return HTTP 200 so Hesabe does not retry.
    // =========================================================================
    public function handleCallback(Request $request): \Illuminate\Http\Response
    {
        // Try all possible locations Hesabe may send the data parameter
        $encryptedData = $request->input('data')
            ?? $request->query('data')
            ?? null;

        // Fallback: parse raw body manually (some gateways send non-standard content-type)
        if (! $encryptedData) {
            $rawBody = $request->getContent();
            if ($rawBody) {
                parse_str($rawBody, $parsed);
                $encryptedData = $parsed['data'] ?? null;
            }
        }

        Log::info('KNET CALLBACK: received', [
            'method'        => $request->method(),
            'content_type'  => $request->header('Content-Type'),
            'has_data'      => ! empty($encryptedData),
            'data_preview'  => $encryptedData ? substr($encryptedData, 0, 80) : null,
            'all_inputs'    => array_keys($request->all()),
            'query_params'  => array_keys($request->query()),
            'raw_preview'   => substr($request->getContent(), 0, 200),
        ]);

        if (! $encryptedData) {
            Log::warning('KNET CALLBACK: no data received — Hesabe may not have sent callback');
            return response('OK', 200);
        }

        // Decrypt the callback payload
        // Hesabe wraps payment data in two layers:
        //   Outer: { status, token, response: { data: "<encrypted>" } }
        //   Inner: { resultCode, paymentId, merchantOrderReferenceNumber, variable1, amount, ... }
        try {
            $outerData = $this->hesabePaymentService->decryptCallbackData($encryptedData);
        } catch (\Throwable $e) {
            Log::error('KNET CALLBACK: outer decrypt failed', ['error' => $e->getMessage()]);
            return response('OK', 200);
        }

        // Resolve the actual payment fields from Hesabe's nested response structure.
        // Hesabe may return payment data in one of these locations (checked in order):
        //   1. $outerData['response']         — direct array with resultCode, paymentId, etc.
        //   2. $outerData['response']['data']  — another encrypted hex blob (decrypt again)
        //   3. $outerData                      — flat response (fallback)

        $response = $outerData['response'] ?? null;

        if (is_array($response) && isset($response['resultCode'])) {
            // Case 1: payment data is directly in response array
            $callbackData = $response;
            Log::info('KNET CALLBACK: used response array directly');

        } elseif (is_array($response) && ! empty($response['data'])) {
            // Case 2: payment data is encrypted inside response.data
            try {
                $innerData    = $this->hesabePaymentService->decryptCallbackData($response['data']);
                $callbackData = ! empty($innerData) ? $innerData : $outerData;
                Log::info('KNET CALLBACK: decrypted inner response.data', ['keys' => array_keys($innerData ?? [])]);
            } catch (\Throwable $e) {
                $callbackData = $outerData;
                Log::warning('KNET CALLBACK: inner decrypt failed', ['error' => $e->getMessage()]);
            }

        } elseif (is_string($response) && ctype_xdigit($response)) {
            // Case 3: response is itself an encrypted hex string
            try {
                $innerData    = $this->hesabePaymentService->decryptCallbackData($response);
                $callbackData = ! empty($innerData) ? $innerData : $outerData;
                Log::info('KNET CALLBACK: decrypted response hex string', ['keys' => array_keys($innerData ?? [])]);
            } catch (\Throwable $e) {
                $callbackData = $outerData;
                Log::warning('KNET CALLBACK: response string decrypt failed', ['error' => $e->getMessage()]);
            }

        } else {
            // Fallback
            $callbackData = $outerData;
        }

        Log::info('KNET CALLBACK: decrypted', [
            'resultCode'   => $callbackData['resultCode'] ?? null,
            'paymentId'    => $callbackData['paymentId'] ?? null,
            'reference'    => $callbackData['merchantOrderReferenceNumber'] ?? null,
            'variable1'    => $callbackData['variable1'] ?? null,
            'amount'       => $callbackData['amount'] ?? null,
            'response_type' => gettype($response),
            'response_keys' => is_array($response) ? array_keys($response) : 'n/a',
        ]);

        // Locate the pending order via variable1 (order_token) or merchantOrderReferenceNumber
        $orderToken     = $callbackData['variable1'] ?? null;
        $orderReference = $callbackData['merchantOrderReferenceNumber'] ?? null;

        $paymentOrder = $orderToken
            ? PaymentOrder::where('order_token', $orderToken)->first()
            : null;

        if (! $paymentOrder && $orderReference) {
            $paymentOrder = PaymentOrder::where('hesabe_order_reference', $orderReference)->first();
        }

        if (! $paymentOrder) {
            Log::error('KNET CALLBACK: order not found', [
                'order_token' => $orderToken,
                'reference'   => $orderReference,
            ]);
            return response('OK', 200);
        }

        // Idempotency guard — already processed
        if ($paymentOrder->isPaid()) {
            Log::info('KNET CALLBACK: already processed', ['order_token' => $paymentOrder->order_token]);
            return response('OK', 200);
        }

        $rawResultCode = $callbackData['resultCode'] ?? null;
        $isSuccess = $rawResultCode === 1
            || $rawResultCode === '1'
            || strtoupper((string) $rawResultCode) === 'CAPTURED';

        if (! $isSuccess) {
            $paymentOrder->update([
                'status'           => 'failed',
                'hesabe_response'  => $callbackData,
            ]);
            Log::warning('KNET CALLBACK: payment failed', [
                'result_code' => $rawResultCode,
                'order_token' => $paymentOrder->order_token,
            ]);
            return response('OK', 200);
        }

        // Payment approved — create the subscription
        try {
            $paymentId = $callbackData['paymentId']
                ?? $callbackData['orderReferenceNumber']
                ?? $paymentOrder->hesabe_order_reference;

            $subscriptionResult = $this->subscriptionService->createSubscription(
                $paymentOrder->subscription_data,
                [
                    'status'    => 'paid',
                    'reference' => $paymentId,
                    'gateway'   => 'hesabe_' . ($paymentOrder->payment_method ?? 'knet'),
                    'currency'  => $paymentOrder->currency,
                    'meta'      => $callbackData,
                ]
            );

            $paymentOrder->update([
                'status'          => 'paid',
                'hesabe_response' => $callbackData,
                'subscription_id' => $subscriptionResult['user_subscription']->id ?? null,
            ]);

            Log::info('KNET CALLBACK: subscription created', [
                'order_token'     => $paymentOrder->order_token,
                'subscription_id' => $subscriptionResult['user_subscription']->id ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('KNET CALLBACK: subscription creation failed', [
                'error'       => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'order_token' => $paymentOrder->order_token,
            ]);
            $paymentOrder->update([
                'status'          => 'failed',
                'hesabe_response' => $callbackData,
            ]);
        }

        return response('OK', 200);
    }

    // =========================================================================
    // GET /v1/payment/callback/failure — Hesabe redirects here on failure
    // =========================================================================
    public function handleFailureCallback(Request $request): \Illuminate\Http\Response
    {
        $encryptedData = $request->input('data') ?? $request->query('data');

        Log::info('KNET FAILURE CALLBACK: received', [
            'has_data' => ! empty($encryptedData),
        ]);

        if ($encryptedData) {
            try {
                $callbackData = $this->hesabePaymentService->decryptCallbackData($encryptedData);
                $orderToken   = $callbackData['variable1'] ?? null;

                if ($orderToken) {
                    PaymentOrder::where('order_token', $orderToken)
                        ->where('status', 'pending')
                        ->update([
                            'status'          => 'failed',
                            'hesabe_response' => $callbackData,
                        ]);

                    Log::info('KNET FAILURE CALLBACK: order marked failed', ['order_token' => $orderToken]);
                }
            } catch (\Throwable $e) {
                Log::error('KNET FAILURE CALLBACK: error', ['error' => $e->getMessage()]);
            }
        }

        return response('Payment failed', 200);
    }

    // =========================================================================
    // GET /v1/payment/status/{orderToken} — app polls to check payment result
    // =========================================================================
    public function checkPaymentStatus(string $orderToken): JsonResponse
    {
        $paymentOrder = PaymentOrder::where('order_token', $orderToken)->first();

        if (! $paymentOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Payment order not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'order_token'    => $orderToken,
            'status'         => $paymentOrder->status,
            'payment_method' => $paymentOrder->payment_method,
            'amount'         => $paymentOrder->amount,
            'currency'       => $paymentOrder->currency,
        ];

        if ($paymentOrder->isPaid() && $paymentOrder->subscription_id) {
            $subscription = UserSubcrption::with([
                'subcrption_plans',
                'subscription_days',
                'subscription_meals',
            ])->find($paymentOrder->subscription_id);

            $data['subscription'] = $subscription;
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // =========================================================================
    // Cash — no gateway, subscription created with payment status "pending"
    // =========================================================================
    private function handleCashCheckout(ProcessHesabePaymentRequest $request): JsonResponse
    {
        try {
            $plan = SubcrptionPlan::findOrFail($request->subcrption_plans_id);

            $amount    = $request->amount ?? $plan->price;
            $currency  = strtoupper($request->currency ?? 'KWD');
            $reference = 'CASH-' . now()->timestamp . '-' . strtoupper(Str::random(6));

            $subscriptionPayload = $request->safe()->except([
                'payment_method', 'amount', 'currency',
                'card_holder_name', 'card_number',
                'card_expiry_month', 'card_expiry_year',
                'card_cvv', 'save_card',
            ]);

            $subscriptionPayload['price']    = $amount;
            $subscriptionPayload['currency'] = $currency;

            $subscriptionResult = $this->subscriptionService->createSubscription(
                $subscriptionPayload,
                [
                    'status'    => 'pending',
                    'reference' => $reference,
                    'gateway'   => 'cash',
                    'currency'  => $currency,
                ]
            );

            Log::info('CHECKOUT [cash]: subscription created', [
                'subscription_id' => $subscriptionResult['user_subscription']->id ?? null,
                'reference'       => $reference,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully. Cash will be collected on delivery.',
                'data'    => [
                    'payment' => [
                        'method'    => 'cash',
                        'reference' => $reference,
                        'amount'    => $amount,
                        'currency'  => $currency,
                        'status'    => 'pending',
                    ],
                    'subscription' => $subscriptionResult,
                ],
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            Log::error('CHECKOUT [cash]: FAILED', [
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'user_id' => $request->user_id ?? null,
                'plan_id' => $request->subcrption_plans_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // Debit / Credit Card — direct card processing through Hesabe /checkout
    // =========================================================================
    private function handleCardCheckout(ProcessHesabePaymentRequest $request, string $paymentMethod): JsonResponse
    {
        try {
            $plan = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            $user = User::findOrFail($request->user_id);

            $amount    = $request->amount ?? $plan->price;
            $currency  = strtoupper($request->currency ?? 'KWD');
            $reference = 'SUB-' . now()->timestamp . '-' . strtoupper(Str::random(6));

            $cardNumber = preg_replace('/\D/', '', $request->card_number ?? '');
            $lastFour   = $cardNumber ? substr($cardNumber, -4) : null;
            $cardBrand  = $this->detectCardBrand($cardNumber);

            $paymentPayload = [
                'amount'                       => number_format((float) $amount, 3, '.', ''),
                'currencyCode'                 => $currency,
                'merchantOrderReferenceNumber' => $reference,
                'customerEmail'                => $user->email ?? '',
                'customerMobileNumber'         => $user->mobile,
                'cardHolderName'               => $request->card_holder_name,
                'cardNumber'                   => $request->card_number,
                'cardExpiryMonth'              => $request->card_expiry_month,
                'cardExpiryYear'               => $request->card_expiry_year,
                'cardSecurityCode'             => $request->card_cvv,
                'language'                     => 'en',
                'paymentType'                  => '0',
                'version'                      => '2.0',
            ];

            Log::info('CHECKOUT [card]: sending to Hesabe', [
                'reference'      => $reference,
                'amount'         => $paymentPayload['amount'],
                'payment_method' => $paymentMethod,
                'card_brand'     => $cardBrand,
                'card_last_four' => $lastFour,
            ]);

            $paymentResponse = $this->hesabePaymentService->checkout($paymentPayload);
            $paymentData     = $paymentResponse['data'] ?? [];
            $paymentStatus   = $paymentData['status'] ?? null;

            $successStatuses = ['success', 'paid', 'captured', 'true', '1'];
            if ($paymentStatus !== true && ! in_array(strtolower((string) $paymentStatus), $successStatuses, true)) {
                throw new PaymentException($paymentData['message'] ?? 'Payment was not successful. Please try again.');
            }

            $transactionReference = $paymentData['transactionReference']
                ?? $paymentData['paymentId']
                ?? $paymentData['token']
                ?? $reference;

            if ($request->boolean('save_card') && $cardNumber) {
                UserPaymentMethod::updateOrCreate(
                    [
                        'user_id'           => $request->user_id,
                        'card_last_four'    => $lastFour,
                        'card_expiry_month' => $request->card_expiry_month,
                        'card_expiry_year'  => $request->card_expiry_year,
                    ],
                    [
                        'card_holder_name'      => $request->card_holder_name,
                        'card_brand'            => $cardBrand,
                        'card_number_encrypted' => Crypt::encryptString($cardNumber),
                        'hesabe_token'          => $paymentData['token'] ?? null,
                        'meta'                  => $paymentResponse,
                    ]
                );
            }

            $subscriptionPayload = $request->safe()->except([
                'payment_method', 'card_holder_name', 'card_number',
                'card_expiry_month', 'card_expiry_year',
                'card_cvv', 'save_card', 'amount', 'currency',
            ]);

            $subscriptionPayload['price']    = $amount;
            $subscriptionPayload['currency'] = $currency;

            $subscriptionResult = $this->subscriptionService->createSubscription(
                $subscriptionPayload,
                [
                    'status'         => 'paid',
                    'reference'      => $transactionReference,
                    'gateway'        => 'hesabe',
                    'currency'       => $currency,
                    'card_last_four' => $lastFour,
                    'card_brand'     => $cardBrand,
                    'meta'           => $paymentResponse,
                ]
            );

            Log::info('CHECKOUT [card]: subscription created', [
                'subscription_id' => $subscriptionResult['user_subscription']->id ?? null,
                'reference'       => $transactionReference,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful and subscription created.',
                'data'    => [
                    'payment' => [
                        'method'         => $paymentMethod,
                        'reference'      => $transactionReference,
                        'gateway'        => 'hesabe',
                        'amount'         => $amount,
                        'currency'       => $currency,
                        'card_brand'     => $cardBrand,
                        'card_last_four' => $lastFour,
                        'status'         => 'paid',
                    ],
                    'subscription' => $subscriptionResult,
                ],
            ], Response::HTTP_CREATED);

        } catch (PaymentException $e) {
            Log::error('CHECKOUT [card]: PaymentException', [
                'error'          => $e->getMessage(),
                'user_id'        => $request->user_id ?? null,
                'payment_method' => $paymentMethod,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);

        } catch (\Throwable $e) {
            Log::error('CHECKOUT [card]: FAILED', [
                'error'          => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'user_id'        => $request->user_id ?? null,
                'payment_method' => $paymentMethod,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function detectCardBrand(?string $cardNumber): ?string
    {
        if (! $cardNumber) {
            return null;
        }

        return match (true) {
            (bool) preg_match('/^4[0-9]{12}(?:[0-9]{3,6})?$/', $cardNumber)                            => 'visa',
            (bool) preg_match('/^5[1-5][0-9]{14}$/', $cardNumber)                                      => 'mastercard',
            (bool) preg_match('/^2(?:2[2-9][1-9]|[3-6]\d{2}|7(?:[01]\d|20))[0-9]{12}$/', $cardNumber) => 'mastercard',
            (bool) preg_match('/^3[47][0-9]{13}$/', $cardNumber)                                       => 'amex',
            (bool) preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cardNumber)                         => 'discover',
            default => 'unknown',
        };
    }
}
