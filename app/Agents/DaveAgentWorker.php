<?php

declare(strict_types=1);

namespace App\Agents;

use App\Agents\Tasks\Task;
use App\Agents\Tasks\TaskStatus;
use App\Agents\Jobs\ProcessTaskJob;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DaveAgentWorker handles task processing for the Dave agent.
 * 
 * Extends the base AgentWorker class to inherit common functionality
 * while implementing agent-specific task polling and processing logic.
 */
class DaveAgentWorker extends AgentWorker
{
    /**
     * Poll for available work items.
     * 
     * @return array<int, array<string, mixed>>
     */
    public function pollForWork(): array
    {
        try {
            // Check for pending tasks in the database
            return Task::where('status', TaskStatus::PENDING)
                ->where('agent', 'dave')
                ->orderBy('created_at', 'asc')
                ->limit(10)
                ->get()
                ->map(fn ($task) => [
                    'id' => $task->id,
                    'payload' => $task->payload,
                    'agent' => $task->agent,
                    'created_at' => $task->created_at->toIso8601String(),
                ])
                ->toArray();
        } catch (Throwable $e) {
            Log::error('DaveAgentWorker pollForWork failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Process a single task.
     * 
     * @param array<string, mixed> $task
     * @return array<string, mixed>
     */
    public function processTask(array $task): array
    {
        try {
            // Dispatch task to job for async processing
            $job = new ProcessTaskJob($task);
            $job->handle();

            return [
                'status' => 'success',
                'task_id' => $task['id'] ?? 'unknown',
                'result' => $job->result ?? null,
                'processed_at' => now()->toIso8601String(),
            ];
        } catch (Throwable $e) {
            Log::error('DaveAgentWorker processTask failed', [
                'task' => $task,
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'error',
                'task_id' => $task['id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'failed_at' => now()->toIso8601String(),
            ];
        }
    }
}
