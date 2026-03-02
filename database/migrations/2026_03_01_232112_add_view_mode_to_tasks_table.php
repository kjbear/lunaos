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
            // Add view_mode column for unified task views (list, board, executive)
            $table->string('view_mode', 20)->default('list')->after('task_type');
            
            // Add index for faster filtering by view mode
            $table->index('view_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex(['view_mode']);
            
            // Then drop column
            $table->dropColumn('view_mode');
        });
    }
};
