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
        Schema::table('projects', function (Blueprint $table) {
            // Add repository_id foreign key
            $table->uuid('repository_id')->after('repo_url')->nullable();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('set null');
            
            // Add index
            $table->index('repository_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('repository_id');
            $table->dropForeign(['repository_id']);
            $table->dropColumn('repository_id');
        });
    }
};
