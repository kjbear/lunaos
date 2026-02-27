<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ActivityLogger;

class MissionControlPolished extends Component
{
    public array $agents = [];
    public array $activity = [];
    public array $tasks = [];
    public array $workload = [];
    public ?string $currentMission = null;
    public array $stats = [];

    protected $listeners = [
        'activity-received' => 'handleActivity',
        'task-moved' => 'refreshTasks',
    ];

    public function mount(): void
    {
        $this->loadAgents();
        $this->loadActivity();
        $this->loadTasks();
        $this->loadWorkload();
        $this->loadStats();
    }

    public function loadAgents(): void
    {
        $this->agents = [
            [
                'id' => 'main',
                'name' => 'Luna',
                'role' => 'Main Assistant',
                'model' => 'GLM-5',
                'status' => 'online',
                'avatar' => '🌙',
                'depth' => 0,
            ],
            [
                'id' => 'pm',
                'name' => 'Jordan',
                'role' => 'Project Manager',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '📋',
                'depth' => 1,
            ],
            [
                'id' => 'dave',
                'name' => 'Dave',
                'role' => 'PHP Coder',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '💻',
                'depth' => 2,
            ],
            [
                'id' => 'maya',
                'name' => 'Maya',
                'role' => 'Frontend',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '🎨',
                'depth' => 2,
            ],
            [
                'id' => 'chen',
                'name' => 'Chen',
                'role' => 'DevOps',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '🔧',
                'depth' => 2,
            ],
            [
                'id' => 'sam',
                'name' => 'Sam',
                'role' => 'Test Engineer',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '✅',
                'depth' => 2,
            ],
            [
                'id' => 'alex',
                'name' => 'Alex',
                'role' => 'API Architect',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '🔌',
                'depth' => 2,
            ],
        ];
    }

    public function loadActivity(): void
    {
        $this->activity = ActivityLogger::getRecent(50);
    }

    public function loadTasks(): void
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/projects.db';
        $this->tasks = [
            'todo' => [],
            'in_progress' => [],
            'blocked' => [],
            'done' => [],
        ];

        if (file_exists($dbPath)) {
            $pdo = new \PDO('sqlite:' . $dbPath);
            $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
            $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($tasks as $task) {
                $status = $task['status'] ?? 'todo';
                if (isset($this->tasks[$status])) {
                    $this->tasks[$status][] = $task;
                }
            }
        }
    }

    public function loadWorkload(): void
    {
        try {
            $stats = ActivityLogger::getStatsByAgent(7);
        } catch (\Exception $e) {
            $stats = [];
        }
        
        $this->workload = [
            'labels' => array_column($stats, 'agent_name'),
            'series' => [[
                'name' => 'Tasks',
                'data' => array_column($stats, 'count'),
            ]],
        ];
    }

    public function loadStats(): void
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/activity.db';
        
        $this->stats = [
            'total_tasks' => 0,
            'completed_today' => 0,
            'active_agents' => 1,
            'total_tokens' => 0,
        ];

        if (file_exists($dbPath)) {
            $pdo = new \PDO('sqlite:' . $dbPath);
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE status = 'done'");
            $this->stats['total_tasks'] = (int)$stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT SUM(tokens_used) FROM activity_logs");
            $this->stats['total_tokens'] = (int)$stmt->fetchColumn();
        }
    }

    public function handleActivity(array $event): void
    {
        array_unshift($this->activity, $event);
        $this->activity = array_slice($this->activity, 0, 50);
        $this->loadAgents();
        $this->loadStats();
    }

    public function refreshTasks(): void
    {
        $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.mission-control-polished');
    }
}
