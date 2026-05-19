<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessHesabePaymentRequest;
use App\Models\SubcrptionPlan;
use App\Models\User;
use App\Models\UserPaymentMethod;
use App\Services\HesabePaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
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

    public function checkout(ProcessHesabePaymentRequest $request): JsonResponse
    {
        $paymentMethod = $request->payment_method; // cash | debit_card | credit_card

        return $paymentMethod === 'cash'
            ? $this->handleCashCheckout($request)
            : $this->handleCardCheckout($request, $paymentMethod);
    }

    // -------------------------------------------------------------------------
    // Cash — no gateway, subscription is created with payment status "pending"
    // -------------------------------------------------------------------------
    private function handleCashCheckout(ProcessHesabePaymentRequest $request): JsonResponse
    {
        try {
            $plan     = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            $amount   = $request->amount ?? $plan->price;
            $currency = strtoupper($request->currency ?? 'KWD');
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
                $subscriptionPayload->toArray(),
                [
                    'status'    => 'pending',   // cash is collected on delivery
                    'reference' => $reference,
                    'gateway'   => 'cash',
                    'currency'  => $currency,
                ]
            );

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
            Log::error('Cash checkout error: ' . $e->getMessage(), [
                'user_id' => $request->user_id ?? null,
                'plan_id' => $request->subcrption_plans_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -------------------------------------------------------------------------
    // Debit / Credit Card — processes through Hesabe gateway
    // -------------------------------------------------------------------------
    private function handleCardCheckout(ProcessHesabePaymentRequest $request, string $paymentMethod): JsonResponse
    {
        try {
            $plan     = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            $user     = User::findOrFail($request->user_id);
            $amount   = $request->amount ?? $plan->price;
            $currency = strtoupper($request->currency ?? 'KWD');
            $reference = 'SUB-' . now()->timestamp . '-' . strtoupper(Str::random(6));

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

            $cardNumber = preg_replace('/\D/', '', $request->card_number);
            $lastFour   = $cardNumber ? substr($cardNumber, -4) : null;
            $cardBrand  = $this->detectCardBrand($cardNumber);

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
                $subscriptionPayload->toArray(),
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        } catch (\Throwable $e) {
            Log::error('Card checkout error: ' . $e->getMessage(), [
                'user_id'        => $request->user_id ?? null,
                'plan_id'        => $request->subcrption_plans_id ?? null,
                'payment_method' => $request->payment_method ?? null,
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
