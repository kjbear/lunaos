# Task Schema Reconciliation

**Date:** March 2, 2026  
**Issue:** Task model didn't match database schema  
**Resolution Time:** ~15 minutes

---

## The Problem

Phase 2A agents (Dave, Maya, Sam, Alex) all timed out because the **Task model expected a different schema** than what was in the database.

### Symptoms
- Model expected `assigned_to` → Database had `agent_id`
- Model expected `title` → Database had `name`
- Model expected `priority` values: `'low', 'medium', 'high', 'critical'`
- Database had: `'low', 'normal', 'high', 'critical'`
- Model expected `status` values: `'pending', 'in_progress', 'complete', 'failed', 'blocked'`
- Database had: `'pending', 'running', 'completed', 'failed'`
- Missing columns: `step`, `task_type`, `view_mode`, `context_json`, `branch_name`, `pr_url`, `artifacts_json`, `retry_count`, `repository_id`

### Root Cause
Migration `2026_02_26_183000_recreate_tasks_table` was **PENDING** (never ran). The database was stuck on the old schema from Feb 21 (`2026_02_21_234312_create_tasks_table`).

---

## The Fix

### 1. Created New Migration
**File:** `database/migrations/2026_03_02_080000_fix_tasks_schema.php`

Dropped the old `tasks` table completely and recreated with the correct unified schema:

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('assigned_to')->nullable();
    $table->foreignId('repository_id')->nullable()->constrained()->onDelete('set null');
    $table->string('status')->default('pending');  // pending, in_progress, complete, failed, blocked
    $table->string('step')->default('develop');    // develop, qa, security, staging, production
    $table->string('priority')->default('medium'); // low, medium, high, critical
    $table->string('task_type')->default('feature'); // feature, bugfix, refactor, test
    $table->string('view_mode')->default('list');  // list, board, executive
    $table->json('context_json')->nullable();
    $table->string('branch_name')->nullable();
    $table->string('pr_url')->nullable();
    $table->json('artifacts_json')->nullable();
    $table->integer('retry_count')->default(0);
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    
    // Indexes for performance
    $table->index(['status', 'step']);
    $table->index('assigned_to');
    $table->index('view_mode');
    $table->index('created_at');
});
```

### 2. Created Missing `repositories` Table
**File:** `database/migrations/2026_03_02_080500_create_repositories_table.php`

The Task model referenced `Repository` but the table didn't exist:

```php
Schema::create('repositories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('path')->nullable();
    $table->string('url')->nullable();
    $table->string('branch')->default('main');
    $table->json('config_json')->nullable();
    $table->timestamps();
    
    $table->index('name');
});
```

### 3. Fixed Task Model Syntax Error
**File:** `app/Models/Task.php`

- Removed accidental `protected $guarded = [];` from inside `boot()` method
- Added it as a class property instead (outside methods)

---

## Verification

✅ All tests passed:

```bash
# Created test task
Created task ID: 1 - Title: Test Task - Priority: medium - View Mode: list

# Tested model attributes
- Count: 1
- First task: Test Task (assigned to: dave)
- Priority badge: bg-yellow-500/20 text-yellow-400 border-yellow-400/30
- View mode label: List View
- Progress: 20%
```

✅ Scopes working: `assignedTo()`, `inStep()`, `withStatus()`, `available()`, `completedToday()`, `withViewMode()`

✅ Attributes working: `progress_percentage`, `priority_badge_class`, `status_badge_class`, `view_mode_label`, `agent_display_name`

✅ Relationships defined: `agent()`, `repository()`, `activities()`

---

## Lessons Learned

1. **Always check migration status** before assuming schema matches model
   ```bash
   php artisan migrate:status
   ```

2. **Use SQLite schema inspection** to verify actual table structure
   ```bash
   sqlite3 database/database.sqlite ".schema tasks"
   ```

3. **Test model creation** early in agent tasks to catch mismatches
   ```php
   \App\Models\Task::create([...]);
   ```

4. **Don't let agents debug schema issues** — fix foundation first, then let them build

---

## Impact on Phase 2A

**Before Fix:** Agents couldn't proceed, all timed out debugging schema  
**After Fix:** Clean foundation ready for Phase 2A implementation

**Next Steps:**
1. Re-spawn Phase 2A agents (Dave, Maya, Sam, Alex) with 20-minute timeout
2. They can now work with correct schema
3. Task 2A.1 (Unify Task Management) can proceed as planned

---

## Files Changed

- ✅ `database/migrations/2026_03_02_080000_fix_tasks_schema.php` (new)
- ✅ `database/migrations/2026_03_02_080500_create_repositories_table.php` (new)
- ✅ `app/Models/Task.php` (syntax fix)
- ✅ Deleted: `database/migrations/2026_02_21_234312_create_tasks_table.php` (old, conflicting)
- ✅ Deleted: `database/migrations/2026_02_26_174344_create_tasks_table.php` (duplicate stub)

**Git Commit:** `Fix: Reconcile Task model with database schema`

---

**Status:** ✅ COMPLETE  
**Ready for:** Phase 2A Task 2A.1 kickoff (retry)
