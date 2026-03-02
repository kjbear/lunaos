<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class EnvDebugTest extends TestCase
{
    public function beforeRefreshingDatabase(): void
    {
        parent::beforeRefreshingDatabase();
        
        echo "\n=== BEFORE REFRESH DB ===\n";
        echo "config sqlite.database: " . config('database.connections.sqlite.database') . "\n";
        echo "config sqlite-activity.database: " . config('database.connections.sqlite-activity.database') . "\n";
        echo "config sqlite-projects.database: " . config('database.connections.sqlite-projects.database') . "\n";
        echo "=== END BEFORE REFRESH ===\n\n";
    }
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Debug: dump the actual config values
        echo "\n=== CONFIG VALUES ===\n";
        echo "config sqlite.database: " . config('database.connections.sqlite.database') . "\n";
        echo "config sqlite-activity.database: " . config('database.connections.sqlite-activity.database') . "\n";
        echo "config sqlite-projects.database: " . config('database.connections.sqlite-projects.database') . "\n";
        echo "config database.default: " . config('database.default') . "\n";
        echo "=== END CONFIG ===\n\n";
    }

    public function test_env_values(): void
    {
        $this->assertTrue(true);
    }
}
