<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Repository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AgentConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create LunaOS repository
        Repository::firstOrCreate(
            ['name' => 'LunaOS'],
            [
                'path' => base_path(),
                'git_url' => 'https://github.com/kjbear/lunaos',
                'default_branch' => 'main',
                'is_active' => true,
                'settings' => [
                    'branch_prefix' => 'feature',
                    'pr_template' => "## Changes\n\n- \n\n## Testing\n\n- [ ] Tests pass\n- [ ] Manual testing complete",
                ],
            ]
        );

        // Create IHSSP repository (In-Home Services SaaS Platform)
        Repository::firstOrCreate(
            ['name' => 'IHSSP'],
            [
                'path' => '/workspace/ihssp',
                'git_url' => 'https://github.com/kjbear/ihssp',
                'default_branch' => 'main',
                'is_active' => false, // Not active yet
                'settings' => [
                    'branch_prefix' => 'feature',
                ],
            ]
        );

        // Create Dave agent (PHP Developer)
        Agent::firstOrCreate(
            ['name' => 'dave'],
            [
                'role' => 'worker',
                'model' => 'gpt-oss:120b-cloud',
                'provider' => 'ollama',
                'strategy_class' => 'develop',
                'system_prompt' => "You are Dave, a senior PHP/Laravel developer with expertise in:\n- Laravel 12.x\n- Livewire 3\n- PHP 8.4\n- Clean architecture\n- Test-driven development\n\nYour job is to:\n1. Read task requirements carefully\n2. Generate complete, working code\n3. Follow Laravel best practices\n4. Write tests when appropriate\n5. Create migrations for database changes\n\nAlways return structured JSON with files array.",
                'status' => 'online',
                'emoji' => '🔧',
                'avatar' => '👨‍💻',
                'model_settings' => [
                    'temperature' => 0.3,
                    'max_tokens' => 8192,
                ],
            ]
        );

        // Create Sam agent (QA Engineer)
        Agent::firstOrCreate(
            ['name' => 'sam'],
            [
                'role' => 'worker',
                'model' => 'gpt-oss:120b-cloud',
                'provider' => 'ollama',
                'strategy_class' => 'qa',
                'system_prompt' => "You are Sam, a QA engineer specializing in:\n- PHPUnit testing\n- Laravel Dusk browser testing\n- API testing\n- Quality assurance\n\nYour job is to:\n1. Review code changes\n2. Write comprehensive tests\n3. Run existing test suite\n4. Report pass/fail status\n5. Catch edge cases",
                'status' => 'online',
                'emoji' => '🧪',
                'avatar' => '👩‍🔬',
                'model_settings' => [
                    'temperature' => 0.2,
                    'max_tokens' => 4096,
                ],
            ]
        );

        // Create Chen agent (DevOps Engineer)
        Agent::firstOrCreate(
            ['name' => 'chen'],
            [
                'role' => 'worker',
                'model' => 'gpt-oss:120b-cloud',
                'provider' => 'ollama',
                'strategy_class' => 'deploy',
                'system_prompt' => "You are Chen, a DevOps engineer specializing in:\n- Deployment automation\n- Infrastructure as code\n- Health checks\n- Rollback strategies\n- CI/CD pipelines\n\nYour job is to:\n1. Deploy to staging/production\n2. Run health checks\n3. Monitor deployments\n4. Handle rollbacks if needed",
                'status' => 'online',
                'emoji' => '🚀',
                'avatar' => '👨‍💼',
                'model_settings' => [
                    'temperature' => 0.3,
                    'max_tokens' => 4096,
                ],
            ]
        );

        // Create Security Bot agent
        Agent::firstOrCreate(
            ['name' => 'security'],
            [
                'role' => 'worker',
                'model' => 'gpt-oss:120b-cloud',
                'provider' => 'ollama',
                'system_prompt' => "You are Security Bot, specializing in:\n- Static code analysis\n- Vulnerability scanning\n- Dependency checks\n- Security best practices\n\nYour job is to:\n1. Scan code for vulnerabilities\n2. Check for hardcoded secrets\n3. Validate input sanitization\n4. Ensure proper authentication/authorization\n5. Report security issues",
                'status' => 'online',
                'emoji' => '🔒',
                'avatar' => '🤖',
                'model_settings' => [
                    'temperature' => 0.1,
                    'max_tokens' => 4096,
                ],
            ]
        );

        $this->command->info('✅ Agent configuration seeded successfully!');
        $this->command->info('Repositories: ' . Repository::count());
        $this->command->info('Agents: ' . Agent::where('role', 'worker')->count());
    }
}
