# Agent Architecture

**Date:** February 27, 2026  
**Version:** 2.0 (Unified Worker Pattern)  
**Author:** Luna

---

## Overview

LunaOS uses a **unified agent worker pattern** where all AI agents (workers, board members, executives) share a common base class with type-specific behaviors.

This architecture provides:
- ✅ **Consistency** - Single mental model across all agents
- ✅ **Reusability** - Shared infrastructure, services, and logging
- ✅ **Extensibility** - Easy to add new agents of any type
- ✅ **Visibility** - Uniform activity tracking and monitoring
- ✅ **Flexibility** - Each agent can use different AI models

---

## Agent Types

### 1. **WORKER** - Execution-Focused Agents

**Purpose:** Execute concrete, actionable tasks (code, test, deploy)

**Examples:**
- **Dave** - PHP/Laravel development
- **Sam** - QA testing and validation
- **Chen** - DevOps and deployment

**Characteristics:**
- Poll interval: **30 seconds** (fast response)
- Tasks: Concrete, well-defined (write code, run tests, deploy)
- Output: Artifacts (files, commits, PRs, test reports)
- Success: Tests pass, code merged, deployments succeed
- AI Model: Specialized (Qwen3-Coder for dev, etc.)

**Workflow:**
```
Poll → Execute Task → Commit/Deploy → Advance Workflow
```

---

### 2. **BOARD** - Coordination-Focused Agents

**Purpose:** Make decisions, coordinate work, remove blockers

**Examples:**
- **Jordan** - Project Manager (prioritization, assignments)
- **Alex** - API Architect (design reviews, standards enforcement)

**Characteristics:**
- Poll interval: **2-5 minutes** (strategic pace)
- Tasks: Abstract, coordination-focused (assign, prioritize, escalate)
- Output: Decisions, priorities, reassignments
- Success: Team aligned, blockers removed, work flowing
- AI Model: Reasoning-focused (GLM-5, Dolphin 3.0)

**Workflow:**
```
Poll → Identify Blocked/Unassigned Tasks → Analyze → Decide → Act
```

**Checks For:**
- Blocked tasks needing escalation
- Unassigned tasks needing prioritization
- Team conflicts needing resolution
- Sprint/iteration planning needs

---

### 3. **EXECUTIVE** - Strategic Oversight Agents

**Purpose:** Monitor overall system health, alert on anomalies

**Examples:**
- **Executive Board** - Strategic oversight, capacity planning

**Characteristics:**
- Poll interval: **5-10 minutes** (big picture view)
- Tasks: System-level monitoring and optimization
- Output: Alerts, recommendations, rebalancing decisions
- Success: Healthy workflow, balanced capacity, strategic alignment
- AI Model: High-level reasoning (GLM-5)

**Workflow:**
```
Poll → Assess Health → Detect Issues → Alert/Rebalance
```

**Checks For:**
- Workflow bottlenecks (>20% blocked tasks)
- Resource allocation issues (team overload >80%)
- Performance anomalies (stale tasks >24h)
- Strategic alignment (sprint goals vs. actual progress)

---

## Class Hierarchy

```
App\Agents\AgentWorker (abstract base)
│
├── AgentType::WORKER
│   ├── DaveAgentWorker (PHP development)
│   ├── SamAgentWorker (QA testing)
│   └── ChenAgentWorker (DevOps deployment)
│
├── AgentType::BOARD
│   ├── JordanAgentWorker (Project Manager)
│   └── AlexAgentWorker (API Architect)
│
└── AgentType::EXECUTIVE
    └── ExecutiveAgentWorker (Strategic oversight)
```

---

## Base Class: `AgentWorker`

### Properties

```php
public string $name;              // Agent identifier (dave, jordan, etc.)
public AgentType $type;           // WORKER | BOARD | EXECUTIVE
public int $pollInterval = 30;    // Seconds between polls
public array $capabilities = [];  // Skills/abilities
```

### Abstract Methods (Must Implement)

**Workers:**
- `pollForWork(): ?Task` - Find tasks to execute
- `processTask(Task $task): void` - Execute the task

**Board Members:**
- `handleBlockedTask(Task $task): void` - Resolve blockers
- `prioritizeAndAssignTasks(array $tasks): void` - Assign work
- `processBoardWork(): void` - Custom coordination logic

**Executives:**
- `addressWorkflowIssues(array $health): void` - Fix bottlenecks
- `rebalanceWorkload(array $capacity): void` - Rebalance work
- `processExecutiveWork(): void` - Strategic initiatives

### Shared Methods (Available to All)

**Configuration:**
- `getAgentConfig(): Agent` - Load from database
- `getModel(): string` - Get AI model identifier
- `getProvider(): Lab` - Get AI provider (Ollama, OpenRouter, etc.)
- `getSystemPrompt(): string` - Get agent's system prompt
- `getModelSettings(): array` - Get temperature, max_tokens, etc.

**Logging:**
- `logActivity(Task $task, string $action, array $artifacts)` - Log task activity
- `logSystemActivity(string $action, array $data)` - Log system-level activity

**Type Checking:**
- `isWorker(): bool` - Is this a worker agent?
- `isBoard(): bool` - Is this a board agent?
- `isExecutive(): bool` - Is this an executive agent?

---

## Implementation Example: Jordan (Project Manager)

```php
<?php

namespace App\Agents;

use App\Models\Task;
use Laravel\Ai\Facades\Ai;

class JordanAgentWorker extends AgentWorker
{
    public string $name = 'jordan';
    
    public AgentType $type = AgentType::BOARD;
    
    public int $pollInterval = 120; // 2 minutes
    
    public array $capabilities = ['prioritize', 'assign', 'escalate', 'unblock'];
    
    protected function handleBlockedTask(Task $task): void
    {
        // Analyze block with AI
        $analysis = $this->analyzeBlockWithAI($task);
        
        // Take action
        if ($analysis['action'] === 'reassign') {
            $task->update(['assigned_to' => $analysis['newAssignee']]);
        } elseif ($analysis['action'] === 'escalate') {
            $this->logSystemActivity('escalation', [...]);
        }
    }
    
    protected function prioritizeAndAssignTasks(array $tasks): void
    {
        foreach ($tasks as $task) {
            $assignment = $this->assignTaskWithAI($task);
            $task->update([
                'assigned_to' => $assignment['assignee'],
                'priority' => $assignment['priority'],
            ]);
        }
    }
}
```

---

## Database Schema

### `agents` Table

```sql
CREATE TABLE agents (
    id BIGINT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,      -- dave, jordan, sam, chen, alex
    type VARCHAR(20),             -- worker, board, executive
    title VARCHAR(100),           -- "PHP Developer", "Project Manager"
    model VARCHAR(100),           -- qwen3-coder, glm-5, dolphin
    provider VARCHAR(50),         -- ollama, openrouter, anthropic
    system_prompt TEXT,
    capabilities JSON,            -- ["php", "laravel"] or ["prioritize", "assign"]
    settings JSON,                -- {temperature: 0.7, max_tokens: 4096}
    avatar VARCHAR(100),
    emoji VARCHAR(10),
    is_online BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### `agent_activities` Table

```sql
CREATE TABLE agent_activities (
    id BIGINT PRIMARY KEY,
    task_id BIGINT NULL,          -- NULL for system-level activities
    agent_name VARCHAR(50),
    action VARCHAR(100),          -- started, completed, failed, escalated, assigned
    artifacts JSON,               -- {files_created: 5, tests_passed: true}
    duration_ms BIGINT,
    created_at TIMESTAMP
);
```

---

## Agent Configuration Examples

### Dave (Worker - PHP Developer)

```json
{
  "name": "dave",
  "type": "worker",
  "title": "PHP/Laravel Developer",
  "model": "qwen3-coder",
  "provider": "ollama",
  "poll_interval": 30,
  "capabilities": ["php", "laravel", "livewire", "blade", "api", "refactor"],
  "system_prompt": "You are Dave, a PHP/Laravel expert...",
  "settings": {
    "temperature": 0.3,
    "max_tokens": 8192
  }
}
```

### Jordan (Board - Project Manager)

```json
{
  "name": "jordan",
  "type": "board",
  "title": "Project Manager",
  "model": "glm-5",
  "provider": "openrouter",
  "poll_interval": 120,
  "capabilities": ["prioritize", "assign", "escalate", "unblock", "plan"],
  "system_prompt": "You are Jordan, the Project Manager...",
  "settings": {
    "temperature": 0.7,
    "max_tokens": 4096
  }
}
```

### Executive (Executive - Strategic Oversight)

```json
{
  "name": "exec-1",
  "type": "executive",
  "title": "Executive Board Member",
  "model": "glm-5",
  "provider": "openrouter",
  "poll_interval": 300,
  "capabilities": ["monitor", "alert", "rebalance", "strategize"],
  "system_prompt": "You are an executive overseeing...",
  "settings": {
    "temperature": 0.8,
    "max_tokens": 4096
  }
}
```

---

## Running Agent Workers

### Manual (CLI)

```bash
# Start Dave (worker)
php artisan tinker --execute="
    \$dave = new App\Agents\DaveAgentWorker();
    \$dave->run();
"

# Start Jordan (board)
php artisan tinker --execute="
    \$jordan = new App\Agents\JordanAgentWorker();
    \$jordan->run();
"
```

### Supervised (Laravel Horizon)

```php
// app/Console/Commands/StartAgentWorker.php
class StartAgentWorker extends Command
{
    protected $signature = 'agent:run {name}';
    
    public function handle()
    {
        $name = $this->argument('name');
        $agent = match($name) {
            'dave' => new DaveAgentWorker(),
            'jordan' => new JordanAgentWorker(),
            'sam' => new SamAgentWorker(),
            'chen' => new ChenAgentWorker(),
            default => throw new \Exception("Unknown agent: $name"),
        };
        
        $agent->run();
    }
}
```

Then configure in Horizon:

```php
// config/horizon.php
'environments' => [
    'local' => [
        'supervisor-1' => [
            'command' => ['php', 'artisan', 'agent:run', 'dave'],
            'balance' => 'simple',
            'processes' => 1,
            'tries' => 3,
        ],
    ],
],
```

---

## Activity Monitoring

All agent activities are logged to `agent_activities` table:

```php
// View recent activities
$activities = AgentActivity::with('task')
    ->latest()
    ->limit(50)
    ->get();

// Filter by agent
$daveActivities = AgentActivity::where('agent_name', 'dave')->get();

// Filter by action type
$escalations = AgentActivity::where('action', 'escalated')->get();

// System-level activities (board/executive)
$systemActivities = AgentActivity::whereNull('task_id')->get();
```

---

## Best Practices

### For Worker Agents
- ✅ Keep tasks focused and actionable
- ✅ Commit frequently (small, atomic commits)
- ✅ Log detailed artifacts (files changed, tests run)
- ✅ Fail fast with clear error messages
- ✅ Use specialized AI models (Qwen3-Coder for code)

### For Board Agents
- ✅ Prioritize ruthlessly (not everything is urgent)
- ✅ Escalate early when blocked >1 iteration
- ✅ Balance workload across team members
- ✅ Document decision reasoning
- ✅ Use reasoning-focused AI models (GLM-5)

### For Executive Agents
- ✅ Monitor trends, not just snapshots
- ✅ Alert only on significant issues (avoid noise)
- ✅ Recommend specific actions, not just problems
- ✅ Consider long-term vs. short-term tradeoffs
- ✅ Maintain strategic alignment with goals

---

## Future Enhancements

### Planned
- [ ] Agent-to-agent communication (direct messaging)
- [ ] Collaborative problem-solving (multiple agents on one task)
- [ ] Learning from past decisions (reinforcement feedback)
- [ ] Dynamic poll interval adjustment (faster when busy, slower when idle)
- [ ] Agent health monitoring and auto-restart

### Experimental
- [ ] Agent specialization (sub-specialties within roles)
- [ ] Mentorship patterns (experienced agents train new agents)
- [ ] Swarm intelligence (emergent behavior from simple rules)

---

## Troubleshooting

### Agent Not Picking Up Work
1. Check `is_online` flag in database
2. Verify poll interval isn't too long
3. Check task assignment (assigned_to matches agent name)
4. Review task status (must be pending/in_progress)

### Agent Crashing Repeatedly
1. Check `agent_activities` for error patterns
2. Review Laravel logs (`storage/logs/laravel.log`)
3. Verify AI model is accessible (Ollama running, API keys valid)
4. Check task complexity (may need to break into smaller tasks)

### Board Agent Not Making Decisions
1. Verify AI model is reasoning-focused (not code-focused)
2. Check system prompt clarity
3. Review decision framework in agent code
4. Ensure task data is complete (description, priority, etc.)

---

**Last Updated:** February 27, 2026  
**Related Docs:** `WORKFLOW_SYSTEM.md`, `AGENT_POLLING_PATTERN.md`
