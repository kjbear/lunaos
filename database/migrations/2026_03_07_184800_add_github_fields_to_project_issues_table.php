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
        Schema::table('project_issues', function (Blueprint $table) {
            $table->string('github_id')->nullable()->unique()->after('assigned_to');
            $table->integer('github_number')->nullable()->after('github_id');
            $table->string('github_url')->nullable()->after('github_number');
            $table->string('github_state')->nullable()->after('github_url');
            $table->json('github_labels')->nullable()->after('github_state');
            $table->json('github_assignees')->nullable()->after('github_labels');
            $table->timestamp('github_created_at')->nullable()->after('github_assignees');
            $table->timestamp('github_updated_at')->nullable()->after('github_created_at');

            // Index for quick lookups by GitHub ID
            $table->index('github_id');
            $table->index('github_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_issues', function (Blueprint $table) {
            $table->dropIndex(['github_id']);
            $table->dropIndex(['github_number']);
            $table->dropColumn([
                'github_id',
                'github_number',
                'github_url',
                'github_state',
                'github_labels',
                'github_assignees',
                'github_created_at',
                'github_updated_at',
            ]);
        });
    }
};