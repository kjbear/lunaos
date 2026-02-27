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
        Schema::table('agents', function (Blueprint $table) {
            // Check if columns already exist (from previous iterations)
            $columns = Schema::getColumnListing('agents');
            
            if (!in_array('provider', $columns)) {
                $table->string('provider')->default('ollama')->after('model');
            }
            
            if (!in_array('system_prompt', $columns)) {
                $table->text('system_prompt')->nullable()->after('provider');
            }
            
            if (!in_array('model_settings', $columns)) {
                $table->json('model_settings')->nullable()->after('system_prompt');
            }
            
            if (!in_array('avatar', $columns)) {
                $table->string('avatar')->default('🤖')->after('emoji');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['provider', 'system_prompt', 'model_settings', 'avatar']);
        });
    }
};
