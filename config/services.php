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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'imagekit' => [
        'public_key' => env('IMAGEKIT_PUBLIC_KEY'),
        'private_key' => env('IMAGEKIT_PRIVATE_KEY'),
        'upload_url' => env('IMAGEKIT_UPLOAD_URL', 'https://upload.imagekit.io/api/v1/files/upload'),
        'folder' => env('IMAGEKIT_FOLDER', '/180dc'),
        'allowed_url_hosts' => array_filter(explode(',', (string) env('IMAGEKIT_ALLOWED_URL_HOSTS', 'ik.imagekit.io,media.imagekit.io'))),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'base_url' => env('MIDTRANS_BASE_URL', 'https://api.sandbox.midtrans.com'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'payments' => [
        'ttl_hours' => (int) env('PAYMENTS_TTL_HOURS', 24),
        'manual' => [
            'bank' => env('MANUAL_BANK_NAME'),
            'account_number' => env('MANUAL_BANK_ACCOUNT_NUMBER'),
            'account_name' => env('MANUAL_BANK_ACCOUNT_NAME'),
        ],
    ],

    'orders' => [
        'expiry_hours' => (int) env('ORDERS_EXPIRY_HOURS', 24),
    ],

];
