# Development Pipeline Workflow System

**Project ID:** `dev-pipeline-workflow`  
**Created:** February 26, 2026  
**Status:** Planning  
**Priority:** High  
**Owner:** Kyle Obear  
**PM:** Luna  

---

## Executive Summary

Build an automated development-to-production pipeline using **Durable Workflow** (Laravel-native workflow engine) that orchestrates AI agents through the complete software delivery lifecycle:

```
Task Assignment → Development → QA Testing → Security Scan → Staging → Production
      ↓               ↓            ↓              ↓           ↓         ↓
    (PM)          (Dave)        (Sam)         (Auto)      (Chen)    (Chen)
```

**Decision:** Use Durable Workflow package over GitHub Actions or custom solution.

**Rationale:**
- ✅ Laravel-native (uses existing stack)
- ✅ Database-backed state (crash recovery)
- ✅ Agent polling pattern (workers poll for tasks)
- ✅ Conditional branching (pass/fail logic)
- ✅ Long-running processes (hours/days between steps)
- ✅ Visual monitoring built-in
- ✅ FREE (self-hosted, no per-minute charges)

---

## Problem Statement

**Current State:**
- Manual task assignment and tracking
- No automated handoff between development stages
- Testing is manual or siloed
- Deployment requires manual intervention
- No audit trail of who did what, when
- No visibility into pipeline bottlenecks

**Desired State:**
- PM assigns task → system auto-routes to developer agent
- Code check-in triggers automated QA
- Test failures auto-return to developer with error details
- Security scanning before production
- Staging validation before production deploy
- Complete audit trail + metrics
- Visual Kanban board showing all tasks in flight

---

## Solution Overview

### Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                        LunaOS Task Kanban                         │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌────────┐ │
│  │  Todo   │  │ In Prog │  │  Ready  │  │ Testing │  │  Done  │ │
│  │   [3]   │  │   [2]   │  │   [1]   │  │   [2]   │  │   [5]  │ │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘  └─────────┘ │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│                    Durable Workflow Engine                        │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │             DevelopmentPipelineWorkflow                   │   │
│  │  Assign → Develop → QA → [Branch] → Security → Staging   │   │
│  │                                              ↓            │   │
│  │                                         [Branch] → Prod   │   │
│  └──────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│                      Agent Workers (Polling)                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │   Dave   │  │   Sam    │  │   Chen   │  │ Security │        │
│  │  (PHP)   │  │   (QA)   │  │ (DevOps) │  │  Scanner │        │
│  │  Poll:30s│  │  Poll:30s│  │  Poll:30s│  │  Auto    │        │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘        │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│                      External Integrations                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │   Git    │  │  PHPUnit │  │  Laravel │  │  Docker  │        │
│  │  (Commits│  │  (Unit   │  │   Dusk   │  │ (Deploy  │        │
│  │   & PRs) │  │  Tests)  │ (Browser)  │  │  to Env) │        │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘        │
└──────────────────────────────────────────────────────────────────┘
```

### Workflow Steps

| # | Step | Agent | Polls For | Outputs | Next Step |
|---|------|-------|-----------|---------|-----------|
| 1 | **Task Assignment** | System (auto) | PM creates task record | Task in "pending" status | Development |
| 2 | **Development** | Dave | `assigned_to='dave'` + `status='pending'` | Git commit, PR created | QA Testing |
| 3 | **QA Testing** | Sam | `assigned_to='sam'` + `status='pending'` | Test report (pass/fail) | → Pass: Security<br>→ Fail: Back to Dave |
| 4 | **Security Scan** | Auto (SAST) | Triggered on QA pass | Security report | Staging Deploy |
| 5 | **Staging Deploy** | Chen | `assigned_to='chen'` + `env='staging'` | Deployment confirmation | Staging Test |
| 6 | **Staging Test** | Sam | `assigned_to='sam'` + `env='staging'` | Test report (pass/fail) | → Pass: Prod Deploy<br>→ Fail: Back to Staging |
| 7 | **Production Deploy** | Chen | `assigned_to='chen'` + `env='production'` | Deployment confirmation | Complete |

---

## Technical Specification

### Package Stack

| Component | Technology | Purpose |
|-----------|-----------|---------|
| **Workflow Engine** | Durable Workflow | Orchestration, state management |
| **Queue System** | Laravel Queues (database) | Agent task distribution |
| **Database** | SQLite (dev) / PostgreSQL (prod) | Workflow state + task tracking |
| **Git Integration** | GitPHP / Command-line | Commits, PRs, branch management |
| **Testing** | PHPUnit + Laravel Dusk | Unit + Browser tests |
| **Security** | Laravel Shift / Phan / Rector | SAST, dependency scanning |
| **Deployment** | Docker + Laravel Envoy | Environment deployments |
| **UI** | LunaOS Task Manager + Livewire | Kanban board, workflow viewer |

### Database Schema

```sql
-- Workflow tracking (from Durable Workflow package)
workflows
├── id                  UUID
├── class               VARCHAR (DevelopmentPipelineWorkflow)
├── status              ENUM (running, completed, failed, cancelled)
├── current_activity    VARCHAR
├── context             JSON (all workflow variables)
├── created_at          TIMESTAMP
└── updated_at          TIMESTAMP

-- Kanban board tasks
tasks
├── id                  UUID
├── workflow_id         FK → workflows.id
├── sprint_id           FK → sprints.id (optional)
├── title               VARCHAR(255)
├── description         TEXT
├── current_step        ENUM (assign, develop, qa, security, staging, prod)
├── assigned_to         ENUM (dave, sam, chen, security)
├── status              ENUM (pending, in_progress, blocked, complete, failed)
├── git_branch          VARCHAR(255)
├── git_commit_hash     VARCHAR(40)
├── git_pr_url          VARCHAR(500)
├── test_report_path    VARCHAR(500)
├── security_report     JSON
├── failure_reason      TEXT
├── retry_count         INT DEFAULT 0
├── created_by          VARCHAR (PM/human user)
├── created_at          TIMESTAMP
└── updated_at          TIMESTAMP

-- Agent activity log
agent_activities
├── id                  UUID
├── task_id             FK → tasks.id
├── workflow_id         FK → workflows.id
├── agent_name          ENUM (dave, sam, chen)
├── action              ENUM (started, completed, failed, retried, skipped)
├── artifacts           JSON (commit hash, test results, deploy URL)
├── duration_ms         INT
├── error_message       TEXT
└── created_at          TIMESTAMP

-- Sprint tracking (optional, for sprint-based workflow)
sprints
├── id                  UUID
├── name                VARCHAR(100)
├── start_date          DATE
├── end_date            DATE
├── status              ENUM (planning, active, review, completed)
└── created_at          TIMESTAMP
```

### Agent Worker Pattern

```php
<?php

namespace App\Agents;

use App\Models\Task;
use App\Models\AgentActivity;
use Illuminate\Support\Facades\DB;

abstract class AgentWorker
{
    public string $name;
    public int $pollInterval = 30; // seconds
    public array $capabilities = [];
    
    /**
     * Main worker loop - polls for work indefinitely
     */
    public function run(): void
    {
        echo "🤖 {$this->name} worker started (poll interval: {$this->pollInterval}s)\n";
        
        while (true) {
            try {
                $task = $this->pollForWork();
                
                if ($task) {
                    echo "📋 {$this->name} picked up task #{$task->id}\n";
                    $this->processTask($task);
                }
                
            } catch (\Exception $e) {
                report($e);
                echo "❌ {$this->name} error: {$e->getMessage()}\n";
            }
            
            sleep($this->pollInterval);
        }
    }
    
    /**
     * Poll database for tasks assigned to this agent
     */
    abstract protected function pollForWork(): ?Task;
    
    /**
     * Process a single task - implemented by each agent
     */
    abstract protected function processTask(Task $task): void;
    
    /**
     * Log agent activity
     */
    protected function logActivity(Task $task, string $action, array $artifacts = []): void
    {
        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => $this->name,
            'action' => $action,
            'artifacts' => $artifacts,
        ]);
    }
    
    /**
     * Update task status and advance workflow
     */
    protected function completeTask(Task $task, string $nextStep, string $nextAssignee, array $artifacts = []): void
    {
        $task->update([
            'status' => 'complete',
            'current_step' => $nextStep,
            'assigned_to' => $nextAssignee,
            'updated_at' => now(),
        ]);
        
        $this->logActivity($task, 'completed', $artifacts);
        
        echo "✅ {$this->name} completed task #{$task->id} → {$nextStep} ({$nextAssignee})\n";
    }
    
    /**
     * Fail task and return to previous step
     */
    protected function failTask(Task $task, string $reason, string $backToStep, string $backToAgent): void
    {
        $task->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'current_step' => $backToStep,
            'assigned_to' => $backToAgent,
            'retry_count' => $task->retry_count + 1,
            'updated_at' => now(),
        ]);
        
        $this->logActivity($task, 'failed', ['reason' => $reason]);
        
        echo "❌ {$this->name} failed task #{$task->id}: {$reason}\n";
    }
}
```

### Dave (PHP Developer) Worker

```php
<?php

namespace App\Agents;

use App\Services\CodeGenerator;
use App\Services\GitService;

class DaveWorker extends AgentWorker
{
    public string $name = 'dave';
    public array $capabilities = ['php', 'laravel', 'livewire', 'blade', 'api'];
    
    protected function pollForWork(): ?Task
    {
        return Task::where('assigned_to', 'dave')
            ->where('status', 'pending')
            ->where('current_step', 'develop')
            ->first();
    }
    
    protected function processTask(Task $task): void
    {
        // Start activity log
        $this->logActivity($task, 'started');
        
        // Generate code based on task description
        $codeGenerator = app(CodeGenerator::class);
        $result = $codeGenerator->generate($task);
        
        // Create branch
        $branchName = "feature/task-{$task->id}";
        $gitService = app(GitService::class);
        $gitService->checkoutBranch($branchName);
        
        // Write files
        foreach ($result['files'] as $path => $content) {
            file_put_contents(base_path($path), $content);
        }
        
        // Commit changes
        $commitHash = $gitService->commitAndPush(
            "AI: {$task->title} (Task #{$task->id})",
            $branchName
        );
        
        // Create PR
        $prUrl = $gitService->createPullRequest(
            $branchName,
            'main',
            $task->title,
            $task->description
        );
        
        // Run quick syntax check
        $syntaxCheck = $this->runSyntaxCheck($result['files']);
        
        if ($syntaxCheck['passed']) {
            // Success - advance to QA
            $this->completeTask($task, 'qa', 'sam', [
                'commit_hash' => $commitHash,
                'branch' => $branchName,
                'pr_url' => $prUrl,
                'files_changed' => count($result['files']),
            ]);
        } else {
            // Self-correct retry
            if ($task->retry_count < 2) {
                echo "⚠️ Syntax errors found, retrying...\n";
                $this->processTask($task); // Retry
            } else {
                $this->failTask($task, "Syntax errors after {$task->retry_count} retries: " . 
                    implode(', ', $syntaxCheck['errors']), 'develop', 'dave');
            }
        }
    }
    
    private function runSyntaxCheck(array $files): array
    {
        $errors = [];
        
        foreach ($files as $path => $content) {
            if (str_ends_with($path, '.php')) {
                $tmpFile = tempnam(sys_get_temp_dir(), 'php_syntax_');
                file_put_contents($tmpFile, $content);
                
                exec("php -l {$tmpFile} 2>&1", $output, $exitCode);
                
                if ($exitCode !== 0) {
                    $errors[] = "{$path}: " . implode("\n", $output);
                }
                
                unlink($tmpFile);
            }
        }
        
        return [
            'passed' => empty($errors),
            'errors' => $errors,
        ];
    }
}
```

### Sam (QA) Worker

```php
<?php

namespace App\Agents;

use App\Services\TestRunner;

class SamWorker extends AgentWorker
{
    public string $name = 'sam';
    public array $capabilities = ['phpunit', 'dusk', 'integration', 'functional'];
    
    protected function pollForWork(): ?Task
    {
        return Task::where('assigned_to', 'sam')
            ->where('status', 'pending')
            ->whereIn('current_step', ['qa', 'staging-test'])
            ->first();
    }
    
    protected function processTask(Task $task): void
    {
        $this->logActivity($task, 'started');
        
        $testRunner = app(TestRunner::class);
        
        // Checkout the PR branch
        $gitService = app(GitService::class);
        $gitService->checkout($task->git_branch);
        
        // Run unit tests
        $unitResults = $testRunner->runUnitTests();
        
        // Run feature tests
        $featureResults = $testRunner->runFeatureTests();
        
        // Run browser tests (if not staging)
        $browserResults = null;
        if ($task->current_step === 'qa') {
            $browserResults = $testRunner->runBrowserTests();
        }
        
        // Generate report
        $report = [
            'unit' => $unitResults,
            'feature' => $featureResults,
            'browser' => $browserResults,
            'passed' => $unitResults['passed'] && $featureResults['passed'] && 
                       ($browserResults === null || $browserResults['passed']),
            'timestamp' => now()->toIso8601String(),
        ];
        
        // Save report
        $reportPath = "storage/test-reports/task-{$task->id}-" . now()->format('Ymd-His') . '.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        if ($report['passed']) {
            $nextStep = $task->current_step === 'qa' ? 'security' : 'prod-deploy';
            $nextAssignee = $task->current_step === 'qa' ? 'security' : 'chen';
            
            $this->completeTask($task, $nextStep, $nextAssignee, [
                'test_report_path' => $reportPath,
                'unit_passed' => $unitResults['passed'],
                'feature_passed' => $featureResults['passed'],
                'browser_passed' => $browserResults['passed'] ?? null,
            ]);
        } else {
            $backToStep = $task->current_step === 'qa' ? 'develop' : 'staging-deploy';
            $backToAgent = $task->current_step === 'qa' ? 'dave' : 'chen';
            
            $failureReason = $this->generateFailureSummary($report);
            
            $this->failTask($task, $failureReason, $backToStep, $backToAgent);
        }
    }
    
    private function generateFailureSummary(array $report): string
    {
        $failures = [];
        
        if (!$report['unit']['passed']) {
            $failures[] = count($report['unit']['failures']) . ' unit test(s) failed';
        }
        
        if (!$report['feature']['passed']) {
            $failures[] = count($report['feature']['failures']) . ' feature test(s) failed';
        }
        
        if ($report['browser'] && !$report['browser']['passed']) {
            $failures[] = count($report['browser']['failures']) . ' browser test(s) failed';
        }
        
        return implode(', ', $failures);
    }
}
```

### Chen (DevOps) Worker

```php
<?php

namespace App\Agents;

use App\Services\DeployService;

class ChenWorker extends AgentWorker
{
    public string $name = 'chen';
    public array $capabilities = ['docker', 'kubernetes', 'laravel-envoy', 'staging', 'production'];
    
    protected function pollForWork(): ?Task
    {
        return Task::where('assigned_to', 'chen')
            ->where('status', 'pending')
            ->whereIn('current_step', ['staging-deploy', 'prod-deploy'])
            ->first();
    }
    
    protected function processTask(Task $task): void
    {
        $this->logActivity($task, 'started');
        
        $deployService = app(DeployService::class);
        $environment = $task->current_step === 'staging-deploy' ? 'staging' : 'production';
        
        // Checkout correct branch
        $gitService = app(GitService::class);
        $gitService->checkout($task->git_branch);
        
        // Deploy to environment
        $result = $deployService->deploy($environment, [
            'task_id' => $task->id,
            'commit' => $task->git_commit_hash,
        ]);
        
        if ($result['success']) {
            $nextStep = $task->current_step === 'staging-deploy' ? 'staging-test' : 'complete';
            $nextAssignee = $task->current_step === 'staging-deploy' ? 'sam' : null;
            
            $artifacts = [
                'environment' => $environment,
                'deploy_url' => $result['url'],
                'deploy_time_ms' => $result['duration_ms'],
                'version' => $result['version'],
            ];
            
            if ($nextAssignee) {
                $this->completeTask($task, $nextStep, $nextAssignee, $artifacts);
            } else {
                // Final step - mark workflow complete
                $task->update(['status' => 'complete']);
                $this->logActivity($task, 'deployed_to_production', $artifacts);
                echo "🚀 {$this->name} deployed task #{$task->id} to PRODUCTION\n";
            }
        } else {
            $this->failTask($task, "Deployment failed: {$result['error']}", 
                $task->current_step === 'staging-deploy' ? 'staging-deploy' : 'staging-deploy', 
                'chen');
        }
    }
}
```

---

## Project Phases

### Phase 1: Foundation (Week 1)
**Goal:** Working workflow engine with basic task tracking

- [ ] Install Durable Workflow package
- [ ] Design workflow definition (8-10 steps)
- [ ] Create database migrations (tasks, agent_activities tables)
- [ ] Build base `AgentWorker` class
- [ ] Create simple Kanban UI in LunaOS (Task Manager extension)
- [ ] Test: Manual task creation → workflow starts

**Deliverables:**
- ✅ Package installed
- ✅ Workflow class defined
- ✅ Database schema deployed
- ✅ Kanban board (basic view)

---

### Phase 2: Agent Workers (Week 2)
**Goal:** Dave and Sam workers functional

- [ ] Dave worker - polls, generates code, commits to git
- [ ] Sam worker - polls, runs PHPUnit tests, generates reports
- [ ] Git integration service (branch, commit, PR)
- [ ] Test report storage
- [ ] Branching logic (pass → next, fail → back)
- [ ] Test: Task → Dave → Git → Sam → Pass/Fail

**Deliverables:**
- ✅ Dave worker running
- ✅ Sam worker running
- ✅ Git integration working
- ✅ Test reports generated

---

### Phase 3: Security & Deployment (Week 3)
**Goal:** Complete pipeline to production

- [ ] Security scan integration (SAST, dependency check)
- [ ] Chen worker - staging deploy
- [ ] Sam staging tests
- [ ] Chen production deploy
- [ ] Error handling + retry policies
- [ ] Notifications (Slack/email on failures)
- [ ] Test: Full end-to-end pipeline

**Deliverables:**
- ✅ Security scanning automated
- ✅ Staging deployment working
- ✅ Production deployment working
- ✅ Full pipeline tested

---

### Phase 4: Monitoring & Metrics (Week 4)
**Goal:** Visibility and operations

- [ ] Workflow dashboard (LunaOS module)
- [ ] Task history + audit trail viewer
- [ ] Agent activity feed
- [ ] Metrics (cycle time, failure rate, throughput)
- [ ] Manual override (force step, retry, cancel workflow)
- [ ] Performance optimization

**Deliverables:**
- ✅ Dashboard in LunaOS
- ✅ Metrics tracking
- ✅ Manual override controls
- ✅ Production-ready system

---

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Cycle Time** | < 4 hours (todo → prod) | Average task completion time |
| **First-Pass Yield** | > 80% | % tasks passing QA on first try |
| **Deployment Frequency** | On-demand (anytime) | Time from code complete to prod |
| **Failure Recovery** | < 30 minutes | Time from failure to retry |
| **Agent Uptime** | > 99% | Worker availability |

---

## Risks & Mitigations

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| **Workflow state corruption** | Low | High | Database transactions, regular backups |
| **Agent worker crashes** | Medium | Medium | Supervisor process, auto-restart |
| **Git conflicts** | Medium | Low | Branch-per-task, auto-merge strategies |
| **Test flakiness** | High | Medium | Retry logic, test isolation |
| **Security scan false positives** | Medium | Low | Manual review gate for production |
| **Queue backlog** | Low | Medium | Multiple workers, load balancing |

---

## Related Files

- `/memory/workflow-system-analysis.md` - Full analysis of Durable Workflow vs alternatives
- `/workspace/lunaos/UI_STANDARDS.md` - LunaOS design patterns
- `/workspace/lunaos/DESIGN_SYSTEM.md` - LunaOS design system

---

## Next Steps

1. **Install Durable Workflow package**
   ```bash
   cd lunaos && composer require laravel-workflow/laravel-workflow
   php artisan vendor:publish --tag=workflow-migrations
   php artisan migrate
   ```

2. **Create workflow definition**
   - `app/Workflows/DevelopmentPipelineWorkflow.php`
   - Define 8-10 steps with branching logic

3. **Build agent worker base class**
   - `app/Agents/AgentWorker.php`
   - Dave, Sam, Chen implementations

4. **Extend LunaOS Task Manager**
   - Add workflow_id column to tasks
   - Add Kanban view grouped by workflow step

5. **Test with mock task**
   - Create task via LunaOS UI
   - Verify Dave picks it up
   - Verify workflow progresses

---

**Estimated Total Effort:** 4 weeks (16-20 days)  
**Team:** Luna (PM), Dave (dev worker), Sam (QA worker), Chen (DevOps worker), Kyle (oversight)  
**Status:** Ready for Phase 1 kickoff 🚀
