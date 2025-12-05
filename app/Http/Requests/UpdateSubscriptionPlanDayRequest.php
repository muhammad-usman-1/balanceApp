<?php

namespace App\Http\Requests;

use App\Models\SubscriptionDay;
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
            'user_subcrptions_id' => [
                'required',
                'integer',
                'exists:user_subcrptions,id',
            ],
            'day' => [
                'required',
                'string',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ],
        ];
    }
}
