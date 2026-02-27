<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add runtime_location column to agents table
     * Tracks where the agent is running: 'openclaw' or 'php'
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('runtime_location')->default('php')->after('type');
            $table->index('runtime_location', 'idx_runtime_location');
            
            // Optional: timestamp of last location check
            $table->timestamp('last_location_check')->nullable()->after('runtime_location');
        });
        
        // Set existing agents to 'php' (Laravel/PHP system)
        DB::statement("UPDATE agents SET runtime_location = 'php' WHERE runtime_location IS NULL");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropIndex('idx_runtime_location');
            $table->dropColumn(['runtime_location', 'last_location_check']);
        });
    }
};
