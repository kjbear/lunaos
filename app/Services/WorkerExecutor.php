<?php

namespace App\Services;

use App\Agents\Strategies\StrategyRegistry;
use App\Agents\Strategies\WorkerStrategy;
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
 * Uses Strategy Pattern to load strategies from database.
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
     * Strategy instance for this agent
     */
    protected WorkerStrategy $strategy;
    
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
     * Create a new WorkerExecutor instance
     */
    public function __construct(string $agentName)
    {
        $this->agentName = strtolower($agentName);
        $this->logFile = storage_path('logs/agent-worker.log');
        
        $this->initializeWorker();
    }
    
    /**
     * Initialize the agent worker using strategy from database
     */
    protected function initializeWorker(): void
    {
        // Load agent configuration from database
        $this->agentConfig = Agent::where('name', $this->agentName)->first();
        
        if (!$this->agentConfig) {
            throw new RuntimeException("Agent '{$this->agentName}' not found in database. Run seeders first.");
        }
        
        // Get strategy class from database
        $strategyClass = $this->agentConfig->strategy_class;
        
        if (empty($strategyClass)) {
            throw new RuntimeException(
                "Agent '{$this->agentName}' has no strategy_class configured. " .
                "Update the agents table to set strategy_class (e.g., 'develop', 'qa', 'deploy')."
            );
        }
        
        // Load strategy from registry
        $this->strategy = StrategyRegistry::get($strategyClass);
        
        $this->log("Initialized {$this->agentName} worker", [
            'strategy' => $strategyClass,
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
        
        $pollInterval = 30; // Default poll interval in seconds
        
        $this->log("Worker started", [
            'mode' => $this->runOnce ? 'once' : 'daemon',
            'poll_interval' => $pollInterval,
            'strategy' => $this->agentConfig->strategy_class,
        ]);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║  🤖 {$this->agentName} Worker Started                              \n";
        echo "║  Model: {$this->agentConfig->model}                              \n";
        echo "║  Strategy: {$this->agentConfig->strategy_class}                          \n";
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
            $this->log("Sleeping for {$pollInterval}s", []);
            sleep($pollInterval);
        }
        
        $this->log("Worker stopped", ['tasks_processed' => $tasksProcessed]);
        
        return 0;
    }
    
    /**
     * Poll for pending tasks using strategy
     */
    public function poll(): ?Task
    {
        $this->log("Polling for tasks", [
            'agent' => $this->agentName,
            'strategy' => $this->agentConfig->strategy_class,
        ]);
        
        // Use strategy to poll for work
        $task = $this->strategy->pollForWork($this->agentConfig);
        
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
     * Execute a task using the strategy
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
            // Execute task using strategy
            $this->strategy->processTask($task, $this->agentConfig);
            
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
     * Get available agents from database
     */
    public static function getAvailableAgents(): array
    {
        return Agent::whereNotNull('strategy_class')
            ->pluck('name')
            ->toArray();
    }
    
    /**
     * Check if agent exists and has strategy configured
     */
    public static function agentExists(string $name): bool
    {
        return Agent::where('name', strtolower($name))
            ->whereNotNull('strategy_class')
            ->exists();
    }
}