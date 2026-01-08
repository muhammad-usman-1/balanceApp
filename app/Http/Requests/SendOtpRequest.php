<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'regex:/^\+?[0-9]{1,4}$/'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{4,14}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'country_code.required' => 'Country code is required.',
            'country_code.regex' => 'Country code must be 1 to 4 digits.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must be 4 to 14 digits.',
        ];
    }
}
