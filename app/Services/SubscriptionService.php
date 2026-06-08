<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Meal;
use App\Models\MealRestriction;
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
            ]);

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

        // Count how many times each meal_id appears in the request
        $mealCounts = [];
        foreach ($meals as $m) {
            $id = $m['meal_id'] ?? null;
            if ($id) {
                $mealCounts[$id] = ($mealCounts[$id] ?? 0) + 1;
            }
        }

        // Validate against restrictions
        $restrictions = MealRestriction::whereIn('meal_id', array_keys($mealCounts))
            ->pluck('weekly_limit', 'meal_id');

        foreach ($mealCounts as $mealId => $count) {
            if (isset($restrictions[$mealId]) && $count > $restrictions[$mealId]) {
                $mealTitle = Meal::find($mealId)?->title ?? "Meal #{$mealId}";
                throw new \InvalidArgumentException(
                    "\"{$mealTitle}\" can only be added {$restrictions[$mealId]} time(s) per week, but was selected {$count} time(s)."
                );
            }
        }

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

            $subscriptionMeal->loadMissing(['meal', 'subscription_days']);
            $subscriptionMeals->push($subscriptionMeal);
        }

        return $subscriptionMeals;
    }
}
