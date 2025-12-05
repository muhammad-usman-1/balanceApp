<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StoreSubscriptionCheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public endpoint, no authentication required
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Normalize is_personalized to boolean
        if ($this->has('is_personalized')) {
            $this->merge([
                'is_personalized' => filter_var($this->is_personalized, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        $selectedDays = $this->input('selected_days');
        if (is_string($selectedDays)) {
            $days = array_filter(array_map('trim', explode(',', $selectedDays)));
            $this->merge(['selected_days' => $days]);
        }

        $address = $this->input('address');
        if (is_array($address)) {
            if (array_key_exists('is_primary', $address)) {
                $address['is_primary'] = filter_var($address['is_primary'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
            $this->merge(['address' => $address]);
        }
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
                'nullable',
                'array',
            ],
            'selected_days.*' => [
                'string',
                Rule::in(array_keys(\App\Models\SubscriptionDay::DAY_SELECT)),
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
            'is_personalized' => [
                'nullable',
                'boolean',
            ],
            'protein' => [
                'required_if:is_personalized,true',
                'nullable',
                'numeric',
                'min:0',
            ],
            'carbs' => [
                'required_if:is_personalized,true',
                'nullable',
                'numeric',
                'min:0',
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
            'address' => [
                'required',
                'array',
            ],
            'address.first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'address.last_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.area' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.block_number' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.street' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.house_building' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.floor_apartment' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.phone_number' => [
                'required',
                'string',
                'max:20',
            ],
            'address.remarks' => [
                'nullable',
                'string',
            ],
            'address.category' => [
                'required',
                'string',
                Rule::in(['home', 'office']),
            ],
            'address.is_primary' => [
                'nullable',
                'boolean',
            ],
            'address.preferred_delivery_slot' => [
                'required',
                'string',
                Rule::in(['four_pm_to_eight_pm', 'eight_pm_to_midnight']),
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
            'protein.required_if' => 'Protein value is required when personalized plan is selected.',
            'protein.numeric' => 'Protein must be a valid number.',
            'protein.min' => 'Protein must be greater than or equal to 0.',
            'carbs.required_if' => 'Carbs value is required when personalized plan is selected.',
            'carbs.numeric' => 'Carbs must be a valid number.',
            'carbs.min' => 'Carbs must be greater than or equal to 0.',
            'selected_days.array' => 'Selected days must be provided as an array or comma-separated list.',
            'selected_days.*.in' => 'Selected days must be valid weekdays.',
            'address.required' => 'Address information is required.',
            'address.first_name.required' => 'First name is required for the address.',
            'address.phone_number.required' => 'Address phone number is required.',
            'address.category.required' => 'Address category is required.',
            'address.category.in' => 'Address category must be home or office.',
            'address.preferred_delivery_slot.required' => 'Preferred delivery time is required.',
            'address.preferred_delivery_slot.in' => 'Preferred delivery time must be four_pm_to_eight_pm or eight_pm_to_midnight.',
        ];
    }
}

