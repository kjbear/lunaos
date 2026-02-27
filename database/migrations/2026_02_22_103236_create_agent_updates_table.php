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
        Schema::create('agent_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standup_id')->constrained()->onDelete('cascade');
            $table->string('agent_name', 100);
            $table->string('agent_role', 100)->default('Team Member');
            $table->string('agent_color', 20)->default('#7c3aed');
            $table->text('done_yesterday')->nullable();
            $table->text('doing_today')->nullable();
            $table->text('blockers')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['standup_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_updates');
    }
};