# MaryUI/daisyUI Migration Plan for LunaOS

**Audit Date:** March 4, 2026  
**Current Status:** daisyUI v5.5.19 installed + MaryUI v2.8.0 installed  
**Custom Theme:** ✅ 'lunaos' theme active with purple/violet colors

---

## 📊 Current State Analysis

### Total Blade Files: **93 files**

**By Category:**
- **Layout files:** 3 (app.blade, board.blade, guest.blade)
- **Component files:** 16 (card, modal, buttons, inputs, etc.)
- **Livewire components:** 47 (task-*, team-*, projects-*, etc.)
- **Page files:** 27 (tasks.blade, team.blade, board.blade, etc.)

### Key Findings

#### 1. **Cards/Containers** ✅ Already Compatible
- **Current:** Custom `<x-card>` component with Tailwind classes
- **daisyUI equivalent:** `card` class
- **Migration effort:** LOW - Just replace CSS classes

**Example:**
```blade
<!-- Current -->
<x-card class="bg-[#1a1a2e] border-[#2a2a40] rounded-lg p-4 card-glow">

<!-- MaryUI/daisyUI -->
<div class="card bg-base-200 border border-base-300 shadow-xl">
```

#### 2. **Buttons** ✅ Already Using daisyUI
- **Current:** Using `btn`, `btn-primary`, `btn-ghost` (from recent integration)
- **Effort:** ZERO - Already migrated
- **Status:** ✅ Complete

#### 3. **Tables** ⚠️ HIGH PRIORITY
- **Current:** Custom HTML tables with Tailwind styling
- **Files with tables:** ~15 files
  - `livewire/task-list.blade.php` (main task table)
  - `livewire/team/team-index.blade.php` (team members)
  - `livewire/projects/projects-index.blade.php` (project list)
  - Multiple agent/HR pages
- **MaryUI equivalent:** `<x-table>` with built-in sorting, pagination, search
- **Migration effort:** MEDIUM-HIGH (8-12 hours total)

**Benefit:** MaryUI's `<x-table>` provides:
- Built-in sorting (clickable headers)
- Search/filter
- Pagination (auto-wired)
- Empty states
- Loading states
- Responsive design

#### 4. **Forms** ⚠️ MEDIUM PRIORITY
- **Current:** Custom `<input>`, `<label>`, `<select>` with Tailwind
- **Files with forms:** ~20 files
  - Auth pages (login, register, forgot-password)
  - Task forms (task-create, task-edit)
  - Team forms (team-create, team-edit)
  - Project forms
- **MaryUI equivalent:** `<x-form>`, `<x-input>`, `<x-select>`, `<x-datepicker>`
- **Migration effort:** MEDIUM (6-10 hours)

**Benefit:** MaryUI forms provide:
- Built-in validation styling
- Error messages (auto-wired to Livewire)
- Consistent spacing/styling
- Date pickers, file uploads, rich text

#### 5. **Modals/Dialogs** ⚠️ MEDIUM PRIORITY
- **Current:** Custom `<x-modal>` component (Alpine.js based)
- **Files using modals:** ~8 files
  - Task detail (task-edit modal)
  - Confirmations (delete-user-form)
  - Various action modals
- **MaryUI equivalent:** `<x-modal>` (more feature-rich)
- **Migration effort:** LOW-MEDIUM (3-5 hours)

#### 6. **Badges/Tags** ✅ Already Using daisyUI
- **Current:** Using `badge` class
- **Status:** ✅ Complete

#### 7. **Stats Cards** ⚠️ Could Use MaryUI
- **Current:** Custom stat cards (see task-list blade, lines 33-63)
- **Files:** ~10 files
- **MaryUI equivalent:** `<x-stat>` or `stat` class
- **Migration effort:** LOW (2-3 hours)

#### 8. **Navigation/Sidebar** ✅ Custom, Keep As-Is
- **Current:** Custom sidebar with Alpine.js + daisyUI buttons
- **Status:** ✅ Already uses daisyUI `btn`, `btn-ghost`
- **Recommendation:** Keep custom (core UX, already themed)

#### 9. **Avatars** ⚠️ Could Use daisyUI
- **Current:** Custom `<img>` with Tailwind classes
- **Files:** ~12 files
- **MaryUI equivalent:** `avatar` class or `<x-avatar>`
- **Migration effort:** VERY LOW (1 hour)

#### 10. **Pagination** ✅ Using Livewire/Tailwind
- **Current:** `pagination::tailwind` vendor views
- **Status:** ✅ Already compatible
- **Optional:** MaryUI has prettier default, but not required

---

## 🎯 Migration Strategy

### Recommended: **Incremental Phase Approach**

**Why not big-bang:**
- 93 files to audit/update
- High risk of breaking UI
- Testing burden (would need full regression)
- Blocks feature development during migration

**Why incremental:**
- Low-risk, page-by-page
- Can pause/resume anytime
- No downtime
- Each phase delivers value independently

---

## 📋 Migration Phases

### **Phase 1: Quick Wins (4-6 hours)**
**Goal:** Replace simple components, build confidence

**Tasks:**
1. Replace custom stat cards → daisyUI `stat` class (1 hr)
2. Replace avatar `<img>` → daisyUI `avatar` class (1 hr)
3. Replace badges → daisyUI `badge` where not already used (1 hr)
4. Replace buttons → ensure consistency (2 hrs)

**Files touched:** 15-20 files  
**Risk:** LOW  
**Testing:** Visual spot-check  

**Deliverable:** UI consistency improvements, minimal code changes

---

### **Phase 2: Tables (8-12 hours)**
**Goal:** Replace complex tables with MaryUI `<x-table>`

**Priority order:**
1. Task list (most complex, highest value) - 4 hrs
2. Team member list - 2 hrs
3. Projects list - 2 hrs
4. Agent/HR tables - 4 hrs

**Files touched:** 12 files  
**Risk:** MEDIUM  
**Testing:** Each table needs functional testing

**Deliverable:** Better UX (sorting, search, pagination) with less code

**Example migration:**
```blade
<!-- BEFORE (≈60 lines of custom HTML) -->
<table class="min-w-full divide-y divide-gray-200">
    <thead>...</thead>
    <tbody>
        @foreach($tasks as $task)...
    </tbody>
</table>

<!-- AFTER (≈15 lines) -->
<x-table :headers="$headers" :rows="$tasks" with-pagination />
```

---

### **Phase 3: Forms (6-10 hours)**
**Goal:** Replace form inputs with MaryUI components

**Priority order:**
1. Auth forms (login, register) - 2 hrs
2. Task forms (create, edit) - 3 hrs
3. Team forms - 2 hrs
4. Project/other forms - 3 hrs

**Files touched:** 20 files  
**Risk:** MEDIUM (forms are user-facing)  
**Testing:** Each form needs submission testing

**Deliverable:** Consistent form styling, auto-validation, less code

**Example migration:**
```blade
<!-- BEFORE (≈20 lines) -->
<div>
    <label for="email" class="block text-sm font-medium text-gray-700">
        Email
    </label>
    <input 
        type="email" 
        id="email" 
        wire:model="email"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    >
    @error('email')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<!-- AFTER (≈8 lines) -->
<x-input 
    label="Email" 
    wire:model="email" 
    type="email"
    error-field="email"
/>
```

---

### **Phase 4: Modals & Dialogs (3-5 hours)**
**Goal:** Replace custom modals with MaryUI `<x-modal>`

**Files touched:** 8 files  
**Risk:** LOW-MEDIUM  
**Testing:** Modal open/close, form submissions

**Deliverable:** Consistent modal behavior, backdrop, escape key handling

---

### **Phase 5: Cards & Layouts (Optional, 4-6 hours)**
**Goal:** Replace custom card styling with daisyUI

**Files touched:** 25 files  
**Risk:** LOW  
**Note:** Custom card has LunaOS-specific glow effects — may want to keep

**Recommendation:** SKIP unless maintaining custom styling becomes burden

---

## ⏱️ Time Estimates Summary

| Phase | Focus Area | Hours | Priority |
|-------|-----------|-------|----------|
| Phase 1 | Stats, avatars, badges | 4-6 | HIGH |
| Phase 2 | Tables (all) | 8-12 | HIGH |
| Phase 3 | Forms (all) | 6-10 | MEDIUM |
| Phase 4 | Modals | 3-5 | MEDIUM |
| Phase 5 | Cards (optional) | 4-6 | LOW |
| **TOTAL** | | **25-39 hours** | |

**Realistic timeline:** 3-5 days of focused work (can be done incrementally over 1-2 weeks)

---

## 🎨 Custom Styling Considerations

### LunaOS-Specific Features to Preserve:
1. **Purple/violet gradient theme** - Already in `lunaos` daisyUI theme ✅
2. **Glassy effects** (backdrop-blur, gradients) - Keep custom CSS where MaryUI lacks this
3. **Card glow effects** - May need to keep custom `<x-card>` or extend daisyUI
4. **Custom gradients** (task hero, stat cards) - Keep as custom HTML + daisyUI utilities

### Hybrid Approach:
- Use MaryUI for **functional** components (tables, forms, modals)
- Keep custom CSS for **branding** elements (hero sections, gradients, special effects)

---

## 🧪 Testing Requirements

### Per Phase:
1. **Visual regression:** Screenshots before/after
2. **Functional testing:** Forms submit, tables sort/filter, modals open/close
3. **Responsive testing:** Mobile, tablet, desktop views
4. **Accessibility:** Tab navigation, screen reader labels

### Tools:
- **Dusk tests:** Already exist for some pages (run after each phase)
- **Manual testing:** Required for visual/UI changes

---

## 📦 Dependencies

### Required (Already Installed):
- ✅ `robsontenorio/mary` v2.8.0
- ✅ `daisyui` npm package v5.5.19
- ✅ Alpine.js (already in layout)

### Optional (For Advanced Features):
- 📋 Flatpickr (for date pickers - MaryUI handles this)
- 📋 Choices.js (for dropdowns - MaryUI handles this)

---

## ✅ Recommendation

### **Do This: Incremental Migration**

**Phase 1 + 2 first** (tables + quick wins):
- Highest impact/effort ratio
- Tables are most code-heavy → biggest savings
- Quick wins build momentum
- ~12-18 hours total (1.5-2 days)

**Skip Phase 5** (cards):
- LunaOS custom cards have unique branding
- MaryUI cards are functional but generic
- Not worth losing custom polish

**Defer Phase 3-4** (forms/modals):
- Lower priority than tables
- Can do when you have more time
- Current forms work fine

### **80/20 Rule:**
Migrate **tables only** → 8 hours for 80% of the benefit

---

## 🚀 Next Steps

1. **Review this plan** with Kyle (architect approval)
2. **Start Phase 1** (quick wins - stats, avatars, badges)
3. **Commit after each phase** → easy to rollback if needed
4. **Update docs** → document which components are MaryUI vs custom

---

**Document Status:** Draft - Awaiting Architect Review  
**Author:** Luna (automated audit)  
**Reviewers:** Kyle (Architect)
