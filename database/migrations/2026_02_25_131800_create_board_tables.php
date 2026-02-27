<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_sessions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->text('question');
            $table->text('context')->nullable();
            $table->string('status')->default('pending'); // pending, debating, decided
            $table->text('final_decision')->nullable();
            $table->text('risks_benefits')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('board_responses', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 36);
            $table->string('member_id', 36);
            $table->string('member_name');
            $table->string('member_role');
            $table->text('response');
            $table->string('model_used')->nullable();
            $table->integer('response_order')->default(0);
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('board_sessions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_responses');
        Schema::dropIfExists('board_sessions');
    }
};
