<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('repo_url')->nullable();
            $table->string('health')->default('healthy');
            $table->integer('progress')->default(0);
            $table->string('owner')->default('kyle');
            $table->string('status')->default('planning');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('project_id', 36);
            $table->string('persona_id', 36);
            $table->string('role')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        Schema::create('requirements', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->text('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('draft');
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirements');
        Schema::dropIfExists('project_assignments');
        Schema::dropIfExists('projects');
    }
};
