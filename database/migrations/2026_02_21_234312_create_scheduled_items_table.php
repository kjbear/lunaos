<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['task', 'reminder', 'meeting', 'deadline', 'other'])->default('task');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('agent', 100)->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['start_time', 'end_time']);
            $table->index('agent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_items');
    }
};