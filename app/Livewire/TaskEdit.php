<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\AgentActivity;
use Livewire\Component;

/**
 * TaskEdit Component - Edit task form
 * 
 * Form for creating or editing tasks with validation and proper workflow integration.
 */
class TaskEdit extends Component
{
    public ?Task $task = null;
    public bool $isCreate = false;
    
    // Form fields
    public string $title = '';
    public string $description = '';
    public ?string $assigned_to = null;
    public ?int $repository_id = null;
    public string $step = 'develop';
    public string $status = 'pending';
    public string $priority = 'medium';
    public string $task_type = 'feature';
    public ?string $branch_name = null;
    public ?string $pr_url = null;
    public ?string $failure_reason = null;
    
    // Validation
    public array $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'assigned_to' => 'nullable|string|max:50',
        'step' => 'required|in:develop,qa,security,staging,production',
        'status' => 'required|in:pending,in_progress,complete,blocked,failed',
        'priority' => 'required|in:low,medium,high,critical',
        'task_type' => 'required|in:feature,bug,improvement,hotfix',
        'branch_name' => 'nullable|string|max:255',
        'pr_url' => 'nullable|url|max:500',
        'failure_reason' => 'nullable|string',
    ];
    
    // Available options
    public array $agents = [
        'dave' => 'Dave (Dev)',
        'sam' => 'Sam (QA)',
        'chen' => 'Chen (DevOps)',
        'security' => 'Security Bot',
    ];
    
    public array $steps = [
        'develop' => '🔧 Develop',
        'qa' => '🧪 QA',
        'security' => '🔒 Security',
        'staging' => '🚀 Staging',
        'production' => '✅ Production',
    ];
    
    public array $priorities = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];
    
    public array $taskTypes = [
        'feature' => 'Feature',
        'bug' => 'Bug',
        'improvement' => 'Improvement',
        'hotfix' => 'Hotfix',
    ];
    
    public function mount(?Task $task = null)
    {
        if ($task) {
            $this->task = $task;
            $this->isCreate = false;
            
            // Populate form
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->assigned_to = $task->assigned_to;
            $this->repository_id = $task->repository_id;
            $this->step = $task->step ?? 'develop';
            $this->status = $task->status ?? 'pending';
            $this->priority = $task->priority ?? 'medium';
            $this->task_type = $task->task_type ?? 'feature';
            $this->branch_name = $task->branch_name;
            $this->pr_url = $task->pr_url;
            $this->failure_reason = $task->failure_reason;
        } else {
            $this->isCreate = true;
        }
    }
    
    /**
     * Save task
     */
    public function save()
    {
        $this->validate();
        
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'assigned_to' => $this->assigned_to,
            'repository_id' => $this->repository_id,
            'step' => $this->step,
            'status' => $this->status,
            'priority' => $this->priority,
            'task_type' => $this->task_type,
            'branch_name' => $this->branch_name,
            'pr_url' => $this->pr_url,
            'failure_reason' => $this->failure_reason,
        ];
        
        if ($this->isCreate) {
            // Create new task
            $this->task = Task::create($data);
            
            AgentActivity::create([
                'task_id' => $this->task->id,
                'agent_name' => 'manual',
                'action' => 'created',
                'metadata_json' => json_encode([
                    'title' => $this->title,
                    'step' => $this->step,
                    'assigned_to' => $this->assigned_to,
                    'priority' => $this->priority,
                ]),
            ]);
            
            session()->flash('success', "Task #{$this->task->id} created successfully");
        } else {
            // Update existing task
            $oldStep = $this->task->step;
            $oldStatus = $this->task->status;
            
            $this->task->update($data);
            
            // Log activity if step or status changed
            if ($oldStep !== $this->step || $oldStatus !== $this->status) {
                AgentActivity::create([
                    'task_id' => $this->task->id,
                    'agent_name' => 'manual',
                    'action' => 'updated',
                    'metadata_json' => json_encode([
                        'from_step' => $oldStep,
                        'to_step' => $this->step,
                        'from_status' => $oldStatus,
                        'to_status' => $this->status,
                    ]),
                ]);
            }
            
            $this->dispatch('task-updated');
            
            session()->flash('success', "Task #{$this->task->id} updated successfully");
        }
        
        return redirect()->route('tasks.show', $this->task->id);
    }
    
    /**
     * Cancel and go back
     */
    public function cancel()
    {
        if ($this->task) {
            return redirect()->route('tasks.show', $this->task->id);
        }
        return redirect()->route('tasks');
    }
    
    /**
     * Delete task
     */
    public function deleteTask(int $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->delete();
        
        session()->flash('success', "Task #{$taskId} deleted successfully");
        
        return redirect()->route('tasks');
    }
    
    public function render()
    {
        return view('livewire.task-edit', [
            'isCreate' => $this->isCreate,
            'task' => $this->task,
        ])->layout('components.layouts.app', [
            'title' => $this->isCreate ? 'Create Task' : 'Edit Task'
        ]);
    }
}
