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

    // Optional link thumbnail generation settings
    'link_thumbnail' => [
        // Template for a screenshot service that returns an image for a target URL.
        // Example (Browserless or similar service):
        // 'https://screenshot.example.com/api?url={url}&fullPage=false&width=1280&height=800'
        'screenshot_url_template' => env('LINK_THUMBNAIL_SCREENSHOT_URL_TEMPLATE', null),

        // Timeouts (seconds)
        'timeout' => env('LINK_THUMBNAIL_TIMEOUT', 10),
        'connect_timeout' => env('LINK_THUMBNAIL_CONNECT_TIMEOUT', 5),

        // Custom User Agent for fetching pages/images
        'user_agent' => env('LINK_THUMBNAIL_USER_AGENT', null),
    ],

];
