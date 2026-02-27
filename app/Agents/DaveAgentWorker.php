<?php

namespace App\Agents;

use App\Ai\Agents\DaveCoder;
use App\Models\Task;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;

/**
 * Dave - PHP Development Agent Worker
 * 
 * Handles all PHP/Laravel development tasks using Qwen3-Coder via Ollama Cloud.
 * 
 * Workflow:
 * 1. Poll for tasks assigned to 'dave'
 * 2. Spawn DaveCoder agent with Qwen3-Coder model
 * 3. Pass task description to generate code
 * 4. DaveCoder returns structured JSON with files
 * 5. Write files to workspace
 * 6. Git commit and create PR
 * 7. Advance workflow to QA (Sam)
 */
class DaveAgentWorker extends AgentWorker
{
    public string $name = 'dave';
    
    public int $pollInterval = 30; // 30 seconds
    
    public array $capabilities = ['php', 'laravel', 'livewire', 'blade', 'api', 'refactor', 'bugfix'];
    
    protected string $model = 'qwen3-coder:latest'; // Ollama Cloud model
    
    /**
     * Poll for development tasks
     */
    protected function pollForWork(): ?Task
    {
        $task = Task::where('assigned_to', 'dave')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('step', 'develop')
            ->orderBy('created_at', 'asc')
            ->first();
        
        return $task;
    }
    
    /**
     * Process a development task using DaveCoder AI agent
     */
    protected function processTask(Task $task): void
    {
        try {
            echo "🔍 Dave analyzing task #{$task->id}: {$task->title}\n";
            $this->logActivity($task, 'started', ['action' => 'analysis']);
            
            // Step 1: Use DaveCoder AI agent to generate code
            $codeGeneration = $this->generateCodeWithAI($task);
            
            // Step 2: Validate the response
            if (!isset($codeGeneration['files']) || empty($codeGeneration['files'])) {
                throw new \Exception("AI returned no files");
            }
            
            // Step 3: Write the files to disk
            echo "📝 Writing " . count($codeGeneration['files']) . " files...\n";
            $artifacts = [
                'files_created' => [],
                'files_modified' => [],
                'summary' => $codeGeneration['summary'] ?? 'Code generated successfully',
                'tests_created' => $codeGeneration['tests_created'] ?? false,
                'requires_migration' => $codeGeneration['requires_migration'] ?? false,
            ];
            
            foreach ($codeGeneration['files'] as $file) {
                $path = $file['path'];
                $content = $file['content'];
                $action = $file['action'] ?? 'created';
                
                // Build full path
                $fullPath = base_path($path);
                
                // Create directory if needed
                $dir = dirname($fullPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                // Write file
                $bytes = file_put_contents($fullPath, $content);
                echo "  ✅ {$action}: {$path} ({$bytes} bytes)\n";
                
                // Track artifacts
                $key = $action === 'created' ? 'files_created' : 'files_modified';
                $artifacts[$key][] = $path;
            }
            
            echo "✅ Dave: Generated " . count($codeGeneration['files']) . " files\n";
            echo "   Summary: {$artifacts['summary']}\n";
            
            // Step 4: Git operations (handled by GitService in parent)
            $branchName = $this->createFeatureBranch($task);
            $commitHash = $this->gitCommit($task, $artifacts);
            $prUrl = $this->createPullRequest($task, $branchName);
            
            // Step 5: Mark complete and advance to QA (Sam)
            $this->completeTask($task, 'qa', 'sam', [
                'branch' => $branchName,
                'commit_hash' => $commitHash,
                'pr_url' => $prUrl,
                'ai_summary' => $artifacts['summary'],
                'files_created' => $artifacts['files_created'],
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Dave failed task #{$task->id}: {$e->getMessage()}", [
                'task' => $task->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->failTask($task, $e->getMessage(), 'develop', 'dave');
        }
    }
    
    /**
     * Use DaveCoder AI agent with configured model to generate code
     */
    protected function generateCodeWithAI(Task $task): array
    {
        $model = $this->getModel();
        $provider = $this->getProvider();
        $settings = $this->getModelSettings();
        
        echo "🤖 Spawning DaveCoder with {$model} ({$provider->name})...\n";
        
        $agent = new DaveCoder;
        
        // Build the prompt with system prompt from config
        $prompt = $this->buildCodeGenerationPrompt($task);
        
        // Call the AI agent with database-configured model
        $response = $agent->prompt(
            $prompt,
            provider: $provider,
            model: $model,
            timeout: 300,  // 5 minutes for code generation
            options: $settings,
        );
        
        // Extract structured output from response
        // Note: Manual JSON extraction is more reliable than structured() method
        $content = (string) $response;
        
        // Remove markdown code blocks if present
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
        
        // Parse JSON
        $result = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse AI JSON response: " . json_last_error_msg());
        }
        
        // Ensure boolean fields are actually booleans (AI sometimes returns arrays)
        if (isset($result['tests_created']) && !is_bool($result['tests_created'])) {
            $result['tests_created'] = !empty($result['tests_created']);
        }
        if (isset($result['requires_migration']) && !is_bool($result['requires_migration'])) {
            $result['requires_migration'] = !empty($result['requires_migration']);
        }
        
        return $result;
    }
    
    /**
     * Build the code generation prompt
     */
    protected function buildCodeGenerationPrompt(Task $task): string
    {
        $repo = $this->getRepository($task);
        $repoName = $repo?->name ?? 'LunaOS';
        $systemPrompt = $this->getSystemPrompt();
        
        return <<<PROMPT
{$systemPrompt}

---

**TASK:** {$task->title}

**DESCRIPTION:** 
{$task->description}

**PROJECT:** {$repoName}

**CONTEXT:**
- Assigned to: {$this->name}
- Current step: {$task->step}
- Task ID: {$task->id}
- Priority: {$task->priority}
- Repository: {$repoName}

**REQUIREMENTS:**
1. Generate complete, working code
2. Follow Laravel 12 best practices
3. Use strict types
4. Include comprehensive docblocks
5. Write testable code
6. Follow existing project structure

**YOUR TOOLS:**
- WriteFile: Create or modify files
- ReadFile: Read existing files (if needed)
- ListDirectory: Explore directory structure (if needed)

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
    
}
