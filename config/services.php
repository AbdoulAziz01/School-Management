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

    /*
    |--------------------------------------------------------------------------
    | School Bot (Botpress) — API JSON Bearer
    |--------------------------------------------------------------------------
    |
    */
    'school_bot' => [
        'secret' => env('SCHOOL_BOT_SECRET'),
        'passing_grade_min' => env('SCHOOL_BOT_PASSING_GRADE_MIN', 10.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | UltraMsg — Gateway WhatsApp (notifications parents inclusives)
    |--------------------------------------------------------------------------
    | ULTRAMSG_INSTANCE_ID  → ID de l'instance UltraMsg (ex: instance12345)
    | ULTRAMSG_TOKEN        → Token API UltraMsg
    |
    | Pour Twilio : remplacer le endpoint dans NotificationService.php
    */
    'ultramsg' => [
        'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
        'token'       => env('ULTRAMSG_TOKEN'),
        // Coupe-circuit temporaire : désactive l'envoi auto des messages WhatsApp aux parents.
        'notify_parents_enabled' => env('NOTIFY_PARENTS_ENABLED', false),
    ],

];
