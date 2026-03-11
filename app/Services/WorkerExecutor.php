<?php

declare(strict_types=1);

namespace App\Services;

use App\Agents\Strategies\WorkerStrategy;
use App\Models\Agent;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

/**
 * WorkerExecutor handles task execution using dynamic strategy pattern.
 * 
 * Loads strategies from StrategyRegistry based on agent strategy_class field.
 * Supports multiple agent tiers including board, executive, and worker levels.
 */
class WorkerExecutor
{
    /**
     * The strategy registry instance.
     *
     * @var StrategyRegistry
     */
    protected StrategyRegistry $strategyRegistry;

    /**
     * Create a new WorkerExecutor instance.
     *
     * @param  \App\Services\StrategyRegistry  $strategyRegistry
     * @return void
     */
    public function __construct(StrategyRegistry $strategyRegistry)
    {
        $this->strategyRegistry = $strategyRegistry;
    }

    /**
     * Execute a task using the appropriate strategy for the given agent.
     *
     * @param  \App\Models\Agent  $agent
     * @param  \App\Models\Task  $task
     * @return array<string, mixed>
     */
    public function execute(Agent $agent, Task $task): array
    {
        try {
            // Get strategy class from agent configuration
            $strategyClass = $agent->strategy_class;

            if (empty($strategyClass)) {
                throw new \RuntimeException(
                    "Agent {$agent->name} does not have a strategy_class configured."
                );
            }

            // Validate strategy class exists and implements WorkerStrategy
            if (!class_exists($strategyClass)) {
                throw new \RuntimeException(
                    "Strategy class {$strategyClass} does not exist for agent {$agent->name}."
                );
            }

            /** @var class-string<WorkerStrategy> $strategyClass */
            if (!is_a($strategyClass, WorkerStrategy::class, true)) {
                throw new \RuntimeException(
                    "Strategy class {$strategyClass} must implement WorkerStrategy interface."
                );
            }

            // Get strategy from registry
            $strategy = $this->strategyRegistry->getStrategy($strategyClass);

            // Validate agent has required skill
            if (!$this->hasMatchingSkill($agent, $strategy)) {
                throw new \RuntimeException(
                    "Agent {$agent->name} does not have skill matching strategy {$strategyClass}."
                );
            }

            Log::info("Executing task with strategy", [
                'agent' => $agent->name,
                'task' => $task->name,
                'strategy' => $strategyClass,
            ]);

            // Execute the strategy
            return $strategy->execute($agent, $task);

        } catch (\Throwable $e) {
            Log::error("Task execution failed", [
                'agent' => $agent->name ?? 'unknown',
                'task' => $task->name ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'agent_id' => $agent->id ?? null,
                'task_id' => $task->id ?? null,
            ];
        }
    }

    /**
     * Check if the agent has a matching skill for the strategy.
     *
     * @param  \App\Models\Agent  $agent
     * @param  \App\Agents\Strategies\WorkerStrategy  $strategy
     * @return bool
     */
    private function hasMatchingSkill(Agent $agent, WorkerStrategy $strategy): bool
    {
        $agentSkillPaths = explode(',', $agent->skill_doc_path ?? '');
        $strategySkills = $strategy->getSupportedSkills();

        foreach ($strategySkills as $strategySkill) {
            if (in_array(trim($strategySkill), $agentSkillPaths)) {
                return true;
            }
        }

        // Support backward compatibility with legacy agents
        return $this->hasLegacySkillMatch($agent, $strategy);
    }

    /**
     * Check for legacy skill matches for backward compatibility.
     *
     * @param  \App\Models\Agent  $agent
     * @param  \App\Agents\Strategies\WorkerStrategy  $strategy
     * @return bool
     */
    private function hasLegacySkillMatch(Agent $agent, WorkerStrategy $strategy): bool
    {
        $legacySkillMap = [
            'dave' => ['develop', 'code', 'backend'],
            'sam' => ['security', 'audit', 'compliance'],
            'chen' => ['devops', 'infrastructure', 'deployment'],
        ];

        $agentName = strtolower($agent->name ?? '');
        if (isset($legacySkillMap[$agentName])) {
            $agentSkills = explode(',', $agent->skill_doc_path ?? '');
            $legacySkills = $legacySkillMap[$agentName];

            foreach ($legacySkills as $legacySkill) {
                foreach ($agentSkills as $agentSkill) {
                    if (str_contains(strtolower(trim($agentSkill)), $legacySkill)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
