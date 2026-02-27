<?php

namespace App\Traits;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

/**
 * Agent Worker Trait
 * 
 * Provides common functionality for all agent workers (Dave, Sam, Chen, etc.)
 * including task polling, state management, and lifecycle hooks.
 * 
 * @mixin \App\Agents\AgentWorker
 */
trait AgentWorkerTrait
{
    /**
     * The agent name/identifier (e.g., 'dave', 'sam', 'chen')
     */
    public string $agentName;
    
    /**
     * Capabilities this agent provides
     */
    public array $capabilities = [];
    
    /**
     * Polling interval in seconds
     */
    public int $pollInterval = 30;
    
    /**
     * Workflow steps this agent handles
     */
    public array $handledSteps = [];
    
    /**
     * Poll for tasks assigned to this agent
     */
    public function pollForTasks(): ?Task
    {
        return Task::where('assigned_to', $this->agentName)
            ->whereIn('status', ['pending', 'in_progress'])
            ->when(!empty($this->handledSteps), function (Builder $query) {
                return $query->whereIn('step', $this->handledSteps);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->first();
    }
    
    /**
     * Mark task as in progress
     */
    public function startTask(Task $task): void
    {
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        $this->logActivity($task, 'started');
    }
    
    /**
     * Mark task as complete and advance to next step
     */
    public function completeTask(Task $task, string $nextStep, ?string $nextAssignee, array $artifacts = []): void
    {
        $task->update([
            'status' => 'complete',
            'step' => $nextStep,
            'assigned_to' => $nextAssignee,
            'completed_at' => now(),
            'artifacts_json' => json_encode($artifacts),
        ]);
        
        $this->logActivity($task, 'completed', [
            'next_step' => $nextStep,
            'next_assignee' => $nextAssignee,
            'artifacts' => $artifacts,
        ]);
        
        echo "✅ Task #{$task->id} complete → {$nextStep} ({$nextAssignee})\n";
    }
    
    /**
     * Mark task as failed
     */
    public function failTask(Task $task, string $reason, ?string $retryStep = null, ?string $retryAssignee = null): void
    {
        $retryCount = $task->retry_count ?? 0;
        
        $task->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'retry_count' => $retryCount + 1,
            'assigned_to' => $retryAssignee ?? $task->assigned_to,
            'step' => $retryStep ?? $task->step,
        ]);
        
        $this->logActivity($task, 'failed', [
            'reason' => $reason,
            'retry_count' => $retryCount + 1,
        ]);
        
        echo "❌ Task #{$task->id} failed: {$reason}\n";
    }
    
    /**
     * Log activity for a task
     */
    public function logActivity(Task $task, string $action, array $metadata = []): void
    {
        \App\Models\AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $this->agentName,
            'action' => $action,
            'metadata_json' => json_encode($metadata),
        ]);
    }
    
    /**
     * Get task count by status for this agent
     */
    public function getTaskCounts(): array
    {
        return [
            'pending' => Task::where('assigned_to', $this->agentName)
                ->where('status', 'pending')
                ->count(),
            'in_progress' => Task::where('assigned_to', $this->agentName)
                ->where('status', 'in_progress')
                ->count(),
            'completed_today' => Task::where('assigned_to', $this->agentName)
                ->where('status', 'complete')
                ->whereDate('completed_at', today())
                ->count(),
            'failed' => Task::where('assigned_to', $this->agentName)
                ->where('status', 'failed')
                ->count(),
        ];
    }
    
    /**
     * Check if this agent can handle a specific step
     */
    public function canHandleStep(string $step): bool
    {
        return empty($this->handledSteps) || in_array($step, $this->handledSteps);
    }
    
    /**
     * Get agent capabilities
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }
    
    /**
     * Check if agent has a specific capability
     */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities);
    }
    
    /**
     * Get available tasks count
     */
    public function getAvailableTaskCount(): int
    {
        return Task::where('assigned_to', $this->agentName)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }
    
    /**
     * Get next task in queue (without claiming it)
     */
    public function peekNextTask(): ?Task
    {
        return $this->pollForTasks();
    }
    
    /**
     * Process a single task (must be implemented by concrete class)
     */
    abstract protected function processTask(Task $task): void;
    
    /**
     * Run the agent worker loop
     */
    public function run(): void
    {
        echo "🚀 Starting {$this->agentName} worker...\n";
        echo "   Capabilities: " . implode(', ', $this->capabilities) . "\n";
        echo "   Poll interval: {$this->pollInterval}s\n\n";
        
        while (true) {
            $task = $this->pollForTasks();
            
            if ($task) {
                echo "📋 Found task #{$task->id}: {$task->title}\n";
                $this->startTask($task);
                
                try {
                    $this->processTask($task);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error(
                        "Agent {$this->agentName} failed task #{$task->id}: {$e->getMessage()}",
                        ['task' => $task->toArray(), 'trace' => $e->getTraceAsString()]
                    );
                    $this->failTask($task, $e->getMessage());
                }
            } else {
                echo "⏳ No tasks for {$this->agentName}. Sleeping {$this->pollInterval}s...\n";
                sleep($this->pollInterval);
            }
        }
    }
}
