<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSubscriptionMealApiRequest;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use App\Models\UserSubcrption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMealApiController extends Controller
{
    /**
     * Assign or update the meal for a specific day in the weekly schedule.
     *
     * The user picks a day slot (subscription_day_id from GET /subscription/meals)
     * and sets which meal they want every week on that day.
     * The same meal repeats automatically for every week of the plan.
     */
    public function updateMeal(UpdateSubscriptionMealApiRequest $request)
    {
        DB::beginTransaction();

        try {
            $subscriptionDay = SubscriptionDay::with('user_subcrption')
                ->find($request->subscription_day_id);

            $userSubscription = $subscriptionDay?->user_subcrption;

            if (
                ! $userSubscription
                || $userSubscription->user_id != $request->user_id
                || $userSubscription->status !== 'active'
            ) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Day not found or does not belong to your active subscription.',
                ], Response::HTTP_NOT_FOUND);
            }

            if ($request->filled('subscription_meal_id')) {
                $subscriptionMeal = SubscriptionMeal::where('id', $request->subscription_meal_id)
                    ->where('subscription_days_id', $subscriptionDay->id)
                    ->first();

                if (! $subscriptionMeal) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Meal entry not found for this day.',
                    ], Response::HTTP_NOT_FOUND);
                }

                $subscriptionMeal->update([
                    'meal_id' => $request->meal_id,
                    'type'    => $request->type,
                ]);
                $message = 'Meal updated successfully.';
            } else {
                $subscriptionMeal = SubscriptionMeal::create([
                    'subscription_days_id' => $subscriptionDay->id,
                    'meal_id'              => $request->meal_id,
                    'type'                 => $request->type,
                ]);
                $message = 'Meal assigned successfully.';
            }

            $subscriptionMeal->load('meal');
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'subscription_meal' => [
                        'id'                  => $subscriptionMeal->id,
                        'subscription_day_id' => $subscriptionDay->id,
                        'day'                 => $subscriptionDay->day,
                        'meal_id'             => $subscriptionMeal->meal_id,
                        'type'                => $subscriptionMeal->type,
                        'meal'                => $this->formatMeal($subscriptionMeal->meal),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateMeal error: ' . $e->getMessage(), ['request' => $request->all()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update meal.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Return the weekly meal schedule for the user's active subscription.
     *
     * Each day slot in the response represents a recurring delivery day.
     * The same meal is delivered on that day every week for the full plan duration.
     *
     * Example: plan = 4 weeks, days = Mon/Wed/Fri
     *   → 3 day slots returned
     *   → Mobile shows Mon meal, Wed meal, Fri meal
     *   → Each repeats for 4 weeks automatically
     */
    public function getMeals(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $userSubscription = UserSubcrption::where('user_id', $request->user_id)
                ->where('status', 'active')
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->with([
                    'subcrption_plans',
                    'subscription_days' => fn ($q) => $q->orderByRaw("FIELD(day,'monday','tuesday','wednesday','thursday','friday','saturday','sunday')"),
                    'subscription_days.subscription_meals.meal',
                ])
                ->latest()
                ->first();

            if (! $userSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.',
                ], Response::HTTP_NOT_FOUND);
            }

            $plan = $userSubscription->subcrption_plans;

            $weeklySchedule = $userSubscription->subscription_days->map(fn ($day) => [
                'subscription_day_id' => $day->id,
                'day'                 => $day->day,
                'meals'               => $day->subscription_meals->map(fn ($m) => [
                    'id'      => $m->id,
                    'meal_id' => $m->meal_id,
                    'type'    => $m->type,
                    'meal'    => $this->formatMeal($m->meal),
                ])->values(),
            ])->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_subscription_id' => $userSubscription->id,
                    'plan_title'           => $plan->title ?? null,
                    'no_of_weeks'          => $plan->no_of_weeks ?? null,
                    'days_per_week'        => $userSubscription->subscription_days->count(),
                    'start_date'           => $userSubscription->start_date,
                    'end_date'             => $userSubscription->end_date,
                    'note'                 => 'This weekly schedule repeats every week for the full plan duration.',
                    'weekly_schedule'      => $weeklySchedule,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('getMeals error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meal schedule.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function formatMeal(?object $meal): ?array
    {
        if (! $meal) {
            return null;
        }

        return [
            'id'          => $meal->id,
            'title'       => $meal->title,
            'description' => $meal->description ?? null,
            'calories'    => $meal->calories ?? null,
            'protein_g'   => $meal->protein_g ?? null,
            'fat_g'       => $meal->fat_g ?? null,
            'carbs_g'     => $meal->carbs_g ?? null,
        ];
    }
}
