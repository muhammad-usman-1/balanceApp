<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class UpdateSubscriptionMealApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    public function rules()
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            // ID of the specific delivery day record (returned from GET /subscription/meals)
            'subscription_day_id' => [
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
            // Provide this to update an existing meal entry instead of creating a new one
            'subscription_meal_id' => [
                'nullable',
                'integer',
                'exists:subscription_meals,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required'             => 'User ID is required.',
            'user_id.exists'               => 'The selected user does not exist.',
            'subscription_day_id.required' => 'Subscription day ID is required.',
            'subscription_day_id.exists'   => 'The selected delivery day does not exist.',
            'meal_id.required'             => 'Meal ID is required.',
            'meal_id.exists'               => 'The selected meal does not exist.',
            'type.required'                => 'Type is required.',
            'type.in'                      => 'Type must be "is meal" or "is snack".',
        ];
    }
}
