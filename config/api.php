<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Student Welfare Fund API
    |
    */

    'version' => 'v1',
    
    'pagination' => [
        'default_per_page' => 10,
        'max_per_page' => 100,
    ],

    'donations' => [
        'expiry_hours' => 24,
        'idempotency_window_minutes' => 5,
    ],

    'payment' => [
        'provider' => env('PAYMENT_PROVIDER', 'stripe'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
        'public_key' => env('PAYMENT_PUBLIC_KEY'),
        'secret_key' => env('PAYMENT_SECRET_KEY'),
    ],

    'notifications' => [
        'whatsapp' => [
            'enabled' => env('WHATSAPP_ENABLED', false),
            'api_url' => env('WHATSAPP_API_URL'),
            'token' => env('WHATSAPP_API_TOKEN'),
            'phone_number' => env('WHATSAPP_PHONE_NUMBER'),
        ],
        'email' => [
            'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
        ],
    ],

    'rate_limiting' => [
        'auth' => env('RATE_LIMIT_AUTH', '60,1'), // 60 requests per minute
        'donations' => env('RATE_LIMIT_DONATIONS', '10,1'), // 10 requests per minute
    ],

    'file_uploads' => [
        'max_size' => 10240, // 10MB
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
        'storage_path' => 'students',
    ],
];
