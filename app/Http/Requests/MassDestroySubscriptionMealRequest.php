<?php

namespace App\Http\Requests;

use App\Models\SubscriptionMeal;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroySubscriptionMealRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('subscription_meal_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:subscription_meals,id',
        ];
    }
}
