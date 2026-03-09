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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('context')->nullable()->comment('Sliding window of recent messages for context');
            $table->json('metadata')->nullable()->comment('Token counts, model used, etc.');
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index('team_member_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};