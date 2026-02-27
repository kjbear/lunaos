<?php

namespace App\Agents\Strategies\Concerns;

use App\Models\Task;
use App\Models\Agent;
use App\Models\Repository;
use App\Services\GitService;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;
use Illuminate\Support\Facades\Log;

/**
 * Trait: HasWorkerCapabilities
 * 
 * Provides common worker functionality shared across all strategies.
 * Includes Git operations, AI calls, task progression, and activity logging.
 */
trait HasWorkerCapabilities
{
    /**
     * Call AI agent with configured model and return parsed response.
     * Includes skill doc context if configured.
     */
    protected function callAI(Agent $agent, string $prompt, int $maxTokens = 1000): string
    {
        // Enhance prompt with skill doc if configured
        $enhancedPrompt = $this->buildEnhancedPrompt($agent, $prompt);
        
        $provider = $this->mapProvider($agent->provider);
        
        $response = Ai::agent()
            ->withLab($provider)
            ->withModel($agent->model)
            ->withSystemPrompt($enhancedPrompt)
            ->withMaxTokens($maxTokens)
            ->run('');
        
        return (string) $response;
    }
    
    /**
     * Build enhanced prompt with skill doc context.
     */
    protected function buildEnhancedPrompt(Agent $agent, string $taskPrompt): string
    {
        $systemPrompt = $agent->system_prompt ?? '';
        
        // Append skill doc if configured
        if ($agent->skill_doc_path) {
            $skillDoc = $this->loadSkillDoc($agent->skill_doc_path);
            if ($skillDoc) {
                $systemPrompt .= "\n\n### SKILL DEFINITION\n{$skillDoc}";
            }
        }
        
        // Append skill metadata constraints if present
        if (!empty($agent->skill_metadata['constraints'])) {
            $constraints = $agent->skill_metadata['constraints'];
            $systemPrompt .= "\n\n### CONSTRAINTS\n";
            
            if (!empty($constraints['must_do'])) {
                $systemPrompt .= "\n**MUST DO:**\n";
                foreach ($constraints['must_do'] as $rule) {
                    $systemPrompt .= "- {$rule}\n";
                }
            }
            
            if (!empty($constraints['must_not'])) {
                $systemPrompt .= "\n**MUST NOT DO:**\n";
                foreach ($constraints['must_not'] as $rule) {
                    $systemPrompt .= "- {$rule}\n";
                }
            }
        }
        
        return "{$systemPrompt}\n\n### TASK\n{$taskPrompt}";
    }
    
    /**
     * Load skill doc from filesystem.
     */
    protected function loadSkillDoc(string $skillDocPath): ?string
    {
        $fullPath = base_path($skillDocPath);
        
        if (!file_exists($fullPath)) {
            \Illuminate\Support\Facades\Log::warning("Skill doc not found: {$skillDocPath}");
            return null;
        }
        
        return file_get_contents($fullPath);
    }
    
    /**
     * Call AI and parse JSON response.
     */
    protected function callAIJson(Agent $agent, string $prompt, int $maxTokens = 1000): array
    {
        $content = $this->callAI($agent, $prompt, $maxTokens);
        
        // Remove markdown code blocks
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
        
        $result = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse AI JSON: " . json_last_error_msg());
        }
        
        return $result;
    }
    
    /**
     * Map provider string to Lab enum.
     */
    protected function mapProvider(string $provider): Lab
    {
        return match($provider) {
            'openrouter' => Lab::OpenRouter,
            'anthropic' => Lab::Anthropic,
            'openai' => Lab::OpenAi,
            default => Lab::Ollama,
        };
    }
    
    /**
     * Get or create GitService instance for a task.
     */
    protected function gitService(Task $task): GitService
    {
        static $services = [];
        
        $key = $task->id;
        if (!isset($services[$key])) {
            $repo = $this->getRepository($task);
            $services[$key] = new GitService($repo);
        }
        
        return $services[$key];
    }
    
    /**
     * Get repository for a task.
     */
    protected function getRepository(Task $task): ?Repository
    {
        if ($task->repository_id) {
            return Repository::find($task->repository_id);
        }
        
        return Repository::where('name', 'LunaOS')->where('is_active', true)->first();
    }
    
    /**
     * Create feature branch for a task.
     */
    protected function createFeatureBranch(Task $task, Agent $agent): string
    {
        $repo = $this->getRepository($task);
        $prefix = $repo?->branch_prefix ?? 'feature';
        $branchName = "{$prefix}/{$task->id}-" . \Illuminate\Support\Str::slug($task->title);
        
        $gitService = $this->gitService($task);
        $gitService->checkoutBranch($branchName);
        
        Log::info("Worker {$agent->name} created branch: {$branchName}", ['task_id' => $task->id]);
        
        return $branchName;
    }
    
    /**
     * Commit changes to git.
     */
    protected function gitCommit(Task $task, Agent $agent, array $artifacts): string
    {
        $gitService = $this->gitService($task);
        
        $gitService->stageAll();
        
        $message = "Dev: {$task->title} (#{$task->id})\n\nAI-generated by {$agent->name}";
        $gitService->commit($message);
        
        $commitHash = $gitService->getCommitHash('HEAD');
        
        Log::info("Worker {$agent->name} committed: {$commitHash}", ['task_id' => $task->id]);
        
        return $commitHash;
    }
    
    /**
     * Create pull request (placeholder for GitHub API).
     */
    protected function createPullRequest(Task $task, Agent $agent, string $branchName): string
    {
        $gitService = $this->gitService($task);
        $gitService->push($branchName);
        
        $prUrl = "https://github.com/kjbear/lunaos/pulls/new/{$branchName}";
        
        Log::info("Worker {$agent->name} created PR: {$prUrl}", ['task_id' => $task->id]);
        
        return $prUrl;
    }
    
    /**
     * Complete task and advance to next workflow step.
     */
    protected function completeTask(
        Task $task,
        Agent $agent,
        string $nextStep,
        string $nextAssignee,
        array $artifacts = []
    ): void {
        $task->update([
            'status' => 'complete',
            'step' => $nextStep,
            'assigned_to' => $nextAssignee,
            'artifacts_json' => json_encode($artifacts),
            'updated_at' => now(),
        ]);
        
        $this->logActivity($task, $agent, 'completed', array_merge([
            'next_step' => $nextStep,
            'next_assignee' => $nextAssignee,
        ], $artifacts));
        
        Log::info("Task #{$task->id} advanced to {$nextStep} ({$nextAssignee})", [
            'agent' => $agent->name,
            'artifacts' => $artifacts,
        ]);
    }
    
    /**
     * Fail task and return to previous step/agent.
     */
    protected function failTask(
        Task $task,
        Agent $agent,
        string $reason,
        string $backToStep,
        string $backToAgent
    ): void {
        $task->update([
            'status' => 'failed',
            'step' => $backToStep,
            'assigned_to' => $backToAgent,
            'updated_at' => now(),
        ]);
        
        $this->logActivity($task, $agent, 'failed', [
            'reason' => $reason,
            'back_to_step' => $backToStep,
            'back_to_agent' => $backToAgent,
        ]);
        
        Log::warning("Task #{$task->id} failed: {$reason}", [
            'agent' => $agent->name,
            'back_to' => "{$backToStep} ({$backToAgent})",
        ]);
    }
    
    /**
     * Log agent activity to database.
     */
    protected function logActivity(Task $task, Agent $agent, string $action, array $metadata = []): void
    {
        \App\Models\AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $agent->name,
            'action' => $action,
            'metadata_json' => $metadata,
        ]);
    }
    
    /**
     * Checkout a feature branch.
     */
    protected function checkoutBranch(Task $task, Agent $agent): string
    {
        $gitService = $this->gitService($task);
        $branchName = "feature/{$task->id}-" . \Illuminate\Support\Str::slug($task->title);
        
        $gitService->checkoutBranch($branchName, true);
        
        Log::info("Worker {$agent->name} checked out branch: {$branchName}", ['task_id' => $task->id]);
        
        return $branchName;
    }
}
