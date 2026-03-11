<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Task;
use App\Services\WorkerExecutor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * AgentRunCommand executes tasks for agents.
 * 
 * Supports running tasks for any agent from the database with proper
 * strategy resolution. Maintains backward compatibility with legacy agents.
 */
class AgentRunCommand extends Command
{
    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'agent:run
                            {agent : The name or ID of the agent to execute}
                            {task? : The name or ID of the task to execute}
                            {--F|force : Force execution without validation}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Execute a task for a specific agent';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\WorkerExecutor  $executor
     * @return int
     */
    public function handle(WorkerExecutor $executor): int
    {
        $agentIdentifier = $this->argument('agent');
        $taskIdentifier = $this->argument('task');

        try {
            // Find agent by name or ID
            $agent = $this->findAgent($agentIdentifier);

            if (!$agent) {
                $this->error("Agent not found: {$agentIdentifier}");
                return self::FAILURE;
            }

            $this->info("Found agent: {$agent->name} ({$agent->strategy_class})");

            // Find task by name or ID if provided
            $task = null;
            if ($taskIdentifier) {
                $task = $this->findTask($taskIdentifier);

                if (!$task) {
                    $this->error("Task not found: {$taskIdentifier}");
                    return self::FAILURE;
                }

                $this->info("Found task: {$task->name}");
            } else {
                // Get first pending task if no specific task provided
                $task = Task::where('status', 'pending')
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($task) {
                    $this->info("Using pending task: {$task->name}");
                } else {
                    $this->warn('No pending tasks found. Would execute dummy task in production.');
                    return self::SUCCESS;
                }
            }

            // Execute task
            $this->info("Executing task...");
            $result = $executor->execute($agent, $task);

            // Display result
            if ($result['success'] ?? true) {
                $this->info('Task completed successfully!');
                $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                return self::SUCCESS;
            } else {
                $this->error('Task failed!');
                $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                return self::FAILURE;
            }

        } catch (\Throwable $e) {
            Log::error('Agent execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Fatal error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Find an agent by name or ID.
     *
     * @param  string  $identifier
     * @return \App\Models\Agent|null
     */
    private function findAgent(string $identifier): ?Agent
    {
        // Try by ID first
        if (is_numeric($identifier)) {
            return Agent::find((int) $identifier);
        }

        // Try by name
        return Agent::where('name', $identifier)->first();
    }

    /**
     * Find a task by name or ID.
     *
     * @param  string  $identifier
     * @return \App\Models\Task|null
     */
    private function findTask(string $identifier): ?Task
    {
        // Try by ID first
        if (is_numeric($identifier)) {
            return Task::find((int) $identifier);
        }

        // Try by name
        return Task::where('name', $identifier)->first();
    }
}
