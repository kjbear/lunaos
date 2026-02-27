<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;

class TaskDetail extends Component
{
    public ?Task $task = null;
    public $activities = [];
    public $agent = null;

    public function mount($task = null)
    {
        // Handle string ID or Task model from route binding
        if (is_string($task)) {
            $this->task = Task::findOrFail($task);
        } elseif ($task instanceof Task) {
            $this->task = $task;
        } elseif (request()->route('task')) {
            $routeTask = request()->route('task');
            $this->task = $routeTask instanceof Task ? $routeTask : Task::findOrFail($routeTask);
        } else {
            abort(404, 'Task not found');
        }
        
        $this->loadActivities();
        $this->loadAgent();
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

    public function render()
    {
        return view('livewire.task-detail');
    }
}
