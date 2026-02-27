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
        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('repository_id')->nullable()->after('assigned_to');
            $table->foreign('repository_id')->references('id')->on('repositories')->nullOnDelete();
            
            $table->index('repository_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['repository_id']);
            $table->dropIndex(['repository_id']);
            $table->dropColumn('repository_id');
        });
    }
};
