<?php

namespace App\Services;

use App\Agents\AgentWorker;
use App\Agents\DaveAgentWorker;
use App\Agents\SamAgentWorker;
use App\Agents\ChenAgentWorker;
use App\Models\Agent;
use App\Models\Task;
use App\Models\AgentActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Worker Executor Service
 * 
 * The execution engine for AI worker agents.
 * Handles polling, task execution, status updates, and observability.
 * 
 * Usage:
 *   php artisan agent:run dave --once
 *   php artisan agent:run sam --daemon
 */
class WorkerExecutor
{
    /**
     * Agent name to execute
     */
    protected string $agentName;
    
    /**
     * Agent worker instance
     */
    protected AgentWorker $worker;
    
    /**
     * Agent configuration from database
     */
    protected Agent $agentConfig;
    
    /**
     * Log file path
     */
    protected string $logFile;
    
    /**
     * Whether to run once or continuously
     */
    protected bool $runOnce = false;
    
    /**
     * Running flag
     */
    protected bool $running = false;
    
    /**
     * Agent class mapping
     */
    protected static array $agentClasses = [
        'dave' => DaveAgentWorker::class,
        'sam' => SamAgentWorker::class,
        'chen' => ChenAgentWorker::class,
    ];
    
    /**
     * Create a new WorkerExecutor instance
     */
    public function __construct(string $agentName)
    {
        $this->agentName = strtolower($agentName);
        $this->logFile = storage_path('logs/agent-worker.log');
        
        $this->initializeWorker();
    }
    
    /**
     * Initialize the agent worker instance
     */
    protected function initializeWorker(): void
    {
        if (!isset(static::$agentClasses[$this->agentName])) {
            throw new RuntimeException("Unknown agent: {$this->agentName}. Available: " . implode(', ', array_keys(static::$agentClasses)));
        }
        
        // Load agent configuration from database
        $this->agentConfig = Agent::where('name', $this->agentName)->first();
        
        if (!$this->agentConfig) {
            throw new RuntimeException("Agent '{$this->agentName}' not found in database. Run seeders first.");
        }
        
        // Create worker instance
        $workerClass = static::$agentClasses[$this->agentName];
        $this->worker = new $workerClass();
        
        $this->log("Initialized {$this->agentName} worker", [
            'model' => $this->agentConfig->model,
            'provider' => $this->agentConfig->provider,
            'type' => $this->agentConfig->type ?? 'worker',
        ]);
    }
    
    /**
     * Set whether to run once or continuously
     */
    public function setRunOnce(bool $once = true): self
    {
        $this->runOnce = $once;
        return $this;
    }
    
    /**
     * Run the worker polling loop
     */
    public function run(): int
    {
        $this->running = true;
        $tasksProcessed = 0;
        
        $this->log("Worker started", [
            'mode' => $this->runOnce ? 'once' : 'daemon',
            'poll_interval' => $this->worker->pollInterval,
        ]);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║  🤖 {$this->agentName} Worker Started                              \n";
        echo "║  Model: {$this->agentConfig->model}                              \n";
        echo "║  Mode: " . ($this->runOnce ? 'Single Poll' : 'Daemon') . "                                    \n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
        
        while ($this->running) {
            try {
                $task = $this->poll();
                
                if ($task) {
                    $this->execute($task);
                    $tasksProcessed++;
                } else {
                    echo "⏳ [" . now()->format('H:i:s') . "] No pending tasks for {$this->agentName}\n";
                }
                
            } catch (\Throwable $e) {
                $this->handleError($e);
            }
            
            // If running once, exit after first poll
            if ($this->runOnce) {
                break;
            }
            
            // Sleep before next poll
            $this->log("Sleeping for {$this->worker->pollInterval}s", []);
            sleep($this->worker->pollInterval);
        }
        
        $this->log("Worker stopped", ['tasks_processed' => $tasksProcessed]);
        
        return 0;
    }
    
    /**
     * Poll for pending tasks
     */
    public function poll(): ?Task
    {
        $this->log("Polling for tasks", ['agent' => $this->agentName]);
        
        $task = Task::where('assigned_to', $this->agentName)
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->first();
        
        if ($task) {
            $this->log("Task found", [
                'task_id' => $task->id,
                'title' => $task->title,
                'step' => $task->step,
                'priority' => $task->priority,
            ]);
            
            echo "\n┌──────────────────────────────────────────────────────────────┐\n";
            echo "│  📋 Task #{$task->id}: {$task->title}                          \n";
            echo "│  Step: {$task->step}  |  Priority: {$task->priority}           \n";
            echo "└──────────────────────────────────────────────────────────────┘\n\n";
        }
        
        return $task;
    }
    
    /**
     * Execute a task using the worker agent
     */
    public function execute(Task $task): void
    {
        $startTime = microtime(true);
        
        $this->log("Executing task", ['task_id' => $task->id]);
        
        // Update task to in_progress
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        // Log activity
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $this->agentName,
            'action' => 'started',
            'metadata_json' => [
                'step' => $task->step,
                'priority' => $task->priority,
            ],
        ]);
        
        echo "🚀 Starting execution at " . now()->format('H:i:s') . "\n";
        
        try {
            // Use reflection to call protected processTask method
            $reflection = new \ReflectionClass($this->worker);
            $method = $reflection->getMethod('processTask');
            $method->setAccessible(true);
            $method->invoke($this->worker, $task);
            
            $duration = round((microtime(true) - $startTime) * 1000);
            
            $this->log("Task completed", [
                'task_id' => $task->id,
                'duration_ms' => $duration,
            ]);
            
            echo "\n✅ Task #{$task->id} completed in {$duration}ms\n\n";
            
        } catch (\Throwable $e) {
            $this->handleFailure($task, $e);
        }
    }
    
    /**
     * Complete a task successfully
     */
    public function complete(Task $task, array $artifacts = []): void
    {
        $nextStep = $task->getNextStep();
        $nextAssignee = $task->getNextAssignee();
        
        $task->update([
            'status' => 'complete',
            'step' => $nextStep ?? $task->step,
            'assigned_to' => $nextAssignee,
            'completed_at' => now(),
            'artifacts_json' => array_merge($task->artifacts_json ?? [], $artifacts),
        ]);
        
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $this->agentName,
            'action' => 'completed',
            'metadata_json' => [
                'next_step' => $nextStep,
                'next_assignee' => $nextAssignee,
                'artifacts' => $artifacts,
            ],
        ]);
        
        $this->log("Task completed and handed off", [
            'task_id' => $task->id,
            'next_step' => $nextStep,
            'next_assignee' => $nextAssignee,
        ]);
        
        echo "📤 Task handed off to {$nextAssignee} ({$nextStep})\n";
    }
    
    /**
     * Handle task failure
     */
    public function handleFailure(Task $task, \Throwable $e): void
    {
        $this->log("Task failed", [
            'task_id' => $task->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 'error');
        
        // Update task status
        $task->update([
            'status' => 'failed',
            'failure_reason' => $e->getMessage(),
            'retry_count' => ($task->retry_count ?? 0) + 1,
        ]);
        
        // Log activity
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $this->agentName,
            'action' => 'failed',
            'metadata_json' => [
                'error' => $e->getMessage(),
                'retry_count' => $task->retry_count,
            ],
        ]);
        
        echo "\n❌ Task #{$task->id} failed: {$e->getMessage()}\n\n";
    }
    
    /**
     * Handle general errors
     */
    protected function handleError(\Throwable $e): void
    {
        $this->log("Worker error", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 'error');
        
        echo "\n❌ Error: {$e->getMessage()}\n";
        echo "   File: {$e->getFile()}:{$e->getLine()}\n\n";
    }
    
    /**
     * Stop the worker
     */
    public function stop(): void
    {
        $this->running = false;
        $this->log("Worker stopped by signal", []);
    }
    
    /**
     * Log message to file and database
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$this->agentName}] {$message}";
        
        if (!empty($context)) {
            $logEntry .= " " . json_encode($context);
        }
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Append to log file
        File::append($this->logFile, $logEntry . "\n");
        
        // Also log to Laravel
        Log::{$level}("[{$this->agentName}] {$message}", $context);
    }
    
    /**
     * Get available agents
     */
    public static function getAvailableAgents(): array
    {
        return array_keys(static::$agentClasses);
    }
    
    /**
     * Check if agent exists
     */
    public static function agentExists(string $name): bool
    {
        return isset(static::$agentClasses[strtolower($name)]);
    }
}