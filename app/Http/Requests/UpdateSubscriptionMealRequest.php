<?php

namespace App\Http\Requests;

use App\Models\SubscriptionMeal;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateSubscriptionMealRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('subscription_meal_edit');
    }

    public function rules()
    {
        return [
            'subscription_plan_days_id' => [
                'required',
                'integer',
            ],
            'meal_id' => [
                'required',
                'integer',
            ],
            'type_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
