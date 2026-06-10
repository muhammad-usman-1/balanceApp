<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserSubcrptionRequest;
use App\Http\Requests\StoreUserSubcrptionRequest;
use App\Http\Requests\UpdateUserSubcrptionRequest;
use App\Models\Duration;
use App\Models\SubcrptionPlan;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionPauseLog;
use App\Models\User;
use App\Models\UserSubcrption;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSubcrptionController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user_subcrption_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = auth()->user()->isBranchUser() ? auth()->user()->branch_id : null;

        $userSubcrptions = UserSubcrption::with(['user', 'subcrption_plans'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.userSubcrptions.index', compact('userSubcrptions'));
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
            ->with(['subscription_meals.meal'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        return view('admin.userSubcrptions.show', compact('userSubcrption', 'subscriptionDays'));
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
            ->with(['subscription_meals.meal'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        return view('admin.userSubcrptions.details', compact('userSubcrption', 'subscriptionDays'));
    }

    /**
     * Pause a subscription
     * 
     * @param Request $request
     * @param UserSubcrption $userSubcrption
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pause(Request $request, UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $performedByName = auth()->user()->name ?? 'Admin';
        $performedById = auth()->id();

        $result = $userSubcrption->pause(
            $request->days,
            $request->reason,
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
            : 'Unable to pause subscription. It may already be paused or inactive.';
        
        return redirect()->back()->with('error', $errorMessage);
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
     * Show pause/resume logs for a subscription
     * 
     * @param UserSubcrption $userSubcrption
     * @return \Illuminate\View\View
     */
    public function pauseLogs(UserSubcrption $userSubcrption)
    {
        abort_if(Gate::denies('user_subcrption_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userSubcrption->load('user', 'subcrption_plans');
        $pauseLogs = $userSubcrption->pause_logs()->orderBy('action_timestamp', 'desc')->get();

        return view('admin.userSubcrptions.pause-logs', compact('userSubcrption', 'pauseLogs'));
    }
}
