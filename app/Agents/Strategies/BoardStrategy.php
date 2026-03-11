<?php

declare(strict_types=1);

namespace App\Agents\Strategies;

use App\Models\Agent;
use App\Models\Task;

class BoardStrategy extends Strategy
{
    /**
     * Execute high-level strategic tasks.
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
            'strategy' => 'board',
            'action' => 'executing_high_level_strategy',
            'details' => "Board level strategy execution for task: {$task->name}",
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
        return 100; // Highest priority
    }

    /**
     * Get supported skill paths.
     *
     * @return array<string>
     */
    public function getSupportedSkills(): array
    {
        return [
            'board/overview',
            'board/strategic',
            'board/governance',
            'board/financial',
        ];
    }
}
