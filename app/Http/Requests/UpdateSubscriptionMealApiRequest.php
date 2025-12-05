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
        return true; // Can be used with or without authentication
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
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
            'day' => [
                'required',
                'string',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
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
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'day.required' => 'Day is required.',
            'day.in' => 'Day must be one of: monday, tuesday, wednesday, thursday, friday, saturday, sunday.',
            'meal_id.required' => 'Meal ID is required.',
            'meal_id.exists' => 'The selected meal does not exist.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type must be either "is meal" or "is snack".',
            'subscription_meal_id.exists' => 'The selected subscription meal does not exist.',
        ];
    }
}

