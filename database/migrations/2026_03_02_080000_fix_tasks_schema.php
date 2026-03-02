<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing tasks table completely
        Schema::dropIfExists('tasks');
        
        // Recreate with the correct unified schema
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assigned_to')->nullable();  // 'dave', 'sam', 'chen', etc
            $table->foreignId('repository_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('pending');  // pending, in_progress, complete, failed, blocked
            $table->string('step')->default('develop');  // develop, qa, security, staging, production
            $table->string('priority')->default('medium');  // low, medium, high, critical
            $table->string('task_type')->default('feature');  // feature, bugfix, refactor, test
            $table->string('view_mode')->default('list');  // list, board, executive
            $table->json('context_json')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('pr_url')->nullable();
            $table->json('artifacts_json')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'step']);
            $table->index('assigned_to');
            $table->index('view_mode');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        
        // Recreate the old schema for rollback
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('cost', 8, 4)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('agent_id');
            $table->index('created_at');
        });
    }
};
