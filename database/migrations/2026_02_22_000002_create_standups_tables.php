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
        Schema::create('standups', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('team', 100)->default('LunaOS Team');
            $table->string('facilitator', 100)->nullable();
            $table->text('transcript')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('recording_path', 255)->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('status');
        });

        Schema::create('standup_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standup_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['standup_id', 'order']);
        });

        Schema::create('standup_action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standup_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('assigned_to', 100)->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->index(['standup_id', 'completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standup_action_items');
        Schema::dropIfExists('standup_deliverables');
        Schema::dropIfExists('standups');
    }
};