<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckUserExistsRequest extends FormRequest
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
            ],
            'otp' => [
                'required',
                'integer',
                'min:1000',
                'max:999999',
            ],
        ];
    }

    public function messages()
    {
        return [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must contain only digits.',
            'otp.required' => 'OTP is required.',
            'otp.integer' => 'OTP must be a number.',
            'otp.min' => 'OTP must be at least 4 digits.',
            'otp.max' => 'OTP must not exceed 6 digits.',
        ];
    }
}

