<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Controller for handling Dave's test endpoint.
 *
 * This controller provides a simple greeting endpoint
 * for testing purposes within the LunaOS project.
 *
 * @package App\Http\Controllers
 */
final class TestDaveController extends Controller
{
    /**
     * Handle the incoming request and return a greeting message.
     *
     * This invokable controller method returns a simple JSON
     * response with a greeting message from Dave.
     *
     * @return JsonResponse The JSON response containing the greeting message.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Hello from Dave!',
        ]);
    }
}
