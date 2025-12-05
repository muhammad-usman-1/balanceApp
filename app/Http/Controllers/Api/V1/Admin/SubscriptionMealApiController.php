<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSubscriptionMealApiRequest;
use App\Models\Meal;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use App\Models\UserSubcrption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMealApiController extends Controller
{
    /**
     * Update meal for a specific day in user's active subscription
     * 
     * This endpoint allows updating or creating a meal for any day in the user's active subscription.
     * If subscription_meal_id is provided, it will update that specific meal.
     * Otherwise, it will create a new meal entry for the specified day.
     * 
     * @param UpdateSubscriptionMealApiRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMeal(UpdateSubscriptionMealApiRequest $request)
    {
        // Force JSON response
        $request->headers->set('Accept', 'application/json');

        DB::beginTransaction();

        try {
            // Find user's active subscription
            $userSubscription = UserSubcrption::where('user_id', $request->user_id)
                ->where('status', 'active')
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->latest()
                ->first();

            if (!$userSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found for this user.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Normalize day to lowercase
            $day = strtolower(trim($request->day));

            // Find or create subscription day for this day
            $subscriptionDay = SubscriptionDay::where('user_subcrptions_id', $userSubscription->id)
                ->where('day', $day)
                ->first();

            if (!$subscriptionDay) {
                // Check if the day is in the selected_days of the subscription
                $selectedDays = is_array($userSubscription->selected_days)
                    ? $userSubscription->selected_days
                    : explode(',', $userSubscription->selected_days ?? '');

                $selectedDays = array_map('trim', array_map('strtolower', $selectedDays));

                if (!in_array($day, $selectedDays)) {
                    return response()->json([
                        'success' => false,
                        'message' => "The day '{$day}' is not part of the user's selected subscription days.",
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // Create subscription day
                $subscriptionDay = SubscriptionDay::create([
                    'user_subcrptions_id' => $userSubscription->id,
                    'day' => $day,
                ]);
            }

            // Verify meal exists
            $meal = Meal::find($request->meal_id);
            if (!$meal) {
                return response()->json([
                    'success' => false,
                    'message' => 'The specified meal does not exist.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Update existing meal or create new one
            if ($request->has('subscription_meal_id') && $request->subscription_meal_id) {
                // Update existing subscription meal
                $subscriptionMeal = SubscriptionMeal::where('id', $request->subscription_meal_id)
                    ->where('subscription_days_id', $subscriptionDay->id)
                    ->first();

                if (!$subscriptionMeal) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subscription meal not found or does not belong to this subscription day.',
                    ], Response::HTTP_NOT_FOUND);
                }

                $subscriptionMeal->update([
                    'meal_id' => $request->meal_id,
                    'type' => $request->type,
                ]);

                $message = 'Meal updated successfully.';
            } else {
                // Create new subscription meal
                $subscriptionMeal = SubscriptionMeal::create([
                    'subscription_days_id' => $subscriptionDay->id,
                    'meal_id' => $request->meal_id,
                    'type' => $request->type,
                ]);

                $message = 'Meal added successfully.';
            }

            // Load relationships for response
            $subscriptionMeal->load(['meal', 'subscription_days.user_subcrption']);

            DB::commit();

            // Prepare response
            $response = [
                'success' => true,
                'message' => $message,
                'data' => [
                    'subscription_meal' => [
                        'id' => $subscriptionMeal->id,
                        'subscription_days_id' => $subscriptionMeal->subscription_days_id,
                        'day' => $subscriptionMeal->subscription_days->day ?? null,
                        'meal_id' => $subscriptionMeal->meal_id,
                        'meal' => [
                            'id' => $subscriptionMeal->meal->id ?? null,
                            'title' => $subscriptionMeal->meal->title ?? null,
                            'description' => $subscriptionMeal->meal->description ?? null,
                            'calories' => $subscriptionMeal->meal->calories ?? null,
                            'protein_g' => $subscriptionMeal->meal->protein_g ?? null,
                            'fat_g' => $subscriptionMeal->meal->fat_g ?? null,
                            'carbs_g' => $subscriptionMeal->meal->carbs_g ?? null,
                        ],
                        'type' => $subscriptionMeal->type,
                        'user_subscription_id' => $subscriptionMeal->subscription_days->user_subcrptions_id ?? null,
                        'created_at' => $subscriptionMeal->created_at,
                        'updated_at' => $subscriptionMeal->updated_at,
                    ],
                ],
            ];

            return response()->json($response, Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Update subscription meal error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update meal',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while updating the meal.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

