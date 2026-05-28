<?php

namespace App\Http\Requests;

use App\Models\SubcrptionPlan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StoreSubscriptionCheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
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
            // User selects an area — branch is auto-resolved by the server
            'area_id' => [
                'required',
                'integer',
                'exists:areas,id',
            ],
            'selected_days' => [
                'required',
                'array',
                'min:1',
            ],
            'selected_days.*' => [
                'string',
                Rule::in(array_keys(\App\Models\SubscriptionDay::DAY_SELECT)),
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
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
                Rule::in(array_keys(\App\Models\SubscriptionDay::DAY_SELECT)),
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
            'address.delivery_notes' => [
                'nullable',
                'string',
                'max:1000',
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

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $v) {
            // Validate plan
            $planId = $this->input('subcrption_plans_id');
            if ($planId) {
                $plan = SubcrptionPlan::find($planId);
                if ($plan) {
                    if (! $plan->is_active) {
                        $v->errors()->add('subcrption_plans_id', 'The selected subscription plan is not currently available.');
                    } else {
                        $selectedDays = $this->input('selected_days', []);
                        if (is_array($selectedDays)) {
                            $dayCount = count(array_unique($selectedDays));
                            if ($plan->min_days && $dayCount < $plan->min_days) {
                                $v->errors()->add('selected_days', "This plan requires at least {$plan->min_days} day(s) per week.");
                            }
                            if ($plan->max_days && $dayCount > $plan->max_days) {
                                $v->errors()->add('selected_days', "This plan allows a maximum of {$plan->max_days} day(s) per week.");
                            }
                        }
                        if (empty($plan->no_of_weeks) || $plan->no_of_weeks < 1) {
                            $v->errors()->add('subcrption_plans_id', 'The selected plan has no duration configured. Please contact support.');
                        }
                    }
                }
            }

            // Validate area is active and has a linked branch
            $areaId = $this->input('area_id');
            if ($areaId) {
                $area = \App\Models\Area::with('branches')->find($areaId);
                if ($area) {
                    if ($area->status !== 'active') {
                        $v->errors()->add('area_id', 'The selected area is not currently available for delivery.');
                    } elseif ($area->branches->where('status', 'active')->isEmpty()) {
                        $v->errors()->add('area_id', 'No active branch is assigned to the selected area. Please contact support.');
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'subcrption_plans_id.required' => 'Subscription plan ID is required.',
            'subcrption_plans_id.exists' => 'The selected subscription plan does not exist.',
            'area_id.required' => 'Please select a delivery area.',
            'area_id.exists' => 'The selected delivery area does not exist.',
            'selected_days.required' => 'Please select at least one delivery day.',
            'selected_days.min' => 'Please select at least one delivery day.',
            'selected_days.*.in' => 'One or more selected days are invalid.',
            'start_date.required' => 'Start date is required.',
            'start_date.date_format' => 'Start date must be in Y-m-d format (e.g. 2026-05-20).',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'protein.required_if' => 'Protein value is required for a personalized plan.',
            'protein.numeric' => 'Protein must be a valid number.',
            'carbs.required_if' => 'Carbs value is required for a personalized plan.',
            'carbs.numeric' => 'Carbs must be a valid number.',
            'address.required' => 'Delivery address is required.',
            'address.first_name.required' => 'First name is required for the delivery address.',
            'address.phone_number.required' => 'Phone number is required for the delivery address.',
            'address.category.required' => 'Address category is required.',
            'address.category.in' => 'Address category must be "home" or "office".',
            'address.preferred_delivery_slot.required' => 'Preferred delivery time slot is required.',
            'address.preferred_delivery_slot.in' => 'Delivery slot must be "four_pm_to_eight_pm" or "eight_pm_to_midnight".',
        ];
    }
}
