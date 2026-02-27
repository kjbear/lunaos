<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ToolCallParserService
{
    private string $sessionsPath;
    
    public function __construct()
    {
        // Default OpenClaw sessions path
        $this->sessionsPath = env('OPENCLAW_SESSIONS_PATH', 
            env('HOME') . '/.openclaw/agents/main/sessions');
    }

    /**
     * Parse tool calls from OpenClaw session JSONL files
     */
    public function parseToolCalls(int $sinceMinutes = 30): int
    {
        $ingested = 0;
        $since = now()->subMinutes($sinceMinutes);
        
        // Get all JSONL files (excluding .lock and reset files)
        $files = File::glob($this->sessionsPath . '/*.jsonl');
        
        foreach ($files as $file) {
            $ingested += $this->parseSessionFile($file, $since);
        }
        
        return $ingested;
    }

    /**
     * Parse a single session JSONL file
     */
    private function parseSessionFile(string $file, Carbon $since): int
    {
        $ingested = 0;
        $sessionId = basename($file, '.jsonl');
        
        // Check if we've already processed this file recently
        $fileModTime = Carbon::createFromTimestamp(File::lastModified($file));
        if ($fileModTime->lt($since)) {
            return 0;
        }

        // Read file line by line
        $handle = fopen($file, 'r');
        if (!$handle) {
            return 0;
        }

        $lastTimestamp = null;
        $currentModel = null;
        $currentProvider = null;
        
        while (($line = fgets($handle)) !== false) {
            $entry = json_decode($line, true);
            
            if (!$entry || ($entry['type'] ?? '') !== 'message') {
                continue;
            }

            $timestamp = Carbon::parse($entry['timestamp'] ?? now());
            $message = $entry['message'] ?? [];
            
            // Skip old entries
            if ($timestamp->lt($since)) {
                continue;
            }

            // Track model from assistant messages
            if (($message['role'] ?? '') === 'assistant') {
                $currentModel = $message['model'] ?? $currentModel;
                $currentProvider = $message['provider'] ?? $currentProvider;
            }

            // Look for tool calls in content
            $content = $message['content'] ?? [];
            if (!is_array($content)) {
                continue;
            }

            foreach ($content as $item) {
                if (($item['type'] ?? '') === 'toolCall') {
                    $ingested += $this->ingestToolCall(
                        $item,
                        $sessionId,
                        $timestamp,
                        $currentModel,
                        $currentProvider,
                        $lastTimestamp
                    );
                }
                
                // Update last timestamp for duration calculation
                $lastTimestamp = $timestamp;
            }
        }

        fclose($handle);
        return $ingested;
    }

    /**
     * Ingest a tool call as an activity entry
     */
    private function ingestToolCall(
        array $toolCall,
        string $sessionId,
        Carbon $timestamp,
        ?string $model,
        ?string $provider,
        ?Carbon $prevTimestamp
    ): int {
        $toolId = $toolCall['id'] ?? null;
        $toolName = $toolCall['name'] ?? 'unknown';
        $arguments = $toolCall['arguments'] ?? [];

        // Check if we've already logged this tool call
        if ($toolId) {
            $exists = ActivityLog::where("context->tool_call_id", $toolId)->exists();
            if ($exists) {
                return 0;
            }
        }

        // Generate human-readable action description
        $actionName = $this->formatActionName($toolName, $arguments);
        $actionType = $this->mapToolToActionType($toolName);
        
        // Calculate duration from previous timestamp (rough estimate)
        $durationMs = $prevTimestamp ? $timestamp->diffInMilliseconds($prevTimestamp) : null;

        // Determine impact based on tool type
        $impact = $this->determineImpact($toolName, $arguments);

        $activity = ActivityLog::create([
            'agent' => 'Luna',
            'action_type' => $actionType,
            'action_name' => $actionName,
            'context' => [
                'session_id' => $sessionId,
                'tool_call_id' => $toolId,
                'tool_name' => $toolName,
                'arguments' => $arguments,
                'model' => $model,
                'provider' => $provider,
                'duration_ms' => $durationMs,
            ],
            'impact' => $impact,
            'status' => 'success',
            'created_at' => $timestamp,
        ]);

        // Create task for high-impact actions
        if ($impact === 'high') {
            $this->createTaskFromActivity($activity, $toolName, $arguments, $model, $timestamp);
        }

        return 1;
    }

    /**
     * Map tool names to action types
     */
    private function mapToolToActionType(string $toolName): string
    {
        return match($toolName) {
            'read' => 'read',
            'write' => 'write',
            'edit' => 'edit',
            'exec' => 'exec',
            'process' => 'exec',
            'browser' => 'browser',
            'web_search' => 'search',
            'web_fetch' => 'fetch',
            'image' => 'image',
            'tts' => 'tts',
            'memory_store' => 'memory',
            'memory_recall' => 'memory',
            'memory_forget' => 'memory',
            'sessions_list' => 'session',
            'sessions_history' => 'session',
            'sessions_send' => 'message',
            'sessions_spawn' => 'spawn',
            'subagents' => 'subagent',
            'cron' => 'cron',
            'gateway' => 'system',
            'message' => 'message',
            'nodes' => 'node',
            'canvas' => 'canvas',
            default => 'task',
        };
    }

    /**
     * Format a human-readable action name from tool call
     */
    private function formatActionName(string $toolName, array $arguments): string
    {
        return match($toolName) {
            'read' => $this->formatReadAction($arguments),
            'write' => $this->formatWriteAction($arguments),
            'edit' => $this->formatEditAction($arguments),
            'exec' => $this->formatExecAction($arguments),
            'process' => $this->formatProcessAction($arguments),
            'web_search' => $this->formatSearchAction($arguments),
            'web_fetch' => $this->formatFetchAction($arguments),
            'browser' => $this->formatBrowserAction($arguments),
            'image' => 'Analyzed image',
            'tts' => 'Text-to-speech',
            'memory_store' => 'Stored memory: ' . substr($arguments['text'] ?? 'memory', 0, 30),
            'memory_recall' => 'Recalled memory: ' . substr($arguments['query'] ?? 'query', 0, 30),
            'sessions_spawn' => 'Spawned subagent: ' . ($arguments['agentId'] ?? 'unknown'),
            'sessions_send' => 'Sent message to session',
            'cron' => 'Cron: ' . ($arguments['action'] ?? 'action'),
            'gateway' => 'Gateway: ' . ($arguments['action'] ?? 'action'),
            'message' => 'Message: ' . ($arguments['action'] ?? 'action'),
            default => ucfirst($toolName),
        };
    }

    private function formatReadAction(array $arguments): string
    {
        $path = $arguments['path'] ?? $arguments['file_path'] ?? 'file';
        $filename = basename($path);
        return "Read {$filename}";
    }

    private function formatWriteAction(array $arguments): string
    {
        $path = $arguments['path'] ?? $arguments['file_path'] ?? 'file';
        $filename = basename($path);
        return "Wrote {$filename}";
    }

    private function formatEditAction(array $arguments): string
    {
        $path = $arguments['path'] ?? $arguments['file_path'] ?? 'file';
        $filename = basename($path);
        return "Edited {$filename}";
    }

    private function formatExecAction(array $arguments): string
    {
        $command = $arguments['command'] ?? 'command';
        // Truncate long commands
        $short = substr($command, 0, 50);
        if (strlen($command) > 50) {
            $short .= '...';
        }
        return "Exec: {$short}";
    }

    private function formatProcessAction(array $arguments): string
    {
        $action = $arguments['action'] ?? 'process';
        return "Process: {$action}";
    }

    private function formatSearchAction(array $arguments): string
    {
        $query = $arguments['query'] ?? 'search';
        return "Search: {$query}";
    }

    private function formatFetchAction(array $arguments): string
    {
        $url = $arguments['url'] ?? 'url';
        // Extract domain
        $domain = parse_url($url, PHP_URL_HOST) ?? $url;
        return "Fetched: {$domain}";
    }

    private function formatBrowserAction(array $arguments): string
    {
        $action = $arguments['action'] ?? 'browser';
        return "Browser: {$action}";
    }

    /**
     * Determine impact based on tool type
     */
    private function determineImpact(string $toolName, array $arguments): string
    {
        // High impact: file changes, exec commands
        if (in_array($toolName, ['write', 'edit', 'exec', 'process', 'gateway', 'cron'])) {
            return 'high';
        }

        // Medium impact: memory, sessions, spawn
        if (in_array($toolName, ['memory_store', 'sessions_spawn', 'sessions_send', 'message'])) {
            return 'medium';
        }

        // Low impact: reads, searches, fetches
        return 'low';
    }

    /**
     * Create a task from a high-impact activity
     */
    private function createTaskFromActivity(ActivityLog $activity, string $toolName, array $arguments, ?string $model, Carbon $timestamp): void
    {
        // Skip polling/internal commands
        if ($toolName === 'exec') {
            $command = $arguments['command'] ?? '';
            if (str_contains($command, 'artisan lunaos:poll') ||
                str_contains($command, 'sqlite3') ||
                str_contains($command, 'artisan view:clear') ||
                str_contains($command, 'curl -s -u')) {
                return; // Skip internal housekeeping
            }
        }

        // Determine task name based on tool
        $taskName = match($toolName) {
            'write' => $this->formatWriteTaskName($arguments),
            'edit' => $this->formatEditTaskName($arguments),
            'exec' => $this->formatExecTaskName($arguments),
            'process' => $this->formatProcessTaskName($arguments),
            'gateway' => 'Gateway: ' . ($arguments['action'] ?? 'action'),
            'cron' => 'Cron: ' . ($arguments['action'] ?? 'action'),
            default => substr($activity->action_name, 0, 100),
        };

        // Find or create Luna agent
        $agent = Agent::firstOrCreate(
            ['name' => 'Luna'],
            ['role' => 'PM & Coordinator', 'model' => $model ?? 'unknown', 'status' => 'active', 'emoji' => '🌙']
        );

        // Create task
        Task::create([
            'name' => $taskName,
            'description' => "Auto-created from activity: {$activity->action_type}",
            'agent_id' => $agent->id,
            'status' => 'completed',
            'priority' => 'normal',
            'tokens_used' => 0,
            'cost' => 0,
            'started_at' => $timestamp,
            'completed_at' => $timestamp,
        ]);
    }

    private function formatWriteTaskName(array $arguments): string
    {
        $path = $arguments['path'] ?? $arguments['file_path'] ?? 'file';
        $filename = basename($path);
        return "Created {$filename}";
    }

    private function formatEditTaskName(array $arguments): string
    {
        $path = $arguments['path'] ?? $arguments['file_path'] ?? 'file';
        $filename = basename($path);
        return "Edited {$filename}";
    }

    private function formatExecTaskName(array $arguments): string
    {
        $command = $arguments['command'] ?? 'command';
        // Extract the main command
        $parts = explode(' ', $command);
        $mainCmd = $parts[0] ?? 'command';
        return "Executed: {$mainCmd}";
    }

    private function formatProcessTaskName(array $arguments): string
    {
        $action = $arguments['action'] ?? 'process';
        return "Process: {$action}";
    }
}