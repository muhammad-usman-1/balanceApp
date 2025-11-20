<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySubscriptionPlanDayRequest;
use App\Http\Requests\StoreSubscriptionPlanDayRequest;
use App\Http\Requests\UpdateSubscriptionPlanDayRequest;
use App\Models\SubcrptionPlan;
use App\Models\SubscriptionPlanDay;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionPlanDaysController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('subscription_plan_day_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionPlanDays = SubscriptionPlanDay::with(['subscription_plans'])->get();

        return view('admin.subscriptionPlanDays.index', compact('subscriptionPlanDays'));
    }

    public function create()
    {
        abort_if(Gate::denies('subscription_plan_day_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscription_plans = SubcrptionPlan::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.subscriptionPlanDays.create', compact('subscription_plans'));
    }

    public function store(StoreSubscriptionPlanDayRequest $request)
    {
        $subscriptionPlanDay = SubscriptionPlanDay::create($request->all());

        return redirect()->route('admin.subscription-plan-days.index');
    }

    public function edit(SubscriptionPlanDay $subscriptionPlanDay)
    {
        abort_if(Gate::denies('subscription_plan_day_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscription_plans = SubcrptionPlan::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $subscriptionPlanDay->load('subscription_plans');

        return view('admin.subscriptionPlanDays.edit', compact('subscriptionPlanDay', 'subscription_plans'));
    }

    public function update(UpdateSubscriptionPlanDayRequest $request, SubscriptionPlanDay $subscriptionPlanDay)
    {
        $subscriptionPlanDay->update($request->all());

        return redirect()->route('admin.subscription-plan-days.index');
    }

    public function show(SubscriptionPlanDay $subscriptionPlanDay)
    {
        abort_if(Gate::denies('subscription_plan_day_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionPlanDay->load('subscription_plans');

        return view('admin.subscriptionPlanDays.show', compact('subscriptionPlanDay'));
    }

    public function destroy(SubscriptionPlanDay $subscriptionPlanDay)
    {
        abort_if(Gate::denies('subscription_plan_day_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionPlanDay->delete();

        return back();
    }

    public function massDestroy(MassDestroySubscriptionPlanDayRequest $request)
    {
        $subscriptionPlanDays = SubscriptionPlanDay::find(request('ids'));

        foreach ($subscriptionPlanDays as $subscriptionPlanDay) {
            $subscriptionPlanDay->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
