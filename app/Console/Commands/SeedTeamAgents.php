<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeamMember;

class SeedTeamAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:seed-agents 
                            {--reset : Clear existing data before seeding}
                            {--type= : Seed specific type only (board-members, workers)}
                            {--dry-run : Show what would be seeded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed realistic AI team member data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $teamData = $this->getTeamData();
        
        if ($this->option('dry-run')) {
            $this->info('Would seed the following team members:');
            foreach ($teamData as $member) {
                if ($this->option('type') && $member['type'] !== $this->option('type')) {
                    continue;
                }
                $this->line("  - {$member['name']} ({$member['title']})");
            }
            return self::SUCCESS;
        }
        
        if ($this->option('reset')) {
            $this->info('Clearing existing team members...');
            TeamMember::query()->delete();
        }
        
        $seeded = 0;
        foreach ($teamData as $memberData) {
            // Skip if filtering by type
            if ($this->option('type') && $memberData['type'] !== $this->option('type')) {
                continue;
            }
            
            TeamMember::updateOrCreate(
                ['name' => $memberData['name']],
                $memberData
            );
            $this->info("✓ Seeded: {$memberData['name']}");
            $seeded++;
        }
        
        $this->info("Successfully seeded {$seeded} team members.");
        return self::SUCCESS;
    }

    /**
     * Get the team data to seed.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getTeamData(): array
    {
        return [
            // Board Members
            [
                'name' => 'Steven',
                'type' => 'board-members',
                'role' => 'board_member',
                'category' => 'board_member',
                'title' => 'Chief Executive Officer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '👔',
                'metadata_json' => [
                    'bio' => 'Visionary leader driving strategic direction',
                    'skills' => ['Strategy', 'Leadership', 'Decision Making'],
                    'current_capacity' => 60,
                ],
            ],
            [
                'name' => 'Gwynne',
                'type' => 'board-members',
                'role' => 'board_member',
                'category' => 'board_member',
                'title' => 'Chief Operating Officer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '👔',
                'metadata_json' => [
                    'bio' => 'Operations expert ensuring execution excellence',
                    'skills' => ['Operations', 'Process Optimization', 'Team Management'],
                    'current_capacity' => 70,
                ],
            ],
            [
                'name' => 'Werner',
                'type' => 'board-members',
                'role' => 'board_member',
                'category' => 'board_member',
                'title' => 'Chief Technology Officer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '👔',
                'metadata_json' => [
                    'bio' => 'Technical visionary and architect',
                    'skills' => ['Architecture', 'Cloud', 'Security'],
                    'current_capacity' => 65,
                ],
            ],
            [
                'name' => 'Warren',
                'type' => 'board-members',
                'role' => 'board_member',
                'category' => 'board_member',
                'title' => 'Chief Financial Officer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '👔',
                'metadata_json' => [
                    'bio' => 'Financial strategist and budget optimizer',
                    'skills' => ['Finance', 'Budgeting', 'ROI Analysis'],
                    'current_capacity' => 55,
                ],
            ],
            [
                'name' => 'Bozoma',
                'type' => 'board-members',
                'role' => 'board_member',
                'category' => 'board_member',
                'title' => 'Chief Marketing Officer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '👔',
                'metadata_json' => [
                    'bio' => 'Brand builder and marketing innovator',
                    'skills' => ['Marketing', 'Brand Strategy', 'Growth'],
                    'current_capacity' => 60,
                ],
            ],
            [
                'name' => 'Fidji',
                'type' => 'board-members',
                'role' => 'board_member',
                'category' => 'board_member',
                'title' => 'Chief Product Officer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '👔',
                'metadata_json' => [
                    'bio' => 'Product strategist focused on user value',
                    'skills' => ['Product Strategy', 'User Research', 'Roadmapping'],
                    'current_capacity' => 65,
                ],
            ],
            
            // Workers (PMs)
            [
                'name' => 'Jordan',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'Senior Project Manager',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '📋',
                'metadata_json' => [
                    'specialty' => 'pm',
                    'bio' => 'Senior PM handling major initiatives',
                    'skills' => ['Project Management', 'Agile', 'Stakeholder Management'],
                    'current_capacity' => 80,
                ],
            ],
            [
                'name' => 'Alex',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'Project Manager - API & Integrations',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '📋',
                'metadata_json' => [
                    'specialty' => 'pm',
                    'bio' => 'PM specializing in API and integration projects',
                    'skills' => ['API Design', 'Integration', 'Technical PM'],
                    'current_capacity' => 75,
                ],
            ],
            
            // Workers (Developers)
            [
                'name' => 'Dave',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'Senior PHP/Laravel Developer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '💻',
                'metadata_json' => [
                    'specialty' => 'backend',
                    'bio' => 'Backend specialist focused on Laravel and APIs',
                    'skills' => ['PHP', 'Laravel', 'MySQL', 'PostgreSQL', 'API Design'],
                    'current_capacity' => 85,
                ],
            ],
            [
                'name' => 'Maya',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'Senior Frontend Developer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '🎨',
                'metadata_json' => [
                    'specialty' => 'frontend',
                    'bio' => 'Frontend expert in Livewire, HTMX, and Tailwind',
                    'skills' => ['Livewire', 'HTMX', 'Tailwind', 'Alpine.js', 'UX'],
                    'current_capacity' => 80,
                ],
            ],
            
            // Workers (DevOps)
            [
                'name' => 'Chen',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'DevOps Engineer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '🔧',
                'metadata_json' => [
                    'specialty' => 'devops',
                    'bio' => 'DevOps specialist in CI/CD and cloud infrastructure',
                    'skills' => ['Docker', 'Kubernetes', 'CI/CD', 'AWS', 'Azure'],
                    'current_capacity' => 75,
                ],
            ],
            
            // Workers (QA)
            [
                'name' => 'Sam',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'QA Engineer',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '🧪',
                'metadata_json' => [
                    'specialty' => 'qa',
                    'bio' => 'Quality assurance and test automation expert',
                    'skills' => ['Testing', 'PHPUnit', 'Browser Tests', 'CI/CD'],
                    'current_capacity' => 90,
                ],
            ],
            
            // Workers (Research)
            [
                'name' => 'Leo',
                'type' => 'workers',
                'role' => 'worker',
                'category' => 'worker',
                'title' => 'Research & Documentation',
                'status' => 'online',
                'provider' => 'ollama',
                'emoji' => '📚',
                'metadata_json' => [
                    'specialty' => 'research',
                    'bio' => 'Research analyst and documentation specialist',
                    'skills' => ['Research', 'Technical Writing', 'Documentation'],
                    'current_capacity' => 70,
                ],
            ],
        ];
    }
}