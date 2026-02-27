<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenClaw Integration
    |--------------------------------------------------------------------------
    |
    | LunaOS polls OpenClaw for session activity. OpenClaw doesn't have native
    | outbound webhooks, so polling is the primary integration method.
    |
    */

    // OpenClaw Gateway URL (local instance)
    'openclaw_url' => env('OPENCLAW_URL', 'http://127.0.0.1:18789'),

    // LunaOS Base URL (Herd local domain)
    'base_url' => env('LUNAOS_URL', 'http://lunaos.test'),

    // OpenClaw Gateway auth token
    'openclaw_token' => env('OPENCLAW_TOKEN', ''),

    // Polling is the PRIMARY method (webhook would require OpenClaw core changes)
    'polling_enabled' => env('LUNAOS_POLLING_ENABLED', true),

    // How often to poll OpenClaw (seconds) - also tied to Activity Feed live mode
    'polling_interval' => env('LUNAOS_POLLING_INTERVAL', 30),

    /*
    |--------------------------------------------------------------------------
    | Agent Mapping
    |--------------------------------------------------------------------------
    |
    | Map OpenClaw session keys to agent names for activity logs.
    |
    */

    'agent_mapping' => [
        'main' => 'Luna',
        'code' => 'Subagent-A',
        'subagent-a' => 'Subagent-A',
        'subagent-b' => 'Subagent-B',
        'subagent-c' => 'Subagent-C',
    ],
];