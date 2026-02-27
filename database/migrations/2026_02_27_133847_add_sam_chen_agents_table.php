<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert Sam (QA) and Chen (DevOps) into agents table
        $now = now();
        
        DB::table('agents')->insert([
            [
                'id' => null,
                'name' => 'sam',
                'role' => 'QA Engineer',
                'emoji' => '🧪',
                'type' => 'worker',
                'model' => 'qwen3-coder:latest',
                'provider' => 'ollama',
                'system_prompt' => 'You are Sam, the QA Engineer AI agent for the LunaOS development team. Your role is to run PHPUnit tests, Laravel Dusk browser tests, validate code quality, check test coverage, and report bugs. You are thorough, detail-oriented, and ensure only high-quality code advances through the pipeline. You run tests, analyze failures, and make clear pass/fail decisions.',
                'settings' => json_encode([
                    'temperature' => 0.2,
                    'max_tokens' => 4096,
                    'top_p' => 0.9,
                ]),
                'capabilities' => json_encode(['phpunit', 'dusk', 'testing', 'qa', 'validation', 'coverage']),
                'is_online' => true,
                'runtime_location' => 'php',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => null,
                'name' => 'chen',
                'role' => 'DevOps Engineer',
                'emoji' => '🚀',
                'type' => 'worker',
                'model' => 'qwen3-coder:latest',
                'provider' => 'ollama',
                'system_prompt' => 'You are Chen, the DevOps Engineer AI agent for the LunaOS development team. Your role is to deploy code to staging and production environments, run health checks, manage Docker containers, monitor deployment status, and perform rollbacks on failures. You are careful, methodical, and prioritize zero-downtime deployments. You verify every deployment with comprehensive health checks.',
                'settings' => json_encode([
                    'temperature' => 0.3,
                    'max_tokens' => 4096,
                    'top_p' => 0.9,
                ]),
                'capabilities' => json_encode(['deploy', 'staging', 'production', 'docker', 'kubernetes', 'healthcheck', 'rollback']),
                'is_online' => true,
                'runtime_location' => 'php',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('agents')->whereIn('name', ['sam', 'chen'])->delete();
    }
};
