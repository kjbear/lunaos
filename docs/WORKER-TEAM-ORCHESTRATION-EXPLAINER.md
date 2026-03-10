# Worker Team Orchestration - Architect's Explanation

**For:** Kyle  
**From:** Architect Agent  
**Date:** March 9, 2026  
**Question:** "How do we make PHP agents a fully functioning team? Do they poll for work? Do they need a common place to put work? How does it work?"

---

## The Short Answer

**Yes, they poll. Yes, they need shared storage. Here's how it works:**

1. **Shared Database** holds all tasks and their state
2. **Each Worker** polls the database every 30 seconds asking "any work for me?"
3. **Work flows** through stages: Develop → QA → Staging → Production
4. **Agents hand off** by updating task status and assigning to the next worker

Think of it like a digital ticket board that agents check for instructions.

---

## The Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              TASK LIFECYCLE                                  │
└─────────────────────────────────────────────────────────────────────────────┘

   Kyle creates feature request
           │
           ▼
    ┌─────────────┐
    │   Backlog   │  ← Jordan (PM) reviews, prioritizes, assigns
    └─────────────┘
           │ assigns to: dave
           ▼
    ┌─────────────┐
    │   Develop   │  ← Dave polls (every 30s), sees task, writes code
    └─────────────┘
           │ status → completed, assigns to: sam
           ▼
    ┌─────────────┐
    │     QA      │  ← Sam polls (every 30s), runs tests, passes/fails
    └─────────────┘
           │ if pass → assigns to: chen (staging)
           │ if fail → returns to Dave with notes
           ▼
    ┌─────────────┐
    │   Staging   │  ← Chen deploys to staging, runs health checks
    └─────────────┘
           │ passes → assigns to: chen (production)
           ▼
    ┌─────────────┐
    │ Production  │  ← Chen deploys to production, monitors
    └─────────────┘
           │
           ▼
    ┌─────────────┐
    │    Done!    │  ← Task complete, activity logged
    └─────────────┘


POLPING MECHANISM
==================

    Every 30 seconds:
    
    DaveAgentWorker              SamAgentWorker              ChenAgentWorker
         │                            │                            │
         │                            │                            │
         ▼                            ▼                            ▼
    ┌─────────────┐             ┌─────────────┐             ┌─────────────┐
    │ SELECT *    │             │ SELECT *    │             │ SELECT *    │
    │ FROM tasks  │             │ FROM tasks  │             │ FROM tasks  │
    │ WHERE       │             │ WHERE       │             │ WHERE       │
    │ assigned_to │             │ assigned_to │             │ assigned_to │
    │ = 'dave'    │             │ = 'sam'     │             │ = 'chen'    │
    │ AND status  │             │ AND step    │             │ AND step IN │
    │ = 'pending' │             │ = 'qa'      │             │ ('staging', │
         │                        │                            │ 'production')
         ▼                            ▼                            ▼
    Found task?                  Found task?                  Found task?
         │                            │                            │
        Yes                          Yes                          Yes
         │                            │                            │
         ▼                            ▼                            ▼
    Execute work                 Run tests                   Deploy code
    (write code)                 (PHPUnit, Dusk)             (health checks)
         │                            │                            │
         ▼                            ▼                            ▼
    Update status                Update status               Update status
    Assign to Sam                Assign to Chen              Mark complete


WHERE WORK LIVES
================

┌─────────────────────────────────────────────────────────────────────────────┐
│                          SHARED WORKSPACE                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                        DATABASE (SQLite → PostgreSQL)                 │   │
│  │                                                                       │   │
│  │   tasks table:                                                        │   │
│  │   ┌─────────────────────────────────────────────────────────────┐    │   │
│  │   │ id | title | assigned_to | status | step | branch_name     │    │   │
│  │   │----|-------|-------------|--------|------|----------------|    │   │
│  │   │ 42 | Auth  | dave        | pending| dev  | feature/42-auth│    │   │
│  │   │ 43 | Tests | sam         | pending| qa   | feature/42-auth│    │   │
│  │   └─────────────────────────────────────────────────────────────┘    │   │
│  │                                                                       │   │
│  │   agent_activities table:                                             │   │
│  │   ┌─────────────────────────────────────────────────────────────┐    │   │
│  │   │ id | task_id | agent_name | action | metadata_json          │    │   │
│  │   │----|---------|------------|--------|------------------------│    │   │
│  │   │ 1  | 42      | dave       | started| {...}                  │    │   │
│  │   │ 2  | 42      | dave       | commit | {"sha": "abc123"}      │    │   │
│  │   └─────────────────────────────────────────────────────────────┘    │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                        FILE SYSTEM                                    │   │
│  │                                                                       │   │
│  │   /workspace/lunaos/                                                  │   │
│  │   ├── app/                    ← Dave writes PHP code here             │   │
│  │   ├── resources/views/        ← Maya writes Blade/Vue here            │   │
│  │   ├── tests/                  ← Sam runs PHPUnit/Dusk here            │   │
│  │   └── database/migrations/    ← Schema changes                        │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                        GIT REPOSITORY                                 │   │
│  │                                                                       │   │
│  │   kjbear/lunaos (GitHub)                                             │   │
│  │   ├── main                    ← Production branch                     │   │
│  │   ├── staging                 ← Staging branch                        │   │
│  │   └── feature/42-auth         ← Dave's feature branch                 │   │
│  │                                                                       │   │
│  │   Commits: "Dev: Add user authentication (#42) - AI-generated by dave"│   │
│  │   PRs: Auto-created when Dave marks task complete                     │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## The Three Key Components

### 1. Shared Database (The Ticket Board)

**What it stores:**
- `tasks` table: Every task with its status, assigned agent, and current step
- `agent_activities` table: Audit trail of every action each agent takes
- `agents` table: Configuration for each agent (model, prompts, capabilities)

**Why it matters:**
Without a shared database, agents can't coordinate. Dave doesn't know Sam finished testing. Jordan can't see what's blocked. The database is the single source of truth.

**Current Status:** ✅ **Built.** Tables exist with proper schema.

```sql
-- Key query workers use:
SELECT * FROM tasks 
WHERE assigned_to = 'dave' 
  AND status = 'pending'
LIMIT 1;

-- Key query Jordan uses:
SELECT * FROM tasks 
WHERE status = 'blocked' OR assigned_to IS NULL;
```

### 2. Polling Loop (The Heartbeat)

**How it works:**
```php
// Pseudo-code for Dave's polling loop
while ($running) {
    $task = Task::where('assigned_to', 'dave')
                 ->where('status', 'pending')
                 ->first();
    
    if ($task) {
        $this->execute($task);
        $task->update(['status' => 'completed', 'assigned_to' => 'sam']);
    }
    
    sleep(30); // Wait 30 seconds before checking again
}
```

**Polling frequency:**
- **Workers (Dave, Sam, Chen):** 30 seconds — need to respond quickly
- **Board (Jordan):** 2 minutes — coordination doesn't need instant response
- **Executive (Board debate):** On-demand — only runs when summoned

**Current Status:** 🔶 **Partially built.** The `AgentWorker` class has the polling loop structure, but nothing is calling it. We need a scheduler/queue worker.

### 3. Git Integration (The Code Flow)

**What it does:**
- Creates feature branches (`feature/42-user-auth`)
- Commits code with standardized messages
- Creates pull requests (via GitHub API)
- Handles rollbacks on failure

**Why it matters:**
Without Git integration, Dave can't save his work. The code needs to live somewhere versioned and reviewable.

**Current Status:** ✅ **Built.** `GitService` class handles branch creation and commits.

---

## The Agents

### Dave (Backend Developer) 🔧
- **Polls every:** 30 seconds
- **Looks for:** Tasks with `step = 'develop'` and `assigned_to = 'dave'`
- **Does:** Writes PHP/Laravel code, creates migrations, implements features
- **On completion:** Updates status to `completed`, assigns to `sam` (QA)
- **File:** `app/Agents/DaveAgentWorker.php`

### Sam (QA Engineer) 🧪
- **Polls every:** 30 seconds
- **Looks for:** Tasks with `step = 'qa'` and `assigned_to = 'sam'`
- **Does:** Runs PHPUnit tests, Dusk browser tests, reports bugs
- **On pass:** Updates status, assigns to `chen` (staging)
- **On fail:** Returns to `dave` with failure notes
- **File:** `app/Agents/SamAgentWorker.php`

### Chen (DevOps) 🚀
- **Polls every:** 30 seconds
- **Looks for:** Tasks with `step IN ('staging', 'production')`
- **Does:** Deploys code, runs health checks, monitors, rolls back on failure
- **On success:** Marks complete or promotes to production
- **File:** `app/Agents/ChenAgentWorker.php`

### Jordan (Architect/PM) 📋
- **Polls every:** 2 minutes
- **Looks for:** Unassigned tasks, blocked tasks, high-priority items
- **Does:** Prioritizes backlog, assigns tasks to workers, escalates blockers
- **On action:** Creates `agent_activities` entries explaining decisions
- **File:** `app/Agents/JordanAgentWorker.php` + `docs/agents/JORDAN.md`

### Maya (Frontend) 🎨
- **Polls every:** 30 seconds
- **Looks for:** Frontend tasks (Blade, Vue, CSS, JavaScript)
- **Does:** UI/UX implementation, style fixes, accessibility improvements
- **Status:** 🔶 Defined but not yet implemented

### Alex (API Specialist) 🔌
- **Polls every:** 30 seconds
- **Looks for:** API tasks (endpoints, integrations, webhooks)
- **Does:** REST API development, external service integrations
- **Status:** 🔶 Defined but not yet implemented

---

## What's Already Built

| Component | Status | Location |
|-----------|--------|----------|
| Database schema | ✅ Complete | `database/migrations/*tasks*`, `*agents*` |
| Agent definitions | ✅ Complete | `app/Agents/*AgentWorker.php` |
| Base Worker class | ✅ Complete | `app/Agents/AgentWorker.php` |
| Git integration | ✅ Complete | `app/Services/GitService.php` |
| Skills directory | ✅ Complete | `lunaos/skills/*/SKILL.md` |
| Activity logging | ✅ Complete | `agent_activities` table + model |
| Polling loop structure | 🔶 Framework only | `AgentWorker::run()` exists but unused |

---

## What's Missing

### 🔴 **Critical Gap: No Execution Engine**

The `AgentWorker::run()` method exists with a polling loop, but **nothing calls it**. We need:

1. **A Supervisor process** to keep agents alive permanently
   ```bash
   # Example supervisor config
   [program:dave-agent]
   command=php artisan agent:run dave
   autostart=true
   autorestart=true
   ```
   
2. **Or a Laravel Queue Worker** approach
   ```php
   // Schedule agents to poll every 30s
   $schedule->call(fn() => DaveAgentWorker::poll())->everyThirtySeconds();
   ```

3. **Or a standalone daemon** script
   ```bash
   php artisan agent:daemon --agent=dave
   ```

### 🔴 **Critical Gap: AI Integration**

The workers have structure but no actual AI capability:

```php
// Dave's execute method needs to call an LLM
protected function execute(Task $task): void
{
    // TODO: This needs to call Ollama/OpenRouter with the task context
    $code = $this->aiService->generate([
        'system' => $this->getSystemPrompt(),
        'prompt' => $task->description,
    ]);
    
    // Write the code to files
    $this->writeCode($task, $code);
    
    // Commit to git
    $this->gitService($task)->commit($task, $code);
}
```

### 🟡 **Secondary Gap: Task Creation Workflow**

We need a way for tasks to enter the system:

```php
// Kyle creates a feature request
Task::create([
    'title' => 'Add user authentication',
    'description' => 'Implement login/logout with session management',
    'assigned_to' => null, // Jordan will assign
    'status' => 'pending',
    'step' => 'develop',
]);

// Jordan polls, sees it, assigns to Dave
// Dave polls, sees it, executes...
```

**Options:**
- UI form (Livewire component)
- CLI command (`php artisan task:create`)
- GitHub webhook (new issue → new task)
- OpenClaw integration (Kyle tells Luna → Luna creates task)

---

## Architect's Recommendation

### Is the architecture sound?

**Yes, fundamentally.** The polling model is proven (it's how most CI/CD systems work). The shared database + Git combination covers state and code. The pipeline stages (Dev → QA → Staging → Production) are standard.

**The architecture is good. The implementation is incomplete.**

### What should we build first?

**I recommend starting with Dave as a proof of concept:**

1. **Week 1: Make Dave Actually Work**
   - Create `php artisan agent:run dave` command
   - Wire up AI integration (Ollama/Qwen3-Coder)
   - Test with a single simple task ("Add a hello world route")
   - Verify: Task → Dave picks up → Code written → Committed → Status updated

2. **Week 2: Add Sam (QA)**
   - Same pattern for Sam
   - Wire Dave → Sam handoff
   - Test: Dave completes → Sam runs tests → Pass/fail logic

3. **Week 3: Add Chen (DevOps)**
   - Deploy to staging (can be local Docker for now)
   - Health check endpoints
   - Test: Sam passes → Chen deploys → Health check

4. **Week 4: Add Jordan (PM)**
   - Backlog prioritization
   - Task assignment logic
   - Monitoring dashboard

### Estimated Effort

| Task | Effort | Dependencies |
|------|--------|--------------|
| Agent run command | 2-4 hours | None |
| AI integration (Ollama) | 4-8 hours | Ollama server running |
| Dave execution logic | 8-16 hours | AI integration |
| Sam testing logic | 8-16 hours | Dave working |
| Chen deployment logic | 16-24 hours | Staging environment |
| Jordan coordination | 8-16 hours | Workers functional |
| **Total MVP** | **~2-3 weeks** | |

### What's the fastest path to proof?

**The 30-minute demo:**

```bash
# 1. Create a simple artisan command
php artisan make:command AgentTestRun

# 2. In the command, manually:
$agent = new DaveAgentWorker();
$config = $agent->getAgentConfig();

# 3. Create a test task
$task = Task::create([
    'title' => 'Test: Add /hello route',
    'assigned_to' => 'dave',
    'status' => 'pending',
    'step' => 'develop',
]);

# 4. Call Dave's poll method
$result = $agent->pollAndExecute();

# 5. Check: Did Dave write the route? Did task status change?
```

This proves the concept without building the full daemon infrastructure.

---

## Next Steps for Kyle

1. **Decide on execution approach:**
   - Option A: Supervisor (most robust, requires server setup)
   - Option B: Laravel scheduler (simpler, less control)
   - Option C: Manual trigger for MVP (fastest to demo)

2. **Confirm AI backend:**
   - Current config: Qwen3-Coder via Ollama Cloud
   - Is this still the right choice?
   - API keys / connection details needed

3. **Define first task:**
   - What should Dave build first?
   - Keep it simple for proof of concept

4. **Approve next sprint:**
   - I recommend: "Make Dave work" as the goal
   - Deliverables: Agent command, AI integration, one successful task execution

---

**Questions for Kyle:**

1. Do you want to build a proof of concept with Dave first, or build all workers in parallel?
2. What's the simplest task you'd like to see Dave execute?
3. Are you comfortable with the Supervisor approach, or prefer something simpler?

---

**Document Version:** 1.0  
**Author:** Architect Agent  
**Review Status:** Ready for Kyle's review