<?php

namespace App\Http\Requests;

/**
 * Validates the KNET redirect payment initiation request.
 * Extends the base subscription checkout request so all subscription
 * fields are validated together.
 */
class InitiatePaymentRequest extends StoreSubscriptionCheckoutRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'payment_method' => [
                'required',
                'string',
                'in:knet',
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
            'payment_method.in'       => 'Only knet is supported for this endpoint.',
        ]);
    }
}
