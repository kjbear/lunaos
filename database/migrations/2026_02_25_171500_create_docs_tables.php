<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_collections', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->text('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('source_url')->nullable();
            $table->text('storage_path')->nullable();
            $table->integer('file_count')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('doc_categories', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('collection_id', 36);
            $table->string('parent_id', 36)->nullable();
            $table->text('name');
            $table->string('slug');
            $table->text('path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('doc_collections')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('doc_categories')->onDelete('cascade');
        });

        Schema::create('doc_files', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('collection_id', 36);
            $table->string('category_id', 36)->nullable();
            $table->text('title');
            $table->string('slug');
            $table->text('file_path');
            $table->text('source_url')->nullable();
            $table->string('content_hash')->nullable();
            $table->integer('word_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('doc_collections')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('doc_categories')->onDelete('set null');
        });

        // Indexes
        Schema::table('doc_files', function (Blueprint $table) {
            $table->index('collection_id');
            $table->index('category_id');
        });

        Schema::table('doc_categories', function (Blueprint $table) {
            $table->index('collection_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_files');
        Schema::dropIfExists('doc_categories');
        Schema::dropIfExists('doc_collections');
    }
};