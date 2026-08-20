<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSubscriptionMealApiRequest;
use App\Models\Meal;
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

            // Enforce weekly meal-group limit before any write — the limit is shared
            // across every meal in the same group, not just the one being added.
            $meal = Meal::find($request->meal_id);
            $group = $meal?->mealGroup;

            if ($group) {
                $alreadyAssigned = SubscriptionMeal::whereHas('subscription_days', function ($q) use ($userSubscription) {
                    $q->where('user_subcrptions_id', $userSubscription->id);
                })
                ->whereHas('meal', function ($q) use ($group) {
                    $q->where('meal_group_id', $group->id);
                })
                ->when(
                    $request->filled('subscription_meal_id'),
                    fn ($q) => $q->where('id', '!=', $request->subscription_meal_id)
                )
                ->count();

                if ($alreadyAssigned >= $group->weekly_limit) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Weekly meal limit reached. You have reached the maximum number of meals allowed from this category for the current week. Please choose a meal from another category or remove one of your previously selected meals.',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            $previousMealId = null;

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

                $previousMealId = $subscriptionMeal->meal_id;

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

            // Persist the customer's extra choices. If none are sent but the meal
            // was changed, clear the stale choices that belonged to the old meal.
            if ($request->has('extra_ingredient_ids')) {
                $extrasError = $this->applySelectedExtras($subscriptionMeal, $meal, (array) $request->input('extra_ingredient_ids', []));
                if ($extrasError) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $extrasError,
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            } elseif ($previousMealId !== null && $previousMealId != $request->meal_id) {
                $subscriptionMeal->selectedIngredients()->sync([]);
            }

            $subscriptionMeal->load(['meal.mealExtras', 'meal.availableIngredients', 'selectedIngredients.mealExtra']);
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
                        'selected_extras'     => $this->formatSelectedExtras($subscriptionMeal),
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
                    'subscription_days.subscription_meals.meal.mealExtras',
                    'subscription_days.subscription_meals.meal.availableIngredients',
                    'subscription_days.subscription_meals.selectedIngredients.mealExtra',
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
                    'id'              => $m->id,
                    'meal_id'         => $m->meal_id,
                    'type'            => $m->type,
                    'meal'            => $this->formatMeal($m->meal),
                    'selected_extras' => $this->formatSelectedExtras($m),
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
            'id'              => $meal->id,
            'title'           => $meal->title,
            'title_ar'        => $meal->title_ar ?? null,
            'description'     => $meal->description ?? null,
            'description_ar'  => $meal->description_ar ?? null,
            'calories'        => $meal->calories ?? null,
            'protein_g'       => $meal->protein_g ?? null,
            'fat_g'           => $meal->fat_g ?? null,
            'carbs_g'         => $meal->carbs_g ?? null,
            'available_extras' => $this->formatAvailableExtras($meal),
        ];
    }

    /**
     * The extras this meal offers, each with only the options enabled for it.
     * Requires the meal to have mealExtras + availableIngredients loaded.
     */
    private function formatAvailableExtras(?object $meal): array
    {
        if (! $meal || ! $meal->relationLoaded('mealExtras')) {
            return [];
        }

        $byExtra = $meal->availableIngredients->groupBy('meal_extra_id');

        return $meal->mealExtras->map(function ($extra) use ($byExtra) {
            return [
                'id'             => $extra->id,
                'name'           => $extra->name,
                'name_ar'        => $extra->name_ar,
                'selection_type' => $extra->selection_type,
                'is_required'    => $extra->pivot->is_required !== null
                    ? (bool) $extra->pivot->is_required
                    : (bool) $extra->is_required,
                // Max options the customer may pick: single => 1; multiple => per-meal cap or null (no limit).
                'max_select'     => $extra->selection_type === 'single'
                    ? 1
                    : ($extra->pivot->max_select !== null ? (int) $extra->pivot->max_select : null),
                'options'        => ($byExtra->get($extra->id) ?? collect())
                    ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'name_ar' => $i->name_ar])
                    ->values(),
            ];
        })->values()->all();
    }

    /**
     * The customer's chosen extras for a subscription meal, grouped by extra.
     */
    private function formatSelectedExtras(SubscriptionMeal $subscriptionMeal): array
    {
        $subscriptionMeal->loadMissing('selectedIngredients.mealExtra');

        return $subscriptionMeal->selectedIngredients
            ->groupBy('meal_extra_id')
            ->map(function ($ingredients) {
                $extra = $ingredients->first()->mealExtra;

                return [
                    'extra_id'       => $extra?->id,
                    'name'           => $extra?->name,
                    'name_ar'        => $extra?->name_ar,
                    'selection_type' => $extra?->selection_type,
                    'options'        => $ingredients
                        ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'name_ar' => $i->name_ar])
                        ->values(),
                ];
            })->values()->all();
    }

    /**
     * Validate and persist a customer's extra choices for a subscription meal.
     * Returns an error message string on failure, or null on success.
     */
    private function applySelectedExtras(SubscriptionMeal $subscriptionMeal, ?Meal $meal, array $ingredientIds): ?string
    {
        $ingredientIds = array_values(array_unique(array_filter(
            $ingredientIds,
            fn ($v) => $v !== null && $v !== ''
        )));

        if (empty($ingredientIds)) {
            $subscriptionMeal->selectedIngredients()->sync([]);
            return null;
        }

        if (! $meal) {
            return 'Meal not found for these options.';
        }

        // Every chosen option must be one this meal actually offers.
        $available = $meal->availableIngredients()->with('mealExtra')->get()->keyBy('id');

        foreach ($ingredientIds as $id) {
            if (! $available->has($id)) {
                return 'One or more selected options are not available for this meal.';
            }
        }

        // Per-meal cap: single => 1; multiple => the meal's max_select (null = no cap).
        $mealExtras = $meal->mealExtras()->get()->keyBy('id');
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
                return "You can select at most {$allowed} option(s) for \"{$extra->name}\".";
            }
        }

        $subscriptionMeal->selectedIngredients()->sync($ingredientIds);
        return null;
    }

    /**
     * Save (replace) the extra choices for an existing subscription meal.
     * Use this when the customer edits extras without changing the meal itself.
     */
    public function saveMealExtras(Request $request)
    {
        $request->validate([
            'user_id'                => 'required|integer|exists:users,id',
            'subscription_meal_id'   => 'required|integer|exists:subscription_meals,id',
            'extra_ingredient_ids'   => 'nullable|array',
            'extra_ingredient_ids.*' => 'integer|exists:meal_extra_ingredients,id',
        ]);

        $subscriptionMeal = SubscriptionMeal::with(['subscription_days.user_subcrption', 'meal'])
            ->find($request->subscription_meal_id);

        $userSubscription = $subscriptionMeal?->subscription_days?->user_subcrption;

        if (
            ! $subscriptionMeal
            || ! $userSubscription
            || $userSubscription->user_id != $request->user_id
            || $userSubscription->status !== 'active'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found or does not belong to your active subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        $error = $this->applySelectedExtras($subscriptionMeal, $subscriptionMeal->meal, (array) $request->input('extra_ingredient_ids', []));
        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'message' => 'Extras saved successfully.',
            'data' => [
                'subscription_meal_id' => $subscriptionMeal->id,
                'selected_extras'      => $this->formatSelectedExtras($subscriptionMeal->fresh()),
            ],
        ]);
    }
}
