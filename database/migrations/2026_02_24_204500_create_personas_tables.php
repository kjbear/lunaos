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
        // Personas table
        Schema::create('personas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('role', ['subagent', 'board_member', 'custom'])->default('custom');
            $table->string('model')->default('haiku'); // dolphin, haiku, glm-5
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->string('inspiration')->nullable();
            $table->text('system_prompt')->nullable();
            $table->string('workspace_path')->nullable();
            $table->timestamps();
            $table->timestamp('deactivated_at')->nullable();
        });

        // Persona metrics table
        Schema::create('persona_metrics', function (Blueprint $table) {
            $table->id();
            $table->uuid('persona_id');
            $table->integer('projects_count')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_failed')->default(0);
            $table->integer('tokens_used')->default(0);
            $table->integer('sessions_count')->default(0);
            $table->integer('decisions_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
        });

        // Persona workspaces table
        Schema::create('persona_workspaces', function (Blueprint $table) {
            $table->id();
            $table->uuid('persona_id');
            $table->string('file_name');
            $table->longText('content')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_workspaces');
        Schema::dropIfExists('persona_metrics');
        Schema::dropIfExists('personas');
    }
};