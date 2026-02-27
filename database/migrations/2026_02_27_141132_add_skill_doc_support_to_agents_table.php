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
        Schema::table('agents', function (Blueprint $table) {
            // Path to skill definition markdown file (e.g., 'skills/laravel-specialist/SKILL.md')
            if (!Schema::hasColumn('agents', 'skill_doc_path')) {
                $table->string('skill_doc_path')->nullable()->after('system_prompt');
            }
            
            // Skill metadata: triggers, constraints, references, output templates
            if (!Schema::hasColumn('agents', 'skill_metadata')) {
                $table->json('skill_metadata')->nullable()->after('skill_doc_path');
            }
        });
        
        // Update existing agents with skill doc paths
        DB::table('agents')->where('name', 'dave')->update([
            'skill_doc_path' => 'skills/laravel-specialist/SKILL.md',
            'skill_metadata' => json_encode([
                'triggers' => ['Laravel', 'Eloquent', 'Livewire', 'PHP 8.2', 'API'],
                'constraints' => [
                    'must_do' => [
                        'Use PHP 8.2+ features (readonly, enums, typed properties)',
                        'Type hint all method parameters and return types',
                        'Use Eloquent relationships properly (avoid N+1)',
                        'Implement API resources for transforming data',
                        'Queue long-running tasks',
                        'Write comprehensive tests (>85% coverage)',
                        'Follow PSR-12 coding standards',
                    ],
                    'must_not' => [
                        'Use raw queries without protection (SQL injection)',
                        'Skip eager loading (causes N+1 problems)',
                        'Store sensitive data unencrypted',
                        'Mix business logic in controllers',
                        'Hardcode configuration values',
                        'Skip validation on user input',
                        'Use deprecated Laravel features',
                    ],
                ],
                'references' => [
                    'skills/laravel-specialist/references/eloquent.md',
                    'skills/laravel-specialist/references/routing.md',
                    'skills/laravel-specialist/references/testing.md',
                ],
                'output_template' => [
                    'files' => ['Model', 'Migration', 'Controller', 'Test'],
                    'coverage' => 0.85,
                ],
            ]),
        ]);
        
        DB::table('agents')->where('name', 'sam')->update([
            'skill_doc_path' => 'skills/qa-engineer/SKILL.md',
            'skill_metadata' => json_encode([
                'triggers' => ['PHPUnit', 'Dusk', 'Testing', 'QA', 'Coverage'],
                'constraints' => [
                    'must_do' => [
                        'Write tests before fixing (TDD)',
                        'Achieve >85% code coverage',
                        'Test edge cases and error conditions',
                        'Use factories for test data',
                        'Mock external dependencies',
                        'Run both unit and integration tests',
                    ],
                    'must_not' => [
                        'Skip assertions',
                        'Test implementation details instead of behavior',
                        'Ignore failing tests',
                        'Hardcode test data',
                    ],
                ],
                'references' => [
                    'skills/qa-engineer/references/phpunit.md',
                    'skills/qa-engineer/references/dusk.md',
                ],
            ]),
        ]);
        
        DB::table('agents')->where('name', 'chen')->update([
            'skill_doc_path' => 'skills/devops-engineer/SKILL.md',
            'skill_metadata' => json_encode([
                'triggers' => ['Deploy', 'Docker', 'Kubernetes', 'Health Check', 'Rollback'],
                'constraints' => [
                    'must_do' => [
                        'Run pre-deployment validation checks',
                        'Execute zero-downtime deployments for production',
                        'Verify all health checks pass',
                        'Enable automatic rollback on failure',
                        'Log all deployment actions',
                        'Monitor deployment metrics',
                    ],
                    'must_not' => [
                        'Deploy without pre-checks',
                        'Skip health checks',
                        'Deploy during peak traffic without approval',
                        'Ignore failed health checks',
                    ],
                ],
                'references' => [
                    'skills/devops-engineer/references/docker.md',
                    'skills/devops-engineer/references/health-checks.md',
                ],
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'skill_metadata')) {
                $table->dropColumn('skill_metadata');
            }
            if (Schema::hasColumn('agents', 'skill_doc_path')) {
                $table->dropColumn('skill_doc_path');
            }
        });
    }
};
