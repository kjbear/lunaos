<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class StatusController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'version' => '0.1.0',
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}