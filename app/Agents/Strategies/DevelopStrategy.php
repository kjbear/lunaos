<?php

namespace App\Agents\Strategies;

use App\Models\Task;
use App\Models\Agent;
use App\Agents\Strategies\Concerns\HasWorkerCapabilities;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;

/**
 * Develop Strategy
 * 
 * Handles code generation and feature development tasks.
 * Extracted from DaveAgentWorker.
 */
class DevelopStrategy implements WorkerStrategy
{
    use HasWorkerCapabilities;
    
    /**
     * {@inheritDoc}
     */
    public function pollForWork(Agent $agent): ?Task
    {
        return Task::where('assigned_to', $agent->name)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('step', 'develop')
            ->orderBy('created_at', 'asc')
            ->first();
    }
    
    /**
     * {@inheritDoc}
     */
    public function processTask(Task $task, Agent $agent): void
    {
        try {
            \Illuminate\Support\Facades\Log::info("Develop strategy processing task #{$task->id}", [
                'agent' => $agent->name,
            ]);
            
            // Step 1: Generate code with AI
            $codeGeneration = $this->generateCode($task, $agent);
            
            // Step 2: Validate response
            if (!isset($codeGeneration['files']) || empty($codeGeneration['files'])) {
                throw new \Exception("AI returned no files");
            }
            
            // Step 3: Write files to disk
            $artifacts = $this->writeFiles($codeGeneration['files']);
            
            // Step 4: Git operations
            $branchName = $this->createFeatureBranch($task, $agent);
            $commitHash = $this->gitCommit($task, $agent, $artifacts);
            $prUrl = $this->createPullRequest($task, $agent, $branchName);
            
            // Step 5: Advance to QA
            $this->completeTask($task, $agent, 'qa', 'sam', [
                'branch' => $branchName,
                'commit_hash' => $commitHash,
                'pr_url' => $prUrl,
                'files_created' => $artifacts['files_created'],
                'ai_summary' => $codeGeneration['summary'] ?? 'Code generated',
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Develop strategy failed task #{$task->id}: {$e->getMessage()}");
            $this->failTask($task, $agent, $e->getMessage(), 'develop', 'dave');
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCapabilities(): array
    {
        return ['php', 'laravel', 'livewire', 'blade', 'api', 'refactor', 'bugfix'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getWorkflowSteps(): array
    {
        return ['develop'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'develop';
    }
    
    /**
     * Generate code using AI agent.
     */
    protected function generateCode(Task $task, Agent $agent): array
    {
        $prompt = $this->buildCodeGenerationPrompt($task, $agent);
        
        return $this->callAIJson($agent, $prompt, 4096);
    }
    
    /**
     * Build the code generation prompt.
     */
    protected function buildCodeGenerationPrompt(Task $task, Agent $agent): string
    {
        $repo = $this->getRepository($task);
        $repoName = $repo?->name ?? 'LunaOS';
        
        return <<<PROMPT
{$agent->system_prompt}

---

**TASK:** {$task->title}

**DESCRIPTION:** 
{$task->description}

**PROJECT:** {$repoName}

**CONTEXT:**
- Assigned to: {$agent->name}
- Current step: {$task->step}
- Task ID: {$task->id}
- Priority: {$task->priority}

**REQUIREMENTS:**
1. Generate complete, working code
2. Follow Laravel 12 best practices
3. Use strict types
4. Include comprehensive docblocks
5. Write testable code
6. Follow existing project structure

**OUTPUT:**
Return structured JSON with:
- summary: Brief explanation of implementation
- files: Array of files with path, content, and action (created/modified)
- tests_created: Boolean
- requires_migration: Boolean

**IMPORTANT:**
- Do NOT include markdown formatting in file contents
- Return ONLY valid JSON
- All files must be complete and ready to run
PROMPT;
    }
    
    /**
     * Write files to filesystem.
     */
    protected function writeFiles(array $files): array
    {
        $artifacts = [
            'files_created' => [],
            'files_modified' => [],
        ];
        
        foreach ($files as $file) {
            $path = $file['path'];
            $content = $file['content'];
            $action = $file['action'] ?? 'created';
            
            $fullPath = base_path($path);
            
            // Create directory if needed
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Write file
            $bytes = file_put_contents($fullPath, $content);
            
            // Track artifacts
            $key = $action === 'created' ? 'files_created' : 'files_modified';
            $artifacts[$key][] = $path;
        }
        
        return $artifacts;
    }
}
