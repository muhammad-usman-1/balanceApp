<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\PaymentOrder;
use App\Models\Setting;
use App\Models\SubcrptionPlan;
use App\Models\SubscriptionDay;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UserSubcrption;
use App\Services\HesabePaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionRenewalApiController extends Controller
{
    public function __construct(private readonly HesabePaymentService $hesabePaymentService)
    {
    }

    /**
     * GET /api/v1/my-subscriptions/{id}/renewal
     *
     * Returns renewal status for a given active subscription:
     * - whether auto-renew is on
     * - the queued renewal record (if already created by auto-renew job or manual purchase)
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with([
                'queuedSubscription.subcrption_plans',
                'queuedSubscription.address',
                'queuedSubscription.subscription_days.subscription_meals.meal',
                'queuedSubscription.subscription_days.subscription_meals.selectedIngredients.mealExtra',
            ])
            ->first();

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND);
        }

        $queued = $subscription->queuedSubscription;

        return response()->json([
            'success' => true,
            'data'    => [
                'subscription_id'     => $subscription->id,
                'auto_renew'          => (bool) $subscription->auto_renew,
                'renewal_notified_at' => $subscription->renewal_notified_at,
                'renewal'             => $queued ? $this->formatRenewal($queued) : null,
            ],
        ]);
    }

    /**
     * PUT /api/v1/my-subscriptions/{id}/renewal-days
     *
     * Change the delivery days on the queued renewal (unlimited while it's
     * still unpaid — nothing has started yet). Days that stay selected keep
     * their picked meals; dropped days are removed; new days start empty.
     */
    public function updateDays(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'days'   => ['required', 'array', 'min:1'],
            'days.*' => ['string', Rule::in(array_keys(SubscriptionDay::DAY_SELECT))],
        ]);

        [$error, $queued] = $this->findEditableRenewal($id, $request);
        if ($error) {
            return $error;
        }

        $plan = $queued->subcrption_plans;
        $newDays = collect($request->input('days'))->map(fn ($d) => strtolower(trim($d)))->unique()->values();

        if ($plan && $plan->min_days && $newDays->count() < $plan->min_days) {
            return response()->json(['success' => false, 'message' => "This plan requires at least {$plan->min_days} day(s) per week."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($plan && $plan->max_days && $newDays->count() > $plan->max_days) {
            return response()->json(['success' => false, 'message' => "This plan allows at most {$plan->max_days} day(s) per week."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $oldDays = $queued->selected_days
            ? collect(explode(',', $queued->selected_days))->map(fn ($d) => strtolower(trim($d)))->values()
            : collect();

        $removed = $oldDays->diff($newDays)->values();
        $added   = $newDays->diff($oldDays)->values();

        DB::transaction(function () use ($queued, $removed, $added, $newDays) {
            if ($removed->isNotEmpty()) {
                SubscriptionDay::where('user_subcrptions_id', $queued->id)
                    ->whereIn('day', $removed->all())
                    ->delete();
            }
            foreach ($added as $day) {
                SubscriptionDay::firstOrCreate(['user_subcrptions_id' => $queued->id, 'day' => $day]);
            }
            $queued->selected_days = $newDays->implode(',');
            $queued->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Renewal delivery days updated successfully.',
            'data'    => $this->formatRenewal($queued->fresh(['subcrption_plans', 'address', 'subscription_days.subscription_meals.meal'])),
        ]);
    }

    /**
     * PUT /api/v1/my-subscriptions/{id}/renewal-address
     *
     * Point the queued renewal at a different saved address (and area, if the
     * new address is in a different area). The address itself is managed via
     * the existing /api/v1/addresses endpoints — this just selects one.
     */
    public function updateAddress(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'user_address_id' => ['required', 'integer'],
            'area_id'         => ['nullable', 'integer', 'exists:areas,id'],
        ]);

        [$error, $queued] = $this->findEditableRenewal($id, $request);
        if ($error) {
            return $error;
        }

        $address = UserAddress::where('id', $request->user_address_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $address) {
            return response()->json(['success' => false, 'message' => 'Address not found.'], Response::HTTP_NOT_FOUND);
        }

        $queued->user_address_id = $address->id;

        if ($request->filled('area_id')) {
            $branch = Area::find($request->area_id)?->branches()->where('status', 'active')->first();
            $queued->area_id   = $request->area_id;
            $queued->branch_id = $branch?->id;
        }

        $queued->save();

        return response()->json([
            'success' => true,
            'message' => 'Renewal address updated successfully.',
            'data'    => $this->formatRenewal($queued->fresh(['subcrption_plans', 'address'])),
        ]);
    }

    /**
     * PUT /api/v1/my-subscriptions/{id}/renewal-start-date
     *
     * Let the customer push their renewal's start date later than the default
     * "day after the current plan ends". Cannot start before that, since the
     * current plan is still running until then. end_date is recomputed from
     * the plan's duration. Activation (subscriptions:activate-queued) now
     * respects this stored date instead of forcing "today".
     */
    public function updateStartDate(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
        ]);

        [$error, $queued, $subscription] = $this->findEditableRenewal($id, $request);
        if ($error) {
            return $error;
        }

        $earliestStart = Carbon::parse($subscription->getRawOriginal('end_date'))->addDay();
        $newStart      = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();

        if ($newStart->lt($earliestStart)) {
            return response()->json([
                'success' => false,
                'message' => "Renewal cannot start before {$earliestStart->toDateString()} — your current plan is running until then.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $plan   = $queued->subcrption_plans;
        $newEnd = $newStart->copy()->addWeeks((int) $plan->no_of_weeks);

        $queued->start_date = $newStart->format('Y-m-d');
        $queued->end_date   = $newEnd->format('Y-m-d');
        $queued->save();

        return response()->json([
            'success' => true,
            'message' => 'Renewal start date updated successfully.',
            'data'    => $this->formatRenewal($queued->fresh(['subcrption_plans', 'address'])),
        ]);
    }

    /**
     * Loads the caller's queued renewal (via the source active subscription id)
     * and guards the common edit preconditions: exists, not already paid.
     * Returns [errorResponse|null, $queued, $subscription].
     */
    private function findEditableRenewal(int $id, Request $request): array
    {
        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with('queuedSubscription.subcrption_plans')
            ->first();

        if (! $subscription) {
            return [response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND), null, null];
        }

        $queued = $subscription->queuedSubscription;
        if (! $queued) {
            return [response()->json(['success' => false, 'message' => 'No pending renewal found for this subscription.'], Response::HTTP_NOT_FOUND), null, null];
        }

        if ($queued->renewal_confirmed_at !== null) {
            return [response()->json(['success' => false, 'message' => 'Cannot change the renewal — payment has already been confirmed.'], Response::HTTP_UNPROCESSABLE_ENTITY), null, null];
        }

        return [null, $queued, $subscription];
    }

    private function formatRenewal(UserSubcrption $queued): array
    {
        return [
            'id'             => $queued->id,
            'plan'           => $queued->subcrption_plans,
            'address'        => $queued->address,
            'selected_days'  => $queued->selected_days,
            'start_date'     => $queued->start_date,
            'end_date'       => $queued->end_date,
            'price'          => $queued->price,
            'currency'       => $queued->currency,
            'payment'        => $queued->payment,
            'payment_gateway'=> $queued->payment_gateway,
            'confirmed_at'   => $queued->renewal_confirmed_at,
            'status'         => $queued->status,
            'schedule'       => $queued->relationLoaded('subscription_days')
                ? $queued->subscription_days->map(fn ($day) => [
                    'subscription_day_id' => $day->id,
                    'day'                 => $day->day,
                    'meals'               => $day->subscription_meals->map(fn ($m) => [
                        'id'      => $m->id,
                        'meal_id' => $m->meal_id,
                        'type'    => $m->type,
                        'meal'    => $m->meal ? [
                            'id'    => $m->meal->id,
                            'title' => $m->meal->title,
                        ] : null,
                    ])->values(),
                ])->values()
                : null,
        ];
    }

    /**
     * POST /api/v1/my-subscriptions/{id}/cancel-renewal
     *
     * Turns off auto-renew for the subscription and deletes any unpaid queued renewal.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with('queuedSubscription')
            ->first();

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND);
        }

        $subscription->auto_renew = false;
        $subscription->save();

        $queued = $subscription->queuedSubscription;
        if ($queued && $queued->payment === 'pending' && $queued->status === 'queued') {
            $queued->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto-renewal cancelled. Your plan will not renew after it ends.',
        ]);
    }

    /**
     * PUT /api/v1/my-subscriptions/{id}/renewal-plan
     *
     * Change the plan on the queued renewal subscription.
     * The app calls this when the user picks a different plan from the renewal page.
     */
    public function changePlan(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'subcrption_plans_id' => ['required', 'integer', 'exists:subcrption_plans,id'],
        ]);

        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with('queuedSubscription')
            ->first();

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND);
        }

        $queued = $subscription->queuedSubscription;
        if (! $queued) {
            return response()->json([
                'success' => false,
                'message' => 'No pending renewal found for this subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($queued->renewal_confirmed_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change plan — payment has already been confirmed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newPlan = SubcrptionPlan::where('id', $request->subcrption_plans_id)
            ->where('is_active', true)
            ->first();

        if (! $newPlan) {
            return response()->json([
                'success' => false,
                'message' => 'The selected plan is not currently available.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newStart = Carbon::parse($queued->getRawOriginal('start_date'));
        $newEnd   = $newStart->copy()->addWeeks((int) $newPlan->no_of_weeks);

        $queued->subcrption_plans_id = $newPlan->id;
        $queued->price               = $newPlan->price;
        $queued->end_date            = $newEnd->format('Y-m-d');
        $queued->save();

        return response()->json([
            'success' => true,
            'message' => 'Renewal plan updated successfully.',
            'data'    => [
                'renewal_id' => $queued->id,
                'plan'       => $newPlan,
                'start_date' => $queued->start_date,
                'end_date'   => $queued->end_date,
                'price'      => $queued->price,
            ],
        ]);
    }

    /**
     * POST /api/v1/my-subscriptions/{id}/renewal/pay-cash
     *
     * Confirms Cash on Delivery for the renewal. Payment stays "pending" —
     * cash is collected on delivery, same as a first-time cash checkout.
     */
    public function payCash(int $id, Request $request): JsonResponse
    {
        [$error, $queued] = $this->findEditableRenewal($id, $request);
        if ($error) {
            return $error;
        }

        $settings = Setting::firstOrCreateDefault();
        if (! $settings->payment_cash) {
            return response()->json(['success' => false, 'message' => 'Cash on delivery is currently disabled.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $queued->payment_gateway     = 'cash';
        $queued->payment_reference   = 'CASH-' . now()->timestamp . '-' . strtoupper(Str::random(6));
        $queued->payment             = 'pending';
        $queued->renewal_confirmed_at = now();
        $queued->save();

        return response()->json([
            'success' => true,
            'message' => 'Renewal confirmed. Cash will be collected on delivery.',
            'data'    => $this->formatRenewal($queued->fresh(['subcrption_plans', 'address'])),
        ]);
    }

    /**
     * POST /api/v1/my-subscriptions/{id}/renewal/pay-card
     *
     * Direct debit/credit card charge for the renewal via Hesabe, same as
     * the first-time checkout card flow — but updates the existing queued
     * renewal instead of creating a new subscription.
     */
    public function payCard(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'payment_method'    => ['required', 'in:debit_card,credit_card'],
            'card_holder_name'  => ['required', 'string'],
            'card_number'       => ['required', 'string'],
            'card_expiry_month' => ['required', 'string'],
            'card_expiry_year'  => ['required', 'string'],
            'card_cvv'          => ['required', 'string'],
            'save_card'         => ['nullable', 'boolean'],
        ]);

        [$error, $queued] = $this->findEditableRenewal($id, $request);
        if ($error) {
            return $error;
        }

        $settings = Setting::firstOrCreateDefault();
        if (! $settings->payment_credit_card) {
            return response()->json(['success' => false, 'message' => 'Card payments are currently disabled.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user       = $request->user();
        $reference  = 'RENEW-' . now()->timestamp . '-' . strtoupper(Str::random(6));
        $cardNumber = preg_replace('/\D/', '', $request->card_number ?? '');
        $lastFour   = $cardNumber ? substr($cardNumber, -4) : null;
        $cardBrand  = $this->detectCardBrand($cardNumber);

        $paymentPayload = [
            'amount'                       => number_format((float) $queued->price, 3, '.', ''),
            'currencyCode'                 => strtoupper($queued->currency ?? 'KWD'),
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

        try {
            $paymentResponse = $this->hesabePaymentService->checkout($paymentPayload);
        } catch (PaymentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_GATEWAY);
        }

        $paymentData      = $paymentResponse['data'] ?? [];
        $paymentStatus    = $paymentData['status'] ?? null;
        $successStatuses  = ['success', 'paid', 'captured', 'true', '1'];

        if ($paymentStatus !== true && ! in_array(strtolower((string) $paymentStatus), $successStatuses, true)) {
            return response()->json([
                'success' => false,
                'message' => $paymentData['message'] ?? 'Payment was not successful. Please try again.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        $transactionReference = $paymentData['transactionReference']
            ?? $paymentData['paymentId']
            ?? $paymentData['token']
            ?? $reference;

        if ($request->boolean('save_card') && $cardNumber) {
            UserPaymentMethod::updateOrCreate(
                [
                    'user_id'           => $user->id,
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

        $queued->payment              = 'paid';
        $queued->payment_reference    = $transactionReference;
        $queued->payment_gateway      = 'hesabe';
        $queued->card_last_four       = $lastFour;
        $queued->card_brand           = $cardBrand;
        $queued->renewal_confirmed_at = now();
        $queued->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment successful. Your renewal is confirmed.',
            'data'    => $this->formatRenewal($queued->fresh(['subcrption_plans', 'address'])),
        ]);
    }

    /**
     * POST /api/v1/my-subscriptions/{id}/renewal/pay-knet
     *
     * Hosted KNET (or hosted card) payment for the renewal — same pattern as
     * the first-time checkout hosted flow. Returns a Hesabe payment URL; the
     * app opens it, then polls GET /payment/status/{order_token} like usual.
     * On success, HesabePaymentController::handleCallback marks THIS renewal
     * paid (via PaymentOrder.is_renewal) instead of creating a new one.
     */
    public function payKnet(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:knet,debit_card,credit_card'],
        ]);

        [$error, $queued] = $this->findEditableRenewal($id, $request);
        if ($error) {
            return $error;
        }

        $paymentMethod = $request->payment_method;
        $settings      = Setting::firstOrCreateDefault();

        if ($paymentMethod === 'knet' && ! $settings->payment_knet) {
            return response()->json(['success' => false, 'message' => 'KNET payments are currently disabled.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (in_array($paymentMethod, ['credit_card', 'debit_card'], true) && ! $settings->payment_credit_card) {
            return response()->json(['success' => false, 'message' => 'Card payments are currently disabled.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user           = $request->user();
        $amount         = $queued->price;
        $currency       = strtoupper($queued->currency ?? 'KWD');
        $prefix         = strtoupper(str_replace('_', '', $paymentMethod));
        $orderToken     = (string) Str::uuid();
        $orderReference = $prefix . '-RENEW-' . now()->timestamp . '-' . strtoupper(Str::random(6));

        $paymentOrder = PaymentOrder::create([
            'order_token'            => $orderToken,
            'user_id'                => $user->id,
            'subscription_data'      => [],
            'amount'                 => $amount,
            'currency'               => $currency,
            'payment_method'         => $paymentMethod,
            'status'                 => 'pending',
            'hesabe_order_reference' => $orderReference,
            'subscription_id'        => $queued->id,
            'is_renewal'             => true,
        ]);

        $responseUrl = config('services.hesabe.return_url') ?: url('/api/v1/payment/callback');
        $failureUrl  = config('services.hesabe.failure_url') ?: url('/api/v1/payment/callback/failure');

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

        try {
            $paymentResponse = $this->hesabePaymentService->initiateHostedPayment($hesabePayload);
        } catch (PaymentException $e) {
            $paymentOrder->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_GATEWAY);
        }

        $paymentToken = $paymentResponse['payment_token'] ?? null;
        $paymentUrl   = $paymentResponse['payment_url'] ?? null;

        if (! $paymentToken || ! $paymentUrl) {
            $paymentOrder->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'Failed to initiate payment. Please try again.'], Response::HTTP_BAD_GATEWAY);
        }

        $paymentOrder->update(['hesabe_payment_token' => $paymentToken]);

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
