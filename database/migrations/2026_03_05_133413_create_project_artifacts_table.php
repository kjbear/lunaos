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
        Schema::create('project_artifacts', function (Blueprint $table) {
            $table->id();
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->enum('type', ['requirement', 'board_discussion', 'doc', 'note', 'decision']);
            $table->string('title');
            $table->text('content');
            $table->string('source_type')->nullable(); // e.g., 'board_session'
            $table->uuid('source_id')->nullable(); // e.g., board_session_id
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['project_id', 'type']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_artifacts');
    }
};
