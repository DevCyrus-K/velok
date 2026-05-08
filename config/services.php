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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'kwikshift' => [
        'root' => env('KWIKSHIFT_ROOT_PATH', realpath(base_path('../kwikshift')) ?: base_path('../kwikshift')),
    ],

    'google_analytics' => [
        'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
        'credentials_path' => env('GOOGLE_ANALYTICS_CREDENTIALS_PATH', env('GOOGLE_APPLICATION_CREDENTIALS')),
        'credentials_json' => env('GOOGLE_ANALYTICS_CREDENTIALS_JSON'),
        'client_email' => env('GOOGLE_ANALYTICS_CLIENT_EMAIL'),
        'private_key_id' => env('GOOGLE_ANALYTICS_PRIVATE_KEY_ID'),
        'private_key' => env('GOOGLE_ANALYTICS_PRIVATE_KEY'),
        'cache_ttl' => env('GOOGLE_ANALYTICS_CACHE_TTL', 900),
        'timeout' => env('GOOGLE_ANALYTICS_TIMEOUT', 10),
    ],

];
