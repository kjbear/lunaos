<?php

namespace App\Agents;

use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;
use App\Models\Repository;
use App\Services\GitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;

/**
 * Agent Type Enum
 * Defines the role and behavior pattern for different agent types
 */
enum AgentType: string
{
    case WORKER = 'worker';           // Execution-focused (Dave, Sam, Chen)
    case BOARD = 'board';             // Coordination-focused (Jordan, Alex)
    case EXECUTIVE = 'executive';     // Strategic oversight (Executive Board)
}

/**
 * Base Agent Worker Class
 * 
 * Abstract class for all AI agents in the LunaOS system.
 * Implements polling pattern where agents check for work at configured intervals.
 * 
 * Supports three agent types:
 * - WORKER: Execute concrete tasks (code, test, deploy) - 30s polling
 * - BOARD: Coordinate and make decisions (assign, prioritize) - 2-5 min polling
 * - EXECUTIVE: Strategic oversight and health monitoring - 5-10 min polling
 * 
 * Configuration is loaded from database (agents table) for flexibility.
 */
abstract class AgentWorker
{
    /**
     * Agent name (dave, sam, chen, jordan, alex, etc.)
     */
    public string $name;
    
    /**
     * Agent type (worker, board, executive)
     * Determines behavior patterns and polling frequency
     */
    public AgentType $type = AgentType::WORKER;
    
    /**
     * Poll interval in seconds
     * Default varies by type:
     * - WORKER: 30s
     * - BOARD: 120s (2 min)
     * - EXECUTIVE: 300s (5 min)
     */
    public int $pollInterval = 30;
    
    /**
     * Agent capabilities/skills
     * Examples: ['php', 'laravel'] for workers, ['prioritize', 'assign'] for board
     */
    public array $capabilities = [];
    
    /**
     * Worker running flag
     */
    protected bool $running = false;
    
    /**
     * Cached agent configuration from database
     */
    protected ?Agent $agentConfig = null;
    
    /**
     * Cached repository configuration
     */
    protected ?Repository $repositoryConfig = null;
    
    /**
     * Git service instance (workers only)
     */
    protected ?GitService $gitService = null;
    
    /**
     * Constructor - initialize agent with proper defaults for type
     */
    public function __construct()
    {
        // Set default poll interval based on agent type if not overridden
        if ($this->pollInterval === 30 && $this->type !== AgentType::WORKER) {
            $this->pollInterval = match($this->type) {
                AgentType::BOARD => 120,      // 2 minutes for coordination
                AgentType::EXECUTIVE => 300,  // 5 minutes for oversight
                AgentType::WORKER => 30,      // 30 seconds for execution
            };
        }
    }
    
    /**
     * Get the agent configuration from database
     */
    protected function getAgentConfig(): Agent
    {
        if (!$this->agentConfig) {
            $this->agentConfig = Agent::where('name', $this->name)->firstOrFail();
        }
        
        return $this->agentConfig;
    }
    
    /**
     * Check if this agent is a worker type (executes concrete tasks)
     */
    public function isWorker(): bool
    {
        return $this->type === AgentType::WORKER;
    }
    
    /**
     * Check if this agent is a board type (coordination/decisions)
     */
    public function isBoard(): bool
    {
        return $this->type === AgentType::BOARD;
    }
    
    /**
     * Check if this agent is an executive type (strategic oversight)
     */
    public function isExecutive(): bool
    {
        return $this->type === AgentType::EXECUTIVE;
    }
    
    /**
     * Get the model identifier (provider/model)
     */
    protected function getModel(): string
    {
        return $this->getAgentConfig()->model;
    }
    
    /**
     * Get the AI provider (ollama, openrouter, anthropic, etc.)
     */
    protected function getProvider(): Lab
    {
        $provider = $this->getAgentConfig()->provider;
        return match($provider) {
            'openrouter' => Lab::OpenRouter,
            'anthropic' => Lab::Anthropic,
            'openai' => Lab::OpenAi,
            default => Lab::Ollama,
        };
    }
    
    /**
     * Get the system prompt from database config
     */
    protected function getSystemPrompt(): string
    {
        return $this->getAgentConfig()->system_prompt ?? $this->getDefaultPrompt();
    }
    
    /**
     * Get model settings (temperature, max_tokens, etc.)
     */
    protected function getModelSettings(): array
    {
        return $this->getAgentConfig()->settingsWithDefaults;
    }
    
    /**
     * Get the repository for a task
     */
    protected function getRepository(Task $task): ?Repository
    {
        if ($task->repository_id) {
            return Repository::find($task->repository_id);
        }
        
        // Default to active LunaOS repo
        return Repository::where('name', 'LunaOS')->where('is_active', true)->first();
    }
    
    /**
     * Get GitService instance with repository context
     */
    protected function gitService(Task $task): GitService
    {
        if (!$this->gitService) {
            $repo = $this->getRepository($task);
            $this->gitService = new GitService($repo);
        }
        
        return $this->gitService;
    }
    
    /**
     * Get default system prompt if none configured
     */
    protected function getDefaultPrompt(): string
    {
        return "You are an AI assistant helping with software development tasks.";
    }
    
    /**
     * Main agent loop - polls for work indefinitely
     * Behavior varies by agent type:
     * - WORKER: Execute concrete tasks (code, test, deploy)
     * - BOARD: Coordinate, assign, prioritize, unblock
     * - EXECUTIVE: Monitor health, alert on anomalies
     */
    public function run(): void
    {
        $this->running = true;
        
        $config = $this->getAgentConfig();
        $typeLabel = ucfirst($this->type->value);
        
        echo "🤖 {$this->name} ({$typeLabel}) started (model: {$config->model}, interval: {$this->pollInterval}s)\n";
        Log::info("Agent started: {$this->name}", [
            'type' => $this->type->value,
            'model' => $config->model,
            'provider' => $config->provider,
            'interval' => $this->pollInterval
        ]);
        
        while ($this->running) {
            try {
                // Different polling strategies based on agent type
                match($this->type) {
                    AgentType::WORKER => $this->runWorkerCycle(),
                    AgentType::BOARD => $this->runBoardCycle(),
                    AgentType::EXECUTIVE => $this->runExecutiveCycle(),
                };
                
            } catch (\Exception $e) {
                Log::error("Agent error: {$e->getMessage()}", [
                    'agent' => $this->name,
                    'type' => $this->type->value,
                    'trace' => $e->getTraceAsString()
                ]);
                echo "❌ {$this->name} error: {$e->getMessage()}\n";
            }
            
            sleep($this->pollInterval);
        }
    }
    
    /**
     * Worker cycle: poll for tasks and execute them
     * Used by: Dave (dev), Sam (QA), Chen (DevOps)
     */
    protected function runWorkerCycle(): void
    {
        $task = $this->pollForWork();
        
        if ($task) {
            echo "📋 {$this->name} picked up task #{$task->id}: {$task->title}\n";
            Log::info("Worker {$this->name} processing task #{$task->id}");
            $this->processTask($task);
        }
    }
    
    /**
     * Board cycle: check for coordination needs
     * Used by: Jordan (PM), Alex (API Architect)
     * 
     * Checks for:
     * - Blocked tasks needing escalation
     * - Unassigned tasks needing prioritization
     * - Team conflicts needing resolution
     * - Sprint/iteration planning needs
     */
    protected function runBoardCycle(): void
    {
        // Check for blocked tasks
        $blockedTasks = $this->pollForBlockedTasks();
        foreach ($blockedTasks as $task) {
            echo "🚧 {$this->name} addressing blocked task #{$task->id}: {$task->title}\n";
            $this->handleBlockedTask($task);
        }
        
        // Check for unassigned tasks needing prioritization
        $unassignedTasks = $this->pollForUnassignedTasks();
        if (count($unassignedTasks) > 0) {
            echo "📊 {$this->name} prioritizing " . count($unassignedTasks) . " unassigned tasks\n";
            $this->prioritizeAndAssignTasks($unassignedTasks);
        }
        
        // Agent-specific board work
        $this->processBoardWork();
    }
    
    /**
     * Executive cycle: monitor overall system health
     * Used by: Executive Board members
     * 
     * Checks for:
     * - Workflow bottlenecks
     * - Resource allocation issues
     * - Performance anomalies
     * - Strategic alignment
     */
    protected function runExecutiveCycle(): void
    {
        // Monitor workflow health
        $workflowHealth = $this->assessWorkflowHealth();
        if ($workflow_health['hasIssues']) {
            echo "⚠️  {$this->name} detected workflow issues\n";
            $this->addressWorkflowIssues($workflowHealth);
        }
        
        // Check team capacity
        $capacity = $this->assessTeamCapacity();
        if ($capacity['overloaded']) {
            echo "⚠️  {$this->name} detected team overload\n";
            $this->rebalanceWorkload($capacity);
        }
        
        // Executive-specific strategic work
        $this->processExecutiveWork();
    }
    
    /**
     * Stop the worker loop
     */
    public function stop(): void
    {
        $this->running = false;
        echo "🛑 {$this->name} worker stopped\n";
    }
    
    // ============================================
    // WORKER METHODS (for execution-focused agents)
    // ============================================
    
    /**
     * Poll database for tasks assigned to this worker
     * Override this method in worker child classes to specify polling criteria
     */
    protected function pollForWork(): ?Task
    {
        return Task::where('assigned_to', $this->name)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->first();
    }
    
    /**
     * Process a single task - implemented by worker agents
     * Default implementation for board/executive agents (no-op)
     */
    protected function processTask(Task $task): void
    {
        // Board and executive agents don't process individual tasks
        // Worker agents override this method
    }
    
    // ============================================
    // BOARD METHODS (for coordination-focused agents)
    // ============================================
    
    /**
     * Poll for blocked tasks needing escalation
     */
    protected function pollForBlockedTasks()
    {
        return Task::where('status', 'blocked')
            ->orderBy('updated_at', 'asc') // Oldest first
            ->get();
    }
    
    /**
     * Poll for unassigned tasks needing prioritization
     */
    protected function pollForUnassignedTasks()
    {
        return Task::whereNull('assigned_to')
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    /**
     * Handle a blocked task - override in board agent classes
     */
    protected function handleBlockedTask(Task $task): void
    {
        // Default: log and notify
        $this->logActivity($task, 'escalated', [
            'reason' => 'Task blocked, needs human/AI intervention'
        ]);
    }
    
    /**
     * Prioritize and assign unassigned tasks - override in board agent classes
     */
    protected function prioritizeAndAssignTasks(array $tasks): void
    {
        // Default: log overview
        Log::info("Board agent reviewing " . count($tasks) . " unassigned tasks");
    }
    
    /**
     * Process board-specific coordination work
     * Override in board agent child classes
     */
    protected function processBoardWork(): void
    {
        // Board agents can override this for custom coordination logic
    }
    
    // ============================================
    // EXECUTIVE METHODS (for strategic oversight)
    // ============================================
    
    /**
     * Assess overall workflow health
     * Returns array with metrics and issue flags
     */
    protected function assessWorkflowHealth(): array
    {
        $totalTasks = Task::count();
        $blockedTasks = Task::where('status', 'blocked')->count();
        $staleTasks = Task::where('updated_at', '<', now()->subHours(24))->count();
        
        return [
            'total' => $totalTasks,
            'blocked' => $blockedTasks,
            'stale' => $staleTasks,
            'blockedPct' => $totalTasks > 0 ? ($blockedTasks / $totalTasks) * 100 : 0,
            'stalePct' => $totalTasks > 0 ? ($staleTasks / $totalTasks) * 100 : 0,
            'hasIssues' => $blockedTasks > 5 || $staleTasks > 10,
        ];
    }
    
    /**
     * Assess team capacity and workload
     */
    protected function assessTeamCapacity(): array
    {
        $agents = Agent::all();
        $workload = [];
        $totalCapacity = 0;
        $totalWork = 0;
        
        foreach ($agents as $agent) {
            $activeTasks = Task::where('assigned_to', $agent->name)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            
            $workload[$agent->name] = $activeTasks;
            $totalWork += $activeTasks;
            $totalCapacity += 10; // Assume 10 tasks per agent max
        }
        
        return [
            'workload' => $workload,
            'totalWork' => $totalWork,
            'totalCapacity' => $totalCapacity,
            'utilizationPct' => $totalCapacity > 0 ? ($totalWork / $totalCapacity) * 100 : 0,
            'overloaded' => $totalWork > ($totalCapacity * 0.8), // 80% threshold
        ];
    }
    
    /**
     * Address workflow issues - override in executive agent classes
     */
    protected function addressWorkflowIssues(array $health): void
    {
        $this->logSystemActivity('workflow_health_alert', $health);
    }
    
    /**
     * Rebalance workload across team - override in executive agent classes
     */
    protected function rebalanceWorkload(array $capacity): void
    {
        $this->logSystemActivity('capacity_alert', $capacity);
    }
    
    /**
     * Process executive-specific strategic work
     * Override in executive agent child classes
     */
    protected function processExecutiveWork(): void
    {
        // Executive agents can override this for custom strategic logic
    }
    
    /**
     * Log system-level activity (for board/executive agents)
     */
    protected function logSystemActivity(string $action, array $data = []): void
    {
        AgentActivity::create([
            'task_id' => null, // System-level, not task-specific
            'agent_name' => $this->name,
            'action' => $action,
            'artifacts' => $data,
            'duration_ms' => 0,
        ]);
        
        Log::info("System activity: {$this->name} - {$action}", $data);
    }
    
    /**
     * Log agent activity
     */
    protected function logActivity(Task $task, string $action, array $artifacts = []): void
    {
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $this->name,
            'action' => $action,
            'artifacts' => $artifacts,
            'duration_ms' => 0, // Can be calculated if needed
        ]);
        
        Log::info("Agent activity logged: {$this->name} - {$action}", [
            'task_id' => $task->id,
            'artifacts' => $artifacts
        ]);
    }
    
    /**
     * Update task status and advance workflow
     */
    protected function completeTask(Task $task, string $nextStep, string $nextAssignee, array $artifacts = []): void
    {
        $task->update([
            'status' => 'complete',
            'current_step' => $nextStep,
            'assigned_to' => $nextAssignee,
            'updated_at' => now(),
        ]);
        
        $this->logActivity($task, 'completed', $artifacts);
        
        echo "✅ {$this->name} completed task #{$task->id} → {$nextStep} ({$nextAssignee})\n";
        Log::info("Task completed: #{$task->id} advanced to {$nextStep} ({$nextAssignee})");
    }
    
    /**
     * Fail task and return to previous step
     */
    protected function failTask(Task $task, string $reason, string $backToStep, string $backToAgent): void
    {
        $task->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'current_step' => $backToStep,
            'assigned_to' => $backToAgent,
            'retry_count' => $task->retry_count + 1,
            'updated_at' => now(),
        ]);
        
        $this->logActivity($task, 'failed', ['reason' => $reason]);
        
        echo "❌ {$this->name} failed task #{$task->id}: {$reason}\n";
        Log::warning("Task failed: #{$task->id} - {$reason}");
    }
    
    /**
     * Create a feature branch for the task using repository settings
     */
    protected function createFeatureBranch(Task $task): string
    {
        $repo = $this->getRepository($task);
        $prefix = $repo?->branch_prefix ?? 'feature';
        $branchName = "{$prefix}/{$task->id}-" . \Illuminate\Support\Str::slug($task->title);
        
        $gitService = $this->gitService($task);
        $gitService->checkoutBranch($branchName);
        
        echo "🌿 Created branch: {$branchName}\n";
        
        return $branchName;
    }
    
    /**
     * Commit changes to git
     */
    protected function gitCommit(Task $task, array $artifacts): string
    {
        $gitService = $this->gitService($task);
        
        // Stage all changes
        $gitService->stageAll();
        
        // Commit with standardized message
        $message = "Dev: {$task->title} (#{$task->id})\n\nAI-generated by {$this->name}";
        $gitService->commit($message);
        
        // Get commit hash
        $commitHash = $gitService->getCommitHash('HEAD');
        
        echo "💾 Committed: {$commitHash}\n";
        
        return $commitHash;
    }
    
    /**
     * Create pull request (placeholder - implement with GitHub API)
     */
    protected function createPullRequest(Task $task, string $branchName): string
    {
        $gitService = $this->gitService($task);
        
        // Push to remote
        $gitService->push($branchName);
        
        // TODO: Use GitHub API to create actual PR
        // For now, return mock URL
        $prUrl = "https://github.com/kjbear/lunaos/pulls/new/{$branchName}";
        
        echo "🔀 PR created: {$prUrl}\n";
        
        return $prUrl;
    }
}
