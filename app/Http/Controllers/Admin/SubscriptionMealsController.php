<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySubscriptionMealRequest;
use App\Http\Requests\StoreSubscriptionMealRequest;
use App\Http\Requests\UpdateSubscriptionMealRequest;
use App\Models\Meal;
use App\Models\SubscriptionMeal;
use App\Models\SubscriptionPlanDay;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMealsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('subscription_meal_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionMeals = SubscriptionMeal::with(['subscription_plan_days', 'meal', 'type'])->get();

        return view('admin.subscriptionMeals.index', compact('subscriptionMeals'));
    }

    public function create()
    {
        abort_if(Gate::denies('subscription_meal_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscription_plan_days = SubscriptionPlanDay::pluck('day', 'id')->prepend(trans('global.pleaseSelect'), '');

        $meals = Meal::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $types = Meal::pluck('type', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.subscriptionMeals.create', compact('meals', 'subscription_plan_days', 'types'));
    }

    public function store(StoreSubscriptionMealRequest $request)
    {
        $subscriptionMeal = SubscriptionMeal::create($request->all());

        return redirect()->route('admin.subscription-meals.index');
    }

    public function edit(SubscriptionMeal $subscriptionMeal)
    {
        abort_if(Gate::denies('subscription_meal_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscription_plan_days = SubscriptionPlanDay::pluck('day', 'id')->prepend(trans('global.pleaseSelect'), '');

        $meals = Meal::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $types = Meal::pluck('type', 'id')->prepend(trans('global.pleaseSelect'), '');

        $subscriptionMeal->load('subscription_plan_days', 'meal', 'type');

        return view('admin.subscriptionMeals.edit', compact('meals', 'subscriptionMeal', 'subscription_plan_days', 'types'));
    }

    public function update(UpdateSubscriptionMealRequest $request, SubscriptionMeal $subscriptionMeal)
    {
        $subscriptionMeal->update($request->all());

        return redirect()->route('admin.subscription-meals.index');
    }

    public function show(SubscriptionMeal $subscriptionMeal)
    {
        abort_if(Gate::denies('subscription_meal_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionMeal->load('subscription_plan_days', 'meal', 'type');

        return view('admin.subscriptionMeals.show', compact('subscriptionMeal'));
    }

    public function destroy(SubscriptionMeal $subscriptionMeal)
    {
        abort_if(Gate::denies('subscription_meal_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subscriptionMeal->delete();

        return back();
    }

    public function massDestroy(MassDestroySubscriptionMealRequest $request)
    {
        $subscriptionMeals = SubscriptionMeal::find(request('ids'));

        foreach ($subscriptionMeals as $subscriptionMeal) {
            $subscriptionMeal->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
