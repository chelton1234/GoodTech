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

    'debitopay' => [
        'base_url' => env('DBITO_PAY_BASE_URL', 'https://gyqoaningqhurhvdugne.supabase.co/functions/v1'),
        'api_key' => env('DBITO_PAY_API_KEY', ''),
        'merchant_id' => env('DBITO_PAY_MERCHANT_ID', ''),
        'wallet_code' => env('DBITO_PAY_WALLET_CODE', ''),
        'wallet_mpesa' => env('DBITO_PAY_WALLET_MPESA', ''),
        'wallet_emola' => env('DBITO_PAY_WALLET_EMOLA', ''),
        'wallet_mkesh' => env('DBITO_PAY_WALLET_MKESH', ''),
        'wallet_card' => env('DBITO_PAY_WALLET_CARD', ''),
        'wallet_payfast' => env('DBITO_PAY_WALLET_PAYFAST', ''),
        'webhook_secret' => env('DBITO_PAY_WEBHOOK_SECRET', ''),
        'zar_rate' => env('DBITO_PAY_ZAR_RATE', 3.60),
    ],

];
