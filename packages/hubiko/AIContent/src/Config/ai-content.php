<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),
    
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-3.5-turbo'),
            'models' => [
                'gpt-3.5-turbo' => ['max_tokens' => 4096, 'cost_per_1k_tokens' => 0.002],
                'gpt-4' => ['max_tokens' => 8192, 'cost_per_1k_tokens' => 0.03],
                'gpt-4-turbo' => ['max_tokens' => 128000, 'cost_per_1k_tokens' => 0.01],
            ]
        ],
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
            'default_model' => env('DEEPSEEK_DEFAULT_MODEL', 'deepseek-chat'),
            'models' => [
                'deepseek-chat' => ['max_tokens' => 4096, 'cost_per_1k_tokens' => 0.0014],
                'deepseek-coder' => ['max_tokens' => 4096, 'cost_per_1k_tokens' => 0.0014],
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Generation Settings
    |--------------------------------------------------------------------------
    */
    'max_tokens' => [
        'short' => 500,
        'medium' => 1000,
        'long' => 2000,
    ],

    'temperature' => [
        'professional' => 0.3,
        'formal' => 0.2,
        'informative' => 0.4,
        'casual' => 0.7,
        'friendly' => 0.6,
        'persuasive' => 0.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    */
    'export' => [
        'formats' => ['pdf', 'docx', 'txt', 'html'],
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'expiry_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'daily_generations' => env('AI_DAILY_LIMIT', 100),
        'monthly_tokens' => env('AI_MONTHLY_TOKEN_LIMIT', 100000),
    ],
];
