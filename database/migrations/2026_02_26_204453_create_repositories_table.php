<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repositories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "LunaOS", "IHSSP"
            $table->string('path'); // e.g., "/workspace/lunaos"
            $table->string('git_url'); // e.g., "https://github.com/kjbear/lunaos"
            $table->string('default_branch')->default('main');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // branch_prefix, pr_template, etc.
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
