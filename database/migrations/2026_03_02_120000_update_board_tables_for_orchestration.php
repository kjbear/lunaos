<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds support for multi-round executive board debates and enhanced decision tracking.
     */
    public function up(): void
    {
        // Update board_sessions table
        Schema::table('board_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('board_sessions', 'context')) {
                $table->text('context')->nullable()->after('question');
            }
            if (!Schema::hasColumn('board_sessions', 'rounds_planned')) {
                $table->integer('rounds_planned')->default(2)->after('status');
            }
            if (!Schema::hasColumn('board_sessions', 'confidence_score')) {
                $table->decimal('confidence_score', 3, 2)->default(0.50)->after('risks_benefits');
            }
            if (!Schema::hasColumn('board_sessions', 'dissenting_opinions')) {
                $table->json('dissenting_opinions')->nullable()->after('confidence_score');
            }
            if (!Schema::hasColumn('board_sessions', 'key_themes')) {
                $table->json('key_themes')->nullable()->after('dissenting_opinions');
            }
        });

        // Update board_responses table
        Schema::table('board_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('board_responses', 'round')) {
                $table->integer('round')->default(1)->after('response_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_sessions', function (Blueprint $table) {
            $columns = ['context', 'rounds_planned', 'confidence_score', 'dissenting_opinions', 'key_themes'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('board_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('board_responses', function (Blueprint $table) {
            if (Schema::hasColumn('board_responses', 'round')) {
                $table->dropColumn('round');
            }
        });
    }
};
