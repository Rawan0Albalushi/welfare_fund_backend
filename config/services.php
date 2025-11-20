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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'thawani' => [
        'secret_key' => env('THAWANI_SECRET_KEY'),
        'publishable_key' => env('THAWANI_PUBLISHABLE_KEY'),
        'base_url'   => env('THAWANI_BASE_URL', 'https://uatcheckout.thawani.om/api/v1'),
        // استخدام config('app.url') بدلاً من IPs مكودة
        'success_url' => env('THAWANI_SUCCESS_URL', null),
        'cancel_url' => env('THAWANI_CANCEL_URL', null),
		'webhook_secret' => env('THAWANI_WEBHOOK_SECRET'),
		'webhook_signature_header' => env('THAWANI_WEBHOOK_SIGNATURE_HEADER', 'X-Webhook-Signature'),
    ],

];
