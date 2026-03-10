# Worker Execution POC

**Date:** March 9, 2026  
**Status:** 🚧 In Development  
**Goal:** Prove Dave can poll, generate code, commit, and handoff tasks

---

## Overview

This POC proves the worker execution model works:

1. **Dave polls** for pending tasks (every 30s)
2. **Dave picks up** a task with `assigned_to = 'dave'` and `status = 'pending'`
3. **Dave generates code** using AI (Qwen3-Coder via Ollama Cloud)
4. **Dave commits** changes to Git with clear messages
5. **Dave marks complete** and hands off to Sam (QA)

---

## Quick Start

### 1. Create a Test Task

```bash
# Create a predefined test task
php artisan agent:task --test

# Or create a custom task
php artisan agent:task "Add hello world route" --assign=dave
```

### 2. Run Dave

```bash
# Single poll (test mode)
php artisan agent:run dave --once

# Continuous polling (daemon mode)
php artisan agent:run dave
```

### 3. Check Results

```bash
# View logs
tail -f storage/logs/agent-worker.log

# Check task status
php artisan agent:task --list

# Check database
php artisan tinker
>>> App\Models\Task::find(1)->status
```

---

## Architecture

### Components

| Component | File | Description |
|-----------|------|-------------|
| **WorkerExecutor** | `app/Services/WorkerExecutor.php` | Core execution engine |
| **AgentRunCommand** | `app/Console/Commands/AgentRunCommand.php` | CLI command to run agents |
| **AgentTaskCreateCommand** | `app/Console/Commands/AgentTaskCreateCommand.php` | CLI command to create tasks |
| **DaveAgentWorker** | `app/Agents/DaveAgentWorker.php` | Dave's task processing logic |
| **DaveCoder** | `app/Ai/Agents/DaveCoder.php` | AI agent for code generation |
| **GitService** | `app/Services/GitService.php` | Git operations |

### Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                   WORKER EXECUTION FLOW                      │
└─────────────────────────────────────────────────────────────┘

    php artisan agent:run dave --once
                │
                ▼
    ┌─────────────────────┐
    │  AgentRunCommand    │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  WorkerExecutor     │
    │  - Initialize Dave  │
    │  - Set run_once     │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  poll()             │
    │  Query: tasks WHERE │
    │  assigned_to=dave   │
    │  AND status=pending │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  execute(task)      │
    │  - Log start        │
    │  - Update in_progress │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  DaveAgentWorker    │
    │  -> processTask()   │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  DaveCoder AI       │
    │  - Generate code    │
    │  - Return JSON      │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  Write Files        │
    │  - Create migration │
    │  - Create models    │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  GitService         │
    │  - Create branch    │
    │  - Commit changes   │
    │  - Push to remote   │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  completeTask()     │
    │  - Update status    │
    │  - Assign to Sam    │
    │  - Log activity     │
    └─────────────────────┘
                │
                ▼
    ┌─────────────────────┐
    │  Task: step=qa      │
    │  assigned_to=sam    │
    └─────────────────────┘
```

---

## Database Schema

### Tasks Table

```sql
CREATE TABLE tasks (
    id INTEGER PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    assigned_to VARCHAR(50),      -- 'dave', 'sam', 'chen'
    status VARCHAR(50) DEFAULT 'pending',  -- pending, in_progress, complete, failed
    step VARCHAR(50) DEFAULT 'develop',    -- develop, qa, staging, production
    priority VARCHAR(50) DEFAULT 'medium', -- low, medium, high, critical
    task_type VARCHAR(50) DEFAULT 'feature',
    branch_name VARCHAR(255),
    pr_url VARCHAR(255),
    artifacts_json JSON,
    failure_reason TEXT,
    retry_count INTEGER DEFAULT 0,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Agent Activities Table

```sql
CREATE TABLE agent_activities (
    id INTEGER PRIMARY KEY,
    task_id INTEGER,
    agent_name VARCHAR(50),
    action VARCHAR(50),  -- started, completed, failed, advanced
    metadata_json JSON,
    created_at TIMESTAMP
);
```

---

## Environment Configuration

### Required Environment Variables

```env
# AI Provider
AI_DEFAULT_PROVIDER=ollama
AI_DEFAULT_MODEL=qwen3-coder

# Ollama Cloud
OLLAMA_CLOUD_API_KEY=your-api-key
OLLAMA_CLOUD_BASE_URL=https://ollama.com

# Optional: OpenRouter fallback
OPENROUTER_API_KEY=your-openrouter-key
```

### Agent Configuration (Database)

Dave's configuration is stored in the `agents` table:

```php
[
    'name' => 'dave',
    'role' => 'Backend Developer',
    'model' => 'qwen3-coder:latest',
    'provider' => 'ollama',
    'system_prompt' => 'You are Dave...',
    'model_settings' => [
        'temperature' => 0.3,
        'max_tokens' => 4096,
    ],
]
```

---

## Observability

### Log File

All worker activity is logged to `storage/logs/agent-worker.log`:

```
[2026-03-09 20:30:00] [dave] Initialized dave worker {"model":"qwen3-coder:latest","provider":"ollama"}
[2026-03-09 20:30:00] [dave] Worker started {"mode":"once","poll_interval":30}
[2026-03-09 20:30:00] [dave] Polling for tasks {"agent":"dave"}
[2026-03-09 20:30:00] [dave] Task found {"task_id":1,"title":"Create test migration"}
[2026-03-09 20:30:00] [dave] Executing task {"task_id":1}
[2026-03-09 20:30:15] [dave] Task completed {"task_id":1,"duration_ms":15234}
```

### Activity Table

Query `agent_activities` for detailed tracking:

```sql
SELECT * FROM agent_activities WHERE task_id = 1 ORDER BY created_at;
```

---

## Testing the POC

### Test 1: Create Migration Task

```bash
# 1. Create task
php artisan agent:task --test
# Select: migration

# 2. Run Dave
php artisan agent:run dave --once

# 3. Verify
ls database/migrations/*products*
git log --oneline -1

# 4. Check database
php artisan tinker
>>> $t = Task::latest()->first();
>>> $t->status;    // 'complete'
>>> $t->step;      // 'qa'
>>> $t->assigned_to; // 'sam'
```

### Test 2: Manual Task

```bash
# 1. Create task manually
php artisan tinker
>>> Task::create([
>>>     'title' => 'Add /hello-test route',
>>>     'description' => 'Create a test route returning Hello from Dave',
>>>     'assigned_to' => 'dave',
>>>     'step' => 'develop',
>>>     'status' => 'pending',
>>> ]);

# 2. Run Dave
php artisan agent:run dave --once

# 3. Check route exists
php artisan route:list | grep hello-test
```

### Test 3: Daemon Mode

```bash
# Run Dave continuously (polls every 30s)
php artisan agent:run dave

# Create task in another terminal
php artisan agent:task "Create User model" --assign=dave

# Watch Dave pick it up
tail -f storage/logs/agent-worker.log
```

---

## Troubleshooting

### "Agent not found in database"

Ensure agents are seeded:

```bash
php artisan db:seed --class=SeedTeamAgents
```

### "AI returned no files"

The AI response couldn't be parsed. Check:
1. Ollama Cloud API key is valid
2. Model name is correct (`qwen3-coder:latest`)
3. Network connectivity to Ollama Cloud

### "Git commit failed"

Check:
1. Repository exists and is initialized
2. Git config has user.name and user.email
3. No uncommitted changes blocking checkout

### Debug Mode

Add `--verbose` or check logs:

```bash
tail -f storage/logs/agent-worker.log
tail -f storage/logs/laravel.log
```

---

## Next Steps

1. **Add Sam** - QA agent runs tests after Dave
2. **Add Chen** - DevOps agent deploys to staging
3. **Add Jordan** - PM agent assigns tasks to workers
4. **GitHub Integration** - Real PR creation instead of mock URLs
5. **Supervisor Config** - Run agents as system services

---

## Success Criteria

✅ `php artisan agent:run dave --once` works  
✅ Dave finds pending tasks in DB  
✅ Dave generates code using AI  
✅ Dave commits to Git with clear message  
✅ Dave updates task status + assigns to Sam  
✅ Logs show what happened  
✅ No manual intervention needed  

---

## Files Reference

| File | Purpose |
|------|---------|
| `app/Services/WorkerExecutor.php` | Execution engine |
| `app/Console/Commands/AgentRunCommand.php` | Run agents |
| `app/Console/Commands/AgentTaskCreateCommand.php` | Create tasks |
| `app/Agents/DaveAgentWorker.php` | Dave's logic |
| `app/Ai/Agents/DaveCoder.php` | AI agent |
| `storage/logs/agent-worker.log` | Activity log |

---

_Updated: March 9, 2026_