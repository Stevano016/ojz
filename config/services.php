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

    'ozj_sheets' => [
        'tickets_csv_url' => env('GOOGLE_SHEETS_TICKETS_CSV_URL'),
    ],

    'google' => [
        'credentials_path' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        'tickets_range' => env('GOOGLE_SHEETS_TICKETS_RANGE', "'A1'!A1:AH"),
        'sync_enabled' => env('GOOGLE_SHEETS_SYNC_ENABLED', true),
        'http_timeout' => env('GOOGLE_HTTP_TIMEOUT', 45),
        'connect_timeout' => env('GOOGLE_CONNECT_TIMEOUT', 25),
    ],

    'n8n' => [
        'signal_webhook_url' => env('N8N_SIGNAL_WEBHOOK_URL'),
    ],

];
