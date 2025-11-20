<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreSubscriptionCheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public endpoint, no authentication required
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
            'subcrption_plans_id' => [
                'required',
                'integer',
                'exists:subcrption_plans,id',
            ],
            'duration_id' => [
                'required',
                'integer',
                'exists:durations,id',
            ],
            'selected_days' => [
                'string',
                'nullable',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'payment' => [
                'nullable',
                'string',
                'in:pending,paid',
            ],
            'status' => [
                'nullable',
                'string',
                'in:active,inactive',
            ],
            'meals' => [
                'nullable',
                'array',
            ],
            'meals.*.day' => [
                'required_with:meals',
                'string',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ],
            'meals.*.meal_id' => [
                'required_with:meals',
                'integer',
                'exists:meals,id',
            ],
            'meals.*.type' => [
                'nullable',
                'string',
                'in:is meal,is snack',
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'subcrption_plans_id.required' => 'Subscription plan ID is required.',
            'subcrption_plans_id.exists' => 'The selected subscription plan does not exist.',
            'duration_id.required' => 'Duration ID is required.',
            'duration_id.exists' => 'The selected duration does not exist.',
            'start_date.required' => 'Start date is required.',
            'start_date.date_format' => 'Start date must be in Y-m-d format.',
        ];
    }
}

