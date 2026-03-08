# Issue #1: Task List View Returns 404

**Priority:** P1 (CRITICAL)  
**Component:** Tasks Module  
**Assignee:** Dave (Backend)  
**Reported:** Saturday, March 7, 2026  
**Status:** 📋 READY TO CREATE

---

## Problem

Task List View returns 404 error when accessing:
```
http://lunaos.test/tasks/list
```

---

## Expected Behavior

List view should display all tasks in a table/list format (similar to how board view shows Kanban and executive view shows summary).

---

## Steps to Reproduce

1. Navigate to `http://lunaos.test/tasks/list`
2. Observe 404 error page

---

## Acceptance Criteria

- [ ] Route `/tasks/list` defined and working
- [ ] List view displays all tasks in table format
- [ ] List view includes filters (status, priority, agent, project)
- [ ] List view matches design from Phase 2A
- [ ] No console errors
- [ ] Mobile responsive

---

## Technical Notes

- Check `routes/web.php` for missing route definition
- Verify `TaskController@index` handles 'list' view parameter
- Cross-reference with working routes: `/tasks`, `/tasks/board`, `/tasks/executive`
- Likely missing route or controller method

---

**Related:** Phase 2A Task Consolidation (PR #9 merged)
