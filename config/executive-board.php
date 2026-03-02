<?php

/**
 * Executive Board Configuration
 * 
 * Configuration for the AI-powered executive board debate system.
 * Defines personas, models, rounds, timeouts, and other settings.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Executive Board Personas
    |--------------------------------------------------------------------------
    |
    | Define which executive personas participate in board debates.
    | Order matters - they will respond in this sequence.
    |
    */

    'personas' => [
        [
            'class' => \App\Agents\Personas\COOPersona::class,
            'model' => 'glm-5',
            'enabled' => true,
        ],
        [
            'class' => \App\Agents\Personas\CFOPersona::class,
            'model' => 'glm-5',
            'enabled' => true,
        ],
        [
            'class' => \App\Agents\Personas\CTOPersona::class,
            'model' => 'glm-5',
            'enabled' => true,
        ],
        [
            'class' => \App\Agents\Personas\CMOPersona::class,
            'model' => 'glm-5',
            'enabled' => true,
        ],
        [
            'class' => \App\Agents\Personas\CPOPersona::class,
            'model' => 'glm-5',
            'enabled' => true,
        ],
        // Optional: Add CEO persona if desired
        // [
        //     'class' => \App\Agents\Personas\CEOPersona::class,
        //     'model' => 'glm-5',
        //     'enabled' => true,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | Default model to use for all personas and decision consolidation.
    | Supported: glm-5, haiku, sonnet
    |
    */

    'model' => 'glm-5',

    /*
    |--------------------------------------------------------------------------
    | Model Assignments
    |--------------------------------------------------------------------------
    |
    | Map logical model names to OpenRouter model IDs.
    |
    */

    'model_map' => [
        'glm-5' => 'z-ai/glm-5',
        'haiku' => 'anthropic/claude-3-haiku-20240307',
        'sonnet' => 'anthropic/claude-3-5-sonnet-20241022',
        'opus' => 'anthropic/claude-3-opus-20240229',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debate Rounds
    |--------------------------------------------------------------------------
    |
    | Number of debate rounds to run. In each round, all personas respond.
    | Round 1: Initial perspectives
    | Round 2+: Can reference and respond to previous statements
    |
    */

    'rounds' => 2,

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Timeout per persona response in seconds.
    | Total session time ~= rounds * personas * timeout_seconds
    |
    */

    'timeout_seconds' => 120, // 2 minutes per persona

    /*
    |--------------------------------------------------------------------------
    | Response Settings
    |--------------------------------------------------------------------------
    |
    | Maximum tokens for each persona response.
    |
    */

    'max_response_tokens' => 600,

    /*
    |--------------------------------------------------------------------------
    | Temperature
    |--------------------------------------------------------------------------
    |
    | Creativity/temperature for persona responses.
    | Lower = more focused and deterministic
    | Higher = more creative and varied
    |
    */

    'temperature' => 0.7,

    /*
    |--------------------------------------------------------------------------
    | Consolidation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for final decision consolidation.
    |
    */

    'consolidation' => [
        'model' => 'glm-5', // Model for decision analysis
        'max_tokens' => 1200,
        'temperature' => 0.3, // Lower for more consistent analysis
        'require_json' => true, // Expect JSON-formatted response
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenClaw Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenClaw sessions_spawn integration.
    | When enabled, uses OpenClaw's agent orchestration instead of direct API calls.
    |
    */

    'openclaw' => [
        'enabled' => false, // Set to true when OpenClaw integration is ready
        'url' => 'http://127.0.0.1:18789',
        'token' => '',
        'sessions_path' => env('OPENCLAW_SESSIONS_PATH'),
        'model' => 'glm-5', // Model to use in OpenClaw sessions
        'timeout_seconds' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Logging settings for board sessions.
    |
    */

    'logging' => [
        'enabled' => true,
        'log_responses' => true, // Log individual persona responses
        'log_decisions' => true, // Log final decisions
        'log_level' => 'info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Behavior
    |--------------------------------------------------------------------------
    |
    | What to do when API calls fail or responses are invalid.
    |
    */

    'fallback' => [
        'retry_attempts' => 1,
        'timeout_message' => '[Response timed out]',
        'error_message' => '[API unavailable - unable to get response]',
        'use_cached' => false, // Try to use cached responses from similar questions
    ],
];
