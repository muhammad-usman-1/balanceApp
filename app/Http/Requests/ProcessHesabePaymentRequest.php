<?php

namespace App\Http\Requests;

class ProcessHesabePaymentRequest extends StoreSubscriptionCheckoutRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if ($this->has('save_card')) {
            $this->merge([
                'save_card' => filter_var($this->save_card, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            'amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'currency' => [
                'nullable',
                'string',
                'size:3',
            ],
            'card_holder_name' => [
                'required',
                'string',
                'max:255',
            ],
            'card_number' => [
                'required',
                'string',
                'regex:/^[0-9]{13,19}$/',
            ],
            'card_expiry_month' => [
                'required',
                'string',
                'size:2',
            ],
            'card_expiry_year' => [
                'required',
                'string',
                'size:4',
            ],
            'card_cvv' => [
                'required',
                'string',
                'regex:/^[0-9]{3,4}$/',
            ],
            'save_card' => [
                'sometimes',
                'boolean',
            ],
        ]);
    }

    public function messages()
    {
        return array_merge(parent::messages(), [
            'card_holder_name.required' => 'Card holder name is required.',
            'card_number.required' => 'Card number is required.',
            'card_number.regex' => 'Card number must be 13 to 19 digits.',
            'card_expiry_month.required' => 'Expiry month is required.',
            'card_expiry_month.size' => 'Expiry month must be in MM format.',
            'card_expiry_year.required' => 'Expiry year is required.',
            'card_expiry_year.size' => 'Expiry year must be in YYYY format.',
            'card_cvv.required' => 'CVV is required.',
            'card_cvv.regex' => 'CVV must be 3 or 4 digits.',
        ]);
    }
}

