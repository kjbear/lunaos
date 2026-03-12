<?php

namespace App\Agents;

use App\Ai\Agents\DaveCoder;
use App\Models\Task;
use App\Models\Agent;
use Illuminate\Support\Str;

/**
 * Dave - PHP Development Agent Worker
 * 
 * Worker-tier agent responsible for:
 * - Writing PHP/Laravel code
 * - Creating Livewire components
 * - Building API endpoints
 * - Refactoring existing code
 * - Fixing bugs
 * 
 * Uses Qwen3-Coder via Ollama Cloud for code generation
 * Polls every 30 seconds for development tasks
 */
class DaveAgentWorker extends AgentWorker
{
    public string $name = 'dave';
    
    public AgentType $type = AgentType::WORKER;
    
    public int $pollInterval = 30; // 30 seconds (fast polling for execution)
    
    public array $capabilities = ['php', 'laravel', 'livewire', 'blade', 'api', 'refactor', 'bugfix'];
    
    // Model loaded from DB via $agent->model // Ollama Cloud model
    
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
        $providerName = $this->getProviderName();  // Get provider NAME string for proper resolution
        $providerEnum = $this->getProvider();      // Get Lab enum for display
        
        echo "🤖 Spawning DaveCoder with {$model} ({$providerEnum->name})...\n";

        $agent = new DaveCoder;

        // Build the prompt with system prompt from config
        $prompt = $this->buildCodeGenerationPrompt($task);

        // Call the AI agent - use provider NAME string for correct config resolution
        // (ollama-cloud uses OpenAI-compatible API, returns JSON in text)
        $response = $agent->prompt(
            $prompt,
            provider: $providerName,  // Pass string 'ollama-cloud', not Lab::OpenAI
            model: $model,
            timeout: 300,  // 5 minutes for code generation
        );
        
        // Parse JSON from text response
        $text = (string) $response;
        
        // Debug: Log raw response length
        \Illuminate\Support\Facades\Log::debug("Dave raw response", [
            'length' => strlen($text),
            'preview' => substr($text, 0, 500),
        ]);
        
        echo "📦 Response length: " . strlen($text) . " characters\n";
        
        // Remove markdown code blocks if present (model might wrap JSON)
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/', '', $text);
        $text = trim($text);
        
        // Try to decode JSON
        $result = json_decode($text, true);
        
        // If failed, try to fix common issues
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
            
            // Save problematic JSON for debugging
            file_put_contents(storage_path('logs/dave-json-error.json'), $text);
            
            // Try JSON5 or relaxed parsing (if available)
            // For now, just provide better error message
            throw new \Exception("AI returned invalid JSON: {$error}. Saved to logs/dave-json-error.json for inspection.");
        }
        
        if (!is_array($result) || empty($result)) {
            throw new \Exception("AI returned empty or invalid response");
        }
        
        // Ensure required fields exist
        if (!isset($result['files']) || empty($result['files'])) {
            throw new \Exception("AI response missing 'files' array");
        }
        
        // Ensure boolean fields are native booleans
        $result['tests_created'] = (bool) ($result['tests_created'] ?? false);
        $result['requires_migration'] = (bool) ($result['requires_migration'] ?? false);
        
        echo "✅ Parsed " . count($result['files']) . " files from AI response\n";
        
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
