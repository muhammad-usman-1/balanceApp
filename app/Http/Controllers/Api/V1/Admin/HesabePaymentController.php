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

        Log::info('CHECKOUT: request received', [
            'payment_method'      => $paymentMethod,
            'user_id'             => $request->user_id,
            'plan_id'             => $request->subcrption_plans_id,
            'area_id'             => $request->area_id,
            'start_date'          => $request->start_date,
            'selected_days'       => $request->selected_days,
            'is_personalized'     => $request->is_personalized,
            'protein'             => $request->protein,
            'carbs'               => $request->carbs,
            'meals_count'         => count($request->meals ?? []),
            'has_address'         => ! empty($request->address),
            // card fields intentionally omitted — never log raw card data
        ]);

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
            Log::info('CHECKOUT [cash]: looking up plan', ['plan_id' => $request->subcrption_plans_id]);
            $plan = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            Log::info('CHECKOUT [cash]: plan found', [
                'plan_title'  => $plan->title,
                'plan_price'  => $plan->price,
                'meal_count'  => $plan->meal_count,
                'no_of_weeks' => $plan->no_of_weeks,
                'min_days'    => $plan->min_days,
                'max_days'    => $plan->max_days,
                'is_active'   => $plan->is_active,
            ]);

            $amount    = $request->amount ?? $plan->price;
            $currency  = strtoupper($request->currency ?? 'KWD');
            $reference = 'CASH-' . now()->timestamp . '-' . strtoupper(Str::random(6));

            Log::info('CHECKOUT [cash]: building subscription payload', [
                'amount'    => $amount,
                'currency'  => $currency,
                'reference' => $reference,
            ]);

            $subscriptionPayload = $request->safe()->except([
                'payment_method', 'amount', 'currency',
                'card_holder_name', 'card_number',
                'card_expiry_month', 'card_expiry_year',
                'card_cvv', 'save_card',
            ]);

            $subscriptionPayload['price']    = $amount;
            $subscriptionPayload['currency'] = $currency;

            Log::info('CHECKOUT [cash]: calling SubscriptionService::createSubscription');

            $subscriptionResult = $this->subscriptionService->createSubscription(
                $subscriptionPayload,
                [
                    'status'    => 'pending',
                    'reference' => $reference,
                    'gateway'   => 'cash',
                    'currency'  => $currency,
                ]
            );

            Log::info('CHECKOUT [cash]: subscription created successfully', [
                'subscription_id' => $subscriptionResult['user_subscription']->id ?? null,
                'price'           => $subscriptionResult['user_subscription']->price ?? null,
                'days_created'    => count($subscriptionResult['subscription_days'] ?? []),
                'meals_created'   => count($subscriptionResult['subscription_meals'] ?? []),
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
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
                'user_id'   => $request->user_id ?? null,
                'plan_id'   => $request->subcrption_plans_id ?? null,
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
            Log::info('CHECKOUT [card]: looking up plan and user', [
                'plan_id' => $request->subcrption_plans_id,
                'user_id' => $request->user_id,
            ]);

            $plan = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            $user = User::findOrFail($request->user_id);

            Log::info('CHECKOUT [card]: plan and user found', [
                'plan_title'  => $plan->title,
                'plan_price'  => $plan->price,
                'meal_count'  => $plan->meal_count,
                'no_of_weeks' => $plan->no_of_weeks,
                'user_email'  => $user->email,
                'user_mobile' => $user->mobile,
            ]);

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

            Log::info('CHECKOUT [card]: sending to Hesabe gateway', [
                'reference'        => $reference,
                'amount'           => $paymentPayload['amount'],
                'currency'         => $currency,
                'payment_method'   => $paymentMethod,
                'card_brand'       => $cardBrand,
                'card_last_four'   => $lastFour,
                'card_expiry'      => $request->card_expiry_month . '/' . $request->card_expiry_year,
                // card_number and card_cvv intentionally omitted
            ]);

            $paymentResponse = $this->hesabePaymentService->checkout($paymentPayload);
            $paymentData     = $paymentResponse['data'] ?? [];
            $paymentStatus   = $paymentData['status'] ?? null;

            Log::info('CHECKOUT [card]: Hesabe gateway response received', [
                'raw_status'   => $paymentStatus,
                'payment_data' => array_diff_key($paymentData, array_flip(['cardNumber', 'cardSecurityCode'])),
            ]);

            $successStatuses = ['success', 'paid', 'captured', 'true', '1'];
            if ($paymentStatus !== true && ! in_array(strtolower((string) $paymentStatus), $successStatuses, true)) {
                Log::warning('CHECKOUT [card]: gateway returned non-success status', [
                    'status'          => $paymentStatus,
                    'gateway_message' => $paymentData['message'] ?? null,
                    'reference'       => $reference,
                    'user_id'         => $request->user_id,
                    'plan_id'         => $request->subcrption_plans_id,
                ]);
                throw new PaymentException($paymentData['message'] ?? 'Payment was not successful. Please try again.');
            }

            $transactionReference = $paymentData['transactionReference']
                ?? $paymentData['paymentId']
                ?? $paymentData['token']
                ?? $reference;

            Log::info('CHECKOUT [card]: payment approved', [
                'transaction_reference' => $transactionReference,
                'card_brand'            => $cardBrand,
                'card_last_four'        => $lastFour,
                'save_card'             => $request->boolean('save_card'),
            ]);

            if ($request->boolean('save_card') && $cardNumber) {
                Log::info('CHECKOUT [card]: saving card for user', ['user_id' => $request->user_id]);
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

            Log::info('CHECKOUT [card]: calling SubscriptionService::createSubscription');

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

            Log::info('CHECKOUT [card]: subscription created successfully', [
                'subscription_id' => $subscriptionResult['user_subscription']->id ?? null,
                'price'           => $subscriptionResult['user_subscription']->price ?? null,
                'days_created'    => count($subscriptionResult['subscription_days'] ?? []),
                'meals_created'   => count($subscriptionResult['subscription_meals'] ?? []),
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
                'plan_id'        => $request->subcrption_plans_id ?? null,
                'payment_method' => $paymentMethod,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);

        } catch (\Throwable $e) {
            Log::error('CHECKOUT [card]: FAILED', [
                'error'          => $e->getMessage(),
                'exception'      => get_class($e),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'trace'          => $e->getTraceAsString(),
                'user_id'        => $request->user_id ?? null,
                'plan_id'        => $request->subcrption_plans_id ?? null,
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
