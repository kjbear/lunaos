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
        Schema::create('scheduled_items', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 20); // cron, reminder, calendar, email, task
            $table->string('source_id', 100)->nullable(); // External ID from source
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at')->nullable();
            $table->string('timezone', 50)->default('America/New_York');
            $table->string('recurrence_rule', 100)->nullable(); // RRULE or cron expression
            $table->tinyInteger('priority')->default(2); // 1=low, 2=normal, 3=high, 4=critical
            $table->string('status', 20)->default('pending'); // pending, completed, cancelled
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->json('metadata')->nullable(); // Additional data from source

            $table->index(['source_type', 'source_id']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_items');
    }
};