# Phase 2B: Collapsible Navigation

**Priority:** MEDIUM | **Timeline:** 1 sprint | **Owner:** Luna (PM)

---

## **Overview**

Current flat navigation (13 items) overwhelms users. Restructure into **5-6 collapsible categories** for better information architecture and scalability.

**Based on:** Maya's UI/UX Audit Recommendations (March 1, 2026)

---

## **Current State**

**Navigation Items (13 flat):**
1. Tasks → Mission Control
2. Board (Executive)
3. Kanban
4. HR (Personas)
5. Agents
6. Org Chart
7. Projects
8. Workspace
9. Docs
10. Calendar
11. Standup
12. Activity Feed
13. Tests

**Problems:**
- Too many top-level items
- No logical grouping
- Hard to find features as app scales
- Visual clutter in sidebar

---

## **Proposed Navigation Structure**

### **1. 📋 WORK**
- Tasks (Mission Control)
- Board (Executive Decisions)
- ~~Kanban~~ → *Consolidate into Tasks (2A)*

### **2. 👥 TEAM**
- Org Chart
- ~~HR~~ → *Merge into Team (2A)*
- ~~Agents~~ → *Merge into Team (2A)*

### **3. 📊 PROJECTS**
- Projects

### **4. 🏢 WORKSPACE**
- Workspace (File Browser)
- Docs

### **5. 📅 CALENDAR & EVENTS**
- Calendar
- Standup

### **6. 📈 INSIGHTS**
- Activity Feed
- ~~Reports~~ → *Future enhancement*

### **7. 🧪 DEVELOPMENT**
- Tests

---

## **Technical Requirements**

### **Frontend (Maya)**
- [ ] **Collapsible Sidebar Component**
  - Alpine.js for state management
  - Accordion-style collapse/expand
  - Persist state in localStorage
  - Smooth animations (slide/fade)
  - Icons for each category

- [ ] **Navigation Data Structure**
  ```php
  $navigationGroups = [
      [
          'name' => 'Work',
          'icon' => '📋',
          'items' => [
              ['name' => 'Tasks', 'route' => 'tasks', 'icon' => '✓'],
              ['name' => 'Board', 'route' => 'board', 'icon' => '◆'],
          ]
      ],
      // ... more groups
  ];
  ```

- [ ] **Update Layout Component**
  - Replace flat nav in `resources/views/components/layouts/app.blade.php`
  - Add collapsible section toggles
  - Maintain active state highlighting
  - Keyboard shortcuts (optional stretch)

- [ ] **Mobile Responsiveness**
  - Hamburger menu for mobile
  - Touch-friendly expansion
  - Hide/show on smaller screens

### **Backend (Dave)**
- [ ] **Navigation Helper Class**
  - `App/Support/Navigation.php`
  - Method: `getNavigationGroups()`
  - Permissions-aware (show/hide based on user role)
  - Cache frequently accessed navigation data

- [ ] **Route Cleanup**
  - Deprecate old Kanban route (redirect to Tasks)
  - Deprecate old HR/Agents routes (redirect to Team)
  - Update route documentation

### **Tests (Sam)**
- [ ] **Unit Tests**
  - Navigation helper class methods
  - Route redirect logic

- [ ] **Feature Tests**
  - Livewire component renders correctly
  - Collapsible sections toggle state
  - State persistence in localStorage

- [ ] **Browser Tests (Dusk)**
  - Navigation loads with all groups
  - Clicking category expands/collapses
  - Clicking nav item navigates correctly
  - Active item is highlighted
  - Mobile menu behavior
  - State persists after page reload

---

## **Design Specifications**

### **Visual Style**
- **Collapsed State:**
  - Only icons visible
  - Tooltip on hover
  - Compact sidebar width: 80px

- **Expanded State:**
  - Full labels + icons
  - Sidebar width: 260px
  - Smooth transition (200ms)

- **Active State:**
  - Accent purple gradient background
  - Left border highlight (3px)
  - Text color: white

### **Animation**
- **Expand/Collapse:** 200ms ease-in-out
- **Slide:** Transform-based (performance)
- **Opacity:** Fade nested items on collapse

### **Icons**
- Use consistent emoji or SVG icons
- Match category theme:
  - 📋 Work
  - 👥 Team
  - 📊 Projects
  - 🏢 Workspace
  - 📅 Calendar
  - 📈 Insights
  - 🧪 Development

---

## **Implementation Plan**

### **Step 1: Architecture Design (Luna)**
- Define navigation data structure
- Plan component hierarchy
- Document API contract

### **Step 2: Backend Setup (Dave)**
- Create Navigation helper class
- Define route groups
- Add caching layer

### **Step 3: Component Build (Maya)**
- Create collapsible sidebar Livewire component
- Implement Alpine.js state management
- Add animations and transitions
- Test on different screen sizes

### **Step 4: Integration (Maya + Dave)**
- Replace old navigation in layout
- Update all internal links
- Test all routes work

### **Step 5: Testing (Sam)**
- Write unit + feature tests
- Write Dusk browser tests
- Test edge cases (mobile, permissions)

### **Step 6: QA + Documentation (Luna)**
- Visual review
- Accessibility audit
- Update docs
- Deploy to staging

---

## **Testing Requirements**

**Dusk Browser Tests:**
```php
public function test_navigation_groups_collapse_and_expand()
public function test_navigation_state_persists_after_reload()
public function test_active_navigation_item_highlighted()
public function test_mobile_navigation_menu()
public function test_keyboard_navigation()
```

**Feature Tests:**
```php
public function test_navigation_component_renders()
public function test_navigation_groups_are_correct()
public function test_user_permissions_filter_navigation()
```

**Unit Tests:**
```php
public function test_navigation_helper_returns_groups()
public function test_navigation_helper_filters_by_permission()
```

---

## **Definition of Done**

- [ ] All navigation groups implemented
- [ ] Collapsible functionality working
- [ ] State persistence working
- [ ] Mobile responsive
- [ ] All tests passing (unit + feature + Dusk)
- [ ] Accessibility compliant (aria-labels, keyboard nav)
- [ ] Performance optimized (animations smooth)
- [ ] Documentation complete
- [ ] Deployed to production

---

## **Dependencies**
- ✅ Phase 2A complete (Task + Team consolidation)
- ✅ Alpine.js (available via CDN or npm)
- ✅ Livewire (already installed)

---

## **Stretch Goals** (If Time Permits)

1. **Keyboard Shortcuts**
   - Cmd+K → Global search
   - Cmd+T → Tasks
   - Cmd+N → New item
   - Escape → Close modals/collapse nav

2. **Favorites/Pinning**
   - Allow users to pin frequently-used modules
   - Show favorites at top

3. **Recent Items**
   - Track last 5-10 visited modules
   - Quick access in nav

---

**Target Start:** After 2A complete (March 10-12)  
**Target Complete:** March 17, 2026  
**Next Phase:** 2C (Missing Details Pages)
