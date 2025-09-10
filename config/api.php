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

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    | NOTE:
    | - نحن نستخدم ثواني (Thawani) كمزوّد دفع افتراضي.
    | - مفاتيح البيئة هنا مأخوذة من ثواني مباشرة:
    |     THAWANI_SECRET_KEY, THAWANI_PUBLISHABLE_KEY, THAWANI_BASE_URL,
    |     THAWANI_SUCCESS_URL, THAWANI_CANCEL_URL, THAWANI_WEBHOOK_SECRET (اختياري)
    | - كود الـ Laravel عندنا يعتمد فعلياً على config('services.thawani.*') في ThawaniService،
    |   وهذا البلوك الهدف منه توحيد الإعدادات العامة للتطبيق فقط (إن احتجتِه لاحقاً).
    */

    'payment' => [
        // استخدمي "thawani" كمزوّد افتراضي
        'provider'       => env('PAYMENT_PROVIDER', 'thawani'),

        // مفاتيح وقيم ثواني (تعكس نفس القيم في config/services.php)
        'secret_key'     => env('THAWANI_SECRET_KEY'),
        'public_key'     => env('THAWANI_PUBLISHABLE_KEY'),

        // قاعدة الـ API لثواني (UAT/Production)
        'base_url'       => env('THAWANI_BASE_URL', 'https://uatcheckout.thawani.om/api/v1'),

        // روابط النجاح/الإلغاء (يجب أن تكون https ويمكن الوصول لها من الإنترنت)
        'success_url'    => env('THAWANI_SUCCESS_URL'),
        'cancel_url'     => env('THAWANI_CANCEL_URL'),

        // (اختياري) سر التحقق من Webhook إن كنتِ ستفعّلين استقبال الويبهوك
        'webhook_secret' => env('THAWANI_WEBHOOK_SECRET'),
    ],

    'notifications' => [
        'whatsapp' => [
            'enabled'      => env('WHATSAPP_ENABLED', false),
            'api_url'      => env('WHATSAPP_API_URL'),
            'token'        => env('WHATSAPP_API_TOKEN'),
            'phone_number' => env('WHATSAPP_PHONE_NUMBER'),
        ],
        'email' => [
            'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
        ],
    ],

    'rate_limiting' => [
        'auth'      => env('RATE_LIMIT_AUTH', '60,1'),      // 60 requests per minute
        'donations' => env('RATE_LIMIT_DONATIONS', '10,1'), // 10 requests per minute
    ],

    'file_uploads' => [
        'max_size'      => 10240, // 10MB
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
        'storage_path'  => 'students',
    ],
];
