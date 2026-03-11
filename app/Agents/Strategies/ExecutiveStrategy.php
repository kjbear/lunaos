<?php

declare(strict_types=1);

namespace App\Agents\Strategies;

use App\Models\Agent;
use App\Models\Task;

class ExecutiveStrategy extends Strategy
{
    /**
     * Execute executive-level operational tasks.
     *
     * @param  \App\Models\Agent  $agent
     * @param  \App\Models\Task  $task
     * @return array<string, mixed>
     */
    public function execute(Agent $agent, Task $task): array
    {
        return [
            'agent_id' => $agent->id,
            'task_id' => $task->id,
            'strategy' => 'executive',
            'action' => 'executing_executive_operations',
            'details' => "Executive strategy execution for task: {$task->name}",
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the priority level for this strategy.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 50; // Medium priority
    }

    /**
     * Get supported skill paths.
     *
     * @return array<string>
     */
    public function getSupportedSkills(): array
    {
        return [
            'executive/operations',
            'executive/management',
            'executive/coordination',
        ];
    }
}
