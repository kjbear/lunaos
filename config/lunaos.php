<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    'auth_username' => env('LUNAOS_AUTH_USERNAME', 'kyle'),
    'auth_password' => env('LUNAOS_AUTH_PASSWORD', 'kobear'),

    /*
    |--------------------------------------------------------------------------
    | OpenClaw Integration
    |--------------------------------------------------------------------------
    */

    'openclaw_url' => env('OPENCLAW_URL', 'http://127.0.0.1:18789'),
    'base_url' => env('LUNAOS_URL', 'http://lunaos.test'),
    'openclaw_token' => env('OPENCLAW_TOKEN', ''),
    'polling_enabled' => env('LUNAOS_POLLING_ENABLED', true),
    'polling_interval' => env('LUNAOS_POLLING_INTERVAL', 30),

    /*
    |--------------------------------------------------------------------------
    | Agent Mapping
    |--------------------------------------------------------------------------
    */

    'agent_mapping' => [
        'main' => 'Luna',
        'code' => 'Subagent-A',
        'subagent-a' => 'Subagent-A',
        'subagent-b' => 'Subagent-B',
        'subagent-c' => 'Subagent-C',
    ],
];
