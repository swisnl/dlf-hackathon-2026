<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Text generation runs through Claude Code on the developer's Mac, via the
    | Claude bridge (bin/claude-bridge.mjs). Tests fake the agents instead.
    |
    */

    'default' => 'claude-code',

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
        'claude-code' => [
            'driver' => 'claude-code',
            'url' => env('CLAUDE_BRIDGE_URL', 'http://host.docker.internal:8765'),
            'key' => env('CLAUDE_BRIDGE_TOKEN'),
            'models' => [
                'text' => [
                    'default' => env('CLAUDE_CODE_MODEL', 'sonnet'),
                ],
            ],
        ],
    ],
];
