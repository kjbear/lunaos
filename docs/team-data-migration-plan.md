# Team Data Migration Plan
## HR Personas + Agents → Team Module Consolidation

**Document Created:** 2026-03-03  
**Author:** Chen (DevOps/Data Specialist)  
**Status:** Ready for Review  
**Critical Requirement:** ZERO DATA LOSS

---

## Executive Summary

This document outlines the migration strategy for consolidating two separate tables (`personas` and `agents`) into a unified `team_members` table. The migration prioritizes data integrity, rollback capability, and zero production data loss.

---

## 1. Current State Audit

### 1.1 HR Personas Table (`personas`)

**Schema:**
```sql
- id: uuid (primary key)
- name: string
- role: enum('subagent', 'board_member', 'custom')
- model: string (default: 'haiku')
- avatar: string (nullable)
- status: enum('active', 'inactive', 'archived')
- inspiration: string (nullable)
- system_prompt: text (nullable)
- workspace_path: string (nullable)
- created_at: timestamp
- updated_at: timestamp
- deactivated_at: timestamp (nullable)
```

**Related Tables:**
- `persona_metrics` (1:1 relationship, cascade delete)
- `persona_workspaces` (1:many relationship, cascade delete)

**Key Characteristics:**
- UUID primary keys
- Used for board members, subagents, and custom personas
- Has metrics tracking and workspace file storage
- Status includes 'archived' state

### 1.2 Agents Table (`agents`)

**Schema:**
```sql
- id: bigint (primary key, auto-increment)
- name: string
- type: string (added later)
- role: string (default: 'worker')
- model: string (nullable)
- provider: string (default: 'ollama')
- system_prompt: text (nullable)
- model_settings: json (nullable)
- avatar: string (default: '🤖')
- emoji: string (default: '🤖')
- status: enum('online', 'offline', 'error', 'busy')
- parent_id: bigint (nullable, self-reference)
- runtime_location: string (default: 'php')
- last_location_check: timestamp (nullable)
- strategy_class: string (nullable)
- step_filter: string (nullable)
- workflow_config: json (nullable)
- skill_doc_path: string (nullable)
- skill_metadata: json (nullable)
- is_online: boolean (nullable)
- capabilities: json (nullable)
- settings: json (nullable)
- title: string (nullable)
- created_at: timestamp
- updated_at: timestamp
```

**Related Tables:**
- `agent_updates` (historical updates)
- `agent_conversations` (conversation history)
- `agent_activities` (activity logs)
- `tasks` (foreign key: assigned_to references agents.name)
- `workspace_configs` (1:1 relationship)

**Key Characteristics:**
- Auto-increment integer primary keys
- Has parent-child hierarchy (parent_id)
- Worker agents with strategy pattern support
- Rich metadata for runtime configuration

---

## 2. Data Conflicts & Overlaps Analysis

### 2.1 Identified Overlaps

| Aspect | Personas | Agents | Conflict Resolution |
|--------|----------|--------|---------------------|
| **Primary Key Type** | UUID | BigInt | Use UUID for all TeamMembers |
| **Name Field** | Unique identifier | Unique identifier | Preserve names; check duplicates |
| **Role Field** | enum(subagent, board_member, custom) | string (worker) | Unified role mapping (see below) |
| **Status Field** | active/inactive/archived | online/offline/error/busy | Need unified status enum |
| **Model Field** | dolphin/haiku/glm-5 | varies by provider | Keep as-is, add provider field |
| **Avatar** | string | string | Merge (emoji + avatar) |
| **System Prompt** | Yes | Yes | Yes, no conflict |

### 2.2 Potential Data Conflicts

1. **Name Collisions:** If both tables have a record with name "dave", we need merge logic
2. **Role Translation:** Different role systems need mapping
3. **Status Translation:** Different status enums need unified approach
4. **Foreign Key References:** `tasks.assigned_to` references `agents.name` - needs careful handling

---

## 3. Proposed Mapping Strategy

### 3.1 Unified TeamMember Schema

```php
Schema::create('team_members', function (Blueprint $table) {
    $table->uuid('id')->primary();  // UUID for all members
    $table->string('name')->unique();  // Unique across ALL members
    $table->string('title')->nullable();  // From Agents
    $table->enum('type', ['persona', 'agent', 'hybrid']);  // Track origin
    $table->enum('category', ['board_member', 'subagent', 'worker', 'custom']);  // Unified role
    $table->string('model')->nullable();
    $table->string('provider')->default('ollama');  // From Agents
    $table->string('avatar')->nullable();
    $table->string('emoji')->default('🤖');  // From Agents
    $table->enum('status', ['active', 'inactive', 'online', 'offline', 'error', 'busy', 'archived']);  // Unified
    $table->text('system_prompt')->nullable();
    $table->json('settings')->nullable();  // Merged settings
    $table->string('workspace_path')->nullable();  // From Personas
    $table->string('runtime_location')->default('php');  // From Agents
    $table->string('strategy_class')->nullable();  // From Agents
    $table->json('capabilities')->nullable();  // From Agents
    
    // Hierarchy (from Agents)
    $table->uuid('parent_id')->nullable();
    
    // Lifecycle tracking
    $table->timestamp('deactivated_at')->nullable();
    $table->timestamp('last_location_check')->nullable();
    $table->timestamps();
    
    $table->foreign('parent_id')->references('id')->on('team_members')->onDelete('set null');
});
```

### 3.2 Role Mapping

| Persona Role | Agent Role | → | TeamMember Category |
|--------------|------------|---|---------------------|
| board_member | N/A | → | board_member |
| subagent | N/A | → | subagent |
| custom | worker | → | worker |
| N/A | worker | → | worker |

### 3.3 Status Mapping

| Persona Status | Agent Status | → | TeamMember Status |
|----------------|--------------|---|---------------------|
| active | online | → | online |
| active | offline | → | offline |
| inactive | any | → | inactive |
| archived | any | → | archived |
| N/A | error | → | error |
| N/A | busy | → | busy |

---

## 4. Migration Implementation Plan

### 4.1 Pre-Migration Checklist

- [ ] Create full database backup
- [ ] Export `personas` table to JSON
- [ ] Export `agents` table to JSON
- [ ] Export related tables (persona_metrics, persona_workspaces, agent_updates, etc.)
- [ ] Document current record counts
- [ ] Test migration on staging database
- [ ] Verify all Livewire components have fallbacks
- [ ] Create rollback scripts
- [ ] Schedule maintenance window

### 4.2 Migration Steps

1. **Backup Phase**
   - Export both tables to JSON with full data
   - Export related tables
   - Verify backup integrity

2. **Schema Creation Phase**
   - Create `team_members` table
   - Create `team_member_metrics` table (consolidated from persona_metrics)
   - Create `team_member_workspaces` table (consolidated from persona_workspaces)

3. **Data Migration Phase**
   - Migrate Personas → TeamMembers (with type='persona')
   - Migrate Agents → TeamMembers (with type='agent')
   - Handle name collisions (rename or merge)
   - Migrate related data (metrics, workspaces)

4. **Foreign Key Update Phase**
   - Update `tasks.assigned_to` references
   - Update any other foreign key relationships
   - Verify referential integrity

5. **Livewire Component Updates**
   - Update `PersonasIndex` to use TeamMember model
   - Update `AgentList` to use TeamMember model
   - Test all CRUD operations

6. **Cleanup Phase**
   - Archive old tables (rename with `_archive` suffix)
   - Keep for 30 days
   - Final verification

---

## 5. Rollback Strategy

### 5.1 Rollback Triggers

- Migration script fails mid-execution
- Data integrity check fails
- Critical Livewire component breakage
- User-reported data loss

### 5.2 Rollback Procedure

1. **Stop all application writes**
2. **Restore from JSON backup:**
   ```bash
   php artisan db:restore-personas --file=backups/personas-2026-03-03.json
   php artisan db:restore-agents --file=backups/agents-2026-03-03.json
   ```
3. **Drop new tables:**
   ```sql
   DROP TABLE team_member_workspaces;
   DROP TABLE team_member_metrics;
   DROP TABLE team_members;
   ```
4. **Restore archived tables (if already cleaned):**
   ```sql
   RENAME TABLE personas_archive TO personas;
   RENAME TABLE agents_archive TO agents;
   ```
5. **Verify restoration**
6. **Restart application**

### 5.3 Rollback Scripts

- `database/migrations/rollback_consolidate_hr_and_agents.php`
- `scripts/restore-personas.sh`
- `scripts/restore-agents.sh`

---

## 6. Data Integrity Tests

### 6.1 Count Verification

```php
// Pre-migration
$personaCount = DB::table('personas')->count();
$agentCount = DB::table('agents')->count();

// Post-migration
$teamMemberCount = DB::table('team_members')->count();

// Verify: teamMemberCount should equal (personaCount + agentCount) minus any name collisions
```

### 6.2 Relationship Verification

```php
// All foreign keys should still work
$invalidTasks = DB::table('tasks')
    ->whereNotIn('assigned_to', function($query) {
        $query->select('name')->from('team_members');
    })
    ->count();
// Expected: 0
```

### 6.3 Functional Verification

- [ ] All TeamMember records are queryable
- [ ] PersonasIndex Livewire component works
- [ ] AgentList Livewire component works
- [ ] Task assignment still works
- [ ] Parent-child relationships preserved
- [ ] Metrics queryable
- [ ] Workspace files accessible

---

## 7. Known Issues & Edge Cases

### 7.1 Name Collisions

**Scenario:** Both `personas` and `agents` have a record named "dave"

**Resolution:** 
1. Detect during migration
2. Log collision
3. Rename agent record: "dave" → "dave-agent"
4. Or merge if semantically same entity
5. Document in migration log

### 7.2 Missing Related Records

**Scenario:** Persona without metrics record

**Resolution:**
- Create default metrics record during migration
- Log warning

### 7.3 Orphaned Records

**Scenario:** Agent with parent_id pointing to deleted agent

**Resolution:**
- Set parent_id to NULL
- Log warning

---

## 8. Dependencies (Waiting For)

**⚠️ BLOCKED:** Waiting for Dave's TeamMember model design

The migration plan assumes the following (to be confirmed):
- TeamMember model will use UUID primary keys
- TeamMember model will support both persona and agent attributes
- Existing Livewire components will be refactored (not replaced)

**Next Steps:**
1. Review TeamMember model design when available
2. Adjust migration schema accordingly
3. Create actual migration scripts
4. Test on staging environment

---

## 9. Files to Create

### 9.1 Migration Files
- [ ] `database/migrations/2026_03_03_consolidate_hr_and_agents.php`
- [ ] `database/migrations/rollback_consolidate_hr_and_agents.php`

### 9.2 Backup Scripts
- [ ] `scripts/backup-personas-agents.sh`
- [ ] `database/backups/personas-2026-03-03.json`
- [ ] `database/backups/agents-2026-03-03.json`

### 9.3 Restore Scripts
- [ ] `scripts/restore-personas-from-backup.sh`
- [ ] `scripts/restore-agents-from-backup.sh`

### 9.4 Test Scripts
- [ ] `tests/Migration/TeamMemberMigrationTest.php`

### 9.5 Documentation
- [ ] `docs/MIGRATION_RUNBOOK.md` (step-by-step)
- [ ] `docs/ROLLBACK_RUNBOOK.md` (emergency procedures)

---

## 10. Success Criteria

- [ ] Zero data loss
- [ ] All records migrated successfully
- [ ] All foreign key relationships intact
- [ ] All Livewire components functional
- [ ] Rollback tested and working
- [ ] Migration is idempotent (safe to run multiple times)
- [ ] Performance acceptable (< 5 minutes for migration)

---

## Appendix A: Current Record Counts (Pre-Migration)

**Snapshot taken:** 2026-03-03 10:30 EST

| Table | Record Count | Notes |
|-------|--------------|-------|
| `personas` | 6 | All board members (Steven, Gwynne, Warren, Werner, Bozoma, Fidji) |
| `persona_metrics` | 0 | No metrics yet |
| `persona_workspaces` | 0 | No workspace files yet |
| `agents` | 0 | Empty - pending migrations not yet run |

**Total records to migrate:** 6 (all from personas table)

**Note:** Agents table is currently empty because many agent-related migrations are still pending. After those migrations are run, the agent count will need to be re-verified before production migration.

## Appendix B: Migration Log Template

*To be created alongside migration scripts*

---

**Ready for review by Dave and team.**
