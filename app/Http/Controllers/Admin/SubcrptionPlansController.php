<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySubcrptionPlanRequest;
use App\Http\Requests\StoreSubcrptionPlanRequest;
use App\Http\Requests\UpdateSubcrptionPlanRequest;
use App\Models\SubcrptionPlan;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubcrptionPlansController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('subcrption_plan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subcrptionPlans = SubcrptionPlan::orderBy('id')->paginate(25);

        return view('admin.subcrptionPlans.index', compact('subcrptionPlans'));
    }

    public function create()
    {
        abort_if(Gate::denies('subcrption_plan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.subcrptionPlans.create');
    }

    public function store(StoreSubcrptionPlanRequest $request)
    {
        $subcrptionPlan = SubcrptionPlan::create($request->all());

        return redirect()->route('admin.subcrption-plans.index');
    }

    public function edit(SubcrptionPlan $subcrptionPlan)
    {
        abort_if(Gate::denies('subcrption_plan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.subcrptionPlans.edit', compact('subcrptionPlan'));
    }

    public function update(UpdateSubcrptionPlanRequest $request, SubcrptionPlan $subcrptionPlan)
    {
        $subcrptionPlan->update($request->all());

        return redirect()->route('admin.subcrption-plans.index');
    }

    public function show(SubcrptionPlan $subcrptionPlan)
    {
        abort_if(Gate::denies('subcrption_plan_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.subcrptionPlans.show', compact('subcrptionPlan'));
    }

    public function destroy(SubcrptionPlan $subcrptionPlan)
    {
        abort_if(Gate::denies('subcrption_plan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subcrptionPlan->delete();

        return back();
    }

    public function massDestroy(MassDestroySubcrptionPlanRequest $request)
    {
        $subcrptionPlans = SubcrptionPlan::find(request('ids'));

        foreach ($subcrptionPlans as $subcrptionPlan) {
            $subcrptionPlan->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
