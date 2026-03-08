# Issue #31: No Content in Overview or AI Config Tabs

**Priority:** P1 (BLOCKER)  
**Assignee:** Maya (Frontend)  
**Component:** Team Module - Detail Page  
**Related:** Issue #30, PR #32

---

## Problem

Team member detail pages (`/team/{uuid}`) show **empty content** in both tabs after AI Config merge.

---

## Expected Behavior

**Overview Tab:** Member info, bio, skills, status, tasks  
**AI Config Tab:** Model settings, prompts, capabilities, operations

---

## Current Behavior

Both tabs render but show **blank/empty content**.

---

## Possible Causes

1. Tab switching logic broken (Alpine.js)
2. Content conditionals wrong (`x-show`)
3. Livewire component not rendering
4. `$member` variable not passed to view
5. Show page template issue

---

## Files to Check

- `resources/views/team/show.blade.php` (main detail page)
- `resources/views/livewire/team/member-ai-config.blade.php`
- `app/Livewire/Team/MemberAiConfig.php`
- `app/Http/Controllers/TeamController.php` (show method)

---

## Debugging Steps

1. Check browser console for JS errors
2. Check Livewire network requests
3. Verify `$member` is passed and not null
4. Check Alpine.js initialization
5. Inspect tab switching logic
6. Check CSS (hidden content)

---

## Acceptance Criteria

- [ ] Detail page loads with content
- [ ] Overview tab shows member info
- [ ] AI Config tab shows form
- [ ] Tab switching works
- [ ] No console/Livewire errors

---

**Repro:** Navigate to `/team/{any-uuid}` → Both tabs empty
