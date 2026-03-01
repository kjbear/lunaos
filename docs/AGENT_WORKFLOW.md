# Agent Workflow Guide

**Created:** March 1, 2026  
**Version:** Phase 1 MVP  
**Location:** `lunaos/docs/AGENT_WORKFLOW.md`

---

## Overview

This guide explains how PHP agents (Dave, Maya, Chen, etc.) work within the LunaOS system. Agents are specialized AI subagents that execute tasks assigned by Luna or the Board layer.

---

## Agent Architecture

### Three-Tier Hierarchy

```
EXECUTIVE (5-10 min polling)
    ↓
BOARD (2-5 min polling)
    ↓
WORKER (30s polling)
```

**Worker Agents (Dave, Sam, Chen, Maya, Alex):**
- Poll every 30 seconds for new tasks
- Execute assigned tasks
- Report status back to Board

**Board Agents (Jordan, etc.):**
- Poll every 2-5 minutes
- Coordinate Worker agents
- Escalate to Executive if blocked

**Executive Agents:**
- Poll every 5-10 minutes
- Strategic oversight
- Rebalance workload

---

## Agent Configuration

### Database Schema

**Table:** `agents`
```sql
CREATE TABLE agents (
    id INTEGER PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL,
    model VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    strategy_class VARCHAR(255),
    step_filter VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Example Agent Record:**
```php
[
    'id' => 1,
    'name' => 'Dave',
    'role' => 'PHP Developer',
    'model' => 'ollama-local/qwen3-coder-next:cloud',
    'status' => 'active',
    'strategy_class' => 'develop',
    'step_filter' => 'develop',
]
```

---

## Strategy Pattern

Agents use a strategy pattern to determine behavior based on their role.

### Strategy Classes

| Strategy | Purpose | Agents Using |
|----------|---------|--------------|
| `develop` | Code generation, refactoring | Dave, Maya |
| `test` | Test writing, QA | Sam |
| `deploy` | Infrastructure, CI/CD | Chen |
| `design` | API design, architecture | Alex |
| `coordinate` | Task assignment, escalation | Jordan |

### Implementation

**File:** `app/Models/Agent.php`

```php
public function getStrategy()
{
    $className = "App\\Strategies\\" . ucfirst($this->strategy_class) . 'Strategy';
    return new $className();
}

public function executeStep($step)
{
    $strategy = $this->getStrategy();
    return $strategy->execute($step, $this);
}
```

---

## Task Lifecycle

### 1. Task Creation

**Table:** `tasks`
```php
Task::create([
    'agent_id' => $agentId,
    'title' => 'Implement feature X',
    'description' => 'Detailed description...',
    'status' => 'pending',
    'priority' => 'medium',
    'due_at' => now()->addHours(2),
]);
```

### 2. Agent Polling

**Polling Endpoint:** `/api/agents/{id}/poll`

**Response:**
```json
{
    "task": {
        "id": 42,
        "title": "Implement feature X",
        "description": "...",
        "context": {...}
    },
    "timeout": 30
}
```

### 3. Task Execution

**Agent receives task → Executes via strategy → Reports progress**

**Progress Updates:**
```php
Task::find($taskId)->update([
    'status' => 'in_progress',
    'progress' => 50,
]);
```

### 4. Task Completion

**On Success:**
```php
Task::find($taskId)->update([
    'status' => 'completed',
    'completed_at' => now(),
    'result' => 'Feature implemented successfully',
]);
```

**On Failure:**
```php
Task::find($taskId)->update([
    'status' => 'failed',
    'error' => 'Error message...',
]);
```

---

## Activity Logging

All agent actions are logged to `activity_logs` table.

**Schema:**
```sql
CREATE TABLE activity_logs (
    id INTEGER PRIMARY KEY,
    agent_id INTEGER NOT NULL,
    agent_name VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    task VARCHAR(255),
    status VARCHAR(50) NOT NULL,
    tokens_used INTEGER DEFAULT 0,
    runtime_ms INTEGER DEFAULT 0,
    cost DECIMAL(10,6) DEFAULT 0,
    metadata TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Example Log Entry:**
```php
ActivityLog::create([
    'agent_id' => 1,
    'agent_name' => 'Dave',
    'action' => 'task_started',
    'task' => 'Implement feature X',
    'status' => 'success',
    'tokens_used' => 5000,
    'runtime_ms' => 8500,
    'cost' => 0.000045,
    'metadata' => json_encode(['file_count' => 3]),
]);
```

---

## Spawning Agents

### Via OpenClaw CLI

```bash
# Spawn Dave (PHP Developer)
openclaw sessions spawn \
  --agent-id dave \
  --model ollama-local/qwen3-coder-next:cloud \
  --label "Dave - PHP Developer" \
  --task "Implement login feature"
```

### Via LunaOS UI

**Path:** `http://lunaos.test/agents`

1. Navigate to Agents module
2. Click "Spawn Agent"
3. Select agent template (Dave, Sam, Chen, etc.)
4. Configure model (dropdown)
5. Enter task description
6. Click "Spawn"

### Via API

```bash
curl -X POST http://lunaos.test/api/agents/spawn \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Dave",
    "role": "PHP Developer",
    "model": "ollama-local/qwen3-coder-next:cloud",
    "task": "Implement login feature"
  }'
```

---

## Agent Workspaces

Each agent has a dedicated workspace configuration.

**Table:** `workspace_configs`
```php
[
    'agent_id' => 1,
    'agent_name' => 'Dave',
    'config' => json_encode([
        'root' => '/workspace/lunaos',
        'allowed_paths' => [
            '/workspace/lunaos/app',
            '/workspace/lunaos/database',
        ],
        'denied_paths' => [
            '/workspace/lunaos/.env',
            '/workspace/memory',
        ],
        'git_config' => [
            'user.name' => 'Dave (AI)',
            'user.email' => 'dave@lunaos.test',
        ],
    ]),
]
```

---

## Monitoring Agents

### Real-time Status

**Livewire Component:** `SubagentMonitor.php`

**Displays:**
- Active agents
- Current task
- Progress %
- Tokens used
- Runtime
- Cost

### Activity Feed

**Livewire Component:** `ActivityFeed.php`

**Filters:**
- By agent
- By action type
- By date range
- By status

### Metrics Dashboard

**Key Metrics:**
- Tasks completed (today/week/month)
- Average task duration
- Token usage by agent
- Cost by agent
- Success rate

---

## Troubleshooting

### Agent Not Polling

**Symptoms:**
- Agent shows "inactive" status
- No tasks being picked up

**Diagnosis:**
```bash
# Check agent status
openclaw agents list

# Check session
openclaw sessions list --active
```

**Fix:**
1. Verify agent session is running
2. Check polling endpoint is accessible
3. Restart agent session if needed

### Task Stuck "In Progress"

**Symptoms:**
- Task status stays "in_progress" > 30 min
- No activity logs

**Diagnosis:**
```bash
# Check agent activity
SELECT * FROM activity_logs WHERE agent_id = ? ORDER BY created_at DESC LIMIT 10;
```

**Fix:**
1. Check agent session logs
2. Verify model is responding
3. Escalate to Board agent for reassignment

### High Token Usage

**Symptoms:**
- Agent using 10x normal tokens
- Cost alerts triggering

**Diagnosis:**
```bash
# Check token usage by agent
SELECT agent_name, SUM(tokens_used) as total_tokens 
FROM activity_logs 
WHERE created_at >= date('now', '-1 day')
GROUP BY agent_name;
```

**Fix:**
1. Review agent task instructions
2. Add token limits to strategy
3. Switch to more efficient model

---

## Best Practices

### For Agent Developers

1. **Clear Task Descriptions** — Be specific about acceptance criteria
2. **Set Reasonable Deadlines** — Don't set 5-min tasks for 2-hour work
3. **Monitor Progress** — Check Activity Feed regularly
4. **Review Outputs** — AI code needs human review before merge

### For Agent Operations

1. **Rotate Models** — Use cheaper models for simple tasks
2. **Set Token Limits** — Prevent runaway costs
3. **Log Everything** — Debug with activity logs
4. **Scale Gradually** — Add agents as workload increases

---

## Phase 2 Enhancements

- [ ] Real-time WebSocket updates for agent status
- [ ] Agent performance metrics (success rate, avg duration)
- [ ] Model A/B testing framework
- [ ] Automated agent scaling based on queue depth
- [ ] Cost alerts and budget enforcement
- [ ] Agent collaboration workflows (multi-agent tasks)

---

**Related Documentation:**
- `AGENT_MODEL_STRATEGY.md` — Model assignments and costs
- `TESTING_GUIDE.md` — How to test agent functionality
- `PHASE1_COMPLETION_REPORT.md` — Phase 1 status

**Maintainer:** Luna 🌙  
**Last Updated:** March 1, 2026
