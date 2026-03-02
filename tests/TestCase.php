<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * The database connections that should have transactions.
     *
     * @var array
     */
    protected $connectionsToTransact = ['sqlite', 'sqlite-projects', 'sqlite-activity'];

    /**
     * Create a new application from scratch instead of loading from bootstrap/app.php.
     * This ensures environment variables are set before config is loaded.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Set environment variables BEFORE any config is loaded
        putenv('DB_DATABASE=:memory:');
        putenv('DB_PROJECTS_PATH=:memory:');
        putenv('DB_ACTIVITY_PATH=:memory:');
        
        // Now create the app normally using the project's bootstrap/app.php
        $app = require __DIR__.'/../bootstrap/app.php';

        // Override config for in-memory databases
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite-projects.database', ':memory:');
        $app['config']->set('database.connections.sqlite-activity.database', ':memory:');

        return $app;
    }

    /**
     * Perform any work that should take place before the database has started refreshing.
     *
     * @return void
     */
    protected function beforeRefreshingDatabase()
    {
        // Already handled in createApplication(), but keeping this here as a fallback
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('database.connections.sqlite-projects.database', ':memory:');
        $this->app['config']->set('database.connections.sqlite-activity.database', ':memory:');
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
