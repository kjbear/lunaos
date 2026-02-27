<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add type column to agents table
     * Supports: worker, board, executive
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            // Add new type column
            $table->string('type')->default('worker')->after('name');
            
            // Create index for faster querying by type
            $table->index('type');
        });
        
        // Migrate existing 'role' values to 'type'
        DB::statement("UPDATE agents SET type = 'worker' WHERE role = 'worker' OR role IS NULL");
        DB::statement("UPDATE agents SET type = 'board' WHERE role = 'board'");
        DB::statement("UPDATE agents SET type = 'executive' WHERE role = 'executive'");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
