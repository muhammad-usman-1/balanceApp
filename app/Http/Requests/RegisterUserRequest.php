<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public endpoint, no authentication required
    }

    public function rules()
    {
        $userId = $this->getUserIdByPhone();

        return [
            'phone_number' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'exists:users,mobile',
            ],
            'otp' => [
                'required',
                'integer',
                'min:1000',
                'max:999999',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'date_of_birth' => [
                'required',
                'date_format:Y-m-d',
            ],
            'gender' => [
                'required',
                'string',
                'in:male,female,other',
            ],
            'height' => [
                'required',
                'numeric',
                'min:0',
            ],
            'weight' => [
                'required',
                'numeric',
                'min:0',
            ],
            'goal' => [
                'required',
                'string',
                Rule::in(['eat_healthy', 'lose_weight', 'gain_weight', 'build_muscle', 'maintain_weight']),
            ],
            'activity_level' => [
                'required',
                'string',
                Rule::in(['sedentary', 'lightly_active', 'very_active', 'highly_active']),
            ],
            'has_food_allergies' => [
                'required',
                'boolean',
            ],
            'affiliated_code' => [
                'nullable',
                'string',
                'max:50',
                'exists:affiliated_codes,code',
            ],
            'allergies' => [
                Rule::requiredIf($this->boolean('has_food_allergies')),
                'nullable',
                'array',
                'min:1',
            ],
            'allergies.*' => [
                'string',
                'max:255',
            ],
        ];
    }

    private function getUserIdByPhone(): ?int
    {
        $phone = $this->input('phone_number');
        if (! $phone) {
            return null;
        }

        $user = User::where('mobile', (int) $phone)->first();

        return $user?->id;
    }

    public function messages()
    {
        return [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must contain only digits.',
            'phone_number.unique' => 'This phone number is already registered.',
            'otp.required' => 'OTP is required.',
            'otp.integer' => 'OTP must be a number.',
            'otp.min' => 'OTP must be at least 4 digits.',
            'otp.max' => 'OTP must not exceed 6 digits.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'name.required' => 'Name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.date_format' => 'Date of birth must be in Y-m-d format (e.g., 1990-01-15).',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be one of: male, female, or other.',
            'height.required' => 'Height is required.',
            'height.numeric' => 'Height must be a number.',
            'height.min' => 'Height must be a positive number.',
            'weight.required' => 'Weight is required.',
            'weight.numeric' => 'Weight must be a number.',
            'weight.min' => 'Weight must be a positive number.',
            'goal.required' => 'Please select a goal.',
            'goal.in' => 'Goal must be one of: eat_healthy, lose_weight, gain_weight, build_muscle, maintain_weight.',
            'activity_level.required' => 'Activity level is required.',
            'activity_level.in' => 'Activity level must be one of: sedentary, lightly_active, very_active, highly_active.',
            'has_food_allergies.required' => 'Please indicate whether you have food allergies.',
            'has_food_allergies.boolean' => 'Food allergy response must be true or false.',
            'allergies.required' => 'Please select at least one allergy option.',
            'allergies.array' => 'Allergies must be provided as an array.',
            'allergies.min' => 'Select at least one allergy.',
            'allergies.*.string' => 'Each allergy entry must be a string value.',
            'allergies.*.max' => 'Allergy entries may not exceed 255 characters.',
        ];
    }
}

