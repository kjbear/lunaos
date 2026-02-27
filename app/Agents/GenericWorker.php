<?php

namespace App\Agents;

use App\Models\Task;
use App\Models\Agent;
use App\Agents\Strategies\WorkerStrategy;
use App\Agents\Strategies\StrategyRegistry;
use Illuminate\Support\Facades\Log;

/**
 * Generic Worker Agent
 * 
 * Configuration-driven worker that uses strategies for behavior.
 * This eliminates the need for hard-coded agent classes (DaveAgentWorker, SamAgentWorker, etc.).
 * 
 * Usage:
 * 1. Create agent in database with strategy_class = 'develop', 'qa', 'deploy', etc.
 * 2. Run: (new GenericWorker($agent))->run()
 * 
 * The worker loads the strategy and delegates all work to it.
 * New agent types can be added via UI without code changes (just add strategy to registry).
 */
class GenericWorker
{
    /**
     * Agent configuration.
     */
    protected Agent $agent;
    
    /**
     * Strategy instance for this worker.
     */
    protected WorkerStrategy $strategy;
    
    /**
     * Running flag.
     */
    protected bool $running = false;
    
    /**
     * Poll interval in seconds (from agent config or default).
     */
    protected int $pollInterval;
    
    /**
     * Constructor.
     * 
     * @param Agent $agent Agent configuration from database
     * @param WorkerStrategy|null $strategy Optional strategy instance (auto-loaded if null)
     * @throws \InvalidArgumentException If strategy not found
     */
    public function __construct(Agent $agent, ?WorkerStrategy $strategy = null)
    {
        $this->agent = $agent;
        
        // Load strategy from database config or use provided instance
        if ($strategy) {
            $this->strategy = $strategy;
        } else {
            $this->strategy = $this->loadStrategy($agent);
        }
        
        // Poll interval from agent settings or default
        $this->pollInterval = $agent->model_settings['poll_interval'] ?? 30;
    }
    
    /**
     * Load strategy based on agent configuration.
     */
    protected function loadStrategy(Agent $agent): WorkerStrategy
    {
        // Try to load by strategy_class field
        if ($agent->model_settings['strategy_class'] ?? null) {
            $strategyName = $agent->model_settings['strategy_class'];
            return StrategyRegistry::get($strategyName);
        }
        
        // Fallback: infer from agent name or role
        $strategyName = $this->inferStrategyName($agent);
        return StrategyRegistry::get($strategyName);
    }
    
    /**
     * Infer strategy name from agent configuration.
     */
    protected function inferStrategyName(Agent $agent): string
    {
        // Check if agent name matches a known pattern
        $name = strtolower($agent->name);
        
        // Development agents
        if (in_array($name, ['dave', 'dev', 'developer', 'coder'])) {
            return 'develop';
        }
        
        // QA agents
        if (in_array($name, ['sam', 'qa', 'tester', 'test'])) {
            return 'qa';
        }
        
        // Deployment agents
        if (in_array($name, ['chen', 'deploy', 'devops', 'ops'])) {
            return 'deploy';
        }
        
        // Default to develop strategy
        return 'develop';
    }
    
    /**
     * Main worker loop.
     */
    public function run(): void
    {
        $this->running = true;
        
        Log::info("GenericWorker started", [
            'agent' => $this->agent->name,
            'strategy' => $this->strategy->getName(),
            'poll_interval' => $this->pollInterval,
            'model' => $this->agent->model,
        ]);
        
        echo "🤖 {$this->agent->name} started (strategy: {$this->strategy->getName()}, interval: {$this->pollInterval}s)\n";
        
        while ($this->running) {
            try {
                $task = $this->strategy->pollForWork($this->agent);
                
                if ($task) {
                    echo "📋 {$this->agent->name} picked up task #{$task->id}: {$task->title}\n";
                    Log::info("Worker {$this->agent->name} processing task #{$task->id}", [
                        'strategy' => $this->strategy->getName(),
                    ]);
                    
                    $this->strategy->processTask($task, $this->agent);
                }
                
            } catch (\Exception $e) {
                Log::error("GenericWorker error", [
                    'agent' => $this->agent->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                echo "❌ {$this->agent->name} error: {$e->getMessage()}\n";
            }
            
            sleep($this->pollInterval);
        }
    }
    
    /**
     * Stop the worker.
     */
    public function stop(): void
    {
        $this->running = false;
        Log::info("GenericWorker stopped", ['agent' => $this->agent->name]);
        echo "🛑 {$this->agent->name} stopped\n";
    }
    
    /**
     * Get the agent configuration.
     */
    public function getAgent(): Agent
    {
        return $this->agent;
    }
    
    /**
     * Get the strategy instance.
     */
    public function getStrategy(): WorkerStrategy
    {
        return $this->strategy;
    }
    
    /**
     * Get polling interval.
     */
    public function getPollInterval(): int
    {
        return $this->pollInterval;
    }
}
