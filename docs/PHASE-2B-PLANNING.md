# Phase 2B: Executive Board Enhancements

**Owner:** Jordan (PM) + Dave (Backend) + Maya (Frontend)  
**Timeline:** 4-5 days  
**Priority:** 🟠 MEDIUM (after Phase 2A complete)  
**Status:** 📋 PLANNING

---

## Executive Summary

Phase 2B enhances the **Executive Board** system (6 AI C-Suite members) to:
1. Enable **automated project creation** from board decisions
2. Add **mission control dashboard** for AI PMs
3. Implement **board vote tracking** + decision history
4. Improve **executive summary view** with better filtering
5. Add **GitHub repo auto-creation** workflow

---

## Current State (Phase 1 Complete)

### ✅ What's Working

**Executive Board Module:**
- 6 AI C-Suite members (Steven CEO, Gwynne COO, Werner CTO, Warren CFO, Bozoma CMO, Fidji CPO)
- Board sessions with debate + discussions
- Vote tally system (unanimous/majority/minority)
- Integration with task system
- All running on `ollama-local/glm-5`

**Project Module:**
- Full CRUD API (P1 complete)
- GitHub Issue Sync Service (Dave's PR #4)
- 138 tests passing
- Soft delete cascade working

**Task Module:**
- Unified board/list/executive views
- 21 tasks in DB, 16 linked to projects (76% adoption)

### ⚠️ What's Missing

**Executive Board gaps:**
- ❌ No automated project creation from board decisions
- ❌ No mission control dashboard for AI PMs
- ❌ Vote tracking not visible in UI
- ❌ Decision history not tracked
- ❌ Board can't trigger GitHub repo creation
- ❌ No metrics on board effectiveness

**Project management gaps:**
- ❌ Manual project setup (tedious for AI PMs)
- ❌ No template system for common project types
- ❌ GitHub integration incomplete (issues sync only, no repo creation)
- ❌ No automated sprint planning

---

## Phase 2B Goals

### **Goal 1: Automated Project Creation** (Jordan + Dave)

**Problem:** Board debates and makes decisions, but Kyle has to manually create projects.

**Solution:** Board can auto-create projects with AI PM assignment.

**Workflow:**
```
1. Board debates topic (e.g., "Build SPA status page aggregator")
2. Board votes + reaches decision
3. Steven (CEO) proposes project creation
4. Gwynne (COO) assigns AI PM (Jordan/Alex)
5. System auto-creates:
   - Project record
   - Initial requirements
   - Sprint 1 tasks
   - GitHub repo (optional)
   - Assigns AI PM
6. Notifications sent to assigned team
```

**Technical Requirements:**
- BoardSession → Project creation service
- Project template system (SaaS, microservice, integration, etc.)
- AI PM auto-assignment logic
- GitHub repo creation webhook (Chen's work)
- Audit log entry for board decision

**Files to Create:**
- `app/Services/BoardProjectCreator.php`
- `app/Services/ProjectTemplateService.php`
- `app/Console/Commands/CreateProjectFromBoardDecision.php`
- `database/migrations/...add_board_session_id_to_projects.php`

---

### **Goal 2: Mission Control Dashboard** (Maya + Jordan)

**Problem:** AI PMs (Jordan, Alex) need visibility into their projects.

**Solution:** Dedicated PM mission control view.

**Features:**
```
- Project portfolio view (all AI PM's projects)
- Sprint burndown charts
- Task completion velocity
- Risk indicators (overdue tasks, blocked items, budget concerns)
- Resource allocation (which workers assigned to what)
- Quick actions (create sprint, add task, reassign, escalate)
- AI PM chat (Jordan ↔ Kyle discussions)
```

**UI Components:**
- `MissionControlIndex.php` (main dashboard)
- `ProjectHealthWidget.php` (traffic light status)
- `SprintBurndownChart.php` (Chart.js integration)
- `ResourceAllocationTable.php`
- `RiskIndicatorsWidget.php`

**Metrics to Track:**
- Sprint velocity (tasks/week)
- On-time delivery %
- Budget burn rate
- Team workload distribution
- Risk score (weighted algorithm)

---

### **Goal 3: Board Vote Tracking** (Dave + Maya)

**Problem:** Board votes happen, but decisions aren't tracked over time.

**Solution:** Vote history + decision impact analysis.

**Features:**
```
- Vote history per board member
- Decision outcomes (implemented? successful?)
- Voting patterns (who votes yes/nost often)
- Unanimous vs contested decision tracking
- Decision → project outcome correlation
```

**Database Schema:**
```sql
CREATE TABLE board_decisions (
    id UUID PRIMARY KEY,
    board_session_id UUID REFERENCES board_sessions(id),
    topic STRING,
    decision_type ENUM('project_creation', 'task_assignment', 'policy_change', 'other'),
    vote_tally JSON, -- {yes: 4, no: 1, abstain: 1}
    outcome ENUM('unanimous', 'majority', 'contested'),
    implemented BOOLEAN DEFAULT FALSE,
    implementation_date TIMESTAMP NULL,
    success_score INT NULL, -- 1-10 post-mortem rating
    notes TEXT,
    created_at TIMESTAMP,
    INDEX (board_session_id),
    INDEX (decision_type)
);
```

**UI:**
- `BoardDecisionsHistory.php` (timeline view)
- `BoardMemberStats.php` (voting patterns)
- `DecisionImpactAnalysis.php` (correlation charts)

---

### **Goal 4: Executive Summary 2.0** (Maya)

**Problem:** Current executive view is basic. Needs better filtering and insights.

**Enhancements:**
```
- Filter by: project, AI PM, status, date range
- Sort by: health score, velocity, risk, budget
- Export: PDF reports, CSV data
- Drill-down: click project → full details
- AI insights: "3 projects at risk this week", "Jordan's team ahead of schedule"
- Timeline view: Gantt-style project roadmap
```

**UI Components:**
- `ExecutiveSummaryFilters.php` (advanced filters)
- `ProjectHealthScoreCard.php` (traffic light + trend)
- `AIPMDashboard.php` (per-PM view)
- `ExportReportModal.php` (PDF/CSV generation)
- `GanttChartWidget.php` (timeline view)

**AI Insights Engine:**
```php
// app/Services/ExecutiveInsightsService.php
public function generateWeeklyInsights(): array
{
    return [
        'at_risk_projects' => $this->getAtRiskProjects(),
        'ahead_of_schedule' => $this->getAheadOfScheduleProjects(),
        'overdue_tasks' => $this->getOverdueTasks(),
        'team_workload' => $this->getWorkloadDistribution(),
        'budget_alerts' => $this->getBudgetAlerts(),
    ];
}
```

---

### **Goal 5: GitHub Repo Auto-Creation** (Chen + Dave)

**Problem:** Projects need GitHub repos, but manual setup is tedious.

**Solution:** Automated GitHub repo creation with template.

**Workflow:**
```
1. Board votes to create project "SPA"
2. Service calls GitHub API:
   - Create repo: kjbear/spa
   - Add description + topics
   - Initialize with README.md
   - Add .gitignore (Laravel/Go)
   - Add LICENSE
   - Create initial branches (main, develop)
   - Set branch protection rules
   - Add CI/CD workflow
3. Store repo info in `repositories` table:
   - repo_url
   - github_app_installation_id
   - webhook_secret (encrypted)
   - creation_status: 'created'
4. Trigger webhook to LunaOS (confirmation)
5. Project linked to repo automatically
```

**Technical Requirements:**
- GitHub App (not PAT) for authentication
- Store `github_app_installation_id` in `repositories` table
- Webhook endpoint: `POST /api/github/webhooks`
- Template repo creation (starter files)
- Error handling (repo exists, rate limits, etc.)

**Files to Create:**
- `app/Services/GitHubRepositoryCreator.php`
- `app/Http/Controllers/GitHubWebhookController.php`
- `database/migrations/...add_github_fields_to_repositories.php`
- `config/github.php` (app config)

---

## Phase 2B Sprint Plan

### **Sprint 1: Foundation** (2 days)

**Jordan (PM):**
- Define project templates (SaaS, microservice, integration)
- Write user stories for Goal 1 (auto-create)
- Create acceptance criteria for all 5 goals

**Dave (Backend):**
- `BoardProjectCreator` service
- Project template system
- Migration: `board_session_id` FK to projects
- Migration: `board_decisions` table

**Maya (Frontend):**
- Mission Control wireframes
- Board vote tracking UI mockups
- Executive Summary 2.0 design

**Chen (DevOps):**
- GitHub App setup (OAuth credentials)
- Webhook endpoint scaffolding
- Test webhook handling

---

### **Sprint 2: Implementation** (2 days)

**Dave:**
- Goal 1: Automated project creation (complete)
- Goal 3: Vote tracking backend

**Maya:**
- Goal 2: Mission Control dashboard
- Goal 4: Executive Summary 2.0

**Chen:**
- Goal 5: GitHub repo auto-creation
- Integration testing (repo creation flow)

**Jordan:**
- Test automated project creation
- Validate mission control metrics
- Write PM user guide

---

### **Sprint 3: Polish + Testing** (1 day)

**Sam (QA):**
- Board auto-create tests
- Mission Control integration tests
- GitHub webhook tests
- Executive Summary filters tests

**Maya:**
- Mobile responsive for all new UI
- Accessibility fixes
- Performance optimization

**Dave:**
- API documentation
- Bug fixes from QA

**Jordan:**
- User acceptance testing
- Feedback documentation

---

## Acceptance Criteria

### ✅ Phase 2B Complete When:

- [ ] Board can auto-create projects (tested with 3+ scenarios)
- [ ] Mission Control dashboard shows all PM metrics
- [ ] Vote tracking visible (history + stats)
- [ ] Executive Summary 2.0 with filters + insights
- [ ] GitHub repo auto-creation working
- [ ] 40+ tests written + passing
- [ ] Documentation complete
- [ ] Kyle signs off on UX

---

## Success Metrics

**Quantitative:**
- Projects created by board: 100% automated (0 manual)
- Time to create project: <30 seconds (was 10-15 min manual)
- AI PM satisfaction: 8/10+ (survey Jordan/Alex)
- GitHub repo creation: 100% success rate

**Qualitative:**
- Kyle spends less time on project setup
- Board decisions → action faster
- AI PMs have better visibility
- Executive summaries more actionable

---

## Risks + Mitigation

**Risk:** GitHub API rate limits  
**Mitigation:** Use GitHub App (5000 req/hr), add caching

**Risk:** Auto-created projects lack nuance  
**Mitigation:** Human review step before GitHub creation

**Risk:** Mission Control overwhelms PMs  
**Mitigation:** Start minimal, iterate based on feedback

**Risk:** Board debates too long  
**Mitigation:** Add time limits, Kyle override

---

## Technical Debt to Address

From architect review:
- [ ] Add indexes to `requirements.project_id`
- [ ] Standardize soft deletes across modules
- [ ] Audit logging for critical changes
- [ ] Consolidate requirements storage (decide: requirements table vs artifacts)

---

## Phase 2B → Phase 3 Transition

**Phase 3 Goals:**
- Status Page Aggregator (SPA) kickoff
- Multi-tenant architecture for SPA
- Go collector service
- 6 vendor integrations

**Phase 2B delivers foundation for Phase 3:**
- Automated project creation → SPA project auto-setup
- Mission Control → SPA vendor management dashboard
- GitHub integration → SPA vendor status page repos

---

**Estimated Start:** Monday, March 9, 2026 (after Phase 2A complete)  
**Estimated Duration:** 5 days (Sprints 1-3)  
**Completion Target:** Friday, March 13, 2026

---

**Ready for Kyle's review and approval!** 🚀
