# Task #3: Implement Strategy Pattern for WorkerExecutor

## Overview
Refactor WorkerExecutor to use database-driven strategy loading instead of hardcoded agent class mapping.

## Current State
- Strategy classes exist: `DevelopStrategy`, `QAStrategy`, `DeployStrategy`
- StrategyRegistry exists with mappings: develop → DevelopStrategy, qa → QAStrategy, deploy → DeployStrategy
- Agents table has `strategy_class` column (now populated: dave='develop', sam='qa', chen='deploy')
- WorkerExecutor still uses hardcoded `$agentClasses` array

## Required Changes

### 1. Update WorkerExecutor.php
File: `app/Services/WorkerExecutor.php`

Replace hardcoded class mapping:
```php
// OLD - remove this
protected static array $agentClasses = [
    'dave' => DaveAgentWorker::class,
    'sam' => SamAgentWorker::class,
    'chen' => ChenAgentWorker::class,
];
```

With strategy-based loading:
```php
// NEW - use strategy from database
protected function getStrategyForAgent(Agent $agent): WorkerStrategy
{
    $strategyClass = $agent->strategy_class;
    
    if (!$strategyClass) {
        // Fallback for legacy agents without strategy_class
        throw new RuntimeException("Agent {$agent->name} has no strategy_class configured");
    }
    
    return StrategyRegistry::get($strategyClass);
}
```

Update `initializeWorker()` to use strategy:
```php
protected function initializeWorker(): void
{
    $this->agentConfig = Agent::where('name', $this->agentName)->first();
    
    if (!$this->agentConfig) {
        throw new RuntimeException("Agent '{$this->agentName}' not found in database.");
    }
    
    $strategyClass = $this->agentConfig->strategy_class;
    
    if (!$strategyClass) {
        throw new RuntimeException("Agent '{$this->agentName}' has no strategy_class configured.");
    }
    
    $this->log("Initialized {$this->agentName} worker", [
        'strategy' => $strategyClass,
        'model' => $this->agentConfig->model,
    ]);
}
```

Update the task execution to call strategy directly:
```php
public function run(): int
{
    $task = $this->findPendingTask();
    
    if (!$task) {
        $this->log("No pending tasks");
        return 0;
    }
    
    $strategy = StrategyRegistry::get($this->agentConfig->strategy_class);
    
    $strategy->processTask($task, $this->agentConfig);
    
    return 1;
}
```

### 2. Update Strategy Interface
File: `app/Agents/Strategies/WorkerStrategy.php`

Ensure interface has:
```php
public function processTask(Task $task, Agent $agent): void;
public function pollForWork(Agent $agent): ?Task;
```

### 3. Ensure Strategies Have All Needed Methods
Each strategy (DevelopStrategy, QAStrategy, DeployStrategy) must have:
- `processTask()` - Execute the task
- `pollForWork()` - Find task matching strategy
- `getCapabilities()` - Return capabilities array
- `getWorkflowSteps()` - Return workflow steps array
- `getName()` - Return strategy name

### 4. Import Statements
Ensure WorkerExecutor imports:
```php
use App\Agents\Strategies\StrategyRegistry;
use App\Agents\Strategies\WorkerStrategy;
```

### 5. Remove Unused Imports
Remove from WorkerExecutor:
```php
use App\Agents\AgentWorker;
use App\Agents\DaveAgentWorker;
use App\Agents\SamAgentWorker;
use App\Agents\ChenAgentWorker;
```

## Testing Commands
```bash
php artisan agent:run dave --once
php artisan agent:run sam --once
```

## Acceptance Criteria
1. WorkerExecutor has no hardcoded `$agentClasses` array
2. WorkerExecutor loads strategy from `$agent->strategy_class`
3. `php artisan agent:run dave --once` works
4. `php artisan agent:run sam --once` works
5. No unused imports in WorkerExecutor