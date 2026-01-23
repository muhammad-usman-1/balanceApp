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
                'message' => 'Kits fetched successfully.',
                'data' => $kits,
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        } catch (\Throwable $e) {
            Log::error('Hesabe review kits error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch review kits.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkout(ProcessHesabePaymentRequest $request): JsonResponse
    {
        try {
            $plan = SubcrptionPlan::findOrFail($request->subcrption_plans_id);
            $user = User::findOrFail($request->user_id);
            $amount = $request->amount ?? $plan->price;
            $currency = strtoupper($request->currency ?? 'KWD');
            $reference = 'SUB-' . now()->timestamp . '-' . strtoupper(Str::random(4));

            $paymentPayload = [
                'amount' => number_format($amount, 3, '.', ''),
                'currencyCode' => $currency,
                'merchantOrderReferenceNumber' => $reference,
                'customerEmail' => $user->email,
                'customerMobileNumber' => $user->mobile,
                'cardHolderName' => $request->card_holder_name,
                'cardNumber' => $request->card_number,
                'cardExpiryMonth' => $request->card_expiry_month,
                'cardExpiryYear' => $request->card_expiry_year,
                'cardSecurityCode' => $request->card_cvv,
                'language' => 'en',
                'paymentType' => '0', // 0 = Indirect, 1 = KNET, 2 = MPGS
                'version' => '2.0', // API version
            ];

            $paymentResponse = $this->hesabePaymentService->checkout($paymentPayload);
            $paymentData = $paymentResponse['data'] ?? [];
            
            $paymentStatus = $paymentData['status'] ?? null;

            if ($paymentStatus !== true && ! in_array(strtolower((string)$paymentStatus), ['success', 'paid', 'captured', 'true', '1'], true)) {
                throw new PaymentException($paymentData['message'] ?? 'Payment failed with Hesabe.');
            }

            $transactionReference = $paymentData['transactionReference'] ?? $paymentData['paymentId'] ?? $paymentData['token'] ?? $reference;

            $cardNumber = preg_replace('/\D/', '', $request->card_number);
            $lastFour = $cardNumber ? substr($cardNumber, -4) : null;
            $cardBrand = $this->detectCardBrand($cardNumber);

            if ($request->boolean('save_card') && $cardNumber) {
                UserPaymentMethod::updateOrCreate(
                    [
                        'user_id' => $request->user_id,
                        'card_last_four' => $lastFour,
                        'card_expiry_month' => $request->card_expiry_month,
                        'card_expiry_year' => $request->card_expiry_year,
                    ],
                    [
                        'card_holder_name' => $request->card_holder_name,
                        'card_brand' => $cardBrand,
                        'card_number_encrypted' => Crypt::encryptString($cardNumber),
                        'hesabe_token' => $paymentData['token'] ?? null,
                        'card_expiry_month' => $request->card_expiry_month,
                        'card_expiry_year' => $request->card_expiry_year,
                        'meta' => $paymentResponse,
                    ]
                );
            }

            $subscriptionPayload = $request->safe()->except([
                'card_holder_name',
                'card_number',
                'card_expiry_month',
                'card_expiry_year',
                'card_cvv',
                'save_card',
                'amount',
                'currency',
            ]);

            $subscriptionPayload['price'] = $amount;
            $subscriptionPayload['currency'] = $currency;
            $subscriptionPayload['payment'] = 'paid';

            $subscriptionResult = $this->subscriptionService->createSubscription($subscriptionPayload, [
                'status' => 'paid',
                'reference' => $transactionReference,
                'gateway' => 'hesabe',
                'currency' => $currency,
                'card_last_four' => $lastFour,
                'card_brand' => $cardBrand,
                'meta' => $paymentResponse,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment completed and subscription created successfully.',
                'data' => [
                    'payment' => $paymentResponse,
                    'subscription' => $subscriptionResult,
                ],
            ], Response::HTTP_CREATED);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        } catch (\Throwable $e) {
            Log::error('Hesabe checkout error: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function detectCardBrand(?string $cardNumber): ?string
    {
        if (! $cardNumber) {
            return null;
        }

        return match (true) {
            preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $cardNumber) => 'visa',
            preg_match('/^(5[1-5][0-9]{14})$/', $cardNumber) => 'mastercard',
            preg_match('/^3[47][0-9]{13}$/', $cardNumber) => 'amex',
            preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cardNumber) => 'discover',
            default => 'unknown',
        };
    }
}

