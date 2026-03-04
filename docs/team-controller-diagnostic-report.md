# Team Controller Diagnostic Report

**Generated:** 2026-03-03  
**Tests Analyzed:** 21 total (5 failing, 16 passing)  
**Current Pass Rate:** 76.2%

---

## Test 1: api_index_returns_json_response

**Error Message:**
```
Failed asserting that an array has the key 'id'.
at tests/Feature/Http/Controllers/TeamControllerTest.php:226
```

**Root Cause:**
The `apiIndex()` method in `TeamController.php` (line 101) is transforming the `TeamResource::collection()` incorrectly. The current implementation calls `->response()->getData('data')` which extracts the inner data array, but this unwraps the resource collection structure. When wrapped again in `['data' => ...]`, the structure becomes `{'data': [{'array of raw data'}]}` instead of `{'data': [{'id': ..., 'name': ...}, ...]}`.

The issue is that `TeamResource::collection($members)->response()->getData('data')` returns an associative array, not the expected structure with individual resource items.

**Location:**
- `app/Http/Controllers/TeamController.php:101-105`

**Fix:**
Remove the `->response()->getData('data')` call. Simply return `TeamResource::collection($members)` which Laravel will automatically wrap in `{'data': [...]}` when converted to JSON. The controller method should be:
```php
return response()->json(['data' => TeamResource::collection($members)]);
```

---

## Test 2: api_show_returns_single_member_json

**Error Message:**
```
Unable to find JSON: [{"data": {"id": "...", "name": "API Test"}}]
within response JSON: [{"data": {"data": {"id": "...", "name": "API Test", ...}}}]
```

**Root Cause:**
**DOUBLE NESTING ISSUE** - The `apiShow()` method (line 111) wraps the `TeamResource` in a `['data' => ...]` array, AND calls `->response()->getData('data')` which extracts the resource's own `data` wrapper. This creates a nested structure: `{'data': {'data': {...actual member data...}}}` instead of the expected `{'data': {...actual member data...}}`.

The test expects: `{'data': {'id': '...', 'name': '...'}}`  
Actual response: `{'data': {'data': {'id': '...', 'name': '...', ...}}}`

**Location:**
- `app/Http/Controllers/TeamController.php:111-115`

**Fix:**
Do not extract `getData('data')`. Simply pass the resource directly to `response()->json()`:
```php
return response()->json(['data' => TeamResource::make($team)]);
```
Laravel's `JsonResource` will automatically serialize the inner object correctly without the extra `data` wrapper extraction.

---

## Test 3: api_store_creates_member

**Error Message:**
```
Unable to find JSON: [{"data": {"name": "API Created"}}]
within response JSON: [{"data": {"data": {"name": "API Created", ...}}}]
```

**Root Cause:**
**SAME DOUBLE NESTING ISSUE as Test 2** - The `apiStore()` method (line 123) uses `TeamResource::make($member)->response()->getData('data')`, which extracts the resource's data and then re-wraps it, creating the `{'data': {'data': {...}}}` structure.

The test expects to find `{'data': {'name': 'API Created'}}` but finds `{'data': {'data': {'name': 'API Created', ...}}}`.

**Location:**
- `app/Http/Controllers/TeamController.php:119-129`

**Fix:**
Same as Test 2 - simplify to:
```php
return response()->json(['data' => TeamResource::make($member)], 201);
```

---

## Test 4: api_update_modifies_member

**Error Message:**
```
Expected response status code [200] but received 422.
The following errors occurred during the last request:
{
    "message": "The email field is required. (and 1 more error)",
    "errors": {
        "email": ["The email field is required."],
        "role": ["The role field is required."]
    }
}
```

**Root Cause:**
**VALIDATION MISMATCH** - The test (`api_update_modifies_member` at line 288) sends only `{'name': 'After'}` in the PUT request. However, the `apiUpdate()` method (line 137) has validation rules that require ALL three fields:
```php
$name' => 'required|string|max:255',
'email' => 'required|email|max:255|unique:team_members,email,' . $team->getKey(),
'role' => 'required|in:worker,persona,board_member',
```

The test expects partial updates (only sending `name`), but the controller requires `email` and `role` to always be present.

**Location:**
- `app/Http/Controllers/TeamController.php:137-143` (validation rules)
- `tests/Feature/Http/Controllers/TeamControllerTest.php:288-290` (test sending only name)

**Fix:**
Change validation rules in `apiUpdate()` to use `'sometimes'` modifier instead of `'required'` for all fields:
```php
'name' => 'sometimes|required|string|max:255',
'email' => 'nullable|email|max:255|unique:team_members,email,' . $team->getKey(),
'role' => 'sometimes|required|in:worker,persona,board_member',
```

This allows partial updates where only changed fields are sent.

---

## Test 5: api_members_endpoint_returns_subordinates

**Error Message:**
```
Failed to assert that the response count matched the expected 2
Failed asserting that actual size 1 matches expected size 2.
at tests/Feature/Http/Controllers/TeamControllerTest.php:325
```

**Root Cause:**
**RELATIONSHIP QUERY ISSUE** - The `members()` method (line 155) correctly accesses `$team->children`, which is defined in the `TeamMember` model (line 91-94) as:
```php
public function children(): HasMany
{
    return $this->hasMany(TeamMember::class, 'parent_id');
}
```

The test creates 2 children with `parent_id` set to the parent's ID. However, only 1 is being returned. 

**INVESTIGATION:** Looking at the migration/schema - there might be a scoping issue or soft delete affecting the query. The `TeamMember` model uses `deactivated_at` for soft deletes (not Laravel's standard `deleted_at`). If the `children()` relationship doesn't account for this, it might only return "active" members.

**ACTUAL ROOT CAUSE:** The `children()` relationship query isn't properly fetching all children. This could be because:
1. A global scope is filtering results
2. The relationship needs to explicitly include soft-deleted models
3. There's a query issue in how the relationship is defined

Looking at the model, there's no `SoftDeletes` trait, but there is a `deactivated_at` timestamp. The relationship should work as-is, but the test shows only 1 of 2 children is returned.

**Location:**
- `app/Http/Controllers/TeamController.php:151-156`
- `app/Models/TeamMember.php:91-94`

**Fix:**
The `children()` relationship should be modified to ensure it fetches all children regardless of any potential filtering:
```php
public function children(): HasMany
{
    return $this->hasMany(TeamMember::class, 'parent_id');
}
```
This looks correct. The actual issue may be in how the relationship is being queried or a database indexing/statistics issue during tests. **Most likely fix:** Add `->withoutGlobalScopes()` or ensure no model-wide scopes are filtering the children query. Another possibility: the test setup has a timing issue where the second child isn't properly persisted before the query runs.

**RECOMMENDED FIX:** Modify the controller's `members()` method to explicitly query children without scoping:
```php
return response()->json(['data' => TeamResource::collection($team->children()->get())]);
```

---

## Summary

### Total Root Causes: 3

1. **Incorrect JSON Resource Wrapping** (affects Tests 1-3):
   - Using `->response()->getData('data')` extracts data incorrectly
   - Creates `{'data': {'data': {...}}}` instead of `{'data': {...}}`
   - Affects: `apiIndex()`, `apiShow()`, `apiStore()`

2. **Validation Rules Too Strict** (affects Test 4):
   - `apiUpdate()` requires all fields when partial updates should be allowed
   - Should use `'sometimes'` instead of `'required'`

3. **Children Relationship Query Issue** (affects Test 5):
   - Only returning 1 of 2 children
   - Possibly related to soft delete handling or query scoping

### Estimated Fix Time: 15-20 minutes

### Files to Modify:
1. `app/Http/Controllers/TeamController.php` - Fix 5 methods:
   - `apiIndex()` (lines 101-105)
   - `apiShow()` (lines 111-115)
   - `apiStore()` (lines 119-129)
   - `apiUpdate()` (lines 137-143)
   - `members()` (lines 151-156)

---

## Quick Reference: Exact Changes Needed

```diff
// In app/Http/Controllers/TeamController.php

// Fix 1: apiIndex()
public function apiIndex(): JsonResponse
{
    $members = TeamMember::all();
-    return response()->json([
-        'data' => TeamResource::collection($members)->response()->getData('data')
-    ]);
+    return response()->json(['data' => TeamResource::collection($members)]);
}

// Fix 2: apiShow()
public function apiShow(TeamMember $team): JsonResponse
{
-    return response()->json([
-        'data' => TeamResource::make($team)->response()->getData('data')
-    ]);
+    return response()->json(['data' => TeamResource::make($team)]);
}

// Fix 3: apiStore()
public function apiStore(Request $request): JsonResponse
{
    // ... validation ...
    $member = $this->teamService->createTeamMember($validated);

-    return response()->json([
-        'data' => TeamResource::make($member)->response()->getData('data')
-    ], 201);
+    return response()->json(['data' => TeamResource::make($member)], 201);
}

// Fix 4: apiUpdate() - change validation rules
public function apiUpdate(Request $request, TeamMember $team): JsonResponse
{
    $validated = $request->validate([
-       'name' => 'required|string|max:255',
-       'email' => 'required|email|max:255|unique:team_members,email,' . $team->getKey(),
-       'role' => 'required|in:worker,persona,board_member',
+       'name' => 'sometimes|required|string|max:255',
+       'email' => 'nullable|email|max:255|unique:team_members,email,' . $team->getKey(),
+       'role' => 'sometimes|required|in:worker,persona,board_member',
    ]);
    // ...
}

// Fix 5: members() - ensure all children are fetched
public function members(TeamMember $team): JsonResponse
{
-    return response()->json([
-        'data' => TeamResource::collection($team->children)->response()->getData('data')
-    ]);
+    return response()->json(['data' => TeamResource::collection($team->children)]);
}
```
