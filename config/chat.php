<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Individual Agent Chat feature.
    | Handles context management, token limits, and AI settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Context Window Settings
    |--------------------------------------------------------------------------
    |
    | Control how conversation history is managed for AI context.
    | - max_context_tokens: Maximum tokens to include in context (default 8000)
    | - max_context_messages: Maximum messages to keep in sliding window (default 20)
    |
    */

    'max_context_tokens' => env('CHAT_MAX_CONTEXT_TOKENS', 8000),
    'max_context_messages' => env('CHAT_MAX_CONTEXT_MESSAGES', 20),

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    |
    | - max_message_length: Maximum characters per message (default 32000)
    | - session_limit: Maximum sessions to return in list (default 50)
    |
    */

    'max_message_length' => env('CHAT_MAX_MESSAGE_LENGTH', 32000),
    'session_limit' => env('CHAT_SESSION_LIMIT', 50),

    /*
    |--------------------------------------------------------------------------
    | Ollama Integration
    |--------------------------------------------------------------------------
    |
    | Settings for Ollama API integration.
    | - request_timeout: Timeout in seconds for API calls (default 120)
    | - default_model: Default AI model for chat (default glm-5:cloud)
    |
    */

    'request_timeout' => env('CHAT_REQUEST_TIMEOUT', 120),
    'default_model' => env('CHAT_DEFAULT_MODEL', 'glm-5:cloud'),

    /*
    |--------------------------------------------------------------------------
    | Skill Loading
    |--------------------------------------------------------------------------
    |
    | Control how team member skills are loaded and integrated.
    | - skills_path: Path to skills directory (default base_path('skills'))
    | - load_skills: Whether to load skills for context (default true)
    |
    */

    'load_skills' => env('CHAT_LOAD_SKILLS', true),
    'skills_path' => env('CHAT_SKILLS_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Cache settings for chat sessions and context.
    | - cache_ttl: Time-to-live for cached data in seconds (default 3600)
    | - cache_prefix: Cache key prefix
    |
    */

    'cache_ttl' => env('CHAT_CACHE_TTL', 3600),
    'cache_prefix' => 'chat:',

];