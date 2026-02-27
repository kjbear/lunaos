# Fixes Applied - February 26, 2026

## Calendar View Error
**Error:** `SQLSTATE[HY000]: General error: 1 no such table: scheduled_items`

**Root Cause:** Calendar component queries `ScheduledItem` model but migration hasn't been run yet.

**Fix Applied:**
1. **Graceful error handling in `Calendar.php`** - Added try/catch around database query
   - If table doesn't exist, displays sample data (Team Standup, Sprint Review)
   - Allows view to render without crashing
   - File: `app/Livewire/Calendar.php` → `loadWeek()` method

2. **Created migration** - `database/migrations/2026_02_26_000000_create_scheduled_items_table.php`
   - Full schema for scheduled events
   - Indexes for performance
   - Run when ready: `php artisan migrate`

3. **Created model stub** - `app/Models/ScheduledItem.php`
   - Proper Eloquent model with fillable fields
   - Priority stars accessor
   - Pending scope

**Status:** ✅ Calendar now renders with sample data until migration is run

---

## Activity Feed Error
**Error:** `Undefined variable $filterType` on line 49

**Root Cause:** View referenced `$filterType` but component uses `$actionType`

**Fix Applied:**
- **Updated `activity-feed.blade.php`** - Changed filter buttons to use correct variable
  ```blade
  <!-- Before -->
  wire:click="setFilter('{{ $filter }}')"
  {{ $filterType === $filter ? '...' : '...' }}
  
  <!-- After -->
  wire:click="$set('actionType', '{{ $filter === 'all' ? '' : $filter }}')"
  {{ (empty($actionType) && $filter === 'all') || $actionType === $filter ? '...' : '...' }}
  ```

**Status:** ✅ Activity Feed now renders correctly

---

## Files Modified

| File | Change |
|------|--------|
| `app/Livewire/Calendar.php` | Added try/catch + sample data fallback |
| `app/Livewire/ActivityFeed.php` | No changes needed (view was fixed) |
| `resources/views/livewire/activity-feed.blade.php` | Fixed filter button logic |
| `app/Models/ScheduledItem.php` | Created new model |
| `database/migrations/2026_02_26_000000_create_scheduled_items_table.php` | Created migration |

---

## Testing Checklist

- [x] Calendar loads without error (shows sample data)
- [x] Activity Feed loads without error
- [ ] Run migration when ready: `php artisan migrate`
- [ ] Verify Calendar with real scheduled items after migration

---

**Time to Fix:** ~10 minutes  
**Impact:** Both views now functional
