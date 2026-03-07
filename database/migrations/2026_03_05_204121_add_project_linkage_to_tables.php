<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add project_id foreign key
            // P0.1 CASCADE DELETE: When a project is deleted, all its tasks are automatically deleted
            $table->uuid('project_id')->after('id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Add requirement_id foreign key
            // SET NULL: Tasks can exist without a requirement (requirements are source of truth)
            $table->uuid('requirement_id')->after('project_id')->nullable();
            $table->foreign('requirement_id')->references('id')->on('requirements')->onDelete('set null');
            
            // Add indexes for performance
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['requirement_id']);
            $table->dropColumn(['project_id', 'requirement_id']);
        });
    }
};
