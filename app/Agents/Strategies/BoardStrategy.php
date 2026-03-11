<?php

declare(strict_types=1);

namespace App\Agents\Strategies;

use App\Models\Agent;
use App\Models\Task;
use App\Models\Board;
use App\Services\AgentContext;

/**
 * BoardStrategy handles strategic board-level decisions and oversight tasks.
 * 
 * This strategy is designed for executive board members who need to review,
 * approve, or provide high-level direction for initiatives and projects.
 */
class BoardStrategy extends Strategy
{
    /**
     * Process board-level strategic tasks.
     * 
     * @param AgentContext $context The agent execution context
     * @return array<string, mixed>
     */
    public function execute(AgentContext $context): array
    {
        $task = $context->getTask();
        $agent = $context->getAgent();
        
        // Board-level tasks typically involve review, approval, or strategic direction
        return $this->processStrategicReview($task, $agent);
    }
    
    /**
     * Process strategic review for board-level decisions.
     * 
     * @param Task $task The task to process
     * @param Agent $agent The agent executing the task
     * @return array<string, mixed>
     */
    private function processStrategicReview(Task $task, Agent $agent): array
    {
        // Board strategies often involve:
        // - Reviewing comprehensive reports
        // - Making high-level approvals
        // - Providing strategic guidance
        // - Evaluating executive performance
        
        return [
            'status' => 'success',
            'message' => 'Board-level strategic review completed',
            'data' => [
                'task_id' => $task->id,
                'agent' => $agent->name,
                'strategy' => 'board',
                'action' => 'strategic_review',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
    
    /**
     * Get the priority level for this strategy.
     * Board strategies have high priority due to strategic importance.
     * 
     * @return int
     */
    public function getPriority(): int
    {
        return 2; // High priority
    }
}
