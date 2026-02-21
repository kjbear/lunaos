<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->text('log_text');
            $table->enum('level', ['debug', 'info', 'warning', 'error'])->default('info');
            $table->timestamp('created_at')->useCurrent();

            $table->index('task_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};