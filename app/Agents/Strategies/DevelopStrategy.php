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

**⚠️ CRITICAL: FILE MODIFICATION RULES**
1. For NEW files: Return complete file content ✅
2. For EXISTING files (routes/web.php, config files, etc.):
   - READ the current file content first
   - Return ONLY the new code to ADD/APPEND
   - Use `// ADD:` marker to show where new code should be inserted
   - DO NOT replace the entire file
   - Example: "Add this route AFTER the existing routes:"

**REQUIREMENTS:**
1. Generate complete, working code
2. Follow Laravel 12 best practices
3. Use strict types
4. Include comprehensive docblocks
5. Write testable code
6. Follow existing project structure
7. NEVER overwrite core framework files unless explicitly required

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
     * 
     * CRITICAL SAFETY: Protects core framework files from destructive overwrites.
     * For existing files, checks if AI is replacing entire file vs patching.
     */
    protected function writeFiles(array $files): array
    {
        $artifacts = [
            'files_created' => [],
            'files_modified' => [],
        ];
        
        // Protected files that should NEVER be fully replaced
        $protectedFiles = [
            'routes/web.php',
            'routes/api.php',
            'config/database.php',
            'config/app.php',
            'config/auth.php',
            '.env',
        ];
        
        foreach ($files as $file) {
            $path = $file['path'];
            $content = $file['content'];
            $action = $file['action'] ?? 'created';
            
            $fullPath = base_path($path);
            
            // 🛡️ BLOCK destructive overwrites on protected existing files
            if (in_array($path, $protectedFiles) && file_exists($fullPath)) {
                $existingContent = file_get_contents($fullPath);
                $newLines = count(explode("\n", $content));
                $existingLines = count(explode("\n", $existingContent));
                
                // If new content is < 50% of existing, AI probably replaced it
                if ($newLines < $existingLines * 0.5) {
                    \Log::warning("⚠️ BLOCKED destructive overwrite", [
                        'file' => $path,
                        'task_id' => \Request::route('id') ?? 'unknown',
                        'new_lines' => $newLines,
                        'existing_lines' => $existingLines,
                        'ratio' => round($newLines / $existingLines, 2),
                    ]);
                    continue; // SKIP - don't destroy the file
                }
                
                \Log::info("✅ Protected file check passed", [
                    'file' => $path,
                    'new_lines' => $newLines,
                    'existing_lines' => $existingLines,
                ]);
            }
            
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
