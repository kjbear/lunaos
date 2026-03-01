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
        // Skip authentication for health check, public web views, and Livewire
        if ($request->is('up') || 
            $request->is('health') || 
            $request->is('api/health') || 
            $request->is('api/status') ||
            $request->is('kanban') ||
            $request->is('docs') ||
            $request->is('docs/*') ||
            $request->is('livewire/*') ||  // Allow Livewire routes
            $request->is('/') ||
            $request->is('agents/*')) {  // TEMP: Test without auth on agents
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
        // For HTML requests, show a login prompt; for API/JSON, return JSON error
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Valid credentials required',
            ], 401, [
                'WWW-Authenticate' => 'Basic realm="LunaOS"',
            ]);
        }
        
        return response('Unauthorized', 401, [
            'WWW-Authenticate' => 'Basic realm="LunaOS"',
        ]);
    }
}