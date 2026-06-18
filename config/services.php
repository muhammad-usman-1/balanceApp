<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER', env('TWILIO_PHONE_NUMBER')),
        'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),
    ],

    'hesabe' => [
        'base_url' => env('HESABE_BASE_URL', 'https://sandbox.hesabe.com'),
        'merchant_code' => env('HESABE_MERCHANT_CODE'),
        'access_code' => env('HESABE_ACCESS_CODE'),
        'secret_key' => env('HESABE_SECRET_KEY'),
        'iv_key' => env('HESABE_IV_KEY'),
        'checkout_endpoint' => env('HESABE_CHECKOUT_ENDPOINT', '/checkout'),
        'payment_endpoint' => env('HESABE_PAYMENT_ENDPOINT', '/payment'),   // KNET redirect initiation
        'review_kits_endpoint' => env('HESABE_REVIEW_KITS_ENDPOINT', '/api/integration-kits/review'),
        'return_url' => env('HESABE_PAYMENT_RETURN_URL'),
        'failure_url' => env('HESABE_PAYMENT_FAILURE_URL'),
    ],

];
