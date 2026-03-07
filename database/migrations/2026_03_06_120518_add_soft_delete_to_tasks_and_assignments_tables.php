<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add soft delete support to tasks, project_assignments, and project_issues.
     * This enables cascade soft deletes from projects table.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
        });

        Schema::table('project_assignments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
        });

        Schema::table('project_issues', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('project_assignments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('project_issues', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
