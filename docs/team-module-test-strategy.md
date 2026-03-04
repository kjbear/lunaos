# Team Module Test Strategy (Task 2A.2)

**Document Created:** 2026-03-03  
**Author:** Sam (QA/Test Specialist)  
**Status:** DRAFT - Ready for Review  
**Related:** `PHASE-2A-TASKS.md`, `team-data-migration-plan.md`

---

## 🎯 Overview

This document defines the testing strategy for consolidating HR Personas + Agents into a unified Team module. As the QA gatekeeper, **no code merges without ALL tests passing**.

---

## ✅ Acceptance Criteria Checklist

### Backend (Dave)
- [ ] TeamMember model created (unified schema)
- [ ] Database migration consolidates personas + agents → team_members
- [ ] TeamService provides CRUD operations
- [ ] TeamController handles web requests
- [ ] API endpoints: `/api/team`, `/api/team/{id}`, `/api/team/{id}/members`
- [ ] Tab logic implemented: workers, personas, board-members
- [ ] Migration script is idempotent (safe to run multiple times)
- [ ] Rollback script tested and working

### Frontend (Maya)
- [ ] TeamIndex Livewire component (main overview with tabs)
- [ ] TeamDetails page (`/team/{id}`)
- [ ] TeamEdit page (`/team/edit/{id}`)
- [ ] TeamCreate page (`/team/create`)
- [ ] Tabs: Workers, Personas, Board Members
- [ ] Org chart visualization integrated
- [ ] Dark theme styling matches LunaOS design system
- [ ] Responsive design (mobile + desktop)

### Data Migration
- [ ] **Zero data loss verified**
- [ ] All persona records migrated
- [ ] All agent records migrated
- [ ] Name collisions handled (logged + resolved)
- [ ] Foreign key relationships intact (tasks, metrics, workspaces)
- [ ] Related tables migrated (metrics, workspaces, activities)

### Quality Gate
- [ ] All unit tests pass (phpunit)
- [ ] All feature tests pass
- [ ] All Dusk tests pass (php artisan dusk)
- [ ] No console errors in browser
- [ ] Migration + rollback tested on staging
- [ ] Documentation complete

---

## 🧪 Test Plan

### 1. Unit Tests

**Purpose:** Test model methods, service logic, migration logic in isolation.

#### 1.1 TeamMember Model Tests
**File:** `tests/Unit/Models/TeamMemberTest.php`

**Test Cases:**
- [ ] `test_team_member_can_be_created()` - Basic creation
- [ ] `test_team_member_requires_unique_name()` - Name uniqueness constraint
- [ ] `test_team_member_type_enum()` - Type can be 'persona', 'agent', or 'hybrid'
- [ ] `test_team_member_category_enum()` - Category can be 'board_member', 'subagent', 'worker', 'custom'
- [ ] `test_team_member_status_enum()` - Status enum values
- [ ] `test_team_member_parent_child_relationship()` - Self-referencing hierarchy
- [ ] `test_team_member_metrics_relationship()` - Has one metrics record
- [ ] `test_team_member_workspaces_relationship()` - Has many workspace files
- [ ] `test_team_member_activities_relationship()` - Has many activity logs
- [ ] `test_computed_attribute_progress_percentage()` - If applicable
- [ ] `test_computed_attribute_status_badge_class()` - Badge class based on status
- [ ] `test_scope_active_team_members()` - Scope filters correctly
- [ ] `test_scope_by_category()` - Category scope
- [ ] `test_scope_by_type()` - Type scope
- [ ] `test_team_member_serialization()` - JSON serialization for API

#### 1.2 TeamService Tests
**File:** `tests/Unit/Services/TeamServiceTest.php`

**Test Cases:**
- [ ] `test_create_team_member()` - Create new member
- [ ] `test_create_team_member_with_validation_errors()` - Validation rejects invalid data
- [ ] `test_get_all_team_members()` - List all members
- [ ] `test_get_team_member_by_id()` - Find by ID
- [ ] `test_get_team_member_by_name()` - Find by name
- [ ] `test_update_team_member()` - Update existing member
- [ ] `test_delete_team_member()` - Soft delete (deactivated_at)
- [ ] `test_restore_team_member()` - Restore from archived
- [ ] `test_filter_by_category()` - Filter workers/personas/board-members
- [ ] `test_filter_by_status()` - Filter by status
- [ ] `test_search_team_members()` - Search by name/title
- [ ] `test_pagination()` - Paginate results
- [ ] `test_bulk_update_status()` - Bulk status update
- [ ] `test_bulk_delete()` - Bulk delete
- [ ] `test_get_team_statistics()` - Counts by category, status
- [ ] `test_assign_parent_to_team_member()` - Set parent-child relationship
- [ ] `test_prevent_circular_hierarchy()` - Prevent child becoming parent of parent

#### 1.3 Migration Tests
**File:** `tests/Unit/Migrations/ConsolidateHrAndAgentsTest.php`

**Test Cases:**
- [ ] `test_migration_creates_team_members_table()` - Table created with correct schema
- [ ] `test_migration_migrates_personas()` - All persona records migrated
- [ ] `test_migration_migrates_agents()` - All agent records migrated
- [ ] `test_migration_handles_name_collisions()` - Duplicate names handled
- [ ] `test_migration_preserves_foreign_keys()` - Relationships intact
- [ ] `test_migration_migrates_metrics()` - Persona metrics migrated
- [ ] `test_migration_migrates_workspaces()` - Workspace files linked
- [ ] `test_migration_migrates_activities()` - Agent activities migrated
- [ ] `test_rollback_restores_original_tables()` - Rollback restores personas + agents
- [ ] `test_rollback_restores_related_tables()` - Related tables restored
- [ ] `test_migration_is_idempotent()` - Safe to run multiple times
- [ ] `test_no_data_loss()` - Record count matches (personas + agents = team_members)

---

### 2. Feature Tests

**Purpose:** Test Livewire components, controllers, and API endpoints.

#### 2.1 Livewire Component Tests
**File:** `tests/Feature/Livewire/TeamIndexTest.php`

**Test Cases:**
- [ ] `test_team_index_loads()` - Component renders without errors
- [ ] `test_team_index_shows_all_tabs()` - Workers, Personas, Board Members tabs present
- [ ] `test_team_index_shows_team_members()` - Members displayed in correct tabs
- [ ] `test_tab_switching()` - Clicking tabs filters members without page reload
- [ ] `test_search_filters_members()` - Search input filters members
- [ ] `test_create_button_visible()` - Create button present
- [ ] `test_org_chart_rendered()` - Org chart visualization present
- [ ] `test_team_statistics_shown()` - Counts displayed per category

**File:** `tests/Feature/Livewire/TeamDetailsTest.php`

**Test Cases:**
- [ ] `test_team_details_loads()` - Details page renders
- [ ] `test_team_details_shows_member_info()` - All member attributes visible
- [ ] `test_team_details_shows_metrics()` - Metrics data displayed
- [ ] `test_team_details_shows_workspaces()` - Workspace files listed
- [ ] `test_team_details_shows_activities()` - Activity log displayed
- [ ] `test_team_details_edit_button()` - Edit button present
- [ ] `test_team_details_delete_button()` - Delete button present
- [ ] `test_team_details_parent_displayed()` - Parent member shown (if exists)
- [ ] `test_team_details_children_displayed()` - Child members shown (if any)

**File:** `tests/Feature/Livewire/TeamCreateTest.php`

**Test Cases:**
- [ ] `test_create_form_loads()` - Create form renders
- [ ] `test_create_form_has_all_fields()` - All required fields present
- [ ] `test_create_form_validation()` - Validation rules enforced
- [ ] `test_create_form_submits_successfully()` - Valid data creates member
- [ ] `test_create_form_shows_errors()` - Invalid data shows error messages
- [ ] `test_create_form_redirects_to_details()` - Redirect after create
- [ ] `test_create_form_prevents_duplicate_name()` - Unique name validation

**File:** `tests/Feature/Livewire/TeamEditTest.php`

**Test Cases:**
- [ ] `test_edit_form_loads()` - Edit form renders with existing data
- [ ] `test_edit_form_has_all_fields()` - All editable fields present
- [ ] `test_edit_form_validation()` - Validation rules enforced
- [ ] `test_edit_form_submits_successfully()` - Valid data updates member
- [ ] `test_edit_form_shows_errors()` - Invalid data shows error messages
- [ ] `test_edit_form_redirects_to_details()` - Redirect after update
- [ ] `test_edit_form_prevents_duplicate_name()` - Unique name validation (excluding self)

#### 2.2 Controller Tests
**File:** `tests/Feature/Http/Controllers/TeamControllerTest.php`

**Test Cases:**
- [ ] `test_index_returns_view()` - GET /team returns view
- [ ] `test_create_returns_view()` - GET /team/create returns view
- [ ] `test_edit_returns_view()` - GET /team/edit/{id} returns view
- [ ] `test_show_returns_view()` - GET /team/{id} returns view
- [ ] `test_store_creates_member()` - POST /team creates member
- [ ] `test_store_validates_input()` - POST validates required fields
- [ ] `test_update_modifies_member()` - PUT /team/{id} updates member
- [ ] `test_update_validates_input()` - PUT validates fields
- [ ] `test_destroy_deletes_member()` - DELETE /team/{id} soft-deletes member
- [ ] `test_unauthenticated_user_redirected()` - Auth required
- [ ] `test_authorized_user_can_access()` - Authorized user can access
- [ ] `test_unauthorized_user_forbidden()` - Unauthorized user gets 403

#### 2.3 API Endpoint Tests
**File:** `tests/Feature/Api/TeamApiTest.php`

**Test Cases:**
- [ ] `test_get_team_list()` - GET /api/team returns JSON array
- [ ] `test_get_team_list_with_filters()` - Filters work (category, status)
- [ ] `test_get_team_list_with_pagination()` - Pagination works
- [ ] `test_get_single_team_member()` - GET /api/team/{id} returns member
- [ ] `test_create_team_member()` - POST /api/team creates member
- [ ] `test_create_team_member_validation()` - Validation errors returned
- [ ] `test_update_team_member()` - PUT /api/team/{id} updates member
- [ ] `test_delete_team_member()` - DELETE /api/team/{id} deletes member
- [ ] `test_get_team_members_by_tab()` - GET /api/team/{id}/members returns children
- [ ] `test_api_requires_authentication()` - Auth required
- [ ] `test_api_returns_correct_json_structure()` - JSON structure matches spec
- [ ] `test_api_includes_computed_fields()` - Computed fields in response

---

### 3. Browser Tests (Dusk)

**Purpose:** Test real user interactions in the browser.

#### 3.1 Team Module Browser Tests

**File:** `tests/Browser/Team/TeamIndexLoadTest.php`

**Test Cases:**
- [ ] `test_team_index_loads()` - `/team` page loads without errors
- [ ] `test_team_index_has_correct_title()` - Page title is "Team"
- [ ] `test_team_index_shows_all_tabs()` - Workers, Personas, Board Members tabs visible
- [ ] `test_team_index_shows_member_count()` - Total member count displayed
- [ ] `test_team_index_responsive()` - Page is responsive on mobile
- [ ] `test_team_index_no_console_errors()` - No JavaScript console errors

**File:** `tests/Browser/Team/TeamManagementTest.php`

**Test Cases:**
- [ ] `test_view_all_team_members()` - See all members in list
- [ ] `test_filter_by_workers_tab()` - Clicking Workers tab shows only workers
- [ ] `test_filter_by_personas_tab()` - Clicking Personas tab shows only personas
- [ ] `test_filter_by_board_members_tab()` - Clicking Board Members tab shows only board members
- [ ] `test_search_team_members()` - Search filters members by name
- [ ] `test_click_member_shows_details()` - Clicking member navigates to details page
- [ ] `test_create_team_member_button()` - Create button works
- [ ] `test_edit_team_member_button()` - Edit button works
- [ ] `test_delete_team_member_button()` - Delete button works
- [ ] `test_view_org_chart()` - Org chart visualization renders
- [ ] `test_tab_switching_no_page_reload()` - Tabs switch without full page reload

**File:** `tests/Browser/Team/TeamCreateTest.php`

**Test Cases:**
- [ ] `test_create_form_loads()` - Create form page loads
- [ ] `test_create_name_field()` - Name field present and editable
- [ ] `test_create_title_field()` - Title field present and editable
- [ ] `test_create_type_field()` - Type dropdown present (persona/agent/hybrid)
- [ ] `test_create_category_field()` - Category dropdown (board_member/subagent/worker/custom)
- [ ] `test_create_status_field()` - Status dropdown
- [ ] `test_create_model_field()` - Model field present
- [ ] `test_create_system_prompt_field()` - System prompt textarea present
- [ ] `test_create_form_submits_successfully()` - Valid form creates member
- [ ] `test_create_form_shows_validation_errors()` - Invalid form shows errors
- [ ] `test_create_form_prevents_duplicate_name()` - Duplicate name rejected
- [ ] `test_create_redirects_to_details()` - After create, redirects to details page
- [ ] `test_new_member_appears_in_list()` - New member visible in team list

**File:** `tests/Browser/Team/TeamEditTest.php`

**Test Cases:**
- [ ] `test_edit_form_loads_with_data()` - Edit form pre-populated
- [ ] `test_edit_name_field()` - Name field editable
- [ ] `test_edit_title_field()` - Title field editable
- [ ] `test_edit_type_field()` - Type dropdown editable
- [ ] `test_edit_category_field()` - Category dropdown editable
- [ ] `test_edit_status_field()` - Status dropdown editable
- [ ] `test_edit_system_prompt_field()` - System prompt editable
- [ ] `test_edit_form_submits_successfully()` - Valid form updates member
- [ ] `test_edit_form_shows_validation_errors()` - Invalid form shows errors
- [ ] `test_edit_redirects_to_details()` - After update, redirects to details
- [ ] `test_changes_visible_in_list()` - Updated data visible in team list

**File:** `tests/Browser/Team/TeamDetailsTest.php`

**Test Cases:**
- [ ] `test_details_page_loads()` - Details page loads for member
- [ ] `test_details_shows_all_info()` - All member attributes visible
- [ ] `test_details_shows_metrics()` - Metrics section visible (if applicable)
- [ ] `test_details_shows_workspaces()` - Workspace files listed (if applicable)
- [ ] `test_details_shows_activities()` - Activity log visible (if applicable)
- [ ] `test_details_shows_parent()` - Parent member shown (if exists)
- [ ] `test_details_shows_children()` - Child members shown (if any)
- [ ] `test_edit_button_navigates_to_edit()` - Edit button works
- [ ] `test_delete_button_deletes_member()` - Delete button works with confirmation
- [ ] `test_back_button_navigates_to_index()` - Back button returns to team list

**File:** `tests/Browser/Team/TeamMigrationTest.php`

**Test Cases:**
- [ ] `test_legacy_personas_page_redirects()` - Old /personas URL redirects to /team
- [ ] `test_legacy_agents_page_redirects()` - Old /agents URL redirects to /team
- [ ] `test_all_personas_migrated()` - Former personas visible in team list
- [ ] `test_all_agents_migrated()` - Former agents visible in team list
- [ ] `test_metrics_accessible()` - Metrics data accessible for former personas
- [ ] `test_workspaces_accessible()` - Workspace files accessible for former personas
- [ ] `test_activities_accessible()` - Activity logs accessible for former agents
- [ ] `test_task_assignments_intact()` - Tasks assigned to agents still work

---

### 4. Migration-Specific Tests

**Purpose:** Ensure data migration is safe and reliable.

#### 4.1 Data Integrity Tests
**File:** `tests/Feature/Migration/DataIntegrityTest.php`

**Test Cases:**
- [ ] `test_record_count_matches()` - team_members count = personas + agents
- [ ] `test_all_names_unique()` - No duplicate names in team_members
- [ ] `test_all_uuids_valid()` - All UUIDs are valid format
- [ ] `test_all_required_fields_populated()` - No NULL in required fields
- [ ] `test_foreign_keys_intact()` - All foreign key relationships valid
- [ ] `test_no_orphaned_records()` - All related records have parent
- [ ] `test_tasks_assignments_valid()` - All task assigned_to values exist
- [ ] `test_metrics_count_matches()` - Metrics count = team_members with metrics
- [ ] `test_workspaces_count_matches()` - Workspaces linked correctly
- [ ] `test_activities_count_matches()` - Activities linked correctly

#### 4.2 Rollback Tests
**File:** `tests/Feature/Migration/RollbackTest.php`

**Test Cases:**
- [ ] `test_rollback_restores_personas()` - Personas table restored
- [ ] `test_rollback_restores_agents()` - Agents table restored
- [ ] `test_rollback_restores_metrics()` - Metrics table restored
- [ ] `test_rollback_restores_workspaces()` - Workspaces table restored
- [ ] `test_rollback_preserves_data()` - All data preserved after rollback
- [ ] `test_rollback_removes_team_members()` - team_members table removed
- [ ] `test_rollback_is_safe()` - Rollback doesn't corrupt data
- [ ] `test_rerun_migration_after_rollback()` - Migration can run again after rollback

---

## 📊 Test Execution Strategy

### Phase 1: Pre-Migration (NOW)
- [ ] Write all unit tests for TeamMember model
- [ ] Write all unit tests for TeamService
- [ ] Write migration tests
- [ ] Write feature tests for Livewire components
- [ ] Write feature tests for controllers/API

### Phase 2: During Implementation
- [ ] Run unit tests as Dave builds model/service
- [ ] Run feature tests as Maya builds components
- [ ] Fix tests as implementation evolves
- [ ] Update tests if requirements change

### Phase 3: Post-Implementation
- [ ] Run all Dusk tests
- [ ] Verify no console errors
- [ ] Test migration on staging database
- [ ] Test rollback procedure
- [ ] Performance testing (< 5 minutes migration)

### Phase 4: QA Sign-Off
- [ ] All unit tests pass ✅
- [ ] All feature tests pass ✅
- [ ] All Dusk tests pass ✅
- [ ] No console errors ✅
- [ ] Migration tested ✅
- [ ] Rollback tested ✅
- [ ] Documentation complete ✅

**Only after ALL checks pass → Approve for merge**

---

## 🛠️ Test Commands

```bash
# Run all unit tests
php artisan test --testsuite=Unit

# Run TeamMember model tests only
php artisan test tests/Unit/Models/TeamMemberTest.php

# Run TeamService tests only
php artisan test tests/Unit/Services/TeamServiceTest.php

# Run migration tests
php artisan test tests/Unit/Migrations/

# Run all feature tests
php artisan test --testsuite=Feature

# Run Livewire component tests
php artisan test tests/Feature/Livewire/

# Run API tests
php artisan test tests/Feature/Api/

# Run all Dusk tests
php artisan dusk

# Run Team module Dusk tests only
php artisan dusk --filter=Team

# Run specific Dusk test
php artisan dusk tests/Browser/Team/TeamManagementTest.php

# Run migration integrity tests
php artisan test tests/Feature/Migration/

# Check for console errors (manual)
# Open browser console while running Dusk tests

# Run all tests with coverage
php artisan test --coverage

# Generate test report
php artisan test --log-junit=report.xml
```

---

## 📁 File Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   └── TeamMemberTest.php
│   ├── Services/
│   │   └── TeamServiceTest.php
│   └── Migrations/
│       └── ConsolidateHrAndAgentsTest.php
├── Feature/
│   ├── Livewire/
│   │   ├── TeamIndexTest.php
│   │   ├── TeamDetailsTest.php
│   │   ├── TeamCreateTest.php
│   │   └── TeamEditTest.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── TeamControllerTest.php
│   ├── Api/
│   │   └── TeamApiTest.php
│   └── Migration/
│       ├── DataIntegrityTest.php
│       └── RollbackTest.php
└── Browser/
    └── Team/
        ├── TeamIndexLoadTest.php
        ├── TeamManagementTest.php
        ├── TeamCreateTest.php
        ├── TeamEditTest.php
        ├── TeamDetailsTest.php
        └── TeamMigrationTest.php
```

---

## ⚠️ Known Risks

1. **Name Collisions**: If both personas and agents have same name
   - Mitigation: Test handles this explicitly
   - Migration logs collisions
   - Automatic rename (e.g., "dave" → "dave-agent")

2. **Foreign Key Breaks**: Tasks referencing agents.name
   - Mitigation: Migration updates all foreign keys
   - Test verifies all references intact

3. **Livewire Component Breakage**: Old components reference old models
   - Mitigation: Components refactored to use TeamMember
   - Feature tests catch rendering errors

4. **Data Loss**: Critical records lost in migration
   - Mitigation: Full backup before migration
   - Rollback procedure tested
   - Data integrity tests verify counts

---

## 📝 Quality Gate Checklist (Final)

**Before approving ANY PR for Task 2A.2:**

- [ ] **Unit Tests** - All pass (phpunit)
- [ ] **Feature Tests** - All pass (phpunit)
- [ ] **Browser Tests** - All pass (php artisan dusk)
- [ ] **Console Errors** - None in browser console
- [ ] **Migration** - Tested on staging, completes successfully
- [ ] **Rollback** - Tested and verified working
- [ ] **Data Integrity** - Zero data loss confirmed
- [ ] **Documentation** - All docs updated
  - [ ] `docs/TEAM-MODULE-CONSOLIDATION.md` created
  - [ ] HR + Agents docs have migration guide
  - [ ] README updated with new routes
- [ ] **Backward Compatibility** - Old routes redirect correctly
- [ ] **Performance** - Migration completes in < 5 minutes
- [ ] **Security** - Auth/authorization working correctly

**Status:** 🟡 IN PROGRESS

**Next Steps:**
1. Review this strategy with Dave and Maya
2. Start writing unit tests (I'll do this as Dave builds model)
3. Write feature tests (as Maya builds components)
4. Write Dusk tests (before manual testing phase)
5. Execute all tests and verify quality gate

---

## 📞 Contact

**QA Lead:** Sam (QA/Test Specialist)  
**Spawning Session:** `sam-2a2-team-qa`  
**Task:** 2A.2 - Consolidate HR + Agents → Team Module  
**Quality Gate:** NO MERGES WITHOUT ALL TESTS PASSING ✅
