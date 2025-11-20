<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'unique:users,email',
                'max:255',
            ],
            'mobile' => [
                'required',
                'integer',
                'unique:users,mobile',
                'min:-2147483648',
                'max:2147483647',
            ],
            'otp' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
            'roles' => [
                'nullable',
                'array',
            ],
            'gender' => [
                'string',
                'nullable',
                'in:male,female,other',
            ],
            'height' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'dob' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.unique' => 'This mobile number is already registered.',
            'otp.required' => 'OTP is required.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
