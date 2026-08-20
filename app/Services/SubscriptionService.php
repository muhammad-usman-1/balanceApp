<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Coupon;
use App\Models\Meal;
use App\Models\MealGroup;
use App\Models\ProteinOption;
use App\Models\SubcrptionPlan;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use App\Models\UserAddress;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function createSubscription(array $payload, array $paymentContext = []): array
    {
        return DB::transaction(function () use ($payload, $paymentContext) {
            $plan = SubcrptionPlan::findOrFail($payload['subcrption_plans_id']);

            // If the user already has an active subscription, queue the new one to start after it ends
            $latestActive = UserSubcrption::where('user_id', $payload['user_id'])
                ->where('status', 'active')
                ->whereDate('end_date', '>=', now()->format('Y-m-d'))
                ->latest('end_date')
                ->first();

            $queuedAfterId = $latestActive?->id;

            if ($latestActive) {
                // Start the day after the current plan ends — ignore user's requested start_date
                $startDate = Carbon::parse($latestActive->getRawOriginal('end_date'))->addDay();
            } else {
                $startDate = Carbon::parse($payload['start_date']);
            }

            // end_date is calculated from the plan's no_of_weeks — no external duration table needed
            $endDate   = $startDate->copy()->addWeeks((int) $plan->no_of_weeks);

            $basePrice = (float) $plan->price;

            // Add protein surcharge when this is a personalized plan with a protein selection
            $proteinSurcharge = 0.0;
            $isPersonalized   = $payload['is_personalized'] ?? false;
            if ($isPersonalized && ! empty($payload['protein'])) {
                $proteinOption = ProteinOption::where('protein_grams', (int) $payload['protein'])
                    ->where('is_active', true)
                    ->first();

                if ($proteinOption) {
                    $selectedDaysCount = is_array($payload['selected_days'] ?? [])
                        ? count($payload['selected_days'])
                        : count(array_filter(explode(',', $payload['selected_days'] ?? '')));

                    $totalMeals       = (int) $plan->meal_count * $selectedDaysCount;
                    $proteinSurcharge = (float) $proteinOption->extra_price_per_meal * $totalMeals;
                }
            }

            $price    = round($basePrice + $proteinSurcharge, 3);
            $currency = $paymentContext['currency'] ?? ($payload['currency'] ?? 'KWD');

            // Apply coupon discount if provided
            $couponCode     = ! empty($payload['coupon_code']) ? strtoupper(trim($payload['coupon_code'])) : null;
            $couponId       = null;
            $discountAmount = 0.0;
            $coupon         = null;

            if ($couponCode) {
                $coupon = Coupon::where('coupon_code', $couponCode)->first();
                if ($coupon) {
                    $check = $coupon->canUserUse($payload['user_id']);
                    if ($check['can_use']) {
                        if ($coupon->type === 'percentage') {
                            $discountAmount = round($price * ($coupon->value / 100), 3);
                        } else {
                            $discountAmount = min((float) $coupon->value, $price);
                        }
                        $couponId = $coupon->id;
                        $price    = round(max(0, $price - $discountAmount), 3);
                    }
                }
            }

            $selectedDaysRaw = $payload['selected_days'] ?? [];
            $selectedDaysStr = is_array($selectedDaysRaw)
                ? implode(',', $selectedDaysRaw)
                : $selectedDaysRaw;

            $address = isset($payload['address']) && is_array($payload['address'])
                ? $this->createAddress($payload['user_id'], $payload['address'])
                : null;

            // Auto-resolve branch from the selected area — user never picks branch directly
            $areaId   = $payload['area_id'] ?? null;
            $branchId = null;
            if ($areaId) {
                $branch   = Area::find($areaId)?->branches()->where('status', 'active')->first();
                $branchId = $branch?->id;
            }

            $userSubscription = UserSubcrption::create([
                'user_id'             => $payload['user_id'],
                'user_address_id'     => $address?->id,
                'area_id'             => $areaId,
                'branch_id'           => $branchId,
                'subcrption_plans_id' => $payload['subcrption_plans_id'],
                'selected_days'       => $selectedDaysStr,
                'start_date'          => $startDate->format('Y-m-d'),
                'end_date'            => $endDate->format('Y-m-d'),
                'price'               => $price,
                'currency'            => $currency,
                'payment'             => $paymentContext['status'] ?? ($payload['payment'] ?? 'pending'),
                'payment_reference'   => $paymentContext['reference'] ?? null,
                'payment_gateway'     => $paymentContext['gateway'] ?? null,
                'card_last_four'      => $paymentContext['card_last_four'] ?? null,
                'card_brand'          => $paymentContext['card_brand'] ?? null,
                'payment_meta'        => $paymentContext['meta'] ?? null,
                'status'                       => $queuedAfterId ? 'queued' : 'active',
                'queued_after_subscription_id' => $queuedAfterId,
                'auto_renew'                   => true,
                'is_personalized'              => $isPersonalized,
                'protein'             => $isPersonalized ? ($payload['protein'] ?? null) : null,
                'carbs'               => $isPersonalized ? ($payload['carbs'] ?? null) : null,
                'coupon_code'         => $couponId ? $couponCode : null,
                'coupon_id'           => $couponId,
                'discount_amount'     => $couponId ? $discountAmount : null,
            ]);

            // Record coupon usage after subscription is persisted
            if ($coupon && $couponId) {
                $coupon->recordUsage(
                    $payload['user_id'],
                    $discountAmount,
                    $basePrice + $proteinSurcharge,
                    'Subscription #' . $userSubscription->id
                );
            }

            // Create one SubscriptionDay record per selected day (weekly pattern)
            [$subscriptionDays, $createdDays] = $this->createSubscriptionDays(
                $userSubscription->id,
                $selectedDaysRaw
            );

            // Assign meals per day — same meal repeats every week automatically
            $subscriptionMeals = $this->createSubscriptionMeals(
                $payload['meals'] ?? [],
                $createdDays
            );

            return [
                'user_subscription' => $userSubscription->load(['subcrption_plans', 'address']),
                'subscription_days' => $subscriptionDays,
                'subscription_meals' => $subscriptionMeals,
            ];
        });
    }

    protected function createAddress(int $userId, array $addressData): UserAddress
    {
        $address = UserAddress::create([
            'user_id'                 => $userId,
            'first_name'              => $addressData['first_name'],
            'last_name'               => $addressData['last_name'] ?? null,
            'area'                    => $addressData['area'] ?? null,
            'block_number'            => $addressData['block_number'] ?? null,
            'street'                  => $addressData['street'] ?? null,
            'house_building'          => $addressData['house_building'] ?? null,
            'floor_apartment'         => $addressData['floor_apartment'] ?? null,
            'phone_number'            => $addressData['phone_number'] ?? null,
            'remarks'                 => $addressData['remarks'] ?? null,
            'delivery_notes'          => $addressData['delivery_notes'] ?? null,
            'category'                => $addressData['category'] ?? 'home',
            'is_primary'              => (bool) ($addressData['is_primary'] ?? false),
            'preferred_delivery_slot' => $addressData['preferred_delivery_slot'] ?? null,
        ]);

        if ($address->is_primary) {
            UserAddress::where('user_id', $userId)
                ->where('id', '!=', $address->id)
                ->update(['is_primary' => false]);
        }

        return $address;
    }

    /**
     * Create ONE SubscriptionDay record per selected day name.
     * This is the weekly pattern — it repeats automatically every week
     * for the full duration of the plan.
     *
     * e.g. Mon/Wed/Fri → 3 records. Same meals delivered Mon/Wed/Fri every week for 4 weeks.
     *
     * @return array{0: Collection, 1: array<string, int>}
     */
    protected function createSubscriptionDays(int $userSubscriptionId, array|string $selectedDays): array
    {
        $subscriptionDays = collect();
        $createdDays      = []; // ['monday' => subscription_day_id, ...]

        if (empty($selectedDays)) {
            return [$subscriptionDays, $createdDays];
        }

        $days = is_array($selectedDays) ? $selectedDays : explode(',', $selectedDays);
        $days = array_values(array_filter(array_map(fn ($d) => trim(strtolower($d)), $days)));

        foreach ($days as $dayName) {
            if (! array_key_exists($dayName, SubscriptionDay::DAY_SELECT)) {
                continue;
            }

            $subscriptionDay = SubscriptionDay::create([
                'user_subcrptions_id' => $userSubscriptionId,
                'day'                 => $dayName,
            ]);

            $subscriptionDays->push($subscriptionDay);
            $createdDays[$dayName] = $subscriptionDay->id;
        }

        return [$subscriptionDays, $createdDays];
    }

    /**
     * Assign meals to specific days by day name.
     * Each meal is set once and repeats every week.
     *
     * @param array<int, array<string, mixed>> $meals  e.g. [["day"=>"monday","meal_id"=>10,"type"=>"is meal"]]
     * @param array<string, int>               $createdDays
     * @return Collection
     */
    protected function createSubscriptionMeals(array $meals, array &$createdDays): Collection
    {
        $subscriptionMeals = collect();

        if (empty($meals)) {
            return $subscriptionMeals;
        }

        $this->validateMealGroupLimits($meals);

        foreach ($meals as $mealData) {
            if (! isset($mealData['day'], $mealData['meal_id'])) {
                continue;
            }

            $normalizedDay = trim(strtolower($mealData['day']));

            if (! isset($createdDays[$normalizedDay])) {
                continue;
            }

            $subscriptionMeal = SubscriptionMeal::create([
                'subscription_days_id' => $createdDays[$normalizedDay],
                'meal_id'              => $mealData['meal_id'],
                'type'                 => $mealData['type'] ?? null,
            ]);

            // Persist the customer's extra choices picked at checkout (if any).
            $this->attachSelectedExtras(
                $subscriptionMeal,
                (int) $mealData['meal_id'],
                (array) ($mealData['extra_ingredient_ids'] ?? [])
            );

            $subscriptionMeal->loadMissing(['meal', 'subscription_days']);
            $subscriptionMeals->push($subscriptionMeal);
        }

        return $subscriptionMeals;
    }

    /**
     * Validate and persist a customer's extra choices for a subscription meal
     * created at checkout. Throws if an option isn't offered by the meal or a
     * per-meal max_select cap is exceeded (keeps checkout consistent).
     *
     * @param array<int, int|string> $ingredientIds meal_extra_ingredients ids
     */
    protected function attachSelectedExtras(SubscriptionMeal $subscriptionMeal, int $mealId, array $ingredientIds): void
    {
        $ingredientIds = array_values(array_unique(array_filter(
            $ingredientIds,
            fn ($v) => $v !== null && $v !== ''
        )));

        if (empty($ingredientIds)) {
            return;
        }

        $meal = Meal::with(['mealExtras', 'availableIngredients.mealExtra'])->find($mealId);
        if (! $meal) {
            return;
        }

        $available = $meal->availableIngredients->keyBy('id');

        foreach ($ingredientIds as $id) {
            if (! $available->has($id)) {
                throw new \InvalidArgumentException('One or more selected options are not available for the chosen meal.');
            }
        }

        $mealExtras = $meal->mealExtras->keyBy('id');
        $byExtra    = collect($ingredientIds)->groupBy(fn ($id) => $available[$id]->meal_extra_id);

        foreach ($byExtra as $iids) {
            $extra = $available[$iids->first()]->mealExtra;
            if (! $extra) {
                continue;
            }

            $pivot   = $mealExtras->get($extra->id);
            $allowed = $extra->selection_type === 'single'
                ? 1
                : (($pivot && $pivot->pivot->max_select) ? (int) $pivot->pivot->max_select : PHP_INT_MAX);

            if ($iids->count() > $allowed) {
                throw new \InvalidArgumentException("You can select at most {$allowed} option(s) for \"{$extra->name}\".");
            }
        }

        $subscriptionMeal->selectedIngredients()->sync($ingredientIds);
    }

    /**
     * Validate a set of meal picks against meal-group weekly limits, without
     * persisting anything. Shared by checkout (createSubscriptionMeals) and
     * the standalone validate-pick endpoint used while building a new plan.
     *
     * The limit is shared across every meal in a group, not per individual
     * meal — e.g. a group of 10 meals with limit 2 allows any combination of
     * 2 meals from that group per week, total, not 2 of each meal.
     *
     * @param array<int, array<string, mixed>> $meals e.g. [["day"=>"monday","meal_id"=>10,"type"=>"is meal"]]
     * @throws \InvalidArgumentException if any group's weekly limit is exceeded
     */
    public function validateMealGroupLimits(array $meals): void
    {
        if (empty($meals)) {
            return;
        }

        // Count how many times each meal_id appears in the request
        $mealCounts = [];
        foreach ($meals as $m) {
            $id = $m['meal_id'] ?? null;
            if ($id) {
                $mealCounts[$id] = ($mealCounts[$id] ?? 0) + 1;
            }
        }

        if (empty($mealCounts)) {
            return;
        }

        $mealsById = Meal::whereIn('id', array_keys($mealCounts))->get(['id', 'title', 'meal_group_id'])->keyBy('id');

        $groupCounts = [];
        foreach ($mealCounts as $mealId => $count) {
            $groupId = $mealsById->get($mealId)?->meal_group_id;
            if ($groupId) {
                $groupCounts[$groupId] = ($groupCounts[$groupId] ?? 0) + $count;
            }
        }

        if (empty($groupCounts)) {
            return;
        }

        $groups = MealGroup::whereIn('id', array_keys($groupCounts))->get()->keyBy('id');

        foreach ($groupCounts as $groupId => $count) {
            $group = $groups->get($groupId);
            if ($group && $count > $group->weekly_limit) {
                throw new \InvalidArgumentException(
                    "\"{$group->name}\" allows a maximum of {$group->weekly_limit} meal(s) per week, but {$count} were selected."
                );
            }
        }
    }
}
