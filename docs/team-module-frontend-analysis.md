# Team Module Frontend Analysis

**Task:** 2A.2 - Consolidate HR + Agents → Team Module  
**Author:** Maya (Frontend/Livewire Specialist)  
**Date:** 2026-03-03  
**Status:** Analysis Phase

---

## 1. Current State Audit

### 1.1 HR Module (Personas)

**Route:** `/hr` → `resources/views/hr.blade.php`  
**Livewire Component:** `App\Livewire\HR\PersonasIndex.php`  
**View:** `resources/views/livewire/hr/personas-index.blade.php`

**Features:**
- **Stats Dashboard:** Total, Active, Subagents, Board Members, By Model breakdown
- **Filter System:** Tabs for all/active/subagents/board/custom
- **Search:** Live search by name, inspiration, or system prompt
- **Persona Cards:** Display avatar, name, status, role, model, metrics
- **Actions per Persona:**
  - View Workspace (links to `/hr/{id}/workspace`)
  - Edit (modal form)
  - Deactivate (with confirmation)
- **Create/Edit Modal:** Name, Role, Model, Avatar, Inspiration, System Prompt

**Data Model:** `Persona` model (UUID primary key)
- Fields: name, title, role, model, avatar, status, inspiration, system_prompt, workspace_path, deactivated_at
- Relations: metrics (hasOne), workspaces (hasMany)
- Roles: subagent, board_member, custom

**UI Quality:** ⭐⭐⭐⭐⭐
- Highly polished with gradient backgrounds, blur effects, glow animations
- Consistent design system with purple/pink/cyan color scheme
- Excellent visual hierarchy and spacing

---

### 1.2 Agents Module (Workers)

**Route:** `/agents` → `resources/views/agents.blade.php`  
**Livewire Component:** `App\Livewire\Agents\AgentList.php`  
**View:** `resources/views/livewire/agents/agent-list.blade.php`

**Features:**
- **Agent Grid:** Card-based layout (responsive: 1-4 columns)
- **Agent Cards Display:**
  - Header with emoji, name, role, type badge
  - Status indicator (online/offline)
  - Model information
  - Strategy class
  - Skill doc path
  - Stats: Total tasks, Completed tasks
- **Actions per Agent:**
  - Edit (links to `/agents/{id}/edit`)
  - Delete (with confirmation modal, protected agents: dave, sam, chen)
- **Create Button:** Links to `/agents/create`

**Data Model:** `Agent` model (integer primary key)
- Fields: name, type, role, model, provider, system_prompt, model_settings, avatar, status, parent_id, emoji, runtime_location, strategy_class, step_filter, workflow_config, skill_doc_path, skill_metadata, is_online, capabilities, settings, title
- Relations: parent (belongsTo), children (hasMany), tasks (hasMany), workspaceConfig (hasOne)
- Roles: worker (primary)

**UI Quality:** ⭐⭐⭐⭐
- Clean card-based design with gradients
- Good visual hierarchy
- Less polished than HR module (no advanced effects)

---

## 2. Key Differences & Challenges

| Aspect | HR (Personas) | Agents (Workers) |
|--------|---------------|------------------|
| **Primary Key** | UUID (string) | Integer |
| **Roles** | subagent, board_member, custom | worker |
| **UI Pattern** | List view with detailed cards | Grid view with compact cards |
| **Stats** | Built into component (projects, tasks, success rate, sessions, decisions) | Derived from tasks relationship |
| **Search** | Live search with debounce | None implemented |
| **Filtering** | 5 filter tabs | None implemented |
| **Modal Forms** | Inline modal for create/edit | Separate page for create/edit |
| **Deletion** | Deactivation only | Hard delete (with protected agents) |
| **Design Polish** | Very high (gradients, blurs, glows) | Moderate |

---

## 3. Proposed Unified Team Module Design

### 3.1 Navigation Structure

**Replace:**
- `/hr` → Remove
- `/agents` → Remove

**With:**
- `/team` → Main Team dashboard (index with tabs)
- `/team/{id}` → Individual team member details page
- `/team/edit/{id}` → Edit team member page
- `/team/create` → Create new team member page

**Navigation Menu Update:**
- Remove "HR" menu item
- Remove "Agents" menu item
- Add "Team" menu item (single unified entry)

---

### 3.2 Tab Navigation Design

**Main Team Index** (`/team`) will have three primary tabs:

```
┌─────────────────────────────────────────────────┐
│  👥 Team                                        │
│  ┌──────────┬──────────┬──────────────┐         │
│  │ Workers  │ Personas │ Board Members│         │
│  └──────────┴──────────┴──────────────┘         │
└─────────────────────────────────────────────────┘
```

**Tab Behavior:**
- **Workers Tab:** Shows all `Agent` records with role='worker'
  - Grid layout (current agent-card design)
  - Filter by: Online/Offline, Strategy type
  - Search by: name, role, strategy
  
- **Personas Tab:** Shows all `Persona` records with role='subagent' or 'custom'
  - List layout (current persona-card design)
  - Filter by: Active/Subagent/Custom
  - Search by: name, inspiration, system prompt
  
- **Board Members Tab:** Shows all `Persona` records with role='board_member'
  - List layout (subset of persona design)
  - Filter by: Active only
  - Shows: Sessions count, Decisions count

---

### 3.3 Unified Stats Dashboard

**Top Stats Bar** (visible on all tabs):
```
┌──────────────────────────────────────────────────────────────┐
│  📊 Total: X  |  ✓ Active: X  |  🤖 Workers: X  |            │
│  👥 Personas: X  |  🎯 Board: X  |  ⚡ Online: X             │
└──────────────────────────────────────────────────────────────┘
```

---

### 3.4 Component Architecture

**New Livewire Components:**
```
app/Livewire/Team/
├── TeamIndex.php          # Main tabbed dashboard
├── TeamDetails.php        # Individual member details page
└── TeamEdit.php           # Edit form (reusable for create/edit)
```

**New Views:**
```
resources/views/
├── team.blade.php                    # Main layout wrapper
└── livewire/team/
    ├── team-index.blade.php          # Tabbed dashboard view
    ├── team-details.blade.php        # Details page view
    ├── team-edit.blade.php           # Edit form view
    └── partials/
        ├── worker-card.blade.php     # Worker grid card
        ├── persona-card.blade.php    # Persona list card
        └── board-member-card.blade.php # Board member card
```

---

### 3.5 Consolidation Strategy

**Option A: Unified Model (Recommended for Phase 2B)**
- Create new `TeamMember` model that abstracts both Agent and Persona
- Migrate existing data to unified schema
- Pros: Single source of truth, simpler queries, unified API
- Cons: Requires database migration, more complex backend

**Option B: Facade Pattern (Recommended for Phase 2A)**
- Keep Agent and Persona models separate
- Create `TeamMemberFacade` service class that provides unified interface
- Livewire components query both models based on active tab
- Pros: Non-breaking, faster to implement, preserves existing functionality
- Cons: More complex component logic, dual-maintenance

**Decision:** Use **Option B (Facade Pattern)** for this phase to minimize backend changes. Dave can work on Phase 2B unified model separately.

---

### 3.6 API Contract (For Backend - Dave)

**Dave should provide:**
```php
// app/Services/TeamMemberService.php
class TeamMemberService {
    // Get all team members (unified)
    public function getAll(string $type = null): Collection;
    
    // Get single member (polymorphic)
    public function findById(string $id, string $type): Model;
    
    // Get unified stats
    public function getStats(): array;
    
    // Create member
    public function create(array $data, string $type): Model;
    
    // Update member
    public function update(string $id, string $type, array $data): Model;
    
    // Deactivate/Delete member
    public function deactivate(string $id, string $type): bool;
}
```

**API Endpoints Needed:**
- `GET /api/team` → List all members (with ?type=worker|persona|board query param)
- `GET /api/team/{type}/{id}` → Get single member
- `POST /api/team` → Create member (with type in payload)
- `PUT /api/team/{type}/{id}` → Update member
- `DELETE /api/team/{type}/{id}` → Deactivate/delete member

---

### 3.7 Migration Plan

**Phase 1: Foundation (This Task)**
1. ✅ Audit current HR and Agents modules (done)
2. ⏳ Create `TeamIndex` Livewire component with tabs
3. ⏳ Create shared card partials for Workers/Personas/Board
4. ⏳ Implement tab navigation with filtering
5. ⏳ Update navigation menu to replace HR + Agents with "Team"

**Phase 2: Details Pages**
6. ⏳ Create `TeamDetails` page (`/team/{type}/{id}`)
7. ⏳ Create `TeamEdit` page (`/team/{type}/{id}/edit`)
8. ⏳ Create `TeamCreate` page (`/team/create`)

**Phase 3: Polish**
9. ⏳ Add unified search across all member types
10. ⏳ Add unified stats dashboard
11. ⏳ Ensure design consistency (apply HR-level polish to Workers)

---

## 4. Design System Alignment

**Color Palette:**
- Primary: Purple/Pink gradients (consistent with HR module)
- Secondary: Cyan/Blue (for Workers tab)
- Accent: Amber/Orange (for Board Members tab)

**Card Styles:**
- Workers: Grid cards (keep current agent-card style but upgrade with HR polish)
- Personas: List cards (keep current persona-card style)
- Board Members: List cards (variation of persona-card)

**Component Patterns:**
- Use Livewire for all interactive elements
- Modal forms for quick edits
- Separate pages for full create/edit workflows
- Toast notifications for feedback

---

## 5. QA Considerations (For Sam)

**Dusk Test Coverage Needed:**
- Tab navigation works correctly
- Worker grid displays all agents
- Persona list displays all personas
- Board member list displays all board members
- Search filters work on each tab
- Create new worker/persona/board member
- Edit existing member
- Deactivate/delete member
- Stats dashboard shows correct counts
- Responsive design (mobile/tablet/desktop)

**Test Files to Create:**
```
tests/Browser/
├── TeamIndexTest.php
├── TeamDetailsTest.php
├── TeamEditTest.php
└── TeamCreateTest.php
```

---

## 6. Open Questions for Team

### For Dave (Backend):
1. Should we create a `TeamMemberService` facade now, or wait for unified model?
2. Do you need new API endpoints, or will Livewire components query models directly?
3. What's the timeline for Phase 2B (unified model)?

### For Sam (QA):
1. Can you start writing Dusk tests as I build components?
2. Any specific edge cases you want covered?

### For Kyle (Product):
1. Do you want separate create flows for Workers vs Personas, or unified "Add Team Member" with type selector?
2. Should Board Members be editable, or read-only (since they're advisory)?
3. Priority order: Workers tab first, or all tabs equally?

---

## 7. Recommendation

**Start with Workers Tab first** because:
1. Simpler data model (Agent model is straightforward)
2. Grid layout is proven and works well
3. Can establish the tab navigation pattern
4. Then Personas and Board Members tabs are variations on the same pattern

**Implementation Order:**
1. Create `TeamIndex` component skeleton with tab UI
2. Implement Workers tab (query Agent model)
3. Implement Personas tab (query Persona model, filter subagent/custom)
4. Implement Board Members tab (query Persona model, filter board_member)
5. Add shared stats dashboard
6. Update navigation menu
7. Create details/edit pages (future step)

---

**Next Step:** Awaiting approval on this analysis to proceed with implementation.
