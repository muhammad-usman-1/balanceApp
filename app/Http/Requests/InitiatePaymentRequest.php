<?php

namespace App\Http\Requests;

class InitiatePaymentRequest extends StoreSubscriptionCheckoutRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'payment_method' => [
                'required',
                'string',
                'in:knet,credit_card,debit_card',
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
        ]);
    }

    public function messages()
    {
        return array_merge(parent::messages(), [
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in'       => 'Payment method must be knet, credit_card, or debit_card.',
        ]);
    }
}
