<?php

namespace App\Http\Requests;

use App\Models\SubcrptionPlan;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroySubcrptionPlanRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('subcrption_plan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:subcrption_plans,id',
        ];
    }
}
