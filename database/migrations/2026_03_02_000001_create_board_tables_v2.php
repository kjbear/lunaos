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
        Schema::create('board_sessions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->text('question');
            $table->string('status')->default('pending'); // pending, debating, decided, closed
            $table->text('decision_summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('board_participants', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('session_id', 36);
            $table->string('persona_role'); // COO, CFO, CTO, CMO, CPO
            $table->json('model_config')->nullable(); // {model: 'glm-5', temperature: 0.7, etc}
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('board_sessions')->onDelete('cascade');
            $table->index(['session_id', 'persona_role']);
        });

        Schema::create('board_discussion_entries', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('session_id', 36);
            $table->string('participant_id', 36);
            $table->integer('round')->default(1);
            $table->text('message');
            $table->text('model_response')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('board_sessions')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('board_participants')->onDelete('cascade');
            $table->index(['session_id', 'round']);
        });

        Schema::create('board_decisions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('session_id', 36);
            $table->text('decision_text');
            $table->float('confidence_score')->nullable();
            $table->text('reasoning')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('board_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_decisions');
        Schema::dropIfExists('board_discussion_entries');
        Schema::dropIfExists('board_participants');
        Schema::dropIfExists('board_sessions');
    }
};
