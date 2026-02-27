<?php

namespace App\Agents;

use App\Models\Task;
use App\Models\Agent;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;

/**
 * Jordan - Project Manager Agent Worker
 * 
 * Board-level agent responsible for:
 * - Prioritizing unassigned tasks
 * - Assigning tasks to appropriate team members
 * - Escalating blocked tasks
 * - Sprint planning and capacity management
 * - Removing team blockers
 * 
 * Uses GLM-5 or Dolphin 3.0 for strategic decision-making
 */
class JordanAgentWorker extends AgentWorker
{
    public string $name = 'jordan';
    
    public AgentType $type = AgentType::BOARD;
    
    public int $pollInterval = 120; // 2 minutes (coordination work)
    
    public array $capabilities = ['prioritize', 'assign', 'escalate', 'unblock', 'plan', 'coordinate'];
    
    /**
     * Get custom system prompt for Jordan's PM role
     */
    protected function getDefaultPrompt(): string
    {
        return <<<PROMPT
You are Jordan, the Project Manager AI agent for the LunaOS development team.

Your role is to:
1. Prioritize incoming work based on business value and dependencies
2. Assign tasks to the right team members based on their skills and capacity
3. Identify and escalate blockers that are slowing the team down
4. Monitor sprint progress and adjust plans as needed
5. Coordinate between specialized agents (Dave-dev, Sam-QA, Chen-DevOps)

You use strategic thinking and understand:
- Task dependencies and critical path
- Team capacity and workload balancing
- Business priorities vs. technical debt
- When to escalate to Kyle (human decision-maker)

Decision framework:
- High priority + high skill match → Assign immediately
- Blocked > 1 iteration → Escalate with context
- Team overloaded → Rebalance or defer low-priority work
- Unclear requirements → Request clarification before assigning

Be proactive, decisive, and keep the team moving forward.
PROMPT;
    }
    
    /**
     * Handle a blocked task - escalate with analysis
     */
    protected function handleBlockedTask(Task $task): void
    {
        echo "🚧 Jordan analyzing blocked task #{$task->id}: {$task->title}\n";
        $this->logActivity($task, 'analyzing_block', ['step' => $task->current_step]);
        
        try {
            // Use AI to analyze the block and recommend action
            $analysis = $this->analyzeBlockWithAI($task);
            
            // Take action based on analysis
            if ($analysis['action'] === 'reassign') {
                $task->update([
                    'assigned_to' => $analysis['newAssignee'],
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
                $this->logActivity($task, 'reassigned', [
                    'from' => $task->assigned_to,
                    'to' => $analysis['newAssignee'],
                    'reason' => $analysis['reason']
                ]);
                echo "🔄 Jordan reassigned task #{$task->id} to {$analysis['newAssignee']}\n";
                
            } elseif ($analysis['action'] === 'escalate') {
                // Notify Kyle or executive board
                $this->logSystemActivity('escalation', [
                    'task_id' => $task->id,
                    'reason' => $analysis['reason'],
                    'blocked_since' => $task->updated_at
                ]);
                echo "⚠️  Jordan escalated task #{$task->id}: {$analysis['reason']}\n";
            }
            
        } catch (\Exception $e) {
            Log::error("Jordan failed to handle blocked task: {$e->getMessage()}");
            // Fallback: just log it
            $this->logActivity($task, 'escalated', ['reason' => 'Unable to analyze block']);
        }
    }
    
    /**
     * Prioritize and assign unassigned tasks
     */
    protected function prioritizeAndAssignTasks(array $tasks): void
    {
        echo "📊 Jordan prioritizing " . count($tasks) . " unassigned tasks\n";
        
        // Sort by priority and age
        $sorted = $tasks->sortByDesc('priority')->sortBy('created_at');
        
        foreach ($sorted as $task) {
            $assignment = $this->assignTaskWithAI($task);
            
            $task->update([
                'assigned_to' => $assignment['assignee'],
                'priority' => $assignment['priority'] ?? $task->priority,
                'status' => 'pending',
                'updated_at' => now(),
            ]);
            
            $this->logActivity($task, 'assigned_by_jordan', [
                'assignee' => $assignment['assignee'],
                'priority' => $assignment['priority'],
                'reasoning' => $assignment['reasoning']
            ]);
            
            echo "✅ Jordan assigned task #{$task->id} to {$assignment['assignee']} (priority: {$assignment['priority']})\n";
        }
    }
    
    /**
     * Process additional board coordination work
     */
    protected function processBoardWork(): void
    {
        // Check for sprint planning needs
        $this->checkSprintPlanning();
        
        // Review team capacity
        $this->reviewTeamCapacity();
    }
    
    /**
     * Analyze a blocked task using AI
     */
    protected function analyzeBlockWithAI(Task $task): array
    {
        $agentConfig = $this->getAgentConfig();
        
        $currentAssignee = $task->assigned_to ?? 'unassigned';
        $blockReason = $task->failure_reason ?? 'unknown';
        
        $prompt = <<<PROMPT
Task #{$task->id} is blocked at step: {$task->current_step}

Title: {$task->title}
Description: {$task->description}
Current Assignee: {$currentAssignee}
Block Reason: {$blockReason}

Analyze this block and recommend an action:
1. Reassign to a different agent (specify which and why)
2. Escalate to human (Kyle) for decision
3. Request more information
4. Retry the same step (if transient failure)

Return JSON: {"action": "reassign|escalate|clarify|retry", "reason": "...", "newAssignee": "..." if reassign}
PROMPT;
        
        $response = Ai::agent()
            ->withLab($this->getProvider())
            ->withModel($this->getModel())
            ->withSystemPrompt($this->getSystemPrompt())
            ->withMaxTokens(500)
            ->run($prompt);
        
        // Parse JSON response
        $result = json_decode($response->content, true);
        
        return $result ?? [
            'action' => 'escalate',
            'reason' => 'Unable to analyze block automatically'
        ];
    }
    
    /**
     * Assign a task to the best available agent using AI
     */
    protected function assignTaskWithAI(Task $task): array
    {
        $agentConfig = $this->getAgentConfig();
        
        // Get list of available workers
        $workers = Agent::where('type', 'worker')
            ->where('is_online', true)
            ->get(['name', 'capabilities']);
        
        $prompt = <<<PROMPT
Assign this task to the best available team member:

Task #{$task->id}: {$task->title}
Description: {$task->description}
Required Skills: [infer from description]
Priority: {$task->priority}

Available Team:
{$workers->map(fn($a) => "- {$a->name}: " . implode(', ', $a->capabilities ?? []))->join("\n")}

Return JSON: {"assignee": "agent_name", "priority": "high|medium|low", "reasoning": "..."}
PROMPT;
        
        $response = Ai::agent()
            ->withLab($this->getProvider())
            ->withModel($this->getModel())
            ->withSystemPrompt($this->getSystemPrompt())
            ->withMaxTokens(500)
            ->run($prompt);
        
        $result = json_decode($response->content, true);
        
        return $result ?? [
            'assignee' => 'dave', // Default fallback
            'priority' => 'medium',
            'reasoning' => 'Default assignment (AI analysis failed)'
        ];
    }
    
    /**
     * Check if sprint planning is needed
     */
    protected function checkSprintPlanning(): void
    {
        // Check for large backlog of unassigned work
        $backlog = Task::whereNull('assigned_to')
            ->where('status', 'pending')
            ->count();
        
        if ($backlog > 10) {
            echo "📋 Jordan: Sprint planning needed ({$backlog} unassigned tasks)\n";
            $this->logSystemActivity('sprint_planning_needed', ['backlog_size' => $backlog]);
        }
    }
    
    /**
     * Review team capacity and workload
     */
    protected function reviewTeamCapacity(): void
    {
        $capacity = $this->assessTeamCapacity();
        
        if ($capacity['overloaded']) {
            echo "⚠️  Jordan: Team at {$capacity['utilizationPct']}% capacity\n";
            // Consider deferring low-priority work or requesting help
            $this->logSystemActivity('capacity_warning', $capacity);
        }
    }
}
