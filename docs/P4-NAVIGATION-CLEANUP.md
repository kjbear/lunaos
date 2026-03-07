# P4: Navigation + Route Cleanup (Phase 2A.4)

**Owner:** Maya  
**Timeline:** 1-2 days  
**Priority:** 🟡 MEDIUM (after P2/P3 complete)  
**Status:** 📋 READY TO START

---

## Context

Phase 2A consolidated:
- ✅ **Tasks module** ( unified board/list/executive views)
- ✅ **Team module** (agents + personas + board → unified `team_members` table)

**Navigation is NOT yet updated** to reflect this consolidation.

---

## Current State Analysis

### ✅ What's Working

**Team routes exist:**
```
GET  /team ................... team.index › TeamController@index
GET  /team/{id} .............. team.show › TeamController@show
GET  /team/{id}/edit ......... team.edit › TeamController@edit
```

**Sidebar shows Team:**
```blade
<a href="{{ route('team') }}">
    👥 Team
</a>
```

### ⚠️ What Needs Fixed

**Old routes still accessible:**
- `/hr` → Should redirect to `/team?type=persona`
- `/agents` → Should redirect to `/team?type=worker`
- `/kanban` → Still shows (decision needed: keep or remove?)

**Sidebar inconsistencies:**
- Both flat nav AND grouped nav show same items (duplicate entries)
- Kanban listed separately (should be under Tasks or removed)
- No filter params for Team view (type=worker/persona/board)

---

## Tasks

### **P4.1: Update Sidebar Navigation** (2 hrs)

**File:** `resources/views/components/layouts/app.blade.php`

**Changes needed:**

1. **Remove duplicates** - Choose either flat list OR grouped nav (recommend grouped)
2. **Update Team section** - Add filter links:
   ```blade
   <a href="{{ route('team') }}">All Team</a>
   <a href="{{ route('team', ['type' => 'worker']) }}">Workers</a>
   <a href="{{ route('team', ['type' => 'persona']) }}">Personas</a>
   <a href="{{ route('team', ['type' => 'board']) }}">Board Members</a>
   ```
3. **Decide on Kanban** - Remove or move under Tasks
4. **Add Projects subsections** (optional):
   ```blade
   <a href="{{ route('projects') }}">All Projects</a>
   <a href="{{ route('projects', ['status' => 'active']) }}">Active</a>
   ```

---

### **P4.2: Deprecate Old Routes** (1 hr)

**File:** `routes/web.php`

Add redirects:
```php
// Deprecated routes → new Team module
Route::redirect('/hr', '/team?type=persona')->name('hr.redirect');
Route::redirect('/agents', '/team?type=worker')->name('agents.redirect');

// Optional: Kanban deprecation (if removing)
// Route::redirect('/kanban', '/tasks?view=board')->name('kanban.redirect');
```

**OR** use middleware to handle gracefully:
```php
Route::get('/hr', [LegacyRouteController::class, 'redirect'])->name('hr.legacy');
```

---

### **P4.3: Update TeamController** (1 hr)

**File:** `app/Http/Controllers/TeamController.php`

Add filter support:
```php
public function index(Request $request)
{
    $query = TeamMember::query();
    
    if ($request->filled('type')) {
        $query->where('type', $request->type); // worker, persona, board
    }
    
    if ($request->filled('available')) {
        $query->where('available', $request->boolean('available'));
    }
    
    $teamMembers = $query->paginate(20);
    
    return view('team.index', compact('teamMembers'));
}
```

---

### **P4.4: Update Breadcrumbs** (30 min)

**File:** Various blade views

Ensure all pages have correct breadcrumbs:
```blade
<x-breadcrumbs>
    <x-breadcrumb-item href="{{ route('dashboard') }}">Home</x-breadcrumb-item>
    <x-breadcrumb-item href="{{ route('team') }}">Team</x-breadcrumb-item>
    <x-breadcrumb-item active>{{ $teamMember->name }}</x-breadcrumb-item>
</x-breadcrumbs>
```

**Update if needed:**
- Team detail pages
- Project detail pages
- Task detail pages

---

### **P4.5: Write Navigation Tests** (1 hr)

**File:** `tests/Feature/Navigation/NavigationTest.php`

```php
public function test_hr_redirects_to_team()
{
    $response = $this->get('/hr');
    $response->assertRedirect('/team?type=persona');
}

public function test_agents_redirects_to_team()
{
    $response = $this->get('/agents');
    $response->assertRedirect('/team?type=worker');
}

public function test_team_filter_works()
{
    $response = $this->get('/team?type=worker');
    $response->assertStatus(200);
    // Assert only workers shown
}

public function test_sidebar_shows_correct_links()
{
    $response = $this->get('/team');
    $response->assertSee('Workers');
    $response->assertSee('Personas');
    $response->assertSee('Board Members');
}
```

---

## Acceptance Criteria

- [ ] Sidebar navigation cleaned up (no duplicates)
- [ ] Old routes (`/hr`, `/agents`) redirect properly
- [ ] Team view supports type filter
- [ ] All breadcrumbs updated
- [ ] Navigation tests written + passing
- [ ] No console errors
- [ ] Mobile responsive verified
- [ ] PR created: `maya/p4-navigation-cleanup`

---

## Files to Modify

**Navigation:**
- `resources/views/components/layouts/app.blade.php`
- `resources/views/components/layouts/board.blade.php` (if has nav)

**Routes:**
- `routes/web.php`

**Controllers:**
- `app/Http/Controllers/TeamController.php`

**Tests:**
- `tests/Feature/Navigation/NavigationTest.php` (new)

**Views:**
- Any view with breadcrumbs (check 10-15 files)

---

## Testing Checklist

- [ ] `/hr` → redirects to `/team?type=persona`
- [ ] `/agents` → redirects to `/team?type=worker`
- [ ] `/team` → shows all team members
- [ ] `/team?type=worker` → shows only workers
- [ ] `/team?type=persona` → shows only personas
- [ ] `/team?type=board` → shows only board members
- [ ] Sidebar links all work
- [ ] Breadcrumbs display correctly
- [ ] No 404 errors in browser console
- [ ] Mobile nav works (hamburger menu)

---

## Dependencies

**Blocks:**
- Phase 2B (Executive Board enhancements) - needs clean nav first

**Blocked by:**
- P3 (Team consolidation frontend) - need UI working first

---

## Estimated Effort

- Development: 5.5 hours
- Testing: 2 hours
- Review + fixes: 1.5 hours
- **Total: ~1-2 days**

---

## Notes

- **Kanban decision:** Recommend keeping for now, move under Tasks group
- **Mobile first:** Ensure changes work on mobile sidebar
- **Accessibility:** Update aria-labels when removing items

---

**Ready to start when P3 (Team frontend) is complete!**
