<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Agent;
use Illuminate\Database\Seeder;

class JordanTestDataSeeder extends Seeder
{
    /**
     * Seed test data for Jordan (Project Manager) agent testing
     * 
     * Creates realistic scenarios:
     * - Blocked tasks needing escalation
     * - Unassigned tasks needing prioritization
     * - Tasks at various priority levels
     * - Different workflow steps
     */
    public function run(): void
    {
        // Ensure we have Jordan in the database
        $jordan = Agent::updateOrCreate(
            ['name' => 'jordan'],
            [
                'type' => 'board',
                'title' => 'Project Manager',
                'model' => 'glm-5',
                'provider' => 'openrouter',
                'system_prompt' => 'You are Jordan, the Project Manager...',
                'capabilities' => json_encode(['prioritize', 'assign', 'escalate', 'unblock', 'plan']),
                'settings' => json_encode(['temperature' => 0.7, 'max_tokens' => 4096]),
                'avatar' => '👔',
                'emoji' => '📋',
                'is_online' => true,
            ]
        );

        // Ensure worker agents exist with proper types
        $workers = [
            [
                'name' => 'dave',
                'type' => 'worker',
                'title' => 'PHP/Laravel Developer',
                'capabilities' => json_encode(['php', 'laravel', 'livewire', 'blade', 'api', 'refactor', 'bugfix']),
            ],
            [
                'name' => 'sam',
                'type' => 'worker',
                'title' => 'QA Engineer',
                'capabilities' => json_encode(['testing', 'qa', 'automation', 'pest', 'phpunit']),
            ],
            [
                'name' => 'chen',
                'type' => 'worker',
                'title' => 'DevOps Engineer',
                'capabilities' => json_encode(['deployment', 'kubernetes', 'docker', 'ci-cd', 'aws']),
            ],
        ];

        foreach ($workers as $worker) {
            Agent::updateOrCreate(
                ['name' => $worker['name']],
                array_merge($worker, [
                    'model' => 'qwen3-coder',
                    'provider' => 'ollama',
                    'is_online' => true,
                ])
            );
        }

        // Create test scenarios for Jordan

        // Scenario 1: Blocked tasks (Jordan should escalate/reassign)
        Task::create([
            'title' => 'Authentication API failing with 500 error',
            'description' => 'Users cannot log in. API returns 500 error when hitting /api/auth/login. Started happening after last deployment. Blocking all user access. Block: Database connection pool exhausted, unsure how to resolve (retry: 2)',
            'assigned_to' => 'dave',
            'status' => 'blocked',
            'step' => 'develop',
            'priority' => 'critical',
            'task_type' => 'bugfix',
        ]);

        Task::create([
            'title' => 'Payment integration tests failing intermittently',
            'description' => 'Stripe webhook tests fail ~30% of the time in CI. Unable to determine root cause. Tests pass locally consistently. Block: Cannot reproduce locally, need CI environment access (retry: 3)',
            'assigned_to' => 'sam',
            'status' => 'blocked',
            'step' => 'test',
            'priority' => 'high',
            'task_type' => 'testing',
        ]);

        // Scenario 2: Unassigned tasks (Jordan should prioritize and assign)
        Task::create([
            'title' => 'Implement 2FA for user accounts',
            'description' => 'Add two-factor authentication using TOTP (Google Authenticator). Include QR code generation, backup codes, and recovery flow.',
            'assigned_to' => null,
            'status' => 'pending',
            'step' => 'backlog',
            'priority' => 'high',
            'task_type' => 'feature',
        ]);

        Task::create([
            'title' => 'Optimize database queries for dashboard',
            'description' => 'Dashboard load time is 3-5 seconds. Need to add indexes, optimize N+1 queries, and implement caching.',
            'assigned_to' => null,
            'status' => 'pending',
            'step' => 'backlog',
            'priority' => 'medium',
            'task_type' => 'performance',
        ]);

        Task::create([
            'title' => 'Write API documentation for v2 endpoints',
            'description' => 'Document all new API v2 endpoints using OpenAPI/Swagger format. Include examples, error codes, and authentication requirements.',
            'assigned_to' => null,
            'status' => 'pending',
            'step' => 'backlog',
            'priority' => 'low',
            'task_type' => 'documentation',
        ]);

        // Scenario 3: In-progress tasks (for visibility)
        Task::create([
            'title' => 'Refactor user service to use repository pattern',
            'description' => 'Extract business logic from UserController into UserService and UserRepository for better testability.',
            'assigned_to' => 'dave',
            'status' => 'in_progress',
            'step' => 'develop',
            'priority' => 'medium',
            'task_type' => 'refactor',
        ]);

        Task::create([
            'title' => 'Set up automated database backups',
            'description' => 'Configure daily automated backups to S3 with 30-day retention. Include disaster recovery runbook.',
            'assigned_to' => 'chen',
            'status' => 'in_progress',
            'step' => 'deploy',
            'priority' => 'high',
            'task_type' => 'devops',
        ]);

        echo "✅ Jordan test data seeded successfully!\n";
        echo "Created:\n";
        echo "  - 1 Jordan (PM) agent\n";
        echo "  - 3 Worker agents (dave, sam, chen)\n";
        echo "  - 2 Blocked tasks (needing escalation)\n";
        echo "  - 3 Unassigned tasks (needing prioritization)\n";
        echo "  - 2 In-progress tasks (for visibility)\n";
    }
}
