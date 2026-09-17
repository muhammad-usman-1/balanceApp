<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserSubcrptionRequest;
use App\Http\Requests\StoreUserSubcrptionRequest;
use App\Http\Requests\UpdateUserSubcrptionRequest;
use App\Models\Duration;
use App\Models\Meal;
use App\Models\SubcrptionPlan;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use App\Models\SubscriptionPauseLog;
use App\Models\SubscriptionPauseRequest;
use App\Models\User;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserSubcrptionController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('user_subcrption_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId     = auth()->user()->isBranchUser() ? auth()->user()->branch_id : null;
        $search       = trim($request->get('search', ''));
        $statusFilter = $request->get('status_filter', 'active');
        $today        = now()->toDateString();

        $userSubcrptions = UserSubcrption::with(['user', 'subcrption_plans'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', '%'.$search.'%')
                       ->orWhere('mobile', 'like', '%'.$search.'%');
                });
            })
            // A subscription is only truly "active" if it's marked active AND its end
            // date hasn't passed yet — this covers the (rare) lag before the daily
            // subscriptions:expire job flips a stale record's status to "inactive".
            ->when($statusFilter === 'active', function ($q) use ($today) {
                $q->where('status', 'active')
                  ->where(function ($q2) use ($today) {
                      $q2->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
                  });
            })
            ->when($statusFilter === 'ended', function ($q) use ($today) {
                $q->where(function ($q2) use ($today) {
                    $q2->where('status', 'inactive')
                       ->orWhere(function ($q3) use ($today) {
                           $q3->where('status', 'active')->whereDate('end_date', '<', $today);
                       });
                });
            })
            ->when($statusFilter === 'queued', fn($q) => $q->where('status', 'queued'))
            // 'all' => no status restriction
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.userSubcrptions.index', compact('userSubcrptions', 'search', 'statusFilter'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_subcrption_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $subcrption_plans = SubcrptionPlan::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $durations = Duration::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.userSubcrptions.create', compact('durations', 'subcrption_plans', 'users'));
    }

    public function store(StoreUserSubcrptionRequest $request)
    {
        $userSubcrption = UserSubcrption::create($request->all());

        return redirect()->route('admin.user-subcrptions.index');
    }

    public function edit(UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $subcrption_plans = SubcrptionPlan::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $durations = Duration::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $userSubcrption->load('user', 'subcrption_plans', 'duration');

        return view('admin.userSubcrptions.edit', compact('durations', 'subcrption_plans', 'userSubcrption', 'users'));
    }

    public function update(UpdateUserSubcrptionRequest $request, UserSubcrption $userSubcrption)
    {
        $userSubcrption->update($request->all());

        return redirect()->route('admin.user-subcrptions.index');
    }

    public function show(UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userSubcrption->load('user', 'subcrption_plans', 'duration');

        $subscriptionDays = SubscriptionDay::where('user_subcrptions_id', $userSubcrption->id)
            ->with(['subscription_meals.meal', 'subscription_meals.selectedIngredients.mealExtra'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        $allMeals   = Meal::orderBy('title')->pluck('title', 'id');
        $mainMeals  = Meal::where('type', 'is meal')->orderBy('title')->pluck('title', 'id');
        $snackMeals = Meal::where('type', 'is snack')->orderBy('title')->pluck('title', 'id');
        $plan       = $userSubcrption->subcrption_plans;
        $mealLimit  = $plan->meal_count  ?? null;
        $snackLimit = $plan->snack_count ?? null;

        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Current meals per day, split by type, so the bulk form can pre-fill each slot.
        $existingByDay = [];
        foreach ($subscriptionDays as $sd) {
            $existingByDay[$sd->day] = [
                'is meal'  => $sd->subscription_meals->where('type', 'is meal')->pluck('meal_id')->values()->all(),
                'is snack' => $sd->subscription_meals->where('type', 'is snack')->pluck('meal_id')->values()->all(),
            ];
        }

        // Days to render in the bulk form: the subscription's selected days, plus any
        // day that already has meals. Fall back to the full week when none are set.
        $selected = $userSubcrption->selected_days;
        $selectedDays = $selected
            ? (is_array($selected) ? $selected : explode(',', $selected))
            : [];
        $selectedDays = array_map(fn($d) => strtolower(trim($d)), $selectedDays);
        $formDays = array_values(array_intersect(
            $weekdays,
            array_unique(array_merge($selectedDays, array_keys($existingByDay)))
        ));
        if (empty($formDays)) {
            $formDays = $weekdays;
        }

        [$availableDaysToPause, $upcomingPausedDays] = $this->pauseOptionsFor($userSubcrption, $selectedDays);

        return view('admin.userSubcrptions.show', compact(
            'userSubcrption', 'subscriptionDays', 'allMeals', 'mainMeals', 'snackMeals',
            'mealLimit', 'snackLimit', 'formDays', 'existingByDay',
            'availableDaysToPause', 'upcomingPausedDays'
        ));
    }

    public function destroy(UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userSubcrption->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserSubcrptionRequest $request)
    {
        $userSubcrptions = UserSubcrption::find(request('ids'));

        foreach ($userSubcrptions as $userSubcrption) {
            $userSubcrption->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get subscription details with days and meals
     * 
     * @param UserSubcrption $userSubcrption
     * @return \Illuminate\View\View
     */
    public function details(UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Load all relationships
        $userSubcrption->load([
            'user',
            'subcrption_plans',
            'duration'
        ]);

        // Get subscription days with meals
        $subscriptionDays = SubscriptionDay::where('user_subcrptions_id', $userSubcrption->id)
            ->with(['subscription_meals.meal', 'subscription_meals.selectedIngredients.mealExtra'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        return view('admin.userSubcrptions.details', compact('userSubcrption', 'subscriptionDays'));
    }

    /**
     * Pause one specific upcoming delivery day, admin-chosen from the
     * subscription's own scheduled days — no reason/notes needed.
     *
     * Creates an already-approved SubscriptionPauseRequest for that single
     * date and extends end_date by 1 day — the exact same mechanism as a
     * customer-submitted pause request that admin approves. It deliberately
     * does NOT touch is_paused/paused_at/paused_until: only that date is
     * skipped for delivery, the subscription itself keeps showing "Active".
     */
    public function pause(Request $request, UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'pause_date' => 'required|date|after_or_equal:today',
        ]);

        if ($userSubcrption->status !== 'active') {
            return redirect()->back()->with('error', 'Can only pause active subscriptions.');
        }

        $selectedDays = $userSubcrption->selected_days
            ? array_map(fn ($d) => strtolower(trim($d)), explode(',', $userSubcrption->selected_days))
            : [];
        [$availableDaysToPause] = $this->pauseOptionsFor($userSubcrption, $selectedDays);

        if (! in_array($request->pause_date, array_column($availableDaysToPause, 'date'), true)) {
            return redirect()->back()->with('error', 'That date is not an available delivery day for this plan.');
        }

        $date = Carbon::parse($request->pause_date);

        DB::transaction(function () use ($userSubcrption, $date) {
            SubscriptionPauseRequest::create([
                'user_subcrption_id' => $userSubcrption->id,
                'user_id'            => $userSubcrption->user_id,
                'pause_start_date'   => $date->toDateString(),
                'pause_end_date'     => $date->toDateString(),
                'pause_days'         => 1,
                'status'             => 'approved',
                'reviewed_by'        => auth()->id(),
                'reviewed_at'        => now(),
            ]);

            $newEndDate = Carbon::parse($userSubcrption->getRawOriginal('end_date'))
                ->addDay()
                ->format('Y-m-d');

            DB::table('user_subcrptions')->where('id', $userSubcrption->id)->update([
                'total_paused_days' => $userSubcrption->total_paused_days + 1,
                'end_date'          => $newEndDate,
                'updated_at'        => now(),
            ]);
        });

        return redirect()->back()->with(
            'success',
            "{$date->format('l, d M Y')} paused — delivery skipped that day and the plan end date pushed out by 1 day."
        );
    }

    /**
     * Undo one specific admin-created pause (e.g. wrong day picked by
     * mistake): puts the day back on the delivery schedule and rewinds
     * end_date by the day(s) that pause had added.
     */
    public function resumePauseDay(UserSubcrption $userSubcrption, SubscriptionPauseRequest $pauseRequest)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($pauseRequest->user_subcrption_id !== $userSubcrption->id || $pauseRequest->status !== 'approved') {
            return redirect()->back()->with('error', 'This pause can no longer be resumed.');
        }

        DB::transaction(function () use ($userSubcrption, $pauseRequest) {
            $newEndDate = Carbon::parse($userSubcrption->getRawOriginal('end_date'))
                ->subDays($pauseRequest->pause_days)
                ->format('Y-m-d');

            DB::table('user_subcrptions')->where('id', $userSubcrption->id)->update([
                'total_paused_days' => max(0, $userSubcrption->total_paused_days - $pauseRequest->pause_days),
                'end_date'          => $newEndDate,
                'updated_at'        => now(),
            ]);

            $pauseRequest->update([
                'status'      => 'resumed',
                'admin_notes' => 'Resumed by admin on ' . now()->format('Y-m-d H:i'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Pause removed — that day is back on the delivery schedule.');
    }

    /**
     * Build the picker options for the pause modal: upcoming dates that
     * match the subscription's own scheduled days, within its remaining
     * period, excluding dates already paused. Also returns the currently
     * scheduled (not-yet-passed) admin pauses, for the per-day Resume list.
     *
     * @return array{0: array<int, array{date:string, label:string}>, 1: \Illuminate\Support\Collection}
     */
    private function pauseOptionsFor(UserSubcrption $userSubcrption, array $selectedDays): array
    {
        $today     = Carbon::today();
        $planStart = Carbon::parse($userSubcrption->getRawOriginal('start_date'));
        $planEnd   = Carbon::parse($userSubcrption->getRawOriginal('end_date'));
        // Never offer a date before the plan actually starts — there's no
        // real delivery scheduled that early regardless of what "today" is.
        $rangeStart = $planStart->gt($today) ? $planStart : $today;

        $upcomingPausedDays = SubscriptionPauseRequest::where('user_subcrption_id', $userSubcrption->id)
            ->where('status', 'approved')
            ->whereDate('pause_end_date', '>=', $today->toDateString())
            ->orderBy('pause_start_date')
            ->get();

        $pausedDates = $upcomingPausedDays->pluck('pause_start_date')->map(fn ($d) => Carbon::parse($d)->toDateString())->all();

        $availableDaysToPause = [];
        if (! empty($selectedDays) && $planEnd->gte($rangeStart)) {
            $cursor = $rangeStart->copy();
            $iterations = 0;
            while ($cursor->lte($planEnd) && $iterations < 180) {
                $dayName = strtolower($cursor->format('l'));
                if (in_array($dayName, $selectedDays, true) && ! in_array($cursor->toDateString(), $pausedDates, true)) {
                    $availableDaysToPause[] = [
                        'date'  => $cursor->toDateString(),
                        'label' => $cursor->format('l, d M Y'),
                    ];
                }
                $cursor->addDay();
                $iterations++;
            }
        }

        return [$availableDaysToPause, $upcomingPausedDays];
    }

    /**
     * Resume a subscription
     * 
     * @param Request $request
     * @param UserSubcrption $userSubcrption
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resume(Request $request, UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $performedByName = auth()->user()->name ?? 'Admin';
        $performedById = auth()->id();

        $result = $userSubcrption->resume(
            'admin',
            $performedById,
            $performedByName,
            $request->notes
        );

        if (is_array($result) && $result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        $errorMessage = is_array($result) && isset($result['message']) 
            ? $result['message'] 
            : 'Unable to resume subscription. It may not be paused or may be inactive.';
        
        return redirect()->back()->with('error', $errorMessage);
    }

    /**
     * Record a cash-on-delivery payment as collected
     *
     * @param UserSubcrption $userSubcrption
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markPaid(UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($userSubcrption->payment_gateway !== 'cash' || $userSubcrption->payment === 'paid') {
            return redirect()->back()->with('error', 'Only pending cash-on-delivery subscriptions can be marked as paid.');
        }

        $userSubcrption->payment = 'paid';
        $userSubcrption->save();

        return redirect()->back()->with('success', 'Payment recorded as received for this subscription.');
    }

    /**
     * Remove a meal from a subscription day
     */
    public function removeMeal(Request $request, UserSubcrption $userSubcrption, SubscriptionMeal $subscriptionMeal)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $belongs = SubscriptionDay::where('id', $subscriptionMeal->subscription_days_id)
            ->where('user_subcrptions_id', $userSubcrption->id)
            ->exists();

        if (! $belongs) {
            return redirect()->back()->with('error', 'Meal does not belong to this subscription.');
        }

        $subscriptionMeal->delete();

        return redirect()->back()->with('success', 'Meal removed successfully.');
    }

    /**
     * Add a meal to a specific day of a subscription
     */
    public function addMeal(Request $request, UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'day'     => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'meal_id' => ['required', 'exists:meals,id'],
            'type'    => ['required', 'in:is meal,is snack'],
        ]);

        $userSubcrption->load('subcrption_plans');
        $plan = $userSubcrption->subcrption_plans;

        $day = SubscriptionDay::firstOrCreate([
            'user_subcrptions_id' => $userSubcrption->id,
            'day'                 => $request->day,
        ]);

        if ($request->type === 'is meal' && $plan && $plan->meal_count !== null) {
            $current = SubscriptionMeal::where('subscription_days_id', $day->id)->where('type', 'is meal')->count();
            if ($current >= $plan->meal_count) {
                return redirect()->back()->with('error', "Limit reached: this plan allows {$plan->meal_count} meal(s) per day.");
            }
        }

        if ($request->type === 'is snack' && $plan && $plan->snack_count !== null) {
            $current = SubscriptionMeal::where('subscription_days_id', $day->id)->where('type', 'is snack')->count();
            if ($current >= $plan->snack_count) {
                return redirect()->back()->with('error', "Limit reached: this plan allows {$plan->snack_count} snack(s) per day.");
            }
        }

        // Enforce weekly meal-group limit — shared across every meal in the same
        // group, counted across the whole subscription (all days), not per day.
        $meal = Meal::find($request->meal_id);
        $group = $meal?->mealGroup;

        if ($group) {
            $alreadyAssigned = SubscriptionMeal::whereHas('subscription_days', function ($q) use ($userSubcrption) {
                $q->where('user_subcrptions_id', $userSubcrption->id);
            })
            ->whereHas('meal', function ($q) use ($group) {
                $q->where('meal_group_id', $group->id);
            })
            ->count();

            if ($alreadyAssigned >= $group->weekly_limit) {
                return redirect()->back()->with('error', "Weekly limit reached: \"{$group->name}\" allows a maximum of {$group->weekly_limit} meal(s) per week. {$alreadyAssigned} have already been added from this group.");
            }
        }

        SubscriptionMeal::create([
            'subscription_days_id' => $day->id,
            'meal_id'              => $request->meal_id,
            'type'                 => $request->type,
        ]);

        return redirect()->back()->with('success', 'Meal added to ' . ucfirst($request->day) . ' successfully.');
    }

    /**
     * Save meals & snacks for every day of the subscription in one submit.
     *
     * The form shows all days at once, each pre-filled with what's already
     * assigned, so this acts as a full editor: for each submitted day the meals
     * are replaced with exactly what the form contains (add new, drop cleared).
     */
    public function addMeals(Request $request, UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $request->validate([
            'form_days'    => ['required', 'array'],
            'form_days.*'  => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'meals'        => ['nullable', 'array'],
            'meals.*'      => ['nullable', 'array'],
            'meals.*.*'    => ['nullable', 'exists:meals,id'],
            'snacks'       => ['nullable', 'array'],
            'snacks.*'     => ['nullable', 'array'],
            'snacks.*.*'   => ['nullable', 'exists:meals,id'],
        ]);

        $userSubcrption->load('subcrption_plans');
        $plan = $userSubcrption->subcrption_plans;

        $formDays = array_values(array_intersect($weekdays, array_map('strval', $request->input('form_days', []))));

        // Build the desired meal/snack lists per day from the filled-in slots,
        // capping each day at the plan's per-day limits.
        $desired = [];
        foreach ($formDays as $day) {
            $mealsIn  = array_values(array_filter((array) $request->input("meals.$day", []),  fn($v) => $v !== null && $v !== ''));
            $snacksIn = array_values(array_filter((array) $request->input("snacks.$day", []), fn($v) => $v !== null && $v !== ''));

            if ($plan && $plan->meal_count !== null) {
                $mealsIn = array_slice($mealsIn, 0, $plan->meal_count);
            }
            if ($plan && $plan->snack_count !== null) {
                $snacksIn = array_slice($snacksIn, 0, $plan->snack_count);
            }

            $desired[$day] = ['is meal' => $mealsIn, 'is snack' => $snacksIn];
        }

        // Seed weekly meal-group counts from meals on days that this submit does NOT
        // touch, so the weekly limit stays correct across the whole subscription.
        $groupCounts = [];
        $untouched = SubscriptionMeal::whereHas('subscription_days', function ($q) use ($userSubcrption, $formDays) {
            $q->where('user_subcrptions_id', $userSubcrption->id)->whereNotIn('day', $formDays);
        })->with('meal')->get();
        foreach ($untouched as $sm) {
            $gid = $sm->meal?->meal_group_id;
            if ($gid) {
                $groupCounts[$gid] = ($groupCounts[$gid] ?? 0) + 1;
            }
        }

        // Enforce the weekly meal-group limit, skipping over-limit picks.
        $skipped = [];
        foreach ($desired as $day => &$slots) {
            foreach (['is meal', 'is snack'] as $type) {
                $accepted = [];
                foreach ($slots[$type] as $mid) {
                    $meal  = Meal::find($mid);
                    $group = $meal?->mealGroup;
                    if ($group) {
                        if (($groupCounts[$group->id] ?? 0) >= $group->weekly_limit) {
                            $skipped[] = "{$meal->title} on " . ucfirst($day) . " (weekly limit for \"{$group->name}\")";
                            continue;
                        }
                        $groupCounts[$group->id] = ($groupCounts[$group->id] ?? 0) + 1;
                    }
                    $accepted[] = $mid;
                }
                $slots[$type] = $accepted;
            }
        }
        unset($slots);

        // Replace each submitted day's meals with the accepted picks.
        \DB::transaction(function () use ($desired, $userSubcrption) {
            foreach ($desired as $day => $slots) {
                $subscriptionDay = SubscriptionDay::firstOrCreate([
                    'user_subcrptions_id' => $userSubcrption->id,
                    'day'                 => $day,
                ]);

                SubscriptionMeal::where('subscription_days_id', $subscriptionDay->id)->delete();

                foreach (['is meal', 'is snack'] as $type) {
                    foreach ($slots[$type] as $mid) {
                        SubscriptionMeal::create([
                            'subscription_days_id' => $subscriptionDay->id,
                            'meal_id'              => $mid,
                            'type'                 => $type,
                        ]);
                    }
                }
            }
        });

        $message = 'Meals saved successfully for all days.';
        if (! empty($skipped)) {
            $message .= ' Skipped: ' . implode('; ', $skipped) . '.';
        }

        return redirect()->back()->with('success', $message);
    }
}
