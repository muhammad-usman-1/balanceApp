<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public endpoint, no authentication required
    }

    public function rules()
    {
        return [
            'phone_number' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'unique:users,mobile',
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
                'unique:users,email',
                'max:255',
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
        ];
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
        ];
    }
}

