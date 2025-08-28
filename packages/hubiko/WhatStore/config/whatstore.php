<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatStore Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configurations for the WhatStore module.
    |
    */

    // Payment Gateway Configurations
    'payments' => [
        // Stripe Payment Gateway
        'stripe' => [
            'api_key' => env('WHATSTORE_STRIPE_KEY', ''),
            'api_secret' => env('WHATSTORE_STRIPE_SECRET', ''),
            'webhook_secret' => env('WHATSTORE_STRIPE_WEBHOOK_SECRET', ''),
            'test_mode' => env('WHATSTORE_STRIPE_TEST_MODE', true),
        ],

        // PayPal Payment Gateway
        'paypal' => [
            'client_id' => env('WHATSTORE_PAYPAL_CLIENT_ID', ''),
            'client_secret' => env('WHATSTORE_PAYPAL_CLIENT_SECRET', ''),
            'webhook_id' => env('WHATSTORE_PAYPAL_WEBHOOK_ID', ''),
            'test_mode' => env('WHATSTORE_PAYPAL_TEST_MODE', true),
            'currency' => env('WHATSTORE_PAYPAL_CURRENCY', 'USD'),
        ],
    ],

    // Email Configuration
    'email' => [
        'from_address' => env('WHATSTORE_EMAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'from_name' => env('WHATSTORE_EMAIL_FROM_NAME', env('MAIL_FROM_NAME')),
        'default_language' => env('WHATSTORE_EMAIL_DEFAULT_LANGUAGE', 'en'),
    ],

    // Webhook Configuration
    'webhooks' => [
        'verify_signature' => env('WHATSTORE_WEBHOOK_VERIFY', true),
        'log_payload' => env('WHATSTORE_WEBHOOK_LOG_PAYLOAD', true),
    ],

    // Social Media Configuration
    'social_media' => [
        'facebook_pixel' => env('WHATSTORE_FACEBOOK_PIXEL', ''),
        'google_analytics' => env('WHATSTORE_GOOGLE_ANALYTICS', ''),
        'twitter_pixel' => env('WHATSTORE_TWITTER_PIXEL', ''),
        'linkedin_pixel' => env('WHATSTORE_LINKEDIN_PIXEL', ''),
        'pinterest_pixel' => env('WHATSTORE_PINTEREST_PIXEL', ''),
        'tiktok_pixel' => env('WHATSTORE_TIKTOK_PIXEL', ''),
        'snapchat_pixel' => env('WHATSTORE_SNAPCHAT_PIXEL', ''),
    ],
]; 