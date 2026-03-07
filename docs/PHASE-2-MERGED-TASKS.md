# Phase 2: Merged Task List (March 6, 2026)

**Status:** Phase 1 Complete ✅ | Phase 2A In Progress 🚧  
**Last Updated:** March 6, 2026 — 10:10 AM EST  
**Priority Focus:** Project Module Foundation (P0/P1 from March 5 review)

---

## Context

Two parallel work streams merged:

### Stream 1: Phase 2A Module Consolidation (Started March 1)
- **Goal:** Consolidate overlapping modules (Tasks + Board + Kanban → Unified Tasks, HR + Agents → Team)
- **Status:** Partially complete — backend done, testing in progress

### Stream 2: Project Module Re-Architecture (March 5 Review)
- **Goal:** Fix critical database schema issues before adding features
- **Status:** Migrations written (March 5), awaiting implementation verification
- **Trigger:** Three-way review (Dave + Jordan + Leo) identified 6 critical issues

---

## Unified Priority List

### 🔴 P0: BLOCKING — Project Foundation Fixes
**Owner:** Dave (Backend) + Leo (Architect Review)  
**Timeline:** 1-2 days  
**Status:** Migrations written, needs verification

- [ ] **P0.1: Verify tasks.project_id FK exists**
  - Migration: `2026_03_05_204121_add_project_linkage_to_tables.php`
  - Verify: `tasks` table has `project_id` FK → `projects(id)`
  - Test: Create task with project_id, verify cascade delete

- [ ] **P0.2: Verify project_assignments uses agents table (not personas)**
  - Migration: `2026_03_05_204130_fix_project_assignments_to_use_agents.php`
  - Verify: `project_assignments.agent_id` FK → `agents(id)` (not `personas`)
  - Test: Assign agent to project, verify FK constraint

- [ ] **P0.3: Verify projects.repo_uuid FK exists**
  - Migration: `2026_03_05_204130_add_repository_link_to_projects.php`
  - Verify: `projects.repo_uuid` FK → `repositories(uuid)`
  - Test: Create project with repo, verify relationship

- [ ] **P0.4: Verify missing project fields added**
  - Migration: `2026_03_05_204130_add_missing_project_fields.php`
  - Fields: `project_manager_id`, `architecture_type`, `technologies` (JSON), `percent_complete`, `github_*` columns
  - Test: Update project with new fields, verify data persists

- [ ] **P0.5: Requirements duplication resolved**
  - Verify: `requirements` table OR `project_artifacts` (type='requirement'), not both
  - Decision needed: Which is source of truth? (Recommendation: Keep `requirements` table, use artifacts for other types only)

### 🟠 P1: HIGH — Data Integrity + API
**Owner:** Dave + Alex  
**Timeline:** 2-3 days  
**Status:** Not started

- [ ] **P1.1: Create project_issues table**
  - Migration: `2026_03_05_204131_create_project_issues_table.php`
  - Fields: `project_id`, `issue_id` (GitHub), `number`, `title`, `state`, `created_at`, `synced_at`
  - Test: CRUD operations on project issues

- [ ] **P1.2: API endpoints for projects**
  - `PUT /api/projects/{id}` — Update project (all new fields)
  - `DELETE /api/projects/{id}` — Soft delete project
  - `POST /api/projects/{id}/assign-agent` — Assign agent to project
  - Test: API integration tests with authentication

- [ ] **P1.3: Model relationships & methods**
  - `Project` model: `tasks()`, `agents()`, `repository()`, `issues()`
  - `Task` model: `project()` relationship
  - `Agent` model: `projects()` relationship
  - Test: Eloquent relationship queries

### 🟡 P2: MEDIUM — Task Management Consolidation (Phase 2A.1)
**Owner:** Maya + Dave  
**Timeline:** 2-3 days  
**Status:** Backend done, frontend mostly done, testing in progress

- [ ] **P2.1: Verify unified task schema**
  - Migration: `2026_03_02_080000_fix_tasks_schema.php` (already run March 2)
  - Migration: `2026_03_01_232112_add_view_mode_to_tasks_table.php` (view_mode field)
  - Test: Tasks work in all 3 views (list/board/executive)

- [ ] **P2.2: Complete TaskBoardUnified testing**
  - Component: `app/Livewire/TaskBoardUnified.php` (exists)
  - Browser tests: `tests/Browser/Tasks/` (started)
  - Test: Drag-and-drop, status changes, assignment

- [ ] **P2.3: Task detail page polish**
  - Component: `app/Livewire/TaskDetail.php` (exists)
  - Test: All task fields display correctly, edit links work

- [ ] **P2.4: TaskEdit page complete**
  - Component: `app/Livewire/TaskEdit.php` (exists)
  - Test: Form validation, update, redirect

### 🟢 P3: Team Module Consolidation (Phase 2A.2)
**Owner:** Dave + Maya  
**Timeline:** 3-4 days  
**Status:** Backend done, migration written, frontend started

- [ ] **P3.1: Verify team consolidation migration**
  - Migration: `2026_03_03_100000_consolidate_hr_and_agents_to_team.php` (9KB, written March 3)
  - Verify: `team_members` table exists with unified schema
  - Verify: Old `agents` and `personas` tables migrated
  - Test: Query team members by type (worker/persona/board)

- [ ] **P3.2: Team module UI complete**
  - Component: `app/Livewire/HR/` (exists, pre-consolidation)
  - Component: `app/Livewire/Agents/` (exists, pre-consolidation)
  - Need: Unified `TeamIndex.php`, `TeamDetails.php`
  - Test: All tabs work (Workers, Personas, Board)

- [ ] **P3.3: Team browser tests**
  - Directory: `tests/Browser/Team/` (exists, 7 subdirs)
  - Test: Create, edit, delete team members
  - Test: Tab switching, filtering

### 🔵 P4: Navigation + Route Cleanup (Phase 2A.4)
**Owner:** Maya  
**Timeline:** 1-2 days  
**Status:** Not started

- [ ] **P4.1: Update sidebar navigation**
  - Remove: `/hr`, `/agents` (replaced by `/team`)
  - Verify: `/tasks` (unified), `/team` (new)
  - Test: All nav links work

- [ ] **P4.2: Deprecate old routes**
  - Add redirects: `/hr` → `/team?type=persona`, `/agents` → `/team?type=worker`
  - Test: Old URLs redirect correctly

- [ ] **P4.3: Update breadcrumbs**
  - Verify: All pages have correct breadcrumbs for new structure
  - Test: Breadcrumbs render correctly

---

## Cross-Cutting Concerns

### Testing Requirements (All Tasks)
- [ ] Unit tests (PHPUnit) — 80%+ coverage
- [ ] Feature tests (Livewire components)
- [ ] Browser tests (Dusk) for critical flows
- [ ] API tests (Postman collection)

### Documentation
- [ ] Update `lunaos/README.md` with new routes
- [ ] Create `docs/TASK-CONSOLIDATION.md`
- [ ] Create `docs/TEAM-CONSOLIDATION.md`
- [ ] Create `docs/PROJECT-SCHEMA-CHANGES.md`
- [ ] Update OpenAPI spec (Alex)

### Data Migration Safety
- [ ] Backup current database before Phase 2B
- [ ] Test migrations on staging copy
- [ ] Document rollback procedures
- [ ] Verify data integrity post-migration

---

## Agent Assignments

| Agent | Role | Current Tasks |
|-------|------|---------------|
| **Dave** | Backend Dev | P0.1-P0.4, P1.1-P1.3, P3.1 |
| **Maya** | Frontend Dev | P2.2-P2.4, P3.2, P4.1-P4.3 |
| **Sam** | QA | All test suites |
| **Alex** | API Docs | P1.2, OpenAPI updates |
| **Leo** | Architect | Review all P0/P1 changes |
| **Jordan** | PM | Track progress, unblock |
| **Chen** | DevOps | Migration runbook, backups |

---

## Timeline Summary

| Priority | Tasks | Est. Time | Target Date |
|----------|-------|-----------|-------------|
| P0 (BLOCKING) | 5 tasks | 2-3 days | March 8-9 |
| P1 (HIGH) | 3 tasks | 2-3 days | March 10-11 |
| P2 (MEDIUM) | 4 tasks | 2-3 days | March 12-13 |
| P3 (Team) | 3 tasks | 3-4 days | March 14-17 |
| P4 (Nav) | 3 tasks | 1-2 days | March 18-19 |

**Total Estimated Effort:** 11-15 days  
**Phase 2 Complete Target:** March 19, 2026

---

## Immediate Next Steps (Today)

1. **Verify P0 migrations** — Run database inspection to confirm March 5 migrations applied correctly
2. **Test task.project_id relationship** — Create a test task with project assignment
3. **Document any gaps** — If migrations incomplete, write missing ones
4. **Kyle decision** — Requirements duplication: keep `requirements` table or migrate to artifacts-only?

---

## Key Decisions Needed from Kyle

1. **Requirements Storage:** Source of truth for requirements?
   - Option A: Keep `requirements` table (better normalization, easier queries)
   - Option B: Use `project_artifacts` only (simpler, fewer tables)
   - **Recommendation:** Option A (Dave/Jordan/Leo consensus)

2. **Progress Calculation:** Auto or manual?
   - Option A: Auto-calculated from task completion % (recommended)
   - Option B: Manual override allowed (PM adjusts)
   - **Recommendation:** Both (auto-calc default, manual override with audit log)

3. **Green-light P0 sprint?** — Authorize 2-3 days focused on foundation fixes before new features
   - **Recommendation:** YES (all reviewers agree)

---

**Created:** March 6, 2026 — 10:10 AM EST  
**By:** Luna (PM)  
**Next Review:** March 8, 2026 (P0 verification complete)
