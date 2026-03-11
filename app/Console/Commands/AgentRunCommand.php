<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Task;
use App\Services\WorkerExecutor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AgentRunCommand executes tasks for agents.
 * 
 * This command allows running tasks for any agent defined in the database,
 * supporting both legacy agents (dave, sam, chen) and new database-configured agents.
 */
#[AsCommand(name: 'agent:run')]
class AgentRunCommand extends Command
{
    /**
     * The name and signature of the command.
     * 
     * @var string
     */
    protected $signature = 'agent:run
        {agent : The name or ID of the agent to execute} 
        {--task= : The task ID to execute} 
        {--name= : Alternative way to specify agent by name}';
    
    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Execute a task for a specified agent';
    
    /**
     * Execute the console command.
     * 
     * @param InputInterface $input The command input
     * @param OutputInterface $output The command output
     * @return int The command status code
     */
    public function handle(WorkerExecutor $executor): int
    {
        try {
            $agent = $this->resolveAgent($this->input, $this->output);
            
            if ($agent === null) {
                $this->error('Agent not found.');
                return self::FAILURE;
            }
            
            $task = $this->resolveTask($this->input, $this->output);
            
            if ($task === null) {
                $this->error('Task not found.');
                return self::FAILURE;
            }
            
            $this->info(
                sprintf('Executing task %d for agent: %s (%s)', $task->id, $agent->name, get_class($agent))
            );
            
            $result = $executor->execute($agent, $task);
            
            if ($result['status'] === 'success') {
                $this->info('Task completed successfully');
                
                if ($this->output->isVerbose()) {
                    $this->table(
                        ['Key', 'Value'],
                        $this->formatOutput($result)
                    );
                }
                
                Log::info('Agent task completed', [
                    'agent' => $agent->name,
                    'task' => $task->id,
                    'result' => $result,
                ]);
                
                return self::SUCCESS;
            } else {
                $this->error('Task execution failed: ' . ($result['message'] ?? 'Unknown error'));
                
                Log::error('Agent task failed', [
                    'agent' => $agent->name,
                    'task' => $task->id,
                    'result' => $result,
                ]);
                
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Unexpected error: ' . $e->getMessage());
            
            Log::error('Agent execution error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return self::FAILURE;
        }
    }
    
    /**
     * Resolve agent from input.
     * 
     * @param InputInterface $input The command input
     * @param OutputInterface $output The command output
     * @return Agent|null The resolved agent or null if not found
     */
    private function resolveAgent(InputInterface $input, OutputInterface $output): ?Agent
    {
        $agentParam = $input->getArgument('agent');
        $agentName = $input->getOption('name');
        
        // Use name option if provided, otherwise use argument
        $identifier = $agentName ?? $agentParam;
        
        if ($identifier === null) {
            $this->error('Agent name or ID is required.');
            return null;
        }
        
        // Try to find by ID first, then by name
        if (is_numeric($identifier)) {
            return Agent::find($identifier);
        }
        
        return Agent::where('name', $identifier)->first();
    }
    
    /**
     * Resolve task from input.
     * 
     * @param InputInterface $input The command input
     * @param OutputInterface $output The command output
     * @return Task|null The resolved task or null if not found
     */
    private function resolveTask(InputInterface $input, OutputInterface $output): ?Task
    {
        $taskId = $input->getOption('task');
        
        if ($taskId === null) {
            $this->error('Task ID is required. Use --task=<id>.');
            return null;
        }
        
        return Task::find($taskId);
    }
    
    /**
     * Format output for display.
     * 
     * @param array<string, mixed> $result The execution result
     * @return array<array<string, string>>
     */
    private function formatOutput(array $result): array
    {
        $rows = [];
        
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $rows[] = [$key, json_encode($value, JSON_PRETTY_PRINT)];
            } else {
                $rows[] = [$key, (string) $value];
            }
        }
        
        return $rows;
    }
}
