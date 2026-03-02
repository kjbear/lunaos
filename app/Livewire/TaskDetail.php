<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;

#[Layout('components.layouts.app')]
class TaskDetail extends Component
{
    public ?Task $task = null;
    public $activities = [];
    public $agent = null;
    #[Url]
    public string $viewMode = 'list';
    public ?int $taskId = null;

    public function show($taskId)
    {
        $this->task = Task::findOrFail($taskId);
        $this->taskId = $taskId;
        $this->loadActivities();
        $this->loadAgent();
        
        return $this->render();
    }

    public function mount($task = null)
    {
        // Handle string ID or Task model from route binding
        if (is_string($task)) {
            $this->taskId = (int) $task;
        } elseif ($task instanceof Task) {
            $this->task = $task;
        } elseif (request()->route('task')) {
            $routeTask = request()->route('task');
            if ($routeTask instanceof Task) {
                $this->task = $routeTask;
            } else {
                $this->taskId = (int) $routeTask;
            }
        } else {
            abort(404, 'Task not found');
        }
        
        // Load view_mode from query string
        if ($viewMode = request()->query('view_mode')) {
            $this->viewMode = $viewMode;
        }
        
        // Load task if not already loaded
        if (!$this->task && $this->taskId) {
            $this->task = Task::findOrFail($this->taskId);
        }
        
        $this->loadActivities();
        $this->loadAgent();
    }

    public function updateViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function loadActivities(): void
    {
        $this->activities = AgentActivity::where('task_id', $this->task->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadAgent(): void
    {
        if ($this->task && $this->task->assigned_to) {
            $this->agent = Agent::where('name', $this->task->assigned_to)->first();
        }
    }

    public function getStatusBadgeClassProperty(): string
    {
        return match($this->task->status) {
            'completed' => 'bg-green-500/20 text-green-400 border-green-500/30',
            'in_progress' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'blocked' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'pending' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            default => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        };
    }

    public function getPriorityBadgeClassProperty(): string
    {
        return match($this->task->priority) {
            'critical' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'high' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            'medium' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'low' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
            default => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        };
    }

    public function __invoke()
    {
        return $this->render();
    }

    public function render()
    {
        return view('livewire.task-detail', [
            'task' => $this->task,
            'activities' => $this->activities,
            'agent' => $this->agent,
        ])->layout('components.layouts.app', [
            'title' => "Task #{$this->task->id}"
        ]);
    }
}
