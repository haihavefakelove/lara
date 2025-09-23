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

    'momo' => [
        'endpoint'     => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'partner_code' => env('MOMO_PARTNER_CODE', ''),
        'access_key'   => env('MOMO_ACCESS_KEY', ''),
        'secret_key'   => env('MOMO_SECRET_KEY', ''),
        'redirect_url' => env('MOMO_REDIRECT_URL', 'http://127.0.0.1:8000/momo/callback'),
        'ipn_url'      => env('MOMO_IPN_URL', 'http://127.0.0.1:8000/momo/ipn'),
    ],
    'tawk' => [
       
        // Cách 2: tách id (nếu bạn thích)
        'property_id' => env('TAWK_PROPERTY_ID'), // 68cf50f44648ec19228c19f0
        'widget_id'   => env('TAWK_WIDGET_ID'),   // 1j5kt8evm

        // Bật/tắt widget nhanh
        'enabled'     => env('TAWK_ENABLED', true),

        // Tuỳ chọn: SSO/Identity hash (nâng cao – có thì set, không có có thể bỏ)
        'sso_key'     => env('TAWK_SSO_KEY'),
    ],
];
