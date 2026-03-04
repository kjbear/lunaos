# 🔒 Migration Safety Guide

## Overview

This guide documents the safety measures implemented to prevent accidental data loss during database migrations.

**Problem:** On March 3, 2026, a `migrate:fresh` command accidentally wiped the production database, deleting all 11 team members.

**Solution:** Multi-layer safety guards with staging environment, automated checks, and mandatory confirmations.

---

## 🛡️ Safety Layers

### Layer 1: Staging Environment

**Purpose:** Safe space for testing migrations without risk to production data.

**Location:** `/Users/kobear/.openclaw/workspace/lunaos-staging`

**Configuration:**
```env
APP_ENV=staging
APP_URL=http://lunaos-staging.test
DB_DATABASE=.../lunaos-staging/database/database-staging.sqlite
```

**Workflow:**
```bash
# 1. Test migration on staging
cd lunaos-staging/
php artisan migrate:fresh  # Safe to wipe!

# 2. Verify it works
php artisan tinker
>>> TeamMember::count()

# 3. If successful, apply to production
cd ../lunaos/
./scripts/safe-migrate.sh  # Runs safety checks first
```

**Setup Status:** ✅ Complete (March 3, 2026 - 8:18 PM)

---

### Layer 2: AppServiceProvider Guards

**File:** `app/Providers/AppServiceProvider.php`

**Guards Implemented:**

#### Guard A: Block Destructive Commands
```php
// Blocks in production:
php artisan migrate:fresh    ← 403 Forbidden
php artisan migrate:reset    ← 403 Forbidden
php artisan db:wipe          ← 403 Forbidden
```

**Exception:** `migrate:fresh` allowed in staging with `--force` flag

**Error Message:**
```
🚫 Destructive command "migrate:fresh" is disabled in production environment.
Use staging environment for testing: php copy-to-staging.sh && cd lunaos-staging
```

#### Guard B: Pre-Migration Data Check
Before running any migration in production:

1. ✅ Checks if `team_members` table has data
2. ✅ Verifies backup directory exists
3. ✅ Finds latest backup manifest
4. ✅ Validates backup age (< 24 hours)
5. ❌ Aborts if any check fails

**Error Examples:**
```
🚫 Backup directory not found! Run: ./scripts/backup-team-data.sh

🚫 No backup found! Run: ./scripts/backup-team-data.sh

🚫 Backup is older than 24 hours (age: 48h). Run fresh backup: ./scripts/backup-team-data.sh
```

---

### Layer 3: Safe Migration Script

**File:** `scripts/safe-migrate.sh`

**Usage:**
```bash
# Interactive mode (recommended)
./scripts/safe-migrate.sh

# Check-only mode (verify safety without migrating)
./scripts/safe-migrate.sh --check-only
```

**Safety Checks Performed:**

| Check | Description | Fail Action |
|-------|-------------|-------------|
| **Environment** | Detects production vs staging | Warns, requires confirm |
| **Database** | Verifies .env and DB file | Aborts if missing |
| **Data Exists** | Counts team_members records | Triggers backup check |
| **Backup Exists** | Finds latest backup manifest | Aborts if none found |
| **Backup Age** | Validates < 24 hours old | Aborts if too old |
| **Confirmation** | Requires explicit "yes" | Aborts if declined |
| **Production Confirm** | Double-confirm for production | Requires "I accept the risk" |

**Sample Output:**
```
╔════════════════════════════════════════════════════════╗
║     LunaOS Pre-Migration Safety Check                  ║
╚════════════════════════════════════════════════════════╝

✓ Checking environment...
  Environment: production
  
✓ Checking database...
  Database: /path/to/database.sqlite (2.1M)
  
✓ Checking for existing data...
  Team members: 11
  ⚠ Data exists in database
  
✓ Checking for recent backup...
  Latest backup: backup-manifest-2026-03-03_18-58-06.json
  Backup age: 2h ago
  ✓ Recent backup found

╔════════════════════════════════════════════════════════╗
║  WARNING: Database contains data                       ║
╚════════════════════════════════════════════════════════╝

Before running migrations, ensure you have:
  ✓ Recent backup (verified above)
  ✓ Tested migration on staging (recommended)
  ✓ Read the migration runbook: docs/MIGRATION_RUNBOOK.md
  ✓ Reviewed rollback procedure: docs/ROLLBACK_RUNBOOK.md

Proceed with migration? (type 'yes' to confirm): yes

╔════════════════════════════════════════════════════════╗
║  ✓ All safety checks passed                            ║
╚════════════════════════════════════════════════════════╝

Running migrations...
✓ Migration completed successfully!
```

---

### Layer 4: Web Middleware

**File:** `app/Http/Middleware/PreventDestructiveOperations.php`

**Purpose:** Block destructive operations via web interface or API calls.

**Blocked Routes:**
- `*/migrate/fresh*`
- `*/migrate/reset*`
- `*/db/wipe*`
- `*/database/truncate*`

**Response:**
```json
{
  "error": "Destructive operation blocked",
  "message": "This operation is disabled in production environment. Use staging for testing.",
  "hint": "Set up staging environment: cp -r lunaos lunaos-staging && cd lunaos-staging"
}
```

**Status:** ⚠️ Written but not yet registered in kernel (see TODO below)

---

## 📋 Migration Workflow

### Standard Migration (Recommended)

```bash
# Step 1: Test on staging
cd lunaos-staging/
git pull origin main  # Get latest code
php artisan migrate:fresh  # Safe to wipe
# ... verify everything works ...

# Step 2: Backup production
cd ../lunaos/
./scripts/backup-team-data.sh

# Step 3: Run safety check
./scripts/safe-migrate.sh --check-only

# Step 4: Migrate production
./scripts/safe-migrate.sh
# (Interactive confirmations required)

# Step 5: Verify
php artisan tinker
>>> TeamMember::count()
# Should match pre-migration count
```

### Emergency Rollback

If migration fails:

```bash
# Use restore script
./scripts/restore-team-data.sh

# Or manually from backup
cd database/backups/
# Follow ROLLBACK_RUNBOOK.md
```

**Full rollback procedure:** `docs/ROLLBACK_RUNBOOK.md`

---

## 🚨 Incident History

### March 3, 2026 - Data Loss Incident

**Timeline:**
- **12:25 PM:** Backup created (11 team members)
- **12:25-6:45 PM:** `migrate:fresh` ran (intentional or accidental)
- **6:45 PM:** Discovered empty database
- **6:58 PM:** Data restored from backup
- **7:01 PM:** Classifications corrected
- **8:18 PM:** Staging environment created
- **8:28 PM:** Safety guards implemented

**Root Cause:**
`migrate:fresh` or test suite with `RefreshDatabase` trait wiped production database. Migration then ran on empty database, leaving `team_members` table empty.

**Lesson Learned:**
- Tests with `RefreshDatabase` can wipe production DB
- No separation between development and production databases
- No confirmation prompts before destructive operations
- No automated backup verification

**Prevention Measures Implemented:**
1. ✅ Staging environment (physical isolation)
2. ✅ AppServiceProvider guards (command blocking)
3. ✅ Pre-migration safety script (automated checks)
4. ✅ Web middleware (blocks HTTP-based destruction)
5. ✅ Backup verification (ensures recent backup exists)
6. ✅ Double confirmation (production requires "I accept the risk")

---

## 🔧 TODO: Complete Implementation

### Pending Tasks:

- [ ] **Register middleware in kernel**
  ```php
  // bootstrap/app.php or app/Http/Kernel.php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->append(\App\Http\Middleware\PreventDestructiveOperations::class);
  })
  ```

- [ ] **Add migration file guard**
  ```php
  // In migration up() method
  public function up(): void
  {
      if (DB::table('personas')->count() > 0 && !Schema::hasTable('personas_archive')) {
          throw new \Exception('Production data detected! Run backup first: ./scripts/backup-team-data.sh');
      }
      // ... rest of migration
  }
  ```

- [ ] **Add git hook for pre-commit backup reminder**
  ```bash
  # .git/hooks/pre-commit
  if git diff --cached --name-only | grep -q "migration"; then
      echo "⚠ Migration detected. Ensure backup exists!"
  fi
  ```

- [ ] **Automate hourly backups during development**
  ```bash
  # Add to cron
  0 * * * * cd /path/to/lunaos && ./scripts/backup-team-data.sh
  ```

- [ ] **Separate test database for PHPUnit**
  ```xml
  <!-- phpunit.xml -->
  <env name="DB_DATABASE" value=":memory:"/>
  <!-- OR -->
  <env name="DB_DATABASE" value="database-testing.sqlite"/>
  ```

---

## 📚 Related Documentation

- `docs/MIGRATION_RUNBOOK.md` - Step-by-step migration guide
- `docs/ROLLBACK_RUNBOOK.md` - Emergency rollback procedures
- `STAGING-SETUP.md` - Staging environment setup and usage
- `scripts/backup-team-data.sh` - Backup script documentation
- `scripts/restore-team-data.sh` - Restore script documentation

---

## ✅ Implementation Checklist

| Safety Measure | Status | Location |
|----------------|--------|----------|
| Staging environment | ✅ Complete | `../lunaos-staging/` |
| AppServiceProvider guards | ✅ Complete | `app/Providers/AppServiceProvider.php` |
| Safe migration script | ✅ Complete | `scripts/safe-migrate.sh` |
| Web middleware | ⚠️ Written, not registered | `app/Http/Middleware/PreventDestructiveOperations.php` |
| Pre-migration backup check | ✅ Complete | `scripts/safe-migrate.sh` |
| Double confirmation | ✅ Complete | `scripts/safe-migrate.sh` |
| Migration file guard | ❌ TODO | Add to migration files |
| PHPUnit test isolation | ❌ TODO | Configure `phpunit.xml` |
| Automated backups | ❌ TODO | Add to cron |
| Git hooks | ❌ TODO | Add `.git/hooks/` |

**Implementation Date:** March 3, 2026 - 8:28 PM  
**Implemented By:** Luna (with Kyle's guidance)  
**Trigger:** Data loss incident during Task 2A.2 migration

---

## 🎯 Best Practices

### Before Any Migration:

1. ✅ **Test on staging first**
   ```bash
   cd lunaos-staging/
   php artisan migrate:fresh
   # Verify everything works
   ```

2. ✅ **Create fresh backup**
   ```bash
   cd ../lunaos/
   ./scripts/backup-team-data.sh
   ```

3. ✅ **Run safety check**
   ```bash
   ./scripts/safe-migrate.sh --check-only
   ```

4. ✅ **Read documentation**
   - Migration runbook
   - Rollback procedures
   - This safety guide

5. ✅ **Have rollback plan ready**
   - Know how to restore from backup
   - Test restore process periodically

### During Migration:

- ⏱️ **Monitor progress** - Don't walk away
- 📊 **Watch for errors** - Interrupt if something's wrong
- ⏸️ **Be ready to abort** - Better safe than sorry

### After Migration:

1. ✅ **Verify data integrity**
   ```bash
   php artisan tinker
   >>> TeamMember::count()
   >>> TeamMember::where('type', 'workers')->count()
   >>> TeamMember::where('type', 'board-members')->count()
   ```

2. ✅ **Test application functionality**
   - Open browser to `http://lunaos.test`
   - Navigate to Team page
   - Verify all members display correctly

3. ✅ **Run test suite**
   ```bash
   php artisan test
   ```

4. ✅ **Create new backup**
   ```bash
   ./scripts/backup-team-data.sh
   ```

---

**Remember:** The goal is not to make migrations difficult—it's to make them **safe**. These guards exist because data loss is painful and preventable.

**When in doubt:** Test on staging, backup first, and don't skip the confirmations.
