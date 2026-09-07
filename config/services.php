<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // AI provider abstraction (execution plan section 6). "null" means no
    // AI capability is wired up; App\Providers\IntegrationServiceProvider
    // binds App\AI\NullAIProvider until a real provider is configured.
    'ai' => [
        'provider' => env('AI_PROVIDER', 'null'),
        'openai_api_key' => env('OPENAI_API_KEY'),
    ],

    // HubSpot remains the CRM system of record; disabled until Phase 3+.
    'hubspot' => [
        'enabled' => env('HUBSPOT_ENABLED', false),
        'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
    ],

    // Gmail (send) and Google Calendar (scheduling); disabled until Phase 3/4.
    'google' => [
        'enabled' => env('GOOGLE_WORKSPACE_ENABLED', false),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

];
