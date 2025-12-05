<?php

namespace App\Services;

use App\Models\Duration;
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
    /**
     * Create a user subscription along with its days and meals.
     *
     * @param  array  $payload
     * @param  array  $paymentContext
     * @return array
     */
    public function createSubscription(array $payload, array $paymentContext = []): array
    {
        return DB::transaction(function () use ($payload, $paymentContext) {
            $subscriptionPlan = SubcrptionPlan::findOrFail($payload['subcrption_plans_id']);
            $duration = Duration::findOrFail($payload['duration_id']);

            $startDate = Carbon::parse($payload['start_date']);
            $endDate = $startDate->copy()->addWeeks($duration->no_of_weeks);
            $price = $payload['price'] ?? $subscriptionPlan->price;
            $currency = $paymentContext['currency'] ?? ($payload['currency'] ?? null);

            $isPersonalized = $payload['is_personalized'] ?? false;
            $selectedDaysValue = $payload['selected_days'] ?? null;
            if (is_array($selectedDaysValue)) {
                $selectedDaysValue = implode(',', $selectedDaysValue);
            }

            $address = isset($payload['address']) && is_array($payload['address'])
                ? $this->createAddress($payload['user_id'], $payload['address'])
                : null;

            $userSubscription = UserSubcrption::create([
                'user_id' => $payload['user_id'],
                'user_address_id' => $address?->id,
                'subcrption_plans_id' => $payload['subcrption_plans_id'],
                'duration_id' => $payload['duration_id'],
                'selected_days' => $selectedDaysValue,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'price' => $price,
                'currency' => $currency,
                'payment' => $paymentContext['status'] ?? ($payload['payment'] ?? 'pending'),
                'payment_reference' => $paymentContext['reference'] ?? null,
                'payment_gateway' => $paymentContext['gateway'] ?? null,
                'card_last_four' => $paymentContext['card_last_four'] ?? null,
                'card_brand' => $paymentContext['card_brand'] ?? null,
                'payment_meta' => $paymentContext['meta'] ?? null,
                'status' => $payload['status'] ?? 'active',
                'is_personalized' => $isPersonalized,
                'protein' => $isPersonalized ? ($payload['protein'] ?? null) : null,
                'carbs' => $isPersonalized ? ($payload['carbs'] ?? null) : null,
            ]);

            [$subscriptionDays, $createdDays] = $this->createSubscriptionDays($userSubscription->id, $payload['selected_days'] ?? null);
            $subscriptionMeals = $this->createSubscriptionMeals($userSubscription->id, $payload['meals'] ?? [], $createdDays);

            return [
                'user_subscription' => $userSubscription->load(['address']),
                'subscription_days' => $subscriptionDays,
                'subscription_meals' => $subscriptionMeals,
            ];
        });
    }

    protected function createAddress(int $userId, array $addressData): UserAddress
    {
        $address = UserAddress::create([
            'user_id' => $userId,
            'first_name' => $addressData['first_name'],
            'last_name' => $addressData['last_name'] ?? null,
            'area' => $addressData['area'] ?? null,
            'block_number' => $addressData['block_number'] ?? null,
            'street' => $addressData['street'] ?? null,
            'house_building' => $addressData['house_building'] ?? null,
            'floor_apartment' => $addressData['floor_apartment'] ?? null,
            'phone_number' => $addressData['phone_number'] ?? null,
            'remarks' => $addressData['remarks'] ?? null,
            'category' => $addressData['category'] ?? 'home',
            'is_primary' => (bool) ($addressData['is_primary'] ?? false),
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
     * @param  int  $userSubscriptionId
     * @param  mixed  $selectedDays
     * @return array{0: \Illuminate\Support\Collection, 1: array}
     */
    protected function createSubscriptionDays(int $userSubscriptionId, $selectedDays): array
    {
        $subscriptionDays = collect();
        $createdDays = [];

        if (empty($selectedDays)) {
            return [$subscriptionDays, $createdDays];
        }

        $days = is_array($selectedDays) ? $selectedDays : explode(',', $selectedDays);

        foreach ($days as $day) {
            $normalizedDay = trim(strtolower($day));
            if (! array_key_exists($normalizedDay, SubscriptionDay::DAY_SELECT)) {
                continue;
            }

            $subscriptionDay = SubscriptionDay::create([
                'user_subcrptions_id' => $userSubscriptionId,
                'day' => $normalizedDay,
            ]);

            $subscriptionDays->push($subscriptionDay);
            $createdDays[$normalizedDay] = $subscriptionDay->id;
        }

        return [$subscriptionDays, $createdDays];
    }

    /**
     * @param  int  $userSubscriptionId
     * @param  array  $meals
     * @param  array  $createdDays
     * @return \Illuminate\Support\Collection
     */
    protected function createSubscriptionMeals(int $userSubscriptionId, array $meals, array &$createdDays): Collection
    {
        $subscriptionMeals = collect();

        if (empty($meals)) {
            return $subscriptionMeals;
        }

        foreach ($meals as $mealData) {
            if (! isset($mealData['day'], $mealData['meal_id'])) {
                continue;
            }

            $normalizedDay = trim(strtolower($mealData['day']));

            if (! isset($createdDays[$normalizedDay])) {
                $subscriptionDay = SubscriptionDay::firstOrCreate(
                    [
                        'user_subcrptions_id' => $userSubscriptionId,
                        'day' => $normalizedDay,
                    ]
                );

                $createdDays[$normalizedDay] = $subscriptionDay->id;
            }

            $subscriptionMeal = SubscriptionMeal::create([
                'subscription_days_id' => $createdDays[$normalizedDay],
                'meal_id' => $mealData['meal_id'],
                'type' => $mealData['type'] ?? null,
            ]);

            $subscriptionMeal->loadMissing(['meal', 'subscription_days']);
            $subscriptionMeals->push($subscriptionMeal);
        }

        return $subscriptionMeals;
    }
}

