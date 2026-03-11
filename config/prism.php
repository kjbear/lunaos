<?php

return [
    'providers' => [
        'openai' => [
            'url' => 'https://api.openai.com/v1',
        ],
        'ollama' => [
            'url' => env('OLLAMA_CLOUD_BASE_URL', 'https://ollama.com'),
        ],
    ],
];