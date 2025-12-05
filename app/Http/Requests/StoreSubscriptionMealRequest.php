<?php

namespace App\Http\Requests;

use App\Models\SubscriptionMeal;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreSubscriptionMealRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('subscription_meal_create');
    }

    public function rules()
    {
        return [
            'subscription_days_id' => [
                'required',
                'integer',
                'exists:subscription_days,id',
            ],
            'meal_id' => [
                'required',
                'integer',
                'exists:meals,id',
            ],
            'type' => [
                'required',
                'string',
                'in:is meal,is snack',
            ],
        ];
    }
}
