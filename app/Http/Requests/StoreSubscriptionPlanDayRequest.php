<?php

namespace App\Http\Requests;

use App\Models\SubscriptionPlanDay;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreSubscriptionPlanDayRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('subscription_plan_day_create');
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
