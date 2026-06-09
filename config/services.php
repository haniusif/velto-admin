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

    'jawaly' => [
        'base_url' => env('JAWALY_BASE_URL', 'https://api-sms.4jawaly.com/api/v1/'),
        'app_id' => env('JAWALY_APP_ID'),
        'app_secret' => env('JAWALY_APP_SECRET'),
        'sender' => env('JAWALY_SENDER', 'Velto'),
    ],

    // Al Rajhi Bank / Neoleap payment gateway (REST, Bank Hosted). Credentials
    // are issued by ARB via onboarding mail / merchant portal.
    'arb' => [
        'tranportal_id' => env('ARB_TRANPORTAL_ID'),
        'tranportal_password' => env('ARB_TRANPORTAL_PASSWORD'),
        'resource_key' => env('ARB_RESOURCE_KEY'), // 32-char AES-256 key
        // Payment-token generation endpoint (sandbox differs — comes via mail).
        'pg_url' => env('ARB_PG_URL', 'https://securepayments.alrajhibank.com.sa/pg/payment/tranportal.htm'),
        // Inquiry/Void/Refund/Capture endpoint (defaults to the same host).
        'admin_url' => env('ARB_PG_ADMIN_URL'),
        'currency_code' => env('ARB_CURRENCY_CODE', '682'), // SAR
        // Public base URL ARB can reach for redirect/webhook (e.g. a tunnel in dev).
        'callback_base' => env('ARB_CALLBACK_BASE', env('APP_URL')),
    ],

];
