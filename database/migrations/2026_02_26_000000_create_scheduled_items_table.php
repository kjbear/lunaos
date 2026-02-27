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
        if (!Schema::hasTable('scheduled_items')) {
            Schema::create('scheduled_items', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->default('event'); // event, deadline, meeting, standup, reminder
                $table->string('status')->default('pending'); // pending, completed, cancelled
                $table->dateTime('start_time');
                $table->dateTime('end_time')->nullable();
                $table->string('color')->default('purple'); // purple, cyan, red, green, amber
                $table->string('icon')->default('📅');
                $table->integer('priority')->default(1); // 1-3 stars
                $table->text('notes')->nullable();
                $table->string('recurrence')->nullable(); // daily, weekly, monthly
                $table->timestamps();
                
                $table->index(['status', 'start_time']);
                $table->index(['type', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_items');
    }
};
