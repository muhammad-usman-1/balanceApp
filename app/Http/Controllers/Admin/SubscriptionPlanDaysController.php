<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySubscriptionPlanDayRequest;
use App\Http\Requests\StoreSubscriptionPlanDayRequest;
use App\Http\Requests\UpdateSubscriptionPlanDayRequest;
use App\Models\SubcrptionPlan;
use App\Models\SubscriptionDay;
use App\Models\UserSubcrption;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionPlanDaysController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('subscription_plan_day_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionDays = SubscriptionDay::with(['user_subcrption.subcrption_plans', 'user_subcrption.user'])->get();

        return view('admin.subscriptionPlanDays.index', compact('subscriptionDays'));
    }

    public function create()
    {
        abort_if(Gate::denies('subscription_plan_day_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user_subscriptions = UserSubcrption::with('subcrption_plans')->get()->mapWithKeys(function ($sub) {
            return [$sub->id => 'User Subscription #' . $sub->id . ' - ' . ($sub->subcrption_plans->title ?? 'N/A')];
        })->prepend(trans('global.pleaseSelect'), '');

        return view('admin.subscriptionPlanDays.create', compact('user_subscriptions'));
    }

    public function store(StoreSubscriptionPlanDayRequest $request)
    {
        $subscriptionDay = SubscriptionDay::create($request->all());

        return redirect()->route('admin.subscription-plan-days.index');
    }

    public function edit(SubscriptionDay $subscriptionDay)
    {
        abort_if(Gate::denies('subscription_plan_day_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user_subscriptions = UserSubcrption::with('subcrption_plans')->get()->mapWithKeys(function ($sub) {
            return [$sub->id => 'User Subscription #' . $sub->id . ' - ' . ($sub->subcrption_plans->title ?? 'N/A')];
        })->prepend(trans('global.pleaseSelect'), '');

        $subscriptionDay->load('user_subcrption.subcrption_plans');

        return view('admin.subscriptionPlanDays.edit', compact('subscriptionDay', 'user_subscriptions'));
    }

    public function update(UpdateSubscriptionPlanDayRequest $request, SubscriptionDay $subscriptionDay)
    {
        $subscriptionDay->update($request->all());

        return redirect()->route('admin.subscription-plan-days.index');
    }

    public function show(SubscriptionDay $subscriptionDay)
    {
        abort_if(Gate::denies('subscription_plan_day_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionDay->load('user_subcrption.subcrption_plans');

        return view('admin.subscriptionPlanDays.show', compact('subscriptionDay'));
    }

    public function destroy(SubscriptionDay $subscriptionDay)
    {
        abort_if(Gate::denies('subscription_plan_day_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionDay->delete();

        return back();
    }

    public function massDestroy(MassDestroySubscriptionPlanDayRequest $request)
    {
        $subscriptionDays = SubscriptionDay::find(request('ids'));

        foreach ($subscriptionDays as $subscriptionDay) {
            $subscriptionDay->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
