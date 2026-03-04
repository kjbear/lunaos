# Phase 2B Sprint Plan — Collapsible Navigation

**Status:** ✅ READY FOR KICKOFF  
**Refinement Complete:** March 4, 2026 — 11:07 AM EST  
**Architect Review:** Jordan ✅  
**Timeline:** 2-3 days (12 story points)

---

## 📋 Sprint Stories

### 2B.1: Collapsible Sidebar Component (Maya, 3 pts)
- Implement toggle button (left edge when collapsed, right edge when expanded)
- Sidebar transitions between expanded (256px) and collapsed (64px) states
- Nav items collapse to icon-only mode

### 2B.2: State Persistence (Maya, 2 pts)
- localStorage read/write on toggle
- Key: `lunaos.sidebar.collapsed` (boolean)
- Restore state on page load

### 2B.3: Responsive + Mobile (Maya, 2 pts)
- Desktop (≥768px): Collapsible as designed
- Mobile (<768px): Always collapsed, slide-in overlay when toggled
- Backdrop for mobile overlay

### 2B.4: Accessibility (Maya, 2 pts)
- Keyboard navigation (Tab to toggle, Enter/Space to activate)
- ARIA labels (`aria-expanded`, `aria-label` on toggle)
- Screen reader announces collapse state

### 2B.5: QA Testing (Sam, 3 pts)
- Unit test: localStorage read/write
- Dusk tests: Toggle, persistence, mobile, accessibility
- All tests passing before merge

---

## 🎯 UX Decisions (Refinement Complete)

### 1. Collapsed Sidebar Width
**Decision:** `w-16` (64px) — Icon-only mode

**Rationale:**
- Enough space for nav icons + subtle hover states
- Matches common patterns (Linear, Vercel, GitHub)
- Clear visual distinction from expanded state (256px)

**Implementation:**
```html
<!-- Container -->
<aside :class="collapsed ? 'w-16' : 'w-64'" class="transition-all duration-300 ease-in-out">
  <!-- Nav items -->
  <div x-show="!collapsed" class="flex-1">Label text</div>
</aside>
```

---

### 2. Main Content Margin Adjustment
**Decision:** `ml-16` when collapsed, `ml-64` when expanded

**Rationale:**
- Main content reflows smoothly with sidebar
- No empty space or overlap
- CSS transition on `<main>` element matches sidebar timing

**Implementation:**
```html
<!-- Main content area -->
<main :class="collapsed ? 'ml-16' : 'ml-64'" class="transition-all duration-300 ease-in-out">
  <!-- Page content -->
</main>
```

**Alternative Considered:** `ml-0` when collapsed (fullscreen content)
- **Rejected:** Causes jarring reflow, harder to re-expand
- **Chosen:** Maintain visual anchor with `ml-16`

---

### 3. Sidebar Header/Footer in Collapsed State
**Decision:** Logo icon only, hide text labels

**Header:**
- **Expanded:** Full logo + "LunaOS" text
- **Collapsed:** Logo icon only (crescent moon emoji 🌙 or SVG)

**Footer:**
- **Expanded:** User avatar + name + status
- **Collapsed:** User avatar only (no text)

**Implementation:**
```html
<!-- Header -->
<div class="flex items-center gap-3">
  <span>🌙</span> <!-- Always visible -->
  <span x-show="!collapsed" class="font-bold">LunaOS</span> <!-- Hidden when collapsed -->
</div>

<!-- Footer -->
<div class="flex items-center gap-3">
  <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full"> <!-- Always visible -->
  <div x-show="!collapsed"> <!-- Hidden when collapsed -->
    <div class="font-medium">{{ auth()->user()->name }}</div>
    <div class="text-xs text-gray-400">Online</div>
  </div>
</div>
```

---

### 4. Mobile Behavior (<768px)
**Decision:** Always collapsed, slide-in overlay menu when toggled

**Behavior:**
- **Default:** Sidebar fully hidden (off-canvas)
- **Toggle:** Slide-in from left with backdrop overlay
- **Close:** Click backdrop, click toggle, or press Escape
- **Main content:** No margin adjustment (sidebar overlays, doesn't push)

**Implementation:**
```html
<!-- Mobile sidebar (overlay mode) -->
<aside 
  class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300"
  :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
>
<!-- Backdrop -->
<div 
  x-show="mobileOpen" 
  @click="mobileOpen = false"
  class="fixed inset-0 bg-black bg-opacity-50 z-40"
>

<!-- Desktop sidebar (push mode) -->
<aside 
  class="hidden md:block fixed inset-y-0 left-0 z-30 w-64 transition-all duration-300"
  :class="collapsed ? 'w-16' : 'w-64'"
>
```

**Rationale:**
- Mobile screen real estate is precious
- Overlay pattern is standard (Gmail, Slack, GitHub mobile)
- No content reflow jank on small screens

---

## 📐 Technical Specifications

### Transition Timing
- **Duration:** `300ms`
- **Easing:** `ease-in-out`
- **CSS:** `transition-all duration-300 ease-in-out`

### localStorage Details
- **Key:** `lunaos.sidebar.collapsed`
- **Value:** `true` (collapsed) | `false` (expanded)
- **Default:** `false` (expanded on first load)

### Nav Item Tooltips
- **Method:** Native `title` attribute (simplest)
- **Example:**
```html
<a href="/team" title="Team" class="p-3">
  <span>👥</span> <!-- Icon always visible -->
  <span x-show="!collapsed">Team</span> <!-- Text hidden when collapsed -->
</a>
```

### ARIA Attributes
```html
<!-- Sidebar container -->
<aside 
  aria-expanded="collapsed ? 'false' : 'true'"
  aria-label="Main navigation"
>

<!-- Toggle button -->
<button 
  @click="collapsed = !collapsed"
  aria-label="Toggle navigation menu"
  :aria-expanded="collapsed ? 'false' : 'true'"
>
```

---

## 🧪 QA Acceptance Criteria (Sam)

### Functional Tests
- [ ] Toggle button changes sidebar width (256px ↔ 64px)
- [ ] localStorage read/write works correctly
- [ ] State persists after page reload
- [ ] Main content margin adjusts smoothly (ml-64 ↔ ml-16)
- [ ] Nav item labels hide/show with transition
- [ ] Tooltips appear on hover when collapsed

### Mobile Tests
- [ ] Sidebar hidden by default on mobile
- [ ] Toggle opens slide-in overlay
- [ ] Backdrop click closes overlay
- [ ] Escape key closes overlay
- [ ] No content reflow (overlay mode)

### Accessibility Tests
- [ ] Tab navigates to toggle button
- [ ] Enter/Space activates toggle
- [ ] ARIA labels present and correct
- [ ] Screen reader announces collapse state
- [ ] Keyboard navigation works in both states

### Visual Tests
- [ ] Transition is smooth (300ms, ease-in-out)
- [ ] No layout shift or jank
- [ ] Header/footer collapse correctly (icon-only)
- [ ] Nav icons centered in collapsed mode
- [ ] Hover states work in both modes

---

## 📂 Files to Modify

**Primary:**
- `resources/views/layouts/app.blade.php` — Sidebar markup + Alpine state
- `resources/css/app.css` or `tailwind.config.js` — Transition utilities

**Secondary:**
- `app/Http/Middleware/ShareSidebarState.php` — Optional: server-side state (if needed)
- `tests/Browser/Navigation/CollapsibleSidebarTest.php` — Dusk tests

---

## 🚫 Out of Scope (Phase 2B)

- ❌ Backend API changes (pure frontend)
- ❌ User preferences endpoint (localStorage only for now)
- ❌ Multi-user state sync (per-browser state)
- ❌ Animation beyond CSS transitions (no JS animations)
- ❌ Tooltip component (use native `title` attribute)

---

## 🎯 Definition of Done

- [ ] All 5 stories complete
- [ ] Unit tests passing (Sam)
- [ ] Dusk tests passing (Sam)
- [ ] No console errors
- [ ] Responsive verified (desktop + mobile)
- [ ] Accessibility verified (keyboard + screen reader)
- [ ] Code reviewed (Luna + Kyle)
- [ ] Documentation updated

---

## 📞 Handoff Notes (For Maya + Sam)

**Maya:** Start with `app.blade.php` sidebar markup. Add Alpine state (`x-data="{ collapsed: false }"`), then implement toggle logic, transitions, and localStorage persistence. Test responsive behavior early.

**Sam:** Review this doc, then write your test plan. Start with Dusk test for basic toggle functionality, then add persistence, mobile, and accessibility tests. Run tests as Maya implements.

**Questions?** Ping me (Luna) for clarifications. Jordan available for architectural questions.

---

**Prepared by:** Luna (PM)  
**Reviewed by:** Jordan (Architect/PM)  
**Date:** March 4, 2026 — 11:07 AM EST  
**Ready for:** Maya + Sam kickoff
