<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\AgentActivity;
use Livewire\Component;
use Livewire\Attributes\On;

/**
 * Kanban Board Component
 * 
 * Agent-agnostic task visualization showing tasks grouped by workflow step.
 * Supports real-time updates, filtering, and task management.
 */
class KanbanBoard extends Component
{
    public string $selectedAgent = 'all'; // all, dave, sam, chen, security
    public string $selectedStep = 'all'; // all, develop, qa, security, staging, production
    public string $search = '';
    public bool $showCompleted = false;
    public bool $autoRefresh = true;
    public int $refreshInterval = 10; // seconds
    
    // Agent color mapping
    public array $agentColors = [
        'dave' => 'blue',
        'sam' => 'emerald',
        'chen' => 'purple',
        'security' => 'orange',
        'unassigned' => 'slate',
    ];
    
    // Step labels
    public array $stepLabels = [
        'develop' => 'Develop',
        'qa' => 'QA',
        'security' => 'Security',
        'staging' => 'Staging',
        'production' => 'Production',
    ];
    
    // Step icons
    public array $stepIcons = [
        'develop' => '🔧',
        'qa' => '🧪',
        'security' => '🔒',
        'staging' => '🚀',
        'production' => '✅',
    ];
    
    public function mount()
    {
        // Can add initial state loading here
    }
    
    #[On('task-updated')]
    public function refreshTasks()
    {
        // Triggered when a task is updated elsewhere
    }
    
    /**
     * Get filtered tasks grouped by step
     */
    public function getGroupedTasksProperty(): array
    {
        $query = Task::query()
            ->when($this->selectedAgent !== 'all', fn($q) => $q->where('assigned_to', $this->selectedAgent))
            ->when($this->selectedStep !== 'all', fn($q) => $q->where('step', $this->selectedStep))
            ->when(!$this->showCompleted, fn($q) => $q->where('status', '!=', 'complete'))
            ->when($this->search, fn($q) => $q->where(function($sq) {
                $sq->where('title', 'like', "%{$this->search}%")
                   ->orWhere('description', 'like', "%{$this->search}%");
            }))
            // SQLite-compatible ordering (FIELD is MySQL-only)
            ->orderByRaw("CASE status
                WHEN 'pending' THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'blocked' THEN 3
                WHEN 'failed' THEN 4
                ELSE 5
            END")
            ->orderBy('created_at', 'asc');
        
        $tasks = $query->get();
        
        // Group by step
        return [
            'develop' => $tasks->where('step', 'develop')->values(),
            'qa' => $tasks->where('step', 'qa')->values(),
            'security' => $tasks->where('step', 'security')->values(),
            'staging' => $tasks->where('step', 'staging')->values(),
            'production' => $tasks->where('step', 'production')->values(),
        ];
    }
    
    /**
     * Get task counts by agent
     */
    public function getAgentCountsProperty(): array
    {
        return [
            'all' => Task::where('status', '!=', 'complete')->count(),
            'dave' => Task::assignedTo('dave')->available()->count(),
            'sam' => Task::assignedTo('sam')->available()->count(),
            'chen' => Task::assignedTo('chen')->available()->count(),
            'security' => Task::assignedTo('security')->available()->count(),
        ];
    }
    
    /**
     * Get recent activity
     */
    public function getRecentActivityProperty()
    {
        return AgentActivity::with('task')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get overall stats
     */
    public function getStatsProperty(): array
    {
        return [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed_today' => Task::completedToday()->count(),
            'failed' => Task::where('status', 'failed')->count(),
        ];
    }
    
    /**
     * Mark task as complete
     */
    public function completeTask(int $taskId)
    {
        $task = Task::findOrFail($taskId);
        $nextStep = $task->getNextStep();
        $nextAssignee = $task->getNextAssignee();
        
        if ($nextStep && $nextAssignee) {
            $task->update([
                'status' => 'complete',
                'step' => $nextStep,
                'assigned_to' => $nextAssignee,
                'completed_at' => now(),
            ]);
            
            AgentActivity::create([
                'task_id' => $task->id,
                'agent_name' => 'manual',
                'action' => 'advanced',
                'metadata_json' => json_encode([
                    'from_step' => $task->getOriginal('step'),
                    'to_step' => $nextStep,
                    'manual' => true,
                ]),
            ]);
            
            $this->dispatch('task-updated');
            
            session()->flash('success', "Task #{$taskId} advanced to {$nextStep}");
        }
    }
    
    /**
     * Reassign task to different agent
     */
    public function reassignTask(int $taskId, string $newAgent)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['assigned_to' => $newAgent]);
        
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => 'manual',
            'action' => 'reassigned',
            'metadata_json' => json_encode([
                'from' => $task->getOriginal('assigned_to'),
                'to' => $newAgent,
                'manual' => true,
            ]),
        ]);
        
        $this->dispatch('task-updated');
        
        session()->flash('success', "Task #{$taskId} reassigned to {$newAgent}");
    }
    
    /**
     * Delete task
     */
    public function deleteTask(int $taskId)
    {
        Task::destroy($taskId);
        $this->dispatch('task-updated');
        session()->flash('success', 'Task deleted');
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.kanban-board', [
            'groupedTasks' => $this->groupedTasks,
            'agentCounts' => $this->agentCounts,
            'recentActivity' => $this->recentActivity,
            'stats' => $this->stats,
        ])->layout('components.layouts.app', ['title' => 'Kanban Board']);
    }
}
