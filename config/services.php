<?php

declare(strict_types=1);

return [

    

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'speech' => [
        'url' => env('SPEECH_SERVICE_URL', 'http://go-speech:8083'),
    ],

    'ai' => [
        'rabbitmq_timeout' => env('AI_RABBITMQ_TIMEOUT', 3.0),
        'rabbitmq_read_write_timeout' => env('AI_RABBITMQ_RW_TIMEOUT', 3.0),
        'rabbitmq_heartbeat' => env('AI_RABBITMQ_HEARTBEAT', 30),
    ],

    
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/api/v1/auth/google/callback'),
    ],

];
