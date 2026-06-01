<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default WhatsApp Connection
    |--------------------------------------------------------------------------
    |
    | The default connection to use when no connection is explicitly specified.
    |
    */
    'default' => env('WHATSAPP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Connections
    |--------------------------------------------------------------------------
    |
    | Each connection represents a phone number / business account.
    | Supports multiple WhatsApp Business Accounts.
    |
    */
    'connections' => [
        'default' => [
            'api_token' => env('WHATSAPP_API_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'waba_id' => env('WHATSAPP_WABA_ID'),
            'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
            'app_secret' => env('WHATSAPP_APP_SECRET'),
            'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),

            /*
            | API version (default: v22.0)
            */
            'api_version' => env('WHATSAPP_API_VERSION', 'v22.0'),

            /*
            | Graph API base URL
            */
            'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),

            /*
            | HTTP client settings
            */
            'timeout' => env('WHATSAPP_TIMEOUT', 10),
            'retry_on_throttle' => env('WHATSAPP_RETRY_ON_THROTTLE', true),
            'max_retries' => env('WHATSAPP_MAX_RETRIES', 3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for incoming webhook event handling.
    |
    */
    'webhook' => [
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        'events' => [
            'message_received' => true,
            'message_sent' => true,
            'message_delivered' => true,
            'message_read' => true,
            'message_failed' => true,
            'account_update' => true,
        ],
    ],

];
