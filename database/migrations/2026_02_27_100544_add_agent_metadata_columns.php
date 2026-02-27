<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add metadata columns to agents table for enhanced agent configuration
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            // Capabilities/skills as JSON
            if (!Schema::hasColumn('agents', 'capabilities')) {
                $table->json('capabilities')->nullable()->after('system_prompt');
            }
            
            // Model settings (temperature, max_tokens, etc.) as JSON
            if (!Schema::hasColumn('agents', 'settings')) {
                $table->json('settings')->nullable()->after('capabilities');
            }
            
            // Online status flag (simpler than enum)
            if (!Schema::hasColumn('agents', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('emoji');
            }
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'capabilities')) {
                $table->dropColumn('capabilities');
            }
            if (Schema::hasColumn('agents', 'settings')) {
                $table->dropColumn('settings');
            }
            if (Schema::hasColumn('agents', 'is_online')) {
                $table->dropColumn('is_online');
            }
        });
    }
};
