<?php

// Al Rajhi Bank / Neoleap payment gateway — test vs live environments.
// Switch the active environment with ARB_MODE=test|live in .env. Endpoints
// default per environment; credentials are issued by ARB (test via onboarding
// mail, live via the merchant portal: Merchant process → View Plugin Details
// → View). The active set is flattened into services.arb so ArbGateway keeps
// reading tranportal_id / tranportal_password / resource_key / pg_url / admin_url.
$arbMode = env('ARB_MODE', 'test') === 'live' ? 'live' : 'test';

$arbEnvironments = [
    'test' => [
        'tranportal_id' => env('ARB_TEST_TRANPORTAL_ID', env('ARB_TRANPORTAL_ID')),
        'tranportal_password' => env('ARB_TEST_TRANPORTAL_PASSWORD', env('ARB_TRANPORTAL_PASSWORD')),
        'resource_key' => env('ARB_TEST_RESOURCE_KEY', env('ARB_RESOURCE_KEY')),
        'pg_url' => env('ARB_TEST_PG_URL', env('ARB_PG_URL', 'https://securepayments.neoleap.com.sa/pg/payment/hosted.htm')),
        'admin_url' => env('ARB_TEST_PG_ADMIN_URL', env('ARB_PG_ADMIN_URL', 'https://securepayments.neoleap.com.sa/pg/payment/tranportal.htm')),
    ],
    'live' => [
        'tranportal_id' => env('ARB_LIVE_TRANPORTAL_ID'),
        'tranportal_password' => env('ARB_LIVE_TRANPORTAL_PASSWORD'),
        'resource_key' => env('ARB_LIVE_RESOURCE_KEY'),
        'pg_url' => env('ARB_LIVE_PG_URL', 'https://digitalpayments.neoleap.com.sa/pg/payment/hosted.htm'),
        'admin_url' => env('ARB_LIVE_PG_ADMIN_URL', 'https://digitalpayments.neoleap.com.sa/pg/payment/tranportal.htm'),
    ],
];

$arbActive = $arbEnvironments[$arbMode];

return [

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_KEY'),
    ],

    // Firebase Cloud Messaging (worker push). Uses the FCM HTTP v1 API, which
    // authenticates with a Google service-account JSON (not a legacy server key).
    // Drop the service-account file on the server and point FCM_CREDENTIALS at it.
    'fcm' => [
        'project' => env('FCM_PROJECT_ID'),
        'credentials' => env('FCM_CREDENTIALS', storage_path('app/firebase/service-account.json')),
        // Android notification channel + sound the worker app defines for offers.
        'android_channel' => env('FCM_ANDROID_CHANNEL', 'offers'),
        'sound' => env('FCM_SOUND', 'bell'),
    ],
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

    'jawaly' => [
        'base_url' => env('JAWALY_BASE_URL', 'https://api-sms.4jawaly.com/api/v1/'),
        'app_id' => env('JAWALY_APP_ID'),
        'app_secret' => env('JAWALY_APP_SECRET'),
        'sender' => env('JAWALY_SENDER', 'Velto'),
    ],

    // Al Rajhi Bank / Neoleap payment gateway (REST, Bank Hosted). The active
    // environment (test|live) resolves from ARB_MODE above; its credentials and
    // endpoints are merged in here alongside the mode-independent settings.
    'arb' => array_merge($arbActive, [
        'mode' => $arbMode,                          // 'test' | 'live'
        'terminal_id' => env('ARB_TERMINAL_ID'),     // live: PG718400
        'merchant_id' => env('ARB_MERCHANT_ID'),     // live: 600006300
        'currency_code' => env('ARB_CURRENCY_CODE', '682'), // SAR
        // Public base URL ARB can reach for redirect/webhook (e.g. a tunnel in dev).
        'callback_base' => env('ARB_CALLBACK_BASE', env('APP_URL')),
        'environments' => $arbEnvironments,
    ]),

];
