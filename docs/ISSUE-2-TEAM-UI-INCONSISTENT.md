# Issue #2: Team Pages UI Inconsistent with Site Design

**Priority:** P2 (HIGH)  
**Component:** Team Module (Frontend)  
**Assignee:** Maya (Frontend)  
**Reported:** Saturday, March 7, 2026  
**Status:** 📋 READY TO CREATE

---

## Problem

The Team module pages (All Team, Workers, Personas, Board) have inconsistent look and feel compared to the rest of the LunaOS site following Phase 2A navigation cleanup.

**Affected URLs:**
- `/team` (All Team)
- `/team?type=worker` (Workers)
- `/team?type=persona` (Personas)
- `/team?type=board` (Board)

---

## Expected Behavior

Team pages should match the design system used across Tasks, Projects, and other modules:
- Consistent card styles
- Consistent table layouts
- Consistent badge/pill designs
- Consistent spacing and typography
- Consistent color schemes
- Consistent hover states
- Consistent mobile responsive behavior

---

## Acceptance Criteria

- [ ] Team list view matches Task list view styling
- [ ] Team cards match Project card design
- [ ] Badge/pill styles consistent (status, type, role)
- [ ] Table layouts match other modules
- [ ] Spacing/margins consistent with site design
- [ ] Typography (fonts, sizes, weights) consistent
- [ ] Color scheme matches LunaOS palette
- [ ] Hover states consistent across elements
- [ ] Mobile responsive matches other modules
- [ ] No console errors
- [ ] Browser tested on Chrome, Firefox, Safari

---

## Technical Notes

- Reference files for consistent styling:
  - `resources/views/tasks/index.blade.php` (task list view)
  - `resources/views/projects/index.blade.php` (project list)
  - `resources/views/components/layouts/app.blade.php` (main layout)
- Update: `resources/views/team/index.blade.php`
- Check Tailwind config for design tokens
- Ensure Livewire components match styling

---

**Related:** Phase 2A Navigation Cleanup (PR #10 merged)
