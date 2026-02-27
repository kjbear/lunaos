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
        // Drop existing tasks table and recreate with proper schema
        Schema::dropIfExists('tasks');
        
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assigned_to')->nullable();  // 'dave', 'sam', 'chen', etc
            $table->string('status')->default('pending');  // pending, in_progress, completed, failed
            $table->string('step')->default('develop');  // develop, qa, security, staging, production
            $table->string('priority')->default('medium');  // low, medium, high, critical
            $table->string('task_type')->default('feature');  // feature, bugfix, refactor, test
            $table->json('context_json')->nullable();  // additional context
            $table->string('branch_name')->nullable();  // git branch
            $table->string('pr_url')->nullable();  // pull request URL
            $table->json('artifacts_json')->nullable();  // output artifacts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        // Recreate original stub table
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
