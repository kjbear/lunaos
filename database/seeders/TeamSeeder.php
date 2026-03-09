<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        TeamMember::query()->delete();
        
        // Morgan (CPO) - uses board_member role
        $morgan = TeamMember::create([
            'name' => 'Morgan',
            'title' => 'Chief Product Officer',
            'role' => 'board_member',
            'type' => 'workers',
            'category' => 'board_member',
            'model' => 'glm-5',
            'provider' => 'ollama',
            'emoji' => '🎯',
            'system_prompt' => 'You are Morgan, the Chief Product Officer for the product portfolio.',
            'settings' => json_encode(['skills' => ['chief-product-officer', 'architecture-designer']]),
            'status' => 'active',
        ]);
        
        // Board Members (Debate Team) - report to Kyle
        $boardMembers = [
            ['name' => 'Steven', 'title' => 'CEO', 'emoji' => '🎯'],
            ['name' => 'Gwynne', 'title' => 'COO', 'emoji' => '👔'],
            ['name' => 'Werner', 'title' => 'CTO', 'emoji' => '💻'],
            ['name' => 'Warren', 'title' => 'CFO', 'emoji' => '💰'],
            ['name' => 'Bozoma', 'title' => 'CMO', 'emoji' => '📢'],
            ['name' => 'Fidji', 'title' => 'Chief Product Strategy', 'emoji' => '📦'],
        ];
        
        foreach ($boardMembers as $member) {
            TeamMember::create([
                'name' => $member['name'],
                'title' => $member['title'],
                'role' => 'board_member',
                'type' => 'board-members',
                'category' => 'board_member',
                'model' => 'glm-5',
                'provider' => 'ollama',
                'emoji' => $member['emoji'],
                'system_prompt' => "You are {$member['name']}, a board member.",
                'status' => 'active',
            ]);
        }
        
        // PMs - report to Morgan
        $jordan = TeamMember::create([
            'name' => 'Jordan',
            'title' => 'Product Manager - SPA',
            'role' => 'worker',
            'type' => 'workers',
            'category' => 'worker',
            'model' => 'glm-5',
            'provider' => 'ollama',
            'emoji' => '📋',
            'system_prompt' => 'You are Jordan, PM for SPA.',
            'settings' => json_encode(['skills' => ['product-manager', 'feature-forge']]),
            'status' => 'active',
            'parent_id' => $morgan->id,
        ]);
        
        $alex = TeamMember::create([
            'name' => 'Alex',
            'title' => 'Product Manager - IHSSP',
            'role' => 'worker',
            'type' => 'workers',
            'category' => 'worker',
            'model' => 'glm-5',
            'provider' => 'ollama',
            'emoji' => '📱',
            'system_prompt' => 'You are Alex, PM for IHSSP.',
            'settings' => json_encode(['skills' => ['product-manager', 'api-designer']]),
            'status' => 'active',
            'parent_id' => $morgan->id,
        ]);
        
        // Workers
        $workers = [
            ['name' => 'Dave', 'title' => 'Backend Developer', 'parent_id' => $jordan->id, 'emoji' => '🔧'],
            ['name' => 'Maya', 'title' => 'Frontend Developer', 'parent_id' => $jordan->id, 'emoji' => '🎨'],
            ['name' => 'Chen', 'title' => 'DevOps Engineer', 'parent_id' => $jordan->id, 'emoji' => '⚙️'],
            ['name' => 'Sam', 'title' => 'QA Engineer', 'parent_id' => $alex->id, 'emoji' => '🧪'],
            ['name' => 'Leo', 'title' => 'Research', 'parent_id' => $alex->id, 'emoji' => '🔍'],
        ];
        
        foreach ($workers as $worker) {
            TeamMember::create([
                'name' => $worker['name'],
                'title' => $worker['title'],
                'role' => 'worker',
                'type' => 'workers',
                'category' => 'worker',
                'model' => 'dolphin',
                'provider' => 'ollama',
                'emoji' => $worker['emoji'],
                'system_prompt' => "You are {$worker['name']}, a worker.",
                'status' => 'active',
                'parent_id' => $worker['parent_id'],
            ]);
        }
        
        $this->command->info('✅ Team seeded: 1 CPO + 6 Board + 2 PMs + 5 Workers = 14 members');
    }
}
