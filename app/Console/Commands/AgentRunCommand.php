<?php

namespace App\Console\Commands;

use App\Services\WorkerExecutor;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Agent Run Command
 * 
 * Run an AI agent worker to poll for and execute tasks.
 * 
 * Usage:
 *   php artisan agent:run dave          # Run Dave continuously
 *   php artisan agent:run dave --once   # Single poll and exit
 *   php artisan agent:run sam --once    # Run Sam once
 *   php artisan agent:run chen          # Run Chen continuously
 * 
 * Examples:
 *   # Test Dave with a single task
 *   php artisan agent:run dave --once
 *   
 *   # Run Dave as a daemon (polls every 30s)
 *   php artisan agent:run dave
 *   
 *   # List available agents
 *   php artisan agent:run --list
 */
class AgentRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'agent:run {agent? : The agent name (dave, sam, chen)}
                            {--once : Run a single poll and exit}
                            {--list : List available agents}';
    
    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Run an AI agent worker to poll for and execute tasks';
    
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // List available agents
        if ($this->option('list')) {
            return $this->listAgents();
        }
        
        // Validate agent argument
        $agentName = $this->argument('agent');
        
        if (!$agentName) {
            $agentName = $this->chooseAgent();
            
            if (!$agentName) {
                $this->error('No agent specified. Use --list to see available agents.');
                return 1;
            }
        }
        
        // Check if agent exists
        if (!WorkerExecutor::agentExists($agentName)) {
            $this->error("Unknown agent: {$agentName}");
            $this->line('');
            $this->line('Available agents: ' . implode(', ', WorkerExecutor::getAvailableAgents()));
            return 1;
        }
        
        // Create and run the executor
        $runOnce = $this->option('once');
        
        try {
            $executor = new WorkerExecutor($agentName);
            $executor->setRunOnce($runOnce);
            
            // Handle graceful shutdown in daemon mode
            if (!$runOnce && function_exists('pcntl_signal')) {
                pcntl_async_signals(true);
                pcntl_signal(SIGINT, function () use ($executor) {
                    $this->info("\n\nReceived interrupt signal, stopping worker...");
                    $executor->stop();
                });
                pcntl_signal(SIGTERM, function () use ($executor) {
                    $this->info("\n\nReceived termination signal, stopping worker...");
                    $executor->stop();
                });
            }
            
            return $executor->run();
            
        } catch (RuntimeException $e) {
            $this->error("Failed to initialize agent: {$e->getMessage()}");
            $this->line('');
            $this->line('Make sure the agent exists in the database.');
            $this->line('Run: php artisan db:seed --class=SeedTeamAgents');
            return 1;
        } catch (\Throwable $e) {
            $this->error("Unexpected error: {$e->getMessage()}");
            $this->line('');
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * List available agents
     */
    protected function listAgents(): int
    {
        $this->info('Available AI Agent Workers:');
        $this->line('');
        
        $agents = [
            ['dave', 'Dave', 'Backend Developer', 'Writes PHP/Laravel code, creates migrations'],
            ['sam', 'Sam', 'QA Engineer', 'Runs PHPUnit/Dusk tests, validates code'],
            ['chen', 'Chen', 'DevOps Engineer', 'Deploys to staging/production, health checks'],
        ];
        
        $this->table(
            ['Name', 'Alias', 'Role', 'Description'],
            $agents
        );
        
        $this->line('');
        $this->line('Usage: php artisan agent:run <name> [--once]');
        
        return 0;
    }
    
    /**
     * Interactively choose an agent
     */
    protected function chooseAgent(): ?string
    {
        $agents = WorkerExecutor::getAvailableAgents();
        
        if (empty($agents)) {
            return null;
        }
        
        if (count($agents) === 1) {
            return $agents[0];
        }
        
        $choice = $this->choice('Which agent would you like to run?', $agents, 0);
        
        return $choice;
    }
}