<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agent;
use App\Models\Task;
use App\Models\Strategy;
use App\Exceptions\StrategyNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * WorkerExecutor executes tasks using strategies loaded from database.
 * 
 * This class implements the Strategy Pattern by dynamically loading
 * agent strategies from the database based on the agent's strategy_class.
 * It supports backward compatibility with hardcoded agent mappings.
 */
class WorkerExecutor
{
    /**
     * Map of legacy agent names to their strategy classes for backward compatibility.
     * 
     * @var array<string, class-string>
     */
    private const BACKWARD_COMPATIBILITY_MAP = [
        'dave' => DevelopStrategy::class,
        'sam' => WorkerStrategy::class,
        'chen' => WorkerStrategy::class,
    ];
    
    /**
     * Load and execute the appropriate strategy for an agent.
     * 
     * @param Agent $agent The agent to execute tasks for
     * @param Task $task The task to execute
     * @return array<string, mixed>
     * @throws StrategyNotFoundException If no strategy can be found for the agent
     */
    public function execute(Agent $agent, Task $task): array
    {
        $strategy = $this->loadStrategy($agent);
        
        $context = new AgentContext($agent, $task);
        
        return $strategy->execute($context);
    }
    
    /**
     * Load the appropriate strategy class for an agent.
     * 
     * @param Agent $agent The agent to load strategy for
     * @return Strategy The strategy instance
     * @throws StrategyNotFoundException If no strategy can be found
     */
    private function loadStrategy(Agent $agent): Strategy
    {
        // Try to get strategy class from database
        $strategyClass = $this->getStrategyClassFromAgent($agent);
        
        if ($strategyClass === null) {
            throw new StrategyNotFoundException(
                sprintf('No strategy found for agent: %s', $agent->name)
            );
        }
        
        // Create instance of the strategy class
        $instance = new $strategyClass();
        
        if (!$instance instanceof Strategy) {
            throw new StrategyNotFoundException(
                sprintf('Strategy class %s does not implement Strategy interface', $strategyClass)
            );
        }
        
        Log::info('Strategy loaded for agent', [
            'agent' => $agent->name,
            'strategy' => get_class($instance),
        ]);
        
        return $instance;
    }
    
    /**
     * Get strategy class from agent, with fallback to backward compatibility.
     * 
     * @param Agent $agent The agent to get strategy class for
     * @return class-string|null The strategy class name or null if not found
     */
    private function getStrategyClassFromAgent(Agent $agent): ?string
    {
        // First check if strategy_class is directly stored on agent
        if (!empty($agent->strategy_class)) {
            return $agent->strategy_class;
        }
        
        // Fall back to legacy mapping for backward compatibility
        if (isset(self::BACKWARD_COMPATIBILITY_MAP[$agent->name])) {
            return self::BACKWARD_COMPATIBILITY_MAP[$agent->name];
        }
        
        return null;
    }
}
