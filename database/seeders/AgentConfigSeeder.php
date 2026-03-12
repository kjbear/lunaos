<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\TeamMember;
use App\Models\Repository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AgentConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * CRITICAL: Creates agents in BOTH tables:
     * - agents: Daemon/worker functionality
     * - team_members: UI display (what users see)
     */
    public function run(): void
    {
        // Helper to create in BOTH tables (prevents invisibility bug!)
        $createAgent = function($name, $role, $type, $emoji, $strategy, $title, $prompt, $model = 'glm-5') {
            // Create in agents table (daemon functionality)
            Agent::firstOrCreate(
                ['name' => $name],
                [
                    'role' => $role,
                    'type' => $type == 'board' ? 'board' : 'worker',
                    'emoji' => $emoji,
                    'strategy_class' => $strategy,
                    'step_filter' => $strategy,
                    'model' => $model,
                    'provider' => 'ollama',
                    'status' => 'offline',
                    'system_prompt' => $prompt,
                    'runtime_location' => 'php',
                ]
            );
            
            // Create in team_members table (UI display - CRITICAL!)
            TeamMember::firstOrCreate(
                ['name' => $name],
                [
                    'id' => (string) Str::uuid(),
                    'type' => $type == 'board' ? 'board-members' : 'workers',
                    'role' => $role,
                    'category' => $role,
                    'emoji' => $emoji,
                    'title' => $title,
                    'model' => $model,
                    'provider' => 'ollama',
                    'ai_model' => $model,
                    'system_prompt' => $prompt,
                    'status' => 'offline',
                ]
            );
            
            $this->command->info("  ✓ {$name} ({$title})");
        };
        
        // Create LunaOS repository
        Repository::firstOrCreate(
            ['name' => 'LunaOS'],
            [
                'path' => base_path(),
                'git_url' => 'https://github.com/kjbear/lunaos',
                'default_branch' => 'main',
                'is_active' => true,
            ]
        );
        
        $this->info('Workers:');
        
        // Workers
        $createAgent('dave', 'worker', 'worker', '🔧', 'develop', 'Senior Developer', 'You are Dave, Senior Developer AI.');
        $createAgent('sam', 'worker', 'worker', '🧪', 'qa', 'QA Engineer', 'You are Sam, QA Engineer AI.');
        $createAgent('chen', 'worker', 'worker', '⚙️', 'deploy', 'DevOps Engineer', 'You are Chen, DevOps Engineer AI.');
        
        $this->info(PHP_EOL . 'Board Members:');
        
        // Board Members
        $createAgent('steven', 'board_member', 'board', '👔', 'steven', 'CEO', 'You are Steven, CEO board member.');
        $createAgent('gwynne', 'board_member', 'board', '👔', 'gwynne', 'COO', 'You are Gwynne, COO board member.');
        $createAgent('werner', 'board_member', 'board', '💻', 'werner', 'CTO', 'You are Werner, CTO board member.');
        $createAgent('warren', 'board_member', 'board', '💰', 'warren', 'CFO', 'You are Warren, CFO board member.');
        $createAgent('boz', 'board_member', 'board', '📢', 'boz', 'CMO', 'You are Boz, Chief Marketing Officer.', 'gpt-4.1-nano');
        $createAgent('fidji', 'board_member', 'board', '📦', 'fidji', 'Chief Product Strategy', 'You are Fidji, Chief Product Strategy.');
        
        $this->info(PHP_EOL . '✅ Seeded:');
        $this->info('  Agents: ' . Agent::count());
        $this->info('  Team Members: ' . TeamMember::count());
        $this->info('  Repositories: ' . Repository::count());
    }
}
