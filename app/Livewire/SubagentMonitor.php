<?php

namespace App\Livewire;

use Livewire\Component;

class SubagentMonitor extends Component
{
    public array $agents = [];
    public array $recentActivity = [];
    public bool $autoRefresh = true;
    public int $pollInterval = 5000; // 5 seconds in ms
    public ?string $selectedAgent = null;

    public function mount(): void
    {
        $this->loadStatus();
    }

    public function loadStatus(): void
    {
        // Static data for now - will be replaced with real API calls
        $this->agents = [
            [
                'id' => 'main',
                'name' => 'Luna',
                'role' => 'Main Assistant',
                'model' => 'GLM-5',
                'status' => 'online',
                'avatar' => '🌙',
            ],
            [
                'id' => 'pm',
                'name' => 'Jordan',
                'role' => 'Project Manager',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '📋',
            ],
            [
                'id' => 'dave',
                'name' => 'Dave',
                'role' => 'PHP Coder',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '💻',
            ],
            [
                'id' => 'maya',
                'name' => 'Maya',
                'role' => 'Frontend',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '🎨',
            ],
            [
                'id' => 'chen',
                'name' => 'Chen',
                'role' => 'DevOps',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '🔧',
            ],
            [
                'id' => 'sam',
                'name' => 'Sam',
                'role' => 'Test Engineer',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '✅',
            ],
            [
                'id' => 'alex',
                'name' => 'Alex',
                'role' => 'API Architect',
                'model' => 'Dolphin 3.0',
                'status' => 'idle',
                'avatar' => '🔌',
            ],
        ];

        // Static recent activity
        $this->recentActivity = [
            [
                'time' => '15:16',
                'agent' => 'Dave',
                'status' => 'done',
                'task' => 'Created OrgChart component',
            ],
            [
                'time' => '14:52',
                'agent' => 'Dave',
                'status' => 'done',
                'task' => 'Updated OrgChart with static data',
            ],
            [
                'time' => '13:28',
                'agent' => 'Dave',
                'status' => 'done',
                'task' => 'Created Product model',
            ],
        ];
    }

    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function selectAgent(string $agentId): void
    {
        $this->selectedAgent = $this->selectedAgent === $agentId ? null : $agentId;
    }

    public function render()
    {
        return view('livewire.subagent-monitor');
    }
}
