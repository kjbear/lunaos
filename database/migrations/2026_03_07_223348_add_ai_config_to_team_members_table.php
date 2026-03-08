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
        Schema::table('team_members', function (Blueprint $table) {
            // AI Model Configuration
            $table->string('ai_model')->default('glm-5')->after('model');
            $table->float('temperature')->default(0.7)->after('ai_model');
            $table->integer('max_tokens')->default(4096)->after('temperature');
            $table->float('top_p')->default(1.0)->after('max_tokens');
            $table->float('frequency_penalty')->default(0.0)->after('top_p');
            $table->float('presence_penalty')->default(0.0)->after('frequency_penalty');
            
            // Response Configuration
            $table->string('response_style')->default('technical')->after('presence_penalty');
            $table->text('persona_description')->nullable()->after('response_style');
            $table->text('special_instructions')->nullable()->after('persona_description');
            
            // Capabilities & Metadata
            $table->json('capabilities')->nullable()->after('special_instructions');
            $table->integer('max_concurrent_tasks')->default(3)->after('capabilities');
            $table->boolean('auto_assign_enabled')->default(true)->after('max_concurrent_tasks');
            $table->string('priority_level')->default('normal')->after('auto_assign_enabled');
            $table->json('custom_metadata')->nullable()->after('priority_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn([
                'ai_model',
                'temperature',
                'max_tokens',
                'top_p',
                'frequency_penalty',
                'presence_penalty',
                'response_style',
                'persona_description',
                'special_instructions',
                'capabilities',
                'max_concurrent_tasks',
                'auto_assign_enabled',
                'priority_level',
                'custom_metadata',
            ]);
        });
    }
};