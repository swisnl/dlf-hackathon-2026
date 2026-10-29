<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | All text generation runs through our LiteLLM proxy, which exposes an
    | OpenAI-compatible API in front of the actual models.
    |
    */

    'default' => 'litellm',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
            'individually' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'litellm' => [
            'driver' => 'openai-compatible',
            'url' => env('LITELLM_URL'),
            'key' => env('LITELLM_API_KEY'),
            'models' => [
                'text' => [
                    'default' => env('LITELLM_MODEL'),
                ],
            ],
        ],
    ],
];
