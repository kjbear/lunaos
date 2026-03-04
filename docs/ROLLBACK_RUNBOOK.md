# Emergency Rollback Runbook

**Rollback:** Team Module Consolidation → Separate HR + Agents Tables  
**Risk Level:** CRITICAL  
**Time to Execute:** 10-15 minutes  

---

## When to Rollback

**Trigger rollback IMMEDIATELY if:**

- ❌ Data loss detected (missing records)
- ❌ Foreign key constraint violations
- ❌ Critical Livewire components non-functional
- ❌ Application errors impacting core functionality
- ❌ Cannot resolve critical issues within 30 minutes

**Do NOT rollback for:**

- ✅ Minor UI glitches
- ✅ Cosmetic issues
- ✅ Issues already resolved

---

## Pre-Rollback Checklist (2 minutes)

- [ ] **Decision documented** - Who decided to rollback and why
- [ ] **Stakeholders notified** - Team aware of rollback
- [ ] **Current state backed up** - Even failed migration should be preserved

---

## Rollback Execution

### Step 1: Enter Maintenance Mode (1 minute)

```bash
cd /path/to/lunaos

# Put application in maintenance mode
php artisan down --message="Rolling back recent changes..."
```

---

### Step 2: Run Rollback Script (10 minutes)

```bash
# Run the restore script
./scripts/restore-hr-and-agents.sh

# When prompted, type 'RESTORE' to confirm
# This will:
# - Drop team_members and related tables
# - Restore personas and agents from backup
# - Verify restoration
```

**Script actions:**
1. Finds latest backup automatically
2. Drops `team_members`, `team_member_metrics`, `team_member_workspaces`
3. Restores `personas`, `persona_metrics`, `persona_workspaces`
4. Restores `agents`, `agent_updates`, `agent_conversations`, `agent_activities`
5. Verifies record counts

**Expected output:**
```
✓ Table restored: personas
✓ Table restored: persona_metrics
✓ Table restored: persona_workspaces
✓ Table restored: agents
✓ Table restored: agent_updates
✓ Table restored: agent_conversations
✓ Table restored: agent_activities
```

**If script fails:**
- Check error message
- Manual restore option: See "Manual Rollback" below

---

### Step 3: Clear Caches (2 minutes)

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# If using OPcache
php -r "opcache_reset();"
```

---

### Step 4: Verify Restoration (3 minutes)

```bash
php artisan tinker

// Verify record counts
>>> $personaCount = DB::table('personas')->count();
>>> $agentCount = DB::table('agents')->count();
>>> echo "Personas: {$personaCount}, Agents: {$agentCount}";

// Should match pre-backup counts
// Compare with: database/backups/backup-manifest-[timestamp].json

// Verify relationships
>>> $invalidTasks = DB::table('tasks')
    ->whereNotIn('assigned_to', function($q) { 
        $q->select('name')->from('agents'); 
    })
    ->count();
>>> echo "Invalid task references: {$invalidTasks}"; // Should be 0

// Quick smoke test
>>> DB::table('personas')->first();
>>> DB::table('agents')->first();
```

---

### Step 5: Test Core Functionality (5 minutes)

**Quick smoke tests:**

1. **Personas Page**
   - [ ] Navigate to HR → Personas
   - [ ] Verify personas list loads
   - [ ] Check persona count matches expectations

2. **Agents Page**
   - [ ] Navigate to Agents
   - [ ] Verify agents list loads
   - [ ] Check agent count matches expectations

3. **Task Assignment**
   - [ ] Create test task
   - [ ] Verify agent names appear in assignee dropdown

---

### Step 6: Bring Application Back Online (1 minute)

```bash
# Bring application back up
php artisan up

# Send notification to team
php artisan notify "Rollback completed successfully. Application back online."
```

---

## Manual Rollback (If Script Fails)

If the automated rollback script fails, perform manual rollback:

### Manual Step 1: Drop New Tables

```sql
-- Connect to database
mysql -u [user] -p [database]

-- Drop consolidated tables
DROP TABLE IF EXISTS team_member_workspaces;
DROP TABLE IF EXISTS team_member_metrics;
DROP TABLE IF EXISTS team_members;
```

### Manual Step 2: Restore from JSON Backup

```php
// In php artisan tinker

// Restore personas
$personas = json_decode(file_get_contents('database/backups/personas-[timestamp].json'), true);
foreach ($personas as $persona) {
    DB::table('personas')->insert((array) $persona);
}

// Restore agents
$agents = json_decode(file_get_contents('database/backups/agents-[timestamp].json'), true);
foreach ($agents as $agent) {
    DB::table('agents')->insert((array) $agent);
}

// Repeat for related tables...
```

### Manual Step 3: Restore Migrations Table

```php
// Remove the consolidation migration from migrations table
DB::table('migrations')->where('migration', 'like', '%consolidate_hr_and_agents%')->delete();
```

---

## Post-Rollback Actions

### Immediate

- [ ] Monitor application for 2 hours
- [ ] Check error logs
- [ ] Verify no new issues introduced
- [ ] Notify stakeholders rollback complete

### Short-term (24 hours)

- [ ] Document rollback reason
- [ ] Create incident report
- [ ] Review what went wrong
- [ ] Plan fix for original issue

### Long-term

- [ ] Address root cause
- [ ] Update migration strategy
- [ ] Test fix in staging
- [ ] Re-attempt migration when ready

---

## Rollback Log

**Rollback initiated at:** [TIME]  
**Rollback initiated by:** [NAME]  
**Reason:** [DESCRIPTION]

**Completed at:** [TIME]  
**Completed by:** [NAME]  
**Status:** ✅ SUCCESS / ❌ FAILED

**Notes:**
```
[Add any issues encountered during rollback]
```

---

## Emergency Contacts

- **DevOps Lead:** [Name] - [Phone]
- **Database Admin:** [Name] - [Phone]  
- **Application Owner:** [Name] - [Phone]

---

## Backup Verification

After rollback, verify backup files exist:

```bash
ls -lh database/backups/backup-manifest-*.json
ls -lh database/backups/personas-*.json
ls -lh database/backups/agents-*.json
```

**Keep backups for 30 days minimum** before archiving or deleting.
