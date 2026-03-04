<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // Register Console Kernel for testing (fixes teardown errors with $this->artisan())
        if (!$app->bound(ConsoleKernelContract::class)) {
            $app->singleton(ConsoleKernelContract::class, function ($app) {
                return new ConsoleKernel($app, $app['events']);
            });
        }

        // Bootstrap the application (sets up facades, service providers, etc.)
        $app->make(ConsoleKernelContract::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // For testing: use single in-memory SQLite database for all connections
        $this->configureTestDatabases();
    }

    /**
     * Configure all SQLite connections to use :memory: for testing.
     * This prevents "multi-database" issues where models with explicit
     * $connection properties (like ActivityLog) point to file-based DBs
     * instead of in-memory.
     */
    protected function configureTestDatabases(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite-activity.database' => ':memory:',
            'database.connections.sqlite-projects.database' => ':memory:',
            'database.connections.sqlite-staging.database' => ':memory:',
        ]);
    }
}
