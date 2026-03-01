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
        
        // Override all database connections to use in-memory for testing
        config(['database.connections.sqlite.database' => ':memory:']);
        config(['database.connections.sqlite-projects.database' => ':memory:']);
        config(['database.connections.sqlite-activity.database' => ':memory:']);
    }
}
