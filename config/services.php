<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicios de Terceros
    |--------------------------------------------------------------------------
    |
    | Este archivo es para almacenar las credenciales de servicios de terceros
    | como Mailgun, Postmark, AWS y más. Este archivo proporciona un lugar
    | razonable por defecto para este tipo de información, permitiendo
    | que los paquetes tengan un lugar convencional para buscar credenciales.
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/api/auth/google/callback'),
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],

    'nosql' => [
        'driver' => env('NOSQL_LOG_DRIVER', 'redis'),
        'mongodb' => [
            'host' => env('MONGODB_HOST', 'mongodb'),
            'port' => env('MONGODB_PORT', '27017'),
            'database' => env('MONGODB_DATABASE', 'laravel_logs'),
            'username' => env('MONGODB_USERNAME', 'admin'),
            'password' => env('MONGODB_PASSWORD', 'secret'),
        ],
    ],

    'livekit' => [
        'api_key' => env('LIVEKIT_API_KEY', 'devkey'),
        'api_secret' => env('LIVEKIT_API_SECRET', 'secret'),
        'url' => env('LIVEKIT_URL', 'ws://localhost:7880'),
    ],

    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

];
