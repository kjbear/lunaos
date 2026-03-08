# Issue #3: Left Sidebar Navigation Does Not Scroll Vertically

**Priority:** P2 (HIGH)  
**Component:** Navigation (Frontend)  
**Assignee:** Maya (Frontend)  
**Reported:** Saturday, March 7, 2026  
**Status:** 📋 READY TO CREATE

---

## Problem

The left sidebar navigation bar does not scroll vertically when the viewport height is smaller than the navigation content.

**Affected:** All pages (global navigation issue)

---

## Expected Behavior

Sidebar navigation should:
- Scroll vertically when content overflows viewport
- Hide overflow with scrollbar
- Maintain fixed position while main content scrolls
- Be accessible via mouse wheel, trackpad, and keyboard

---

## Steps to Reproduce

1. Open LunaOS in browser (e.g., 13" laptop or smaller viewport)
2. Resize window to make it shorter vertically
3. Attempt to scroll in the left sidebar
4. Observe: sidebar content is cut off, no scrollbar appears

---

## Acceptance Criteria

- [ ] Sidebar has `overflow-y: auto` or `overflow-y: scroll`
- [ ] Sidebar scrolls independently of main content
- [ ] Scrollbar appears when content overflows
- [ ] Mouse wheel scrolling works
- [ ] Trackpad scrolling works
- [ ] Keyboard navigation works (arrow keys, page up/down)
- [ ] Scroll position persists during page navigation (optional)
- [ ] No horizontal scroll introduced
- [ ] Works on all viewport heights
- [ ] Mobile responsive maintained

---

## Technical Notes

- Likely CSS issue in sidebar component
- Check: `resources/views/components/sidebar.blade.php` or inline in `app.blade.php`
- Add CSS:
  ```css
  .sidebar {
      max-height: 100vh;
      overflow-y: auto;
      overflow-x: hidden;
  }
  ```
- Ensure sidebar is positioned correctly (fixed vs relative)
- Test on various viewport heights (13", 15", 27" monitors)

---

**Related:** Phase 2A Navigation Cleanup (PR #10 merged)
