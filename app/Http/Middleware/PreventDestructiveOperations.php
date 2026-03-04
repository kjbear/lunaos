<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to prevent accidental destructive operations via web requests
 * 
 * This is a secondary guard (in addition to AppServiceProvider) that blocks
 * destructive database operations from web interface or API calls.
 */
class PreventDestructiveOperations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply in non-local environments
        if (app()->environment('local')) {
            return $next($request);
        }

        // Block destructive routes
        $blockedPatterns = [
            '*/migrate/fresh*',
            '*/migrate/reset*',
            '*/db/wipe*',
            '*/database/truncate*',
        ];

        $currentPath = $request->path();
        
        foreach ($blockedPatterns as $pattern) {
            if (fnmatch($pattern, $currentPath, FNM_NOESCAPE)) {
                return response()->json([
                    'error' => 'Destructive operation blocked',
                    'message' => sprintf(
                        'This operation is disabled in %s environment. Use staging for testing.',
                        app()->environment()
                    ),
                    'hint' => 'Set up staging environment: cp -r lunaos lunaos-staging && cd lunaos-staging',
                ], 403);
            }
        }

        return $next($request);
    }
}
