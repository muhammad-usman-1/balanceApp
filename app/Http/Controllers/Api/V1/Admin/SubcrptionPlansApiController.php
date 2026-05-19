<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubcrptionPlanRequest;
use App\Http\Requests\UpdateSubcrptionPlanRequest;
use App\Http\Resources\Admin\SubcrptionPlanResource;
use App\Models\SubcrptionPlan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubcrptionPlansApiController extends Controller
{
    public function index()
    {
        return SubcrptionPlanResource::collection(
            SubcrptionPlan::where('is_active', true)->get()
        );
    }

    public function store(StoreSubcrptionPlanRequest $request)
    {
        $subcrptionPlan = SubcrptionPlan::create($request->all());

        return (new SubcrptionPlanResource($subcrptionPlan))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(SubcrptionPlan $subcrptionPlan)
    {
        return new SubcrptionPlanResource($subcrptionPlan);
    }

    public function update(UpdateSubcrptionPlanRequest $request, SubcrptionPlan $subcrptionPlan)
    {
        $subcrptionPlan->update($request->all());

        return (new SubcrptionPlanResource($subcrptionPlan))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(SubcrptionPlan $subcrptionPlan)
    {
        $subcrptionPlan->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
