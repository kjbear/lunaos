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
 * Base Agent Worker Class
 * 
 * Abstract class for all AI agent workers in the development pipeline.
 * Implements polling pattern where agents poll for tasks every 30 seconds.
 * 
 * Configuration is loaded from database (agents table) for flexibility.
 */
abstract class AgentWorker
{
    /**
     * Agent name (dave, sam, chen, etc.)
     */
    public string $name;
    
    /**
     * Poll interval in seconds
     */
    public int $pollInterval = 30;
    
    /**
     * Agent capabilities
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
     * Git service instance
     */
    protected ?GitService $gitService = null;
    
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
     * Main worker loop - polls for work indefinitely
     */
    public function run(): void
    {
        $this->running = true;
        
        $config = $this->getAgentConfig();
        echo "🤖 {$this->name} worker started (model: {$config->model}, interval: {$this->pollInterval}s)\n";
        Log::info("Agent worker started: {$this->name}", ['model' => $config->model, 'provider' => $config->provider]);
        
        while ($this->running) {
            try {
                $task = $this->pollForWork();
                
                if ($task) {
                    echo "📋 {$this->name} picked up task #{$task->id}: {$task->title}\n";
                    Log::info("Agent {$this->name} processing task #{$task->id}");
                    $this->processTask($task);
                }
                
            } catch (\Exception $e) {
                Log::error("Agent worker error: {$e->getMessage()}", [
                    'agent' => $this->name,
                    'trace' => $e->getTraceAsString()
                ]);
                echo "❌ {$this->name} error: {$e->getMessage()}\n";
            }
            
            sleep($this->pollInterval);
        }
    }
    
    /**
     * Stop the worker loop
     */
    public function stop(): void
    {
        $this->running = false;
        echo "🛑 {$this->name} worker stopped\n";
    }
    
    /**
     * Poll database for tasks assigned to this agent
     * Override this method in child classes to specify polling criteria
     */
    abstract protected function pollForWork(): ?Task;
    
    /**
     * Process a single task - implemented by each agent
     */
    abstract protected function processTask(Task $task): void;
    
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
