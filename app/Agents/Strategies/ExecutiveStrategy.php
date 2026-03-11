<?php

declare(strict_types=1);

namespace App\Agents\Strategies;

use App\Models\Agent;
use App\Models\Task;
use App\Services\AgentContext;

/**
 * ExecutiveStrategy handles executive-level operational tasks.
 * 
 * This strategy is designed for executive agents who need to manage
 * operations, coordinate teams, and make tactical decisions.
 */
class ExecutiveStrategy extends Strategy
{
    /**
     * Process executive-level operational tasks.
     * 
     * @param AgentContext $context The agent execution context
     * @return array<string, mixed>
     */
    public function execute(AgentContext $context): array
    {
        $task = $context->getTask();
        $agent = $context->getAgent();
        
        // Executive tasks involve operational management and coordination
        return $this->processOperationalTask($task, $agent);
    }
    
    /**
     * Process operational tasks for executive management.
     * 
     * @param Task $task The task to process
     * @param Agent $agent The agent executing the task
     * @return array<string, mixed>
     */
    private function processOperationalTask(Task $task, Agent $agent): array
    {
        // Executive strategies often involve:
        // - Managing operational workflows
        // - Coordinating cross-functional teams
        // - Making tactical decisions
        // - Overseeing department performance
        
        return [
            'status' => 'success',
            'message' => 'Executive-level operational task completed',
            'data' => [
                'task_id' => $task->id,
                'agent' => $agent->name,
                'strategy' => 'executive',
                'action' => 'operational_management',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
    
    /**
     * Get the priority level for this strategy.
     * Executive strategies have medium-high priority.
     * 
     * @return int
     */
    public function getPriority(): int
    {
        return 3; // Medium-high priority
    }
}
