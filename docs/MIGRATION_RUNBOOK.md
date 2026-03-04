# Migration Runbook: HR + Agents → Team Module

**Migration:** Consolidate HR Personas and Agents into Team Members  
**Date:** 2026-03-03  
**Estimated Duration:** 15-30 minutes  
**Risk Level:** HIGH (requires maintenance window)  
**Critical Requirement:** ZERO DATA LOSS

---

## Pre-Migration Checklist

### Environment Preparation

- [ ] **Schedule maintenance window** (recommended: off-peak hours, low traffic)
- [ ] **Notify stakeholders** (team leads, active users, Sam for Dusk tests)
- [ ] **Verify staging migration** completed successfully ✅ TESTED
- [ ] **Verify rollback tested** on staging ✅ TESTED
- [ ] **Confirm TeamMember model** is finalized and deployed
- [ ] **Backup production database** (full database dump)
- [ ] **Dave coordination:** Confirm migration file is ready

### Technical Prerequisites

- [ ] SSH access to production server
- [ ] Database credentials (read/write)
- [ ] Laravel artisan CLI access
- [ ] Backup storage location available (external backup recommended)
- [ ] `jq` installed for JSON validation
- [ ] Scripts deployed and executable:
  - `./scripts/backup-team-data.sh`
  - `./scripts/restore-team-data.sh`

### Code Deployment

- [ ] Migration file deployed: `database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php`
- [ ] Backup scripts deployed and tested
- [ ] Rollback scripts deployed and tested
- [ ] TeamMember model deployed
- [ ] Livewire components updated (PersonasIndex, AgentList → TeamMembers)

---

## Migration Execution

### Step 1: Pre-Migration Backup (5 minutes) ⚠️ CRITICAL

```bash
# Navigate to project root
cd /path/to/lunaos

# Run backup script
./scripts/backup-team-data.sh

# Verify backup files created
ls -lh database/backups/

# Verify backup manifest
cat database/backups/backup-manifest-*.json | jq
```

**The backup script will:**
- Export `personas` table to JSON
- Export `persona_metrics` table to JSON
- Export `persona_workspaces` table to JSON
- Export `agents` table to JSON
- Export `agent_updates` table to JSON
- Create backup manifest with record counts
- Generate SHA-256 checksums for integrity verification

**Expected output:**
```
✓ Backup created: personas-2026-03-03_12-00-00.json (4.0K) - 6 records
✓ JSON validation passed
✓ Manifest created: backup-manifest-2026-03-03_12-00-00.json
✓ Checksums created: backup-checksums-2026-03-03_12-00-00.txt
```

**Verify backup integrity:**
```bash
# Verify checksums
cd database/backups
shasum -a 256 -c backup-checksums-*.txt

# Verify JSON is valid
jq empty personas-*.json && echo "Valid JSON"
```

**If backup fails:**
- ❌ **STOP** - Do not proceed with migration
- ❌ Investigate backup failure
- ✅ Resolve before continuing
- ✅ Re-run backup script

---

### Step 2: Run Migration (10-15 minutes)

```bash
# Navigate to project root
cd /path/to/lunaos

# Put application in maintenance mode
php artisan down --message="Performing scheduled maintenance..." --retry=60

# Run the consolidation migration
php artisan migrate --path=database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php

# Check migration status
php artisan migrate:status
```

**What the migration does:**
1. Creates `team_members` table with unified schema
2. Migrates all personas → team_members (type='board-members' or 'personas')
3. Migrates all agents → team_members (type='workers')
4. Handles name collisions (appends '-agent' suffix)
5. Preserves all metadata in `metadata_json` field
6. Archives old tables (renames to `*_archive`)
7. Logs migration details

**Expected output:**
```
INFO  Running migrations.
  2026_03_03_100000_consolidate_hr_and_agents_to_team ........... 12.07ms DONE
```

**If migration fails:**
- Check error message carefully
- If mid-migration failure: **initiate rollback immediately**
- Review logs: `storage/logs/laravel.log`
- Check database state: `php artisan tinker`

---

### Step 3: Verify Data Integrity (5 minutes) ✅ TESTED ON STAGING

```bash
# Count verification
php artisan tinker

>>> // Pre/post migration counts
>>> $oldPersonaCount = DB::table('personas_archive')->count();
>>> $oldAgentCount = DB::table('agents_archive')->count();
>>> $newTeamCount = DB::table('team_members')->count();
>>> echo "Old: {$oldPersonaCount} personas + {$oldAgentCount} agents" . PHP_EOL;
>>> echo "New: {$newTeamCount} team members" . PHP_EOL;

>>> // Verify all records migrated
>>> if ($newTeamCount === ($oldPersonaCount + $oldAgentCount)) {
...     echo "✓ Record counts match!" . PHP_EOL;
... } else {
...     echo "⚠ Record count mismatch!" . PHP_EOL;
... }

>>> // Check for name collisions
>>> DB::table('team_members')
    ->select('name', DB::raw('COUNT(*) as count'))
    ->groupBy('name')
    ->having('count', '>', 1)
    ->get();
// Expected: empty collection (no duplicates)

>>> // Verify metadata preserved
>>> DB::table('team_members')->select('name', 'metadata_json')->limit(3)->get();
```

**Expected results:**
- ✅ `team_members` count = `personas` + `agents` count
- ✅ Zero duplicate names
- ✅ All metadata_json fields contain migration info
- ✅ All archived tables exist with original data

---

### Step 4: Test Livewire Components (10 minutes)

```bash
# Bring application back up
php artisan up
```

**Manual Browser Tests:**

1. **Team Page** (`/team`)
   - [ ] Page loads without errors
   - [ ] All tabs display (Board Members, Personas, Workers)
   - [ ] Correct members in each tab
   - [ ] Search/filter works
   - [ ] Edit modal functions
   - [ ] Create new member works

2. **Task Assignment**
   - [ ] Task list shows team members as assignees
   - [ ] Can assign task to former persona
   - [ ] Can assign task to former agent
   - [ ] Assignee dropdown shows all team members

3. **Existing Functionality**
   - [ ] Board member workflows still work
   - [ ] Subagent workflows still work
   - [ ] Worker agent workflows still work
   - [ ] Parent-child relationships intact (if any)

**If tests fail:**
- Document exact error and screenshot
- Check browser console for JavaScript errors
- Review Laravel logs: `storage/logs/laravel.log`
- If critical: **initiate rollback immediately**

---

### Step 5: Post-Migration Cleanup (2 minutes)

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optional: Optimize for production
php artisan optimize
```

**Keep archive tables for 30 days:**
- `personas_archive`
- `persona_metrics_archive`
- `persona_workspaces_archive`
- `agents_archive` (when it exists)

**After 30 days (if no issues):**
```bash
# Drop archive tables
php artisan tinker
>>> Schema::dropIfExists('personas_archive');
>>> Schema::dropIfExists('persona_metrics_archive');
>>> Schema::dropIfExists('persona_workspaces_archive');
>>> Schema::dropIfExists('agents_archive');
```

---

## Post-Migration Verification

### Immediate (Same Day)

- [ ] Monitor error logs for 2 hours
- [ ] Check Sentry/monitoring for new errors
- [ ] Verify scheduled jobs still run
- [ ] Verify agent workflows (if active)
- [ ] **Sam:** Run Dusk tests

### Short-term (24-48 hours)

- [ ] No increase in error rate
- [ ] User feedback positive
- [ ] All Livewire components stable
- [ ] Performance metrics acceptable

### Long-term (7-30 days)

- [ ] No data inconsistencies reported
- [ ] Performance acceptable (< 5 min migration time)
- [ ] Archive tables can be dropped after 30 days

---

## Rollback Decision Matrix

| Issue | Severity | Decision | Time Limit |
|-------|----------|----------|------------|
| Data loss detected | CRITICAL | **Immediate rollback** | < 5 min |
| Foreign key errors | CRITICAL | **Immediate rollback** | < 5 min |
| Livewire components broken | HIGH | Attempt fix → Rollback | 30 min |
| Performance degradation | MEDIUM | Investigate, fix | 1 hour |
| Minor UI issues | LOW | Log issue, fix later | Next sprint |

---

## Emergency Contacts

- **DevOps (Chen):** [Contact info]
- **Backend Lead (Dave):** [Contact info]
- **QA Lead (Sam):** [Contact info]
- **Application Owner:** [Contact info]

---

## Emergency Rollback Procedure ⚠️

**USE THIS IF:** Data loss, foreign key errors, or critical breakage

### Option 1: Script Rollback (Recommended)

```bash
# Navigate to project root
cd /path/to/lunaos

# Run rollback script (will ask for confirmation)
./scripts/restore-team-data.sh

# Or skip confirmation:
./scripts/restore-team-data.sh --force
```

**The restore script will:**
1. Drop `team_members` table
2. Drop `team_member_metrics` table (if exists)
3. Drop `team_member_workspaces` table (if exists)
4. Restore `personas` table from backup
5. Restore `persona_metrics` table from backup
6. Restore `persona_workspaces` table from backup
7. Restore `agents` table from backup (if backed up)
8. Verify restoration

**Expected output:**
```
✓ Table restored: personas
✓ Table restored: persona_metrics
Verification complete!
  personas: 6 records
  persona_metrics: 0 records
Rollback complete!
```

### Option 2: Migration Rollback (Development Only)

```bash
# ⚠️ WARNING: Only use in development!
# In production, use the restore script above

php artisan migrate:rollback --step=1
```

### Verify Rollback Success

```bash
php artisan tinker

>>> DB::table('personas')->count();
// Should match pre-migration count

>>> DB::table('team_members')->count();
// Should be 0 or table should not exist

>>> DB::table('agents')->count();
// Should match pre-migration count
```

### Post-Rollback

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear

# Bring application up
php artisan up

# Notify stakeholders of rollback
# Investigate root cause before re-attempting migration
```

---

## Appendix A: Tested Procedures

### Staging Test Results (2026-03-03)

✅ **Backup Test:**
- Script: `./scripts/backup-team-data.sh`
- Result: SUCCESS - 6 personas backed up
- Files: 7 backup files + manifest + checksums

✅ **Migration Test:**
- Command: `php artisan migrate --database=sqlite-staging`
- Result: SUCCESS - 12.07ms
- Records: 6 personas migrated to team_members

✅ **Rollback Test (Migration):**
- Command: `php artisan migrate:rollback --database=sqlite-staging`
- Result: SUCCESS - 19.99ms
- Verification: personas table restored with 6 records

✅ **Rollback Test (Script):**
- Command: `./scripts/restore-team-data.sh --force`
- Result: SUCCESS
- Verification: All tables restored, counts match

---

## Appendix B: Pre-Migration Snapshot

**Take this snapshot before migration:**

```bash
php artisan tinker --execute="
echo '=== PRE-MIGRATION SNAPSHOT ===' . PHP_EOL;
echo 'Personas: ' . DB::table('personas')->count() . PHP_EOL;
echo 'Persona Metrics: ' . DB::table('persona_metrics')->count() . PHP_EOL;
echo 'Persona Workspaces: ' . DB::table('persona_workspaces')->count() . PHP_EOL;
echo 'Agents: ' . DB::table('agents')->count() . PHP_EOL;
echo 'Agent Updates: ' . DB::table('agent_updates')->count() . PHP_EOL;

// List all personas
echo PHP_EOL . 'Personas:' . PHP_EOL;
DB::table('personas')->select('id', 'name', 'role', 'status')->get()->each(function(\$p) {
    echo '  - ' . \$p->name . ' (' . \$p->role . '/' . \$p->status . ')' . PHP_EOL;
});
"
```

---

## Sign-Off

- [ ] Backup completed successfully ✅
- [ ] Migration executed without errors ✅
- [ ] Data integrity verified ✅
- [ ] Livewire components tested ✅
- [ ] Post-migration monitoring established ✅
- [ ] Stakeholders notified of completion ✅
- [ ] Dusk tests pass (Sam) ⏳

**Migration completed at:** [TIME]  
**Migration completed by:** [NAME]  
**Status:** ✅ SUCCESS / ❌ FAILED → ROLLED BACK

---

**Last Updated:** 2026-03-03 12:15 EST  
**Tested By:** Chen (DevOps/Data Specialist)  
**Test Environment:** SQLite Staging Database
