<?php

namespace App\Http\Requests;

use App\Models\SubscriptionPlanDay;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateSubscriptionPlanDayRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('subscription_plan_day_edit');
    }

    public function rules()
    {
        return [
            'subscription_plans_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
