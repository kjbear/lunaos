<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LunaOS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration values for the LunaOS dashboard application.
    |
    */

    // HTTP Basic Auth credentials
    'auth_username' => env('LUNAOS_AUTH_USERNAME', 'admin'),
    'auth_password' => env('LUNAOS_AUTH_PASSWORD', 'changeme'),

    // Application settings
    'name' => env('APP_NAME', 'LunaOS'),
    'version' => '0.1.0',

    // Dashboard settings
    'default_timezone' => env('LUNAOS_TIMEZONE', 'America/New_York'),
    'date_format' => 'M j, Y',
    'time_format' => 'g:i A',
];