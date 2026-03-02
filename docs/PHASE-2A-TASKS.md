# Phase 2A: Module Consolidation

**Priority:** HIGH | **Timeline:** 2-3 sprints | **Owner:** Luna (PM)

---

## **Overview**

Consolidate overlapping modules identified in Maya's UI/UX audit:

1. **Task Management Consolidation** — Tasks + Board + Kanban → Unified `/tasks` with view modes
2. **Team Module Creation** — HR + Agents → Unified `/team` module

---

## **Task 2A.1: Unify Task Management**

### **Backend (Dave)**
- [ ] Create unified `Task` model with `view_mode` attribute (list/board/executive)
- [ ] Create routes: `/tasks`, `/tasks/{id}`, `/tasks/create`, `/tasks/edit`
- [ ] Create controller/service for task management
- [ ] Create API endpoints: API/tasks, API/tasks/{id}
- [ ] Database migration for any new fields

### **Frontend (Maya)**
- [ ] Create TaskList Livewire component (list view)
- [ ] Create TaskBoard Livewire component (Kanban view)
- [ ] Create TaskExecutive Livewire component (board/strategic view)
- [ ] Create TaskDetails page (`/tasks/{id}`)
- [ ] Create TaskEdit page (`/tasks/edit/{id}`)
- [ ] View mode switcher (tab or dropdown)

### **Tests (Sam)**
- [ ] Unit tests: Task model, service methods
- [ ] Feature tests: Livewire components load correctly
- [ ] **Browser tests (Dusk):**
  - Task list loads with data
  - Task details page loads
  - Create task form submits successfully
  - View mode switcher changes view
  - Edit task updates data

### **QA Validation**
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] All Dusk tests pass
- [ ] No console errors
- [ ] Responsive design verified

**Documentation:**
- Create `docs/TASK-MODULE-CONSOLIDATION.md`
- Update README with new routes

---

## **Task 2A.2: Consolidate HR + Agents → Team Module**

### **Backend (Dave)**
- [ ] Create unified `Team` model or rename `Agent` → `TeamMember`
- [ ] Create database migration to consolidate HR personas + Agents
- [ ] Create routes: `/team`, `/team/{id}`, `/team/create`, `/team/edit`
- [ ] Create API endpoints: API/team, API/team/{id}, API/team/{id}/members
- [ ] Add tabs logic: workers, personas, board-members

### **Frontend (Maya)**
- [ ] Create TeamIndex Livewire component (main overview)
- [ ] Create TeamDetails page (`/team/{id}`)
- [ ] Create team tabs: Workers, Personas, Board Members
- [ ] Create TeamEdit page (`/team/edit/{id}`)
- [ ] Add org chart visualization to Team module

### **Tests (Sam)**
- [ ] Unit tests: TeamMember model, consolidation logic
- [ ] Feature tests: Team module Livewire components
- [ ] **Browser tests (Dusk):**
  - Team index loads with all tabs
  - Team details page shows correct info
  - Create team member form works
  - Edit team member updates data
  - Tab switching works without page reload

### **QA Validation**
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] All Dusk tests pass
- [ ] Data migration successful (no data loss)
- [ ] Backward compatibility maintained

**Documentation:**
- Create `docs/TEAM-MODULE-CONSOLIDATION.md`
- Update HR + Agents docs with migration guide

---

## **Task 2A.3: Data Migration Scripts**

### **Backend (Dave) + DevOps (Chen)**
- [ ] Create migration: `consolidate_hr_and_agents`
- [ ] Create migration: `unify_task_views`
- [ ] Write data export scripts (backup)
- [ ] Write data import scripts (restore if needed)
- [ ] Test migration on staging database
- [ ] Document rollback procedure

### **Tests (Sam)**
- [ ] Unit tests for migration logic
- [ ] Integration tests: migrate → verify data → rollback
- [ ] **Browser tests:** All existing functionality still works post-migration

---

## **Task 2A.4: Update Navigation & Routes**

### **Frontend (Maya)**
- [ ] Update sidebar navigation (remove old links, add new unified links)
- [ ] Update breadcrumbs to match new structure
- [ ] Update internal links across all views

### **Backend (Dave)**
- [ ] Deprecate old routes (with redirects)
- [ ] Update route documentation
- [ ] Update OpenAPI/Swagger specs

### **Tests (Sam)**
- [ ] Feature tests: Old routes redirect correctly
- [ ] Browser tests: Navigation links work

---

## **Testing Requirements**

**Every PR for Phase 2A must include:**
1. ✅ Unit tests (PHPUnit) — 80%+ coverage for new code
2. ✅ Feature tests (Livewire) — Component rendering and interactions
3. ✅ Browser tests (Dusk) — At least 1 per critical user flow
4. ✅ Documentation updated
5. ✅ No breaking changes OR documented migration path

---

## **Definition of Done**

- [ ] All tasks completed
- [ ] All tests passing (unit + feature + browser)
- [ ] Documentation complete
- [ ] Code reviewed (Luna + Kyle approval)
- [ ] Deployed to staging
- [ ] Smoke tests passed
- [ ] Deployed to production

---

## **Dependencies**
- Laravel Dusk ✅ (installed)
- PHPUnit ✅ (installed)
- Livewire ✅ (installed)

---

**Kickoff Date:** March 1, 2026  
**Target Completion:** March 15, 2026  
**Next Phase:** 2B (Collapsible Navigation)
