<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_health', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100);
            $table->enum('status', ['healthy', 'degraded', 'down'])->default('healthy');
            $table->decimal('cpu_percent', 5, 2)->default(0);
            $table->decimal('memory_percent', 5, 2)->default(0);
            $table->decimal('vram_percent', 5, 2)->nullable();
            $table->decimal('tokens_per_sec', 8, 2)->default(0);
            $table->integer('queue_depth')->default(0);
            $table->timestamp('checked_at')->useCurrent();

            $table->index(['model', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_health');
    }
};