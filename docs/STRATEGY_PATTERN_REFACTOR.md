# Strategy Pattern Refactor - Web-Configurable AI Workers

**Date:** February 27, 2026 — 2:15 PM EST  
**Status:** ✅ Phase 1 Complete

---

## Problem Statement

Before this refactor, **every AI worker required a hard-coded PHP class**:

- `DaveAgentWorker` → Development tasks
- `SamAgentWorker` → QA testing
- `ChenAgentWorker` → Deployments

**To add a new worker**, you needed to:
1. Create a new PHP class file
2. Implement `pollForWork()` and `processTask()` methods
3. Add database migration to seed the agent
4. Deploy code changes
5. Restart workers

❌ **Not web-configurable** — required developer intervention for every new agent.

---

## Solution: Strategy Pattern

We refactored to use the **Strategy Pattern** with configuration-driven workers.

### Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│                 GenericWorker                       │
│  - Loads agent config from database                 │
│  - Instantiates strategy based on strategy_class    │
│  - Delegates pollForWork() + processTask()          │
│  - Handles logging, git, task progression           │
└─────────────────────────────────────────────────────┘
              ↓ delegates to strategy
┌─────────────────────────────────────────────────────┐
│              WorkerStrategy (interface)             │
│  + pollForWork(Agent): Task?                        │
│  + processTask(Task, Agent): void                   │
│  + getCapabilities(): array                         │
│  + getWorkflowSteps(): array                        │
└─────────────────────────────────────────────────────┘
              ↑ implements
┌──────────────┴──────────────┬───────────────────────┐
│ DevelopStrategy             │ QAStrategy            │
│ - Code generation           │ - PHPUnit tests       │
│ - File writing              │ - Dusk tests          │
│ - Git commits               │ - AI analysis         │
│ - Advance to QA             │ - Pass/fail decisions │
└─────────────────────────────┴───────────────────────┘
```

---

## Key Components

### 1. WorkerStrategy Interface

**File:** `app/Agents/Strategies/WorkerStrategy.php`

```php
interface WorkerStrategy {
    public function pollForWork(Agent $agent): ?Task;
    public function processTask(Task $task, Agent $agent): void;
    public function getCapabilities(): array;
    public function getWorkflowSteps(): array;
    public function getName(): string;
}
```

**Purpose:** Defines contract for all strategies. Any class implementing this can be used as a worker.

---

### 2. HasWorkerCapabilities Trait

**File:** `app/Agents/Strategies/Concerns/HasWorkerCapabilities.php`

**Provides:**
- AI calls (`callAI()`, `callAIJson()`)
- Git operations (`createFeatureBranch()`, `gitCommit()`)
- Task progression (`completeTask()`, `failTask()`)
- Activity logging (`logActivity()`)
- Repository management (`getRepository()`, `gitService()`)

**Benefits:**
- DRY — shared logic in one place
- Strategies focus on domain logic only
- Consistent behavior across all workers

---

### 3. Strategy Implementations

#### DevelopStrategy (Dave's logic)
**File:** `app/Agents/Strategies/DevelopStrategy.php`
- Polls for `develop` step tasks
- Generates code with AI
- Writes files to disk
- Creates feature branch + commit + PR
- Advances to `qa` step

#### QAStrategy (Sam's logic)
**File:** `app/Agents/Strategies/QAStrategy.php`
- Polls for `qa` step tasks
- Runs PHPUnit tests
- Runs Dusk browser tests (if UI changes)
- AI analysis of results
- Pass → advance to `security`
- Fail → return to `develop`

#### DeployStrategy (Chen's logic)
**File:** `app/Agents/Strategies/DeployStrategy.php`
- Polls for `staging`/`production` steps
- Pre-deployment validation
- Execute deployment (staging: full rebuild, prod: zero-downtime)
- Post-deployment health checks
- AI analysis
- Success → advance or complete
- Failure → rollback

---

### 4. StrategyRegistry

**File:** `app/Agents/Strategies/StrategyRegistry.php`

**Purpose:** Central registry for strategy discovery.

```php
// Get built-in strategy
$strategy = StrategyRegistry::get('develop');

// Register custom strategy at runtime
StrategyRegistry::register('my_custom', MyCustomStrategy::class);

// Check availability
if (StrategyRegistry::has('qa')) {
    // ...
}

// List all strategies
$strategies = StrategyRegistry::keys();
// ['develop', 'qa', 'deploy', 'my_custom']
```

---

### 5. GenericWorker

**File:** `app/Agents/GenericWorker.php`

**The game-changer:** Single worker class that handles ALL agent types.

```php
// Load agent from database
$agent = Agent::where('name', 'dave')->first();

// Create worker (auto-loads strategy)
$worker = new GenericWorker($agent);

// Run worker loop
$worker->run();
```

**How it works:**
1. Reads `agents.strategy_class` field
2. Loads strategy from `StrategyRegistry`
3. Delegates `pollForWork()` to strategy
4. Delegates `processTask()` to strategy
5. Handles common concerns (logging, error handling)

**No more hard-coded agent classes!**

---

### 6. Database Schema Changes

**Migration:** `2026_02_27_135700_add_strategy_support_to_agents_table.php`

**New Fields:**
- `strategy_class` — Strategy name (e.g., 'develop', 'qa', 'deploy')
- `step_filter` — Workflow steps to poll (e.g., 'develop' or 'staging,production')
- `workflow_config` — Strategy-specific JSON config

**Example Agent Config:**
```json
{
  "name": "alex",
  "strategy_class": "develop",
  "step_filter": "api_develop",
  "workflow_config": {
    "next_step": "api_review",
    "next_assignee": "api_reviewer"
  },
  "model": "qwen3-coder:latest",
  "provider": "ollama",
  "system_prompt": "You are Alex, an API developer...",
  "model_settings": {
    "temperature": 0.3,
    "poll_interval": 30
  }
}
```

---

## Capabilities

### Before Refactor

| Task | Action Required |
|------|-----------------|
| Add new worker type | Create PHP class, deploy code |
| Change worker behavior | Modify class, deploy code |
| Configure new agent | Edit code + migration |
| Test new strategy | Manual instantiation |

### After Refactor

| Task | Action Required |
|------|-----------------|
| Add new worker type | Create strategy class OR use existing |
| Change worker behavior | Update database config |
| Configure new agent | **Web UI form** (Phase 2) |
| Test new strategy | Instant via registry |

---

## Usage Examples

### Example 1: Use Existing Strategy

```php
use App\Models\Agent;
use App\Agents\GenericWorker;

// Agent using develop strategy
$agent = Agent::create([
    'name' => 'dev1',
    'strategy_class' => 'develop',
    'step_filter' => 'develop',
    'model' => 'qwen3-coder:latest',
    'provider' => 'ollama',
]);

$worker = new GenericWorker($agent);
$worker->run();
```

### Example 2: Create Custom Strategy

```php
use App\Agents\Strategies\WorkerStrategy;
use App\Agents\Strategies\StrategyRegistry;

class SecurityScanStrategy implements WorkerStrategy {
    public function pollForWork(Agent $agent): ?Task {
        return Task::where('step', 'security_scan')->first();
    }
    
    public function processTask(Task $task, Agent $agent): void {
        // Run security scans...
    }
    
    public function getCapabilities(): array {
        return ['security', 'scanning', 'sast', 'dast'];
    }
    
    public function getWorkflowSteps(): array {
        return ['security_scan'];
    }
    
    public function getName(): string {
        return 'security_scan';
    }
}

// Register at runtime
StrategyRegistry::register('security_scan', SecurityScanStrategy::class);

// Use immediately
$agent = Agent::create([
    'name' => 'security_bot',
    'strategy_class' => 'security_scan',
    'step_filter' => 'security_scan',
]);

$worker = new GenericWorker($agent);
$worker->run();
```

### Example 3: Register Strategy via Service Provider

```php
// AppServiceProvider.php
public function boot()
{
    StrategyRegistry::register('email_sender', EmailSenderStrategy::class);
    StrategyRegistry::register('data_processor', DataProcessorStrategy::class);
}
```

---

## Migration Path

### Existing Agents (Dave, Sam, Chen)

**Auto-migrated** by database migration:

| Agent | Strategy | Step Filter | Workflow Config |
|-------|----------|-------------|-----------------|
| dave | develop | develop | `{"next_step": "qa", "next_assignee": "sam"}` |
| sam | qa | qa | `{"next_step": "security", "next_assignee": "security"}` |
| chen | deploy | staging,production | `{"health_check_url": "/health", "rollback_enabled": true}` |

**Backward Compatibility:**
- Old agent classes (`DaveAgentWorker`, etc.) still exist
- They continue to work as before
- Gradual migration: use `GenericWorker` for new agents first

---

## Testing

**Test File:** `tests/generic-worker-test.php`

**Tests:**
1. ✅ Strategy registry loads all strategies
2. ✅ Strategies report correct capabilities
3. ✅ Strategies report correct workflow steps
4. ✅ GenericWorker instantiates with existing agents
5. ✅ Dynamic agent creation works
6. ✅ Custom strategy registration works

**Run:**
```bash
php tests/generic-worker-test.php
```

---

## Benefits

### Immediate
- ✅ **DRY** — Eliminated code duplication (Dave/Sam/Chen shared 80% logic)
- ✅ **Flexible** — Agents are configuration, not code
- ✅ **Testable** — Strategies can be unit tested independently
- ✅ **Extensible** — New strategies without touching core worker logic

### Future (Phase 2: Web UI)
- 🎯 **Web-Configurable** — HR module to create/manage agents
- 🎯 **No Deployments** — Add agents via UI form
- 🎯 **Strategy Marketplace** — Browse/register strategies from UI
- 🎯 **Agent Templates** — Pre-configured agent types

---

## Phase 1 Summary

### Files Created
- `app/Agents/Strategies/WorkerStrategy.php` (interface)
- `app/Agents/Strategies/Concerns/HasWorkerCapabilities.php` (trait)
- `app/Agents/Strategies/DevelopStrategy.php`
- `app/Agents/Strategies/QAStrategy.php`
- `app/Agents/Strategies/DeployStrategy.php`
- `app/Agents/Strategies/StrategyRegistry.php`
- `app/Agents/GenericWorker.php`
- `tests/generic-worker-test.php`

### Files Modified
- `app/Models/Agent.php` — Added `strategy_class`, `step_filter`, `workflow_config`
- Database migration — Added new columns

### Commit
**`cd1ef4e`** — "Arch: Implement Strategy Pattern for web-configurable AI workers"

---

## Next Steps

### Phase 2: Web UI (HR Module)
1. Create "Manage Agents" page
2. Form: Name, role, strategy (dropdown), capabilities, model config
3. Test agent button (validates strategy works)
4. Start/stop agent controls
5. View agent activity logs

### Phase 3: Strategy Library
1. Build more strategies (security scan, data processing, email sending)
2. Document strategy creation for developers
3. Strategy templates/examples

### Phase 4: Advanced Features
1. Hot-reload agents (no restart needed)
2. Agent scaling (multiple workers per strategy)
3. Load balancing across agents
4. Agent health monitoring + auto-restart

---

## Developer Guide: Creating New Strategies

### Step 1: Implement Interface

```php
use App\Agents\Strategies\WorkerStrategy;

class MyStrategy implements WorkerStrategy {
    // Implement 5 methods...
}
```

### Step 2: Use Trait (Optional)

```php
use App\Agents\Strategies\Concerns\HasWorkerCapabilities;

class MyStrategy implements WorkerStrategy {
    use HasWorkerCapabilities;
    
    // Now you have: callAI(), gitCommit(), completeTask(), etc.
}
```

### Step 3: Register Strategy

```php
// Option A: Runtime registration
StrategyRegistry::register('my_strategy', MyStrategy::class);

// Option B: Service Provider
// AppServiceProvider.php boot() method
StrategyRegistry::register('my_strategy', MyStrategy::class);

// Option C: Config file
// config/strategies.php
return [
    'my_strategy' => MyStrategy::class,
];
```

### Step 4: Create Agent

**Via Tinker:**
```php
Agent::create([
    'name' => 'my_agent',
    'strategy_class' => 'my_strategy',
    'step_filter' => 'my_step',
    'model' => 'qwen3-coder:latest',
    'provider' => 'ollama',
    'system_prompt' => 'You are my custom agent...',
]);
```

**Via Web UI (Phase 2):**
- Navigate to HR → Manage Agents
- Click "Add Agent"
- Fill form
- Save → Agent is live!

---

_Logged by Luna at 2:15 PM EST_
