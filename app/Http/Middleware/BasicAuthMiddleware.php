<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip authentication for health check endpoints
        if ($request->is('up') || $request->is('health') || $request->is('api/health') || $request->is('api/status')) {
            return $next($request);
        }

        // Get credentials from environment
        $username = config('lunaos.auth_username');
        $password = config('lunaos.auth_password');

        // Fallback to env directly if config not set
        $username = $username ?? env('LUNAOS_AUTH_USERNAME', 'admin');
        $password = $password ?? env('LUNAOS_AUTH_PASSWORD', 'changeme');

        // Check if credentials are provided
        $providedUser = $request->getUser();
        $providedPass = $request->getPassword();

        if ($providedUser === $username && $providedPass === $password) {
            return $next($request);
        }

        // Return 401 Unauthorized with Basic Auth header
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'Valid credentials required',
        ], 401, [
            'WWW-Authenticate' => 'Basic realm="LunaOS"',
        ]);
    }
}