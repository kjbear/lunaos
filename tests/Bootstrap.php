<?php

/**
 * Test Bootstrap - Runs before any tests execute
 * 
 * Registers the Console Kernel singleton early to prevent
 * "Target [Illuminate\Contracts\Console\Kernel] is not instantiable" errors
 * during PHPUnit test teardown.
 */

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Events\Dispatcher;

// Load Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Create Laravel app instance
$app = require __DIR__.'/../bootstrap/app.php';

// Bind Console Kernel BEFORE any tests run
// This must happen early to survive test suite execution
$app->singleton(ConsoleKernelContract::class, function ($app) {
    return new ConsoleKernel($app, $app->make(Dispatcher::class));
});

// Pre-resolve the Kernel to ensure it's instantiated immediately
$kernel = $app->make(ConsoleKernelContract::class);

// Register shutdown function to ensure kernel persists
register_shutdown_function(function () use ($app) {
    // Keep app alive
});

// Return app for PHPUnit to use
return $app;
