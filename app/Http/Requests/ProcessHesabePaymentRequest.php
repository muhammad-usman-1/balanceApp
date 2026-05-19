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
            // Which payment method the user selected in the app
            'payment_method' => [
                'required',
                'string',
                'in:cash,debit_card,credit_card',
            ],

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

            // Card fields are only required when the user chose a card payment method.
            // When payment_method=cash all card fields are skipped entirely.
            'card_holder_name' => [
                'required_unless:payment_method,cash',
                'nullable',
                'string',
                'max:255',
            ],
            'card_number' => [
                'required_unless:payment_method,cash',
                'nullable',
                'string',
                'regex:/^[0-9]{13,19}$/',
            ],
            'card_expiry_month' => [
                'required_unless:payment_method,cash',
                'nullable',
                'string',
                'size:2',
            ],
            'card_expiry_year' => [
                'required_unless:payment_method,cash',
                'nullable',
                'string',
                'size:4',
            ],
            'card_cvv' => [
                'required_unless:payment_method,cash',
                'nullable',
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
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in'       => 'Payment method must be cash, debit_card, or credit_card.',

            'card_holder_name.required_unless' => 'Card holder name is required for card payments.',
            'card_number.required_unless'      => 'Card number is required for card payments.',
            'card_number.regex'                => 'Card number must be 13 to 19 digits.',
            'card_expiry_month.required_unless' => 'Expiry month is required for card payments.',
            'card_expiry_month.size'            => 'Expiry month must be in MM format (e.g. 06).',
            'card_expiry_year.required_unless'  => 'Expiry year is required for card payments.',
            'card_expiry_year.size'             => 'Expiry year must be in YYYY format (e.g. 2027).',
            'card_cvv.required_unless'          => 'CVV is required for card payments.',
            'card_cvv.regex'                    => 'CVV must be 3 or 4 digits.',
        ]);
    }
}
