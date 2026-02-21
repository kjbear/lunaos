<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::table('session_costs')->truncate();
        DB::table('model_health')->truncate();
        DB::table('docs')->truncate();
        DB::table('scheduled_items')->truncate();
        DB::table('activity_logs')->truncate();
        DB::table('task_logs')->truncate();
        DB::table('tasks')->truncate();
        DB::table('workspace_configs')->truncate();
        DB::table('agents')->truncate();
        DB::statement('PRAGMA foreign_keys = ON');

        // Seed agents
        $lunaId = DB::table('agents')->insertGetId([
            'name' => 'Luna', 
            'role' => 'coordinator', 
            'model' => 'GLM-5', 
            'status' => 'online', 
            'created_at' => now(), 
            'updated_at' => now()
        ]);
        
        $builderId = DB::table('agents')->insertGetId([
            'name' => 'Builder', 
            'role' => 'code_gen', 
            'model' => 'Dolphin 3.0', 
            'status' => 'offline', 
            'created_at' => now(), 
            'updated_at' => now()
        ]);

        // Seed tasks
        DB::table('tasks')->insert([
            'agent_id' => $lunaId,
            'name' => 'Initialize LunaOS project',
            'description' => 'Set up Laravel project with Herd',
            'status' => 'completed',
            'priority' => 'high',
            'tokens_used' => 50000,
            'cost' => 0.15,
            'started_at' => now()->subDays(7),
            'completed_at' => now()->subDays(6),
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);
        
        DB::table('tasks')->insert([
            'agent_id' => $lunaId,
            'name' => 'Set up database migrations',
            'description' => 'Create all tables for Phase 1',
            'status' => 'completed',
            'priority' => 'high',
            'tokens_used' => 8000,
            'cost' => 0.03,
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
        
        DB::table('tasks')->insert([
            'agent_id' => $lunaId,
            'name' => 'Build Task Manager UI',
            'description' => 'Create Livewire component for task display',
            'status' => 'running',
            'priority' => 'normal',
            'tokens_used' => 25000,
            'cost' => 0.08,
            'started_at' => now()->subDays(1),
            'completed_at' => null,
            'created_at' => now()->subDays(2),
            'updated_at' => now(),
        ]);
        
        DB::table('tasks')->insert([
            'agent_id' => $builderId,
            'name' => 'Generate Agent model',
            'description' => 'Create Eloquent model for agents table',
            'status' => 'pending',
            'priority' => 'normal',
            'tokens_used' => 0,
            'cost' => 0,
            'started_at' => null,
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed activity logs
        DB::table('activity_logs')->insert([
            'agent' => 'Luna',
            'action_type' => 'task',
            'action_name' => 'Created migration',
            'context' => json_encode(['file' => 'create_agents_table.php']),
            'impact' => 'medium',
            'status' => 'success',
            'created_at' => now()->subHours(2),
        ]);
        
        DB::table('activity_logs')->insert([
            'agent' => 'Luna',
            'action_type' => 'commit',
            'action_name' => 'Git commit',
            'context' => json_encode(['message' => 'Story 1.1 complete', 'files' => 59]),
            'impact' => 'high',
            'status' => 'success',
            'created_at' => now()->subHours(1),
        ]);
        
        DB::table('activity_logs')->insert([
            'agent' => 'Luna',
            'action_type' => 'system',
            'action_name' => 'Heartbeat check',
            'context' => json_encode(['emails' => 2, 'reminders' => 0]),
            'impact' => 'low',
            'status' => 'success',
            'created_at' => now()->subMinutes(30),
        ]);

        // Seed scheduled items
        DB::table('scheduled_items')->insert([
            'title' => 'Team standup',
            'type' => 'meeting',
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(9, 15),
            'agent' => 'Luna',
            'priority' => 'normal',
            'status' => 'pending',
            'notes' => 'Daily sync with sub-agents',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('scheduled_items')->insert([
            'title' => 'Review PR #12',
            'type' => 'task',
            'start_time' => now()->addDay()->setTime(14, 0),
            'end_time' => now()->addDay()->setTime(15, 0),
            'agent' => 'Luna',
            'priority' => 'high',
            'status' => 'pending',
            'notes' => 'Review database schema changes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed docs
        DB::table('docs')->insert([
            'slug' => 'getting-started',
            'title' => 'Getting Started with LunaOS',
            'section' => 'intro',
            'content' => '# Getting Started

LunaOS is a dashboard for AI assistant team visibility.

## Features

- Task Manager
- Org Chart
- Activity Feed
- Calendar
- Global Search',
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('docs')->insert([
            'slug' => 'architecture',
            'title' => 'System Architecture',
            'section' => 'technical',
            'content' => '# Architecture

LunaOS uses:
- Laravel 12
- Livewire 3
- HTMX 2
- Tailwind CSS 4
- SQLite with FTS5',
            'order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed model health
        DB::table('model_health')->insert([
            'model' => 'GLM-5',
            'status' => 'healthy',
            'cpu_percent' => 5.2,
            'memory_percent' => 12.5,
            'vram_percent' => null,
            'tokens_per_sec' => 45.5,
            'queue_depth' => 0,
            'checked_at' => now(),
        ]);
        
        DB::table('model_health')->insert([
            'model' => 'Dolphin 3.0',
            'status' => 'healthy',
            'cpu_percent' => 0,
            'memory_percent' => 0,
            'vram_percent' => 22.1,
            'tokens_per_sec' => 18.5,
            'queue_depth' => 0,
            'checked_at' => now(),
        ]);

        // Seed session costs
        DB::table('session_costs')->insert([
            'model' => 'GLM-5',
            'tokens_input' => 500000,
            'tokens_output' => 15000,
            'cost' => 0.12,
            'session_key' => 'main',
            'created_at' => now()->subDay(),
        ]);
        
        DB::table('session_costs')->insert([
            'model' => 'GLM-5',
            'tokens_input' => 450000,
            'tokens_output' => 12000,
            'cost' => 0.10,
            'session_key' => 'main',
            'created_at' => now(),
        ]);

        $this->command->info('Database seeded successfully!');
    }
}