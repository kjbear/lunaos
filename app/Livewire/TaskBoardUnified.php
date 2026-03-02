<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\AgentActivity;
use Livewire\Component;
use Livewire\Attributes\On;

/**
 * TaskBoardUnified Component - Kanban board view for tasks
 * 
 * Combines TaskBoard logic with KanbanBoard styling for a unified board experience.
 */
class TaskBoardUnified extends Component
{
    // Kanban columns mapped to workflow steps
    public array $columns = [
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
    
    // Filters
    public string $selectedAgent = 'all';
    public ?string $selectedStatus = null;
    public bool $autoRefresh = true;
    public int $refreshInterval = 10;
    
    // Agent color mapping
    public array $agentColors = [
        'dave' => 'blue',
        'sam' => 'emerald',
        'chen' => 'purple',
        'security' => 'orange',
        'unassigned' => 'slate',
    ];
    
    public function mount()
    {
        // Initial state
    }
    
    #[On('task-updated')]
    public function refreshTasks()
    {
        // Triggered when a task is updated elsewhere
    }
    
    /**
     * Get tasks grouped by workflow step
     */
    public function getGroupedTasksProperty(): array
    {
        $query = Task::query()
            ->when($this->selectedAgent !== 'all', fn($q) => $q->where('assigned_to', $this->selectedAgent))
            ->when($this->selectedStatus !== null, fn($q) => $q->where('status', $this->selectedStatus))
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
     * Get overall stats
     */
    public function getStatsProperty(): array
    {
        return [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed_today' => Task::where('status', 'complete')->whereDate('completed_at', today())->count(),
            'failed' => Task::where('status', 'failed')->count(),
        ];
    }
    
    /**
     * Get agent counts
     */
    public function getAgentCountsProperty(): array
    {
        return [
            'all' => Task::whereIn('status', ['pending', 'in_progress'])->count(),
            'dave' => Task::where('assigned_to', 'dave')->whereIn('status', ['pending', 'in_progress'])->count(),
            'sam' => Task::where('assigned_to', 'sam')->whereIn('status', ['pending', 'in_progress'])->count(),
            'chen' => Task::where('assigned_to', 'chen')->whereIn('status', ['pending', 'in_progress'])->count(),
            'security' => Task::where('assigned_to', 'security')->whereIn('status', ['pending', 'in_progress'])->count(),
        ];
    }
    
    /**
     * Complete task and advance workflow
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
     * View task details
     */
    public function viewTask(int $taskId)
    {
        return redirect()->route('tasks.show', $taskId);
    }
    
    /**
     * Move task to different step (drag and drop)
     */
    public function moveTask(int $taskId, string $newStep)
    {
        $task = Task::findOrFail($taskId);
        $oldStep = $task->step;
        
        if ($oldStep !== $newStep) {
            $task->update([
                'step' => $newStep,
                'status' => 'in_progress',
            ]);
            
            AgentActivity::create([
                'task_id' => $task->id,
                'agent_name' => 'manual',
                'action' => 'moved',
                'metadata_json' => json_encode([
                    'from_step' => $oldStep,
                    'to_step' => $newStep,
                    'manual' => true,
                ]),
            ]);
            
            $this->dispatch('task-updated');
            
            session()->flash('success', "Task #{$taskId} moved to {$newStep}");
        }
    }
    
    public function render()
    {
        return view('livewire.task-board-unified', [
            'groupedTasks' => $this->groupedTasks,
            'stats' => $this->stats,
            'agentCounts' => $this->agentCounts,
        ])->layout('components.layouts.app', ['title' => 'Task Board']);
    }
}
