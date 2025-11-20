<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionCheckoutRequest;
use App\Models\UserSubcrption;
use App\Models\SubcrptionPlan;
use App\Models\Duration;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionCheckoutApiController extends Controller
{
    /**
     * Create user subscription and store data in all 3 tables:
     * 1. user_subcrptions - Main subscription record
     * 2. subscription_days - Days for this user subscription
     * 3. subscription_meals - Meals for each day (linked to subscription_days)
     *
     * @param StoreSubscriptionCheckoutRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreSubscriptionCheckoutRequest $request)
    {
        // Force JSON response
        $request->headers->set('Accept', 'application/json');

        DB::beginTransaction();

        try {
            // Validate that the subscription plan exists and is active
            $subscriptionPlan = SubcrptionPlan::findOrFail($request->subcrption_plans_id);

            // Validate duration exists
            $duration = Duration::findOrFail($request->duration_id);

            // Calculate end date based on start date and duration
            $startDate = Carbon::parse($request->start_date);
            $endDate = $startDate->copy()->addWeeks($duration->no_of_weeks);

            // Get price from plan if not provided
            $price = $request->price ?? $subscriptionPlan->price;

            // Create user subscription (Table 1: user_subcrptions)
            $userSubscription = UserSubcrption::create([
                'user_id' => $request->user_id,
                'subcrption_plans_id' => $request->subcrption_plans_id,
                'duration_id' => $request->duration_id,
                'selected_days' => $request->selected_days,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'price' => $price,
                'payment' => $request->payment ?? 'pending',
                'status' => $request->status ?? 'active',
            ]);

            // Handle subscription days (Table 2: subscription_days)
            // Create days based on selected_days - linked to user_subcrptions
            $subscriptionDays = collect([]);
            $createdDays = [];

            if ($request->selected_days) {
                $days = is_array($request->selected_days)
                    ? $request->selected_days
                    : explode(',', $request->selected_days);

                foreach ($days as $day) {
                    $day = trim(strtolower($day));
                    if (in_array($day, array_keys(SubscriptionDay::DAY_SELECT))) {
                        // Create subscription day linked to this user subscription
                        $subscriptionDay = SubscriptionDay::create([
                            'user_subcrptions_id' => $userSubscription->id,
                            'day' => $day,
                        ]);

                        $subscriptionDays->push($subscriptionDay);
                        $createdDays[$day] = $subscriptionDay->id;
                    }
                }
            }

            // Handle subscription meals for those days (Table 3: subscription_meals)
            // Create meals using subscription_days_id
            $subscriptionMeals = collect([]);

            if ($request->has('meals') && is_array($request->meals) && !empty($request->meals)) {
                // Create meals from request data
                foreach ($request->meals as $mealData) {
                    $day = trim(strtolower($mealData['day']));

                    // Get the subscription day ID for this day
                    if (isset($createdDays[$day])) {
                        $subscriptionDayId = $createdDays[$day];
                    } else {
                        // Try to find existing subscription day for this user subscription
                        $subscriptionDay = SubscriptionDay::where('user_subcrptions_id', $userSubscription->id)
                            ->where('day', $day)
                            ->first();

                        if ($subscriptionDay) {
                            $subscriptionDayId = $subscriptionDay->id;
                            $createdDays[$day] = $subscriptionDayId;
                        } else {
                            // Create subscription day if it doesn't exist
                            $subscriptionDay = SubscriptionDay::create([
                                'user_subcrptions_id' => $userSubscription->id,
                                'day' => $day,
                            ]);
                            $subscriptionDayId = $subscriptionDay->id;
                            $createdDays[$day] = $subscriptionDayId;
                            $subscriptionDays->push($subscriptionDay);
                        }
                    }

                    // Create subscription meal using subscription_days_id
                    $subscriptionMeal = SubscriptionMeal::create([
                        'subscription_days_id' => $subscriptionDayId,
                        'meal_id' => $mealData['meal_id'],
                        'type' => $mealData['type'] ?? null,
                    ]);

                    $subscriptionMeal->load(['meal', 'subscription_days']);
                    $subscriptionMeals->push($subscriptionMeal);
                }
            } else {
                // If no meals provided, fetch existing meals for the subscription days
                if ($subscriptionDays->isNotEmpty()) {
                    $subscriptionDayIds = $subscriptionDays->pluck('id')->toArray();
                    $subscriptionMeals = SubscriptionMeal::whereIn('subscription_days_id', $subscriptionDayIds)
                        ->with(['meal', 'subscription_days'])
                        ->get();
                }
            }

            DB::commit();

            // Prepare response data
            $response = [
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'user_subscription' => [
                        'id' => $userSubscription->id,
                        'user_id' => $userSubscription->user_id,
                        'subscription_plan_id' => $userSubscription->subcrption_plans_id,
                        'duration_id' => $userSubscription->duration_id,
                        'selected_days' => $userSubscription->selected_days,
                        'start_date' => $userSubscription->start_date,
                        'end_date' => $userSubscription->end_date,
                        'price' => $userSubscription->price,
                        'payment' => $userSubscription->payment,
                        'status' => $userSubscription->status,
                        'created_at' => $userSubscription->created_at,
                    ],
                    'subscription_days' => $subscriptionDays->map(function ($day) {
                        return [
                            'id' => $day->id,
                            'user_subcrptions_id' => $day->user_subcrptions_id,
                            'day' => $day->day,
                        ];
                    }),
                    'subscription_meals' => $subscriptionMeals->map(function ($meal) {
                        return [
                            'id' => $meal->id,
                            'subscription_days_id' => $meal->subscription_days_id,
                            'meal_id' => $meal->meal_id,
                            'type' => $meal->type,
                            'day' => $meal->subscription_days->day ?? null,
                        ];
                    }),
                ],
            ];

            return response()->json($response, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription checkout error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

