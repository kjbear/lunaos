# Phase 2 Merged Tasks - Architect Review
**Date:** March 6, 2026  
**Reviewer:** Leo (Chief Architect)  
**Scope:** System Architecture & Database Design

---

## Executive Summary

The merged task list addresses the **6 critical issues** from March 5 review. Migration files are well-structured and follow Laravel best practices. However, there are **2 critical gaps** and **3 high-priority concerns** that must be addressed before Phase 2B.

---

## ✅ What's Architecturally Sound

### 1. **March 5 Issues - All 6 Addressed**

| Issue | Status | Migration | Verified |
|-------|--------|-----------|----------|
| Orphaned Tasks (no project_id FK) | ✅ Fixed | `2026_03_05_204121_add_project_linkage_to_tables.php` | ✅ FK + cascade |
| Wrong Agent Table (personas vs agents) | ✅ Fixed | `2026_03_05_204130_fix_project_assignments_to_use_agents.php` | ✅ agent_id FK added |
| Repository Disconnected | ✅ Fixed | `2026_03_05_204130_add_repository_link_to_projects.php` | ✅ repository_id FK |
| 8 Missing Required Fields | ✅ Fixed | `2026_03_05_204130_add_missing_project_fields.php` | ✅ All fields present |
| Requirements Duplication | ⚠️ Partial | Both tables exist | Decision needed |
| GitHub Auto-Creation (P2) | 📋 Planned | P1.2 API endpoints | Not started |

### 2. **Foreign Key Design**
- ✅ All FKs use UUIDs consistently (`projects.id`, `agents.id`, `repositories.id`)
- ✅ Cascade deletes configured appropriately (`onDelete('cascade')` for project→tasks)
- ✅ `set null` used where data preservation matters (`requirement_id`, `repository_id`)

### 3. **Indexing Strategy**
- ✅ Composite indexes on high-query fields (`project_id, status`)
- ✅ FK columns indexed for join performance
- ✅ Good coverage on `project_issues` table for severity/status queries

### 4. **Migration Quality**
- ✅ Proper `up()` / `down()` rollback support
- ✅ SQLite compatibility considered (rare, appreciated)
- ✅ Comments explain field purpose

---

## ⚠️ Concerns (By Severity)

### 🔴 CRITICAL: Missing GitHub Integration Schema

**Issue:** No schema for GitHub repository auto-creation workflow (P0.6 missing)

**Impact:** Cannot implement GitHub auto-creation without:
- `repositories` table linkage (✅ exists via `repository_id`)
- GitHub app credentials storage (❌ **missing**)
- Repository creation audit log (❌ **missing**)
- Webhook secret storage (❌ **missing**)

**Gap:** The migration adds `repository_id` FK, but there's no schema for:
- GitHub app installation per project
- OAuth token storage (should be encrypted)
- Webhook configuration
- Repository creation state machine

**Recommendation:** Add P0.6 migration:
```php
Schema::table('repositories', function (Blueprint $table) {
    $table->string('github_app_installation_id')->nullable();
    $table->string('github_webhook_secret')->nullable(); // encrypted
    $table->enum('creation_status', ['pending', 'created', 'failed'])->default('pending');
    $table->timestamp('github_created_at')->nullable();
});
```

---

### 🔴 CRITICAL: Requirements Duplication - Decision Required

**Issue:** Both `requirements` table AND `project_artifacts` (type='requirement') exist

**Current State:**
- `requirements` table (Feb 26) - normalized, proper FK, dedicated fields
- `project_artifacts` table (Mar 5) - polymorphic, stores requirements as one type

**Architectural Recommendation:** **KEEP `requirements` TABLE**

**Rationale:**
1. **Better normalization** - requirements have lifecycle (draft → approved → implemented)
2. **Dedicated fields** - `priority`, `status`, `approved_by`, `approved_at`
3. **Easier queries** - "Show all approved requirements for project X"
4. **Separation of concerns** - artifacts for unstructured docs, requirements for structured specs

**Action:** Migrate any `project_artifacts` where `type='requirement'` to `requirements` table, then restrict `project_artifacts.type` to exclude 'requirement'.

---

### 🟠 HIGH: project_assignments Table Confusion

**Issue:** March 5 migration adds `agent_id`, but `persona_id` column still exists

**Current Schema (post-migration):**
```sql
project_assignments:
  - id
  - project_id → projects(id) ✅
  - persona_id → personas(id) ❌ (deprecated but still present)
  - agent_id → agents(id) ✅ (new)
  - role
  - assigned_at
```

**Risk:** 
- Application code may still use `persona_id`
- Data integrity issues if both columns populated
- No data migration to move `persona_id` → `agent_id`

**Recommendation:**
1. **Add data migration** (P0.2b):
   ```php
   // Migrate existing persona_id assignments to agent_id
   DB::table('project_assignments')
     ->whereNotNull('persona_id')
     ->each(function ($assignment) {
         $persona = DB::table('personas')->find($assignment->persona_id);
         if ($persona && $persona->agent_id) {
             DB::table('project_assignments')
               ->where('id', $assignment->id)
               ->update(['agent_id' => $persona->agent_id]);
         }
     });
   ```
2. **Document deprecation** - add comment to migration
3. **Update all queries** to use `agent_id` only

---

### 🟠 HIGH: Missing `percent_complete` Calculation Logic

**Issue:** Migration adds `percent_complete` field (decimal 5,2), but no mechanism to update it

**Current State:**
- Field exists: ✅
- Auto-calculation: ❌ **missing**
- Manual override: ❌ **not specified**

**Recommendation:** Implement as **Eloquent mutator + event listener**:

```php
// In Project model
public function recalculatePercentComplete(): void
{
    $totalTasks = $this->tasks()->count();
    if ($totalTasks === 0) {
        $this->percent_complete = 0;
    } else {
        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        $this->percent_complete = ($completedTasks / $totalTasks) * 100;
    }
    $this->save();
}

// Listen for Task status changes
Task::saved(function ($task) {
    if ($task->project) {
        $task->project->recalculatePercentComplete();
    }
});
```

**Decision Needed:** Allow manual override? (Recommendation: **Yes, with audit log**)

---

### 🟡 MEDIUM: project_issues Table - GitHub vs Internal Issues

**Issue:** `project_issues` table created, but unclear if this is for:
- Internal project health issues (e.g., "missing documentation")
- GitHub issues sync (e.g., "Issue #42 from repo")
- Both (polymorphic)

**Current Schema (from migration):**
```php
$table->uuid('task_id')->nullable();
// No github_issue_id field
// No external_source tracking
```

**Clarification Needed:**
- If GitHub sync → need `github_issue_id`, `github_node_id`, `synced_at`
- If internal only → rename to `project_health_issues`
- If both → make polymorphic (`issueable_type`, `issueable_id`)

**Recommendation:** Create **separate tables**:
- `project_health_issues` (internal)
- `github_issues_sync` (external, synced from GitHub API)

---

### 🟡 MEDIUM: No Index on `requirements.project_id`

**Issue:** `requirements` table created with FK to `projects`, but no index on `project_id`

**Current Schema (Feb 26):**
```php
$table->string('project_id', 36);
$table->foreign('project_id')->references('id')->on('projects');
// ❌ Missing: $table->index('project_id');
```

**Impact:** Query performance for "get all requirements for project X" will be slow at scale

**Recommendation:** Add migration:
```php
Schema::table('requirements', function (Blueprint $table) {
    $table->index('project_id');
});
```

---

## 💡 Architectural Recommendations

### 1. **Consolidate Requirements Storage** (DECISION NEEDED - Kyle)

**Option A: Keep `requirements` table** (Recommended ✅)
- Better normalization
- Dedicated fields for requirement lifecycle
- Easier reporting/queries

**Option B: Migrate to `project_artifacts` only**
- Simpler schema (fewer tables)
- More flexible (any artifact type)
- Harder to query/validate requirements

**Decision:** Option A → migrate artifacts→requirements, restrict artifacts.type

---

### 2. **Add GitHub Integration Schema** (P0.6)

**Fields needed in `repositories` table:**
```sql
- github_app_installation_id (string, nullable)
- github_webhook_secret (string, encrypted, nullable)
- github_token (string, encrypted, nullable)  -- if using PAT
- creation_status (enum: pending/created/failed)
- github_created_at (timestamp, nullable)
- last_synced_at (timestamp, nullable)
```

---

### 3. **Implement Soft Deletes Consistently**

**Observation:** 
- `projects` table: `archived_at` field (manual soft delete)
- `board_sessions` table: soft deletes (Laravel trait)
- `tasks` table: no soft delete mentioned

**Recommendation:** Standardize on **Laravel soft deletes** for all major entities:
```php
use SoftDeletes;

Schema::table('projects', function (Blueprint $table) {
    $table->softDeletes(); // adds deleted_at
    $table->dropColumn('archived_at'); // or keep both?
});
```

**Benefits:**
- Automatic query scoping (`Task::all()` excludes deleted)
- Restore capability
- Consistent pattern across modules

---

### 4. **Add Audit Logging for Critical Changes**

**Recommendation:** Create `project_audit_log` table:

```php
Schema::create('project_audit_log', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('project_id');
    $table->foreign('project_id')->references('id')->on('projects');
    
    $table->string('action'); // 'status_change', 'percent_override', etc.
    $table->string('field_changed')->nullable();
    $table->json('old_value')->nullable();
    $table->json('new_value')->nullable();
    
    $table->uuid('changed_by_id')->nullable(); // agent_id
    $table->string('changed_by_type'); // 'agent' | 'human'
    
    $table->timestamps();
    
    $table->index(['project_id', 'created_at']);
});
```

**Use Cases:**
- Manual `percent_complete` override
- Status changes
- Agent assignment changes

---

### 5. **Consider Composite Primary Keys for Junction Tables**

**Current:** `project_assignments` uses auto-increment `id`

**Recommendation:** For true junction tables, consider composite PK:
```php
Schema::create('project_assignments', function (Blueprint $table) {
    $table->uuid('project_id');
    $table->uuid('agent_id');
    $table->primary(['project_id', 'agent_id']);
    // ... other fields
});
```

**Pros:** Prevents duplicate assignments, more semantic
**Cons:** Harder to reference in foreign keys (e.g., audit log)

**Decision:** Keep `id` for flexibility, add unique constraint:
```php
$table->unique(['project_id', 'agent_id', 'role']);
```

---

## 🔧 Specific Schema/API Suggestions

### 1. **Update Requirements Table Index**
```bash
# Add migration
php artisan make:migration add_index_to_requirements_project_id
```

```php
Schema::table('requirements', function (Blueprint $table) {
    $table->index('project_id');
    $table->index(['project_id', 'status']);
});
```

---

### 2. **GitHub Integration Fields Migration**
```bash
php artisan make:migration add_github_fields_to_repositories
```

```php
Schema::table('repositories', function (Blueprint $table) {
    $table->string('github_app_installation_id')->nullable()->after('id');
    $table->string('github_webhook_secret')->nullable()->after('github_app_installation_id');
    $table->enum('creation_status', ['pending', 'created', 'failed'])->default('pending');
    $table->timestamp('github_created_at')->nullable();
    $table->timestamp('last_synced_at')->nullable();
    
    $table->index('github_app_installation_id');
    $table->index('creation_status');
});
```

---

### 3. **Project Model Methods (Suggested)**

```php
class Project extends Model
{
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
    
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'project_assignments')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'repository_id');
    }
    
    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }
    
    public function issues(): HasMany
    {
        return $this->hasMany(ProjectIssue::class);
    }
    
    public function recalculatePercentComplete(): void
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->where('status', 'completed')->count();
        $this->percent_complete = $total > 0 ? ($completed / $total) * 100 : 0;
        $this->save();
    }
    
    public function createGithubRepo(array $config): Repository
    {
        // Call GitHub API
        // Create repo
        // Update creation_status
        // Return Repository model
    }
}
```

---

## 📋 Testing Recommendations

### 1. **Database Integrity Tests**
```bash
# Create test that verifies cascade delete
php artisan make:test ProjectCascadeDeleteTest
```

**Test Cases:**
- Delete project → verify tasks deleted (cascade)
- Delete project → verify requirements deleted (cascade)
- Delete agent → verify project assignments set null (not cascade)
- Delete repository → verify projects remain (set null)

---

### 2. **API Contract Tests**
```bash
php artisan make:test ProjectApiContractTest
```

**Endpoints to Test:**
- `PUT /api/projects/{id}` - all new fields
- `POST /api/projects/{id}/assign-agent`
- `DELETE /api/projects/{id}` - soft delete
- `GET /api/projects/{id}/tasks` - relationship query

---

### 3. **Data Migration Tests**
```bash
php artisan make:test ProjectAssignmentsMigrationTest
```

**Verify:**
- `persona_id` → `agent_id` migration successful
- No data loss in migration
- FK constraints enforced post-migration

---

## 🎯 Priority Adjustments

Based on review, recommend **re-prioritizing** as follows:

### P0 (BLOCKING) - Add These:
- [ ] **P0.6: Add GitHub integration fields to repositories**
- [ ] **P0.7: Add index to requirements.project_id**
- [ ] **P0.8: Data migration persona_id → agent_id**

### P1 (HIGH) - Clarify:
- [ ] **P1.1: Clarify project_issues purpose** (internal vs GitHub sync)
- [ ] **P1.4: Implement percent_complete auto-calculation** (model method + events)

### P2 (MEDIUM) - Defer:
- Current P2.1-P2.4 are fine as-is

---

## ✅ Final Verdict

**Foundation Status:** 🟠 **Mostly Solid - 2 Critical Gaps**

**The Good:**
- All 6 March 5 issues addressed
- Migrations well-structured
- FK + indexing design solid
- Cascade logic appropriate

**The Gaps:**
- GitHub integration schema incomplete
- Requirements duplication decision unresolved
- project_assignments data migration needed
- percent_complete logic missing

**Recommendation:** **Proceed with P0 fixes, but add the 3 missing migrations above before Phase 2B.**

---

## 🔔 Message to Main Session

Leo → Main: Review complete. See full report at `lunaos/docs/ARCHITECT-REVIEW-PHASE2-MERGED.md`

**TL;DR:**
- ✅ 6/6 March 5 issues addressed
- 🔴 **2 critical gaps:** GitHub integration schema, requirements duplication decision
- 🟠 **2 high-priority:** project_assignments migration, percent_complete logic
- 🟡 **3 medium:** requirements index, project_issues clarity, soft delete consistency

**Action Required:** Kyle needs to decide on requirements storage (keep table vs artifacts-only). Recommendation: **keep requirements table**.

**Proceed?** Yes, but add P0.6-P0.8 migrations before Phase 2B.

---

**Reviewed by:** Leo (Chief Architect)  
**Date:** March 6, 2026  
**Next Review:** March 8 (after P0 verification)
