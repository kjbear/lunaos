<?php

namespace App\Agents\Strategies;

use App\Models\Task;
use App\Models\Agent;

/**
 * Worker Strategy Interface
 * 
 * Defines the contract for worker agent strategies.
 * Each strategy encapsulates the polling and processing logic for a specific type of work.
 * 
 * This enables adding new agent types via configuration without creating new PHP classes.
 */
interface WorkerStrategy
{
    /**
     * Poll for work that this strategy can handle.
     * 
     * @param Agent $agent The agent configuration
     * @return Task|null The task to process, or null if no work available
     */
    public function pollForWork(Agent $agent): ?Task;
    
    /**
     * Process a task using this strategy.
     * 
     * @param Task $task The task to process
     * @param Agent $agent The agent configuration
     * @return void
     */
    public function processTask(Task $task, Agent $agent): void;
    
    /**
     * Get the capabilities/skills for this strategy.
     * 
     * @return array List of capability strings
     */
    public function getCapabilities(): array;
    
    /**
     * Get the workflow steps this strategy handles.
     * 
     * @return array List of step names (e.g., ['develop', 'qa', 'staging', 'production'])
     */
    public function getWorkflowSteps(): array;
    
    /**
     * Get the strategy name/identifier.
     * 
     * @return string Strategy identifier (e.g., 'develop', 'qa', 'deploy')
     */
    public function getName(): string;
}
