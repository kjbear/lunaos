<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Override multi-database connections for testing
        config(['database.connections.sqlite-projects.database' => ':memory:']);
        config(['database.connections.sqlite-activity.database' => ':memory:']);
        
        // Run migrations for all connections
        $this->artisan('migrate:fresh', ['--database' => 'sqlite-projects'])->run();
        $this->artisan('migrate:fresh', ['--database' => 'sqlite-activity'])->run();
    }
}
