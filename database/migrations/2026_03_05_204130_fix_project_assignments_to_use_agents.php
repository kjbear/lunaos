<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: SQLite doesn't support dropping columns reliably, so we'll just add agent_id
     * and treat persona_id as deprecated. For production MySQL/PostgreSQL, use the full migration.
     */
    public function up(): void
    {
        Schema::table('project_assignments', function (Blueprint $table) {
            // Add agent_id foreign key
            $table->uuid('agent_id')->after('project_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            
            // Add index
            $table->index(['project_id', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_assignments', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'agent_id']);
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
    }
};
