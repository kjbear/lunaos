# Memory & Log Management

**Date:** February 26, 2026  
**Issue:** Memory exhaustion (128MB) + log file growth

---

## Actions Taken

### 1. Cleared Laravel Log
```bash
truncate -s 0 storage/logs/laravel.log
```
**Result:** Log file reset to 0 bytes ✅

---

### 2. Increased Memory Limit

**File:** `bootstrap/app.php`

```php
<?php

// Increase memory limit at bootstrap
ini_set('memory_limit', '512M');

use Illuminate\Foundation\Application;
// ... rest of configuration
```

**Change:** 128MB → 512MB (4x increase)

---

### 3. Created Configuration Files

#### `.env.local`
```bash
PHP_MEMORY_LIMIT=512M
LOG_LEVEL=warning
LOG_DAILY_DAYS=7
```

#### `php.ini` (local)
```ini
memory_limit = 512M
max_execution_time = 120
upload_max_filesize = 64M
post_max_size = 64M
```

---

## Log Rotation Strategy

### Current Setup
- Laravel daily logs (automatic rotation)
- Kept for 7 days (`LOG_DAILY_DAYS=7`)
- Warning level and above only (`LOG_LEVEL=warning`)

### Manual Cleanup Commands
```bash
# Clear all logs
cd lunaos && truncate -s 0 storage/logs/laravel.log

# Delete old daily logs
find storage/logs -name "*.log" -mtime +7 -delete

# Check log size
du -sh storage/logs/
```

### Automated Cleanup (Cron)
Add to system crontab:
```bash
# Weekly log cleanup (Sundays at 2 AM)
0 2 * * 0 cd /Users/kobear/.openclaw/workspace/lunaos && find storage/logs -name "*.log" -mtime +7 -delete
```

---

## Memory Optimization Tips

### For Laravel
1. **Eager load relationships** - Avoid N+1 queries
   ```php
   // Bad
   foreach ($users as $user) {
       $user->posts->count();
   }
   
   // Good
   $users = User::with('posts')->get();
   ```

2. **Use chunking for large datasets**
   ```php
   User::chunk(200, function ($users) {
       // Process 200 at a time
   });
   ```

3. **Queue heavy tasks** - Don't run synchronously
   ```php
   ProcessHeavyTask::dispatch($data);
   ```

4. **Cache expensive queries**
   ```php
   $users = Cache::remember('users', 3600, function () {
       return User::all();
   });
   ```

### For Development
- **Disable debug bar** in production
- **Use telescope sparingly** (or disable)
- **Clear cache regularly**: `php artisan cache:clear`
- **Optimize autoloader**: `composer dump-autoload --optimize`

---

## Monitoring

### Check Current Memory Usage
```php
// In tinker or debug route
echo 'Memory: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
echo 'Peak: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB';
```

### Log File Size Monitoring
```bash
# Check log size
ls -lh storage/logs/laravel.log

# Alert if > 100MB
if [ $(stat -f%z storage/logs/laravel.log 2>/dev/null || stat -c%s storage/logs/laravel.log) -gt 104857600 ]; then
    echo "⚠️ Log file exceeds 100MB"
fi
```

---

## Herd-Specific Notes

Laravel Herd manages PHP versions independently. To ensure memory settings persist:

### Option 1: Herd PHP Settings
1. Open Herd app
2. Go to Settings → PHP
3. Select PHP 8.4 (or your version)
4. Click "Edit php.ini"
5. Set: `memory_limit = 512M`
6. Restart PHP

### Option 2: Project-Specific (What we did)
- Added `ini_set()` in `bootstrap/app.php`
- Created local `php.ini` in project root
- Herd should respect project-level settings

---

## Related Issues Fixed

- ✅ Calendar 500 error (component rewrite)
- ✅ Activity Feed null errors (variable names)
- ✅ Scheduled command failures (logged separately)

---

## Next Steps

### Immediate
- [x] Clear logs
- [x] Increase memory to 512MB
- [x] Configure log retention (7 days)

### Monitor
- [ ] Watch memory usage over next 24h
- [ ] Check if scheduled commands still fail
- [ ] Verify log size stays manageable

### If Problems Persist
- [ ] Increase to 1024M if 512M insufficient
- [ ] Enable query logging to find memory leaks
- [ ] Profile with Blackfire or Telescope

---

**Status:** ✅ Memory increased, logs cleared  
**Next Review:** Check in 24 hours for stability
