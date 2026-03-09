# Chat Archive & Filter Features - Specification

**Author:** Jordan (PM - LunaOS)  
**Date:** 2026-03-09  
**Status:** Draft  
**Priority:** 🟠 P1 - High value, not blocking

---

## Overview

Add organization features to Agent Chat to keep conversations manageable as usage grows. Two core features:

1. **Archive** - Hide old/past conversations without deleting them
2. **Filter/Search** - Quickly find specific conversations

---

## User Stories (Kyle's Perspective)

### Archive Stories

> **US-1:** As a user, I want to archive old conversations so they don't clutter my recent chats list.
> 
> **US-2:** As a user, I want to unarchive a conversation if I need to reference it again.
> 
> **US-3:** As a user, I want to see my archived conversations in a separate view so I can find them when needed.

### Filter/Search Stories

> **US-4:** As a user, I want to filter conversations by agent/team member.
> 
> **US-5:** As a user, I want to search conversations by title to quickly find a specific chat.
> 
> **US-6:** As a user, I want to sort my conversations (most recent, oldest first).

---

## Design Decisions

### Archive Approach

| Question | Decision | Rationale |
|----------|----------|-----------|
| Individual or bulk archive? | **Individual only (MVP)** | Simpler implementation, bulk can be added later |
| Where do archived chats live? | **Filter toggle** | Keep UI simple - one list with archive toggle |
| Can archived be restored? | **Yes** | Unarchive button in same location as archive |
| Auto-archive after X days? | **No (v1)** | Manual control is simpler and more predictable |
| UI placement? | **Context menu on session item** | Clean, discoverable, doesn't add clutter |

### Filter/Search Approach

| Question | Decision | Rationale |
|----------|----------|-----------|
| Filter by agent? | **Yes (MVP)** | Primary use case |
| Filter by date range? | **No (v1)** | Adds complexity, can add later |
| Filter by archived status? | **Yes (MVP)** | Essential for archive feature |
| Full-text search? | **No (v1)** | Titles only - simpler, good enough for now |
| UI placement? | **Search bar + agent filter dropdown** | Clear, accessible |

---

## Database Changes

### Migration: Add `is_archived` to `chat_sessions`

```php
// New migration: add_is_archived_to_chat_sessions_table.php

Schema::table('chat_sessions', function (Blueprint $table) {
    $table->boolean('is_archived')->default(false)->after('metadata');
    $table->index('is_archived');
});
```

**Why not a separate table?** 
- Archived chats are still the same entity, just hidden
- Simpler queries (no joins across tables)
- Unarchive is just a boolean toggle
- Keeps all chat history in one place

---

## API/Backend Requirements

### ChatSession Model Updates

```php
// Add to fillable
protected $fillable = [
    'team_member_id',
    'title',
    'context',
    'metadata',
    'is_archived', // NEW
];

// Add accessor for convenience
public function scopeArchived($query)
{
    return $query->where('is_archived', true);
}

public function scopeActive($query)
{
    return $query->where('is_archived', false);
}
```

### AgentChat Component Updates

| Method | Changes |
|--------|---------|
| `loadRecentSessions()` | Add archived filter, search, sort params |
| `archiveSession($id)` | NEW - Set `is_archived = true` |
| `unarchiveSession($id)` | NEW - Set `is_archived = false` |
| `searchSessions($query)` | NEW - Filter by title (LIKE query) |

### New Component Properties

```php
// AgentChat.php
public string $searchQuery = '';
public string $filterAgent = '';
public string $filterArchive = 'active'; // 'active', 'archived', 'all'
public string $sortBy = 'recent'; // 'recent', 'oldest', 'alpha'
```

---

## UI Mockup Descriptions

### Sidebar - Recent Sessions Section

```
┌─────────────────────────────────────┐
│ 💬 Agent Chat              ● (ws)   │
├─────────────────────────────────────┤
│ Select Agent                         │
│ [🤖 Maya - Developer       ▼]       │
├─────────────────────────────────────┤
│ [🔍 Search conversations...    ]     │ ← Search bar
│ [Recent ▼]  [Hide Archived ✓]       │ ← Sort + filter
├─────────────────────────────────────┤
│ Recent                               │
│ ┌─────────────────────────────────┐ │
│ │ 🤖 Fix the login bug            │ │
│ │    Maya • 2h ago          [📦]  │ │ ← Archive btn (hover)
│ └─────────────────────────────────┘ │
│ ┌─────────────────────────────────┐ │
│ │ 🎨 Dashboard redesign           │ │
│ │    Jordan • yesterday     [📦]  │ │
│ └─────────────────────────────────┘ │
│ ...                                  │
├─────────────────────────────────────┤
│ [Show Archived (5)]                 │ ← Toggle to view archived
├─────────────────────────────────────┤
│ [New Conversation]                   │
└─────────────────────────────────────┘
```

### Archived Sessions View

```
┌─────────────────────────────────────┐
│ 📦 Archived Conversations            │
├─────────────────────────────────────┤
│ [🔍 Search archived...         ]     │
│ [Showing 5 archived]                 │
├─────────────────────────────────────┤
│ ┌─────────────────────────────────┐ │
│ │ 🤖 Old project discussion        │ │
│ │    Alex • 3 weeks ago     [↩️]  │ │ ← Unarchive btn
│ └─────────────────────────────────┘ │
│ ┌─────────────────────────────────┐ │
│ │ 🎨 Logo brainstorm               │ │
│ │    Jordan • 1 month ago   [↩️]  │ │
│ └─────────────────────────────────┘ │
├─────────────────────────────────────┤
│ [← Back to Active]                   │
└─────────────────────────────────────┘
```

### Session Item Hover Actions

When hovering over a session in the list:
- Active session: Shows archive icon (📦) on right side
- Archived session: Shows unarchive icon (↩️) on right side
- Clicking the icon performs the action with brief toast notification

---

## Acceptance Criteria

### Feature: Archive Chat

- [ ] **AC-A1:** User can archive an active conversation from the sidebar via context menu or hover action
- [ ] **AC-A2:** Archived conversations disappear from the default "Recent" list
- [ ] **AC-A3:** User can view all archived conversations via "Show Archived" toggle
- [ ] **AC-A4:** User can unarchive a conversation to restore it to the active list
- [ ] **AC-A5:** Archive state persists across sessions (stored in database)
- [ ] **AC-A6:** Archiving a conversation does NOT delete any messages
- [ ] **AC-A7:** Active conversation in main view is not affected if user switches away

### Feature: Filter/Search

- [ ] **AC-F1:** User can filter conversations by agent/team member via dropdown
- [ ] **AC-F2:** User can search conversations by title (case-insensitive, partial match)
- [ ] **AC-F3:** User can toggle between "Active", "Archived", and "All" views
- [ ] **AC-F4:** User can sort by: Most Recent (default), Oldest First, Alphabetical
- [ ] **AC-F5:** Filters and search work together (can search within filtered results)
- [ ] **AC-F6:** Empty state shown when no results match filters/search

---

## MVP vs Nice-to-Have

### MVP (This Issue)

| Feature | Priority |
|---------|----------|
| Archive/unarchive single chat | ✅ Required |
| View archived chats toggle | ✅ Required |
| Search by title | ✅ Required |
| Filter by agent | ✅ Required |
| Sort by recent/oldest | ✅ Required |
| Filter toggle (active/archived) | ✅ Required |

### Nice-to-Have (Future Issues)

| Feature | Priority |
|---------|----------|
| Bulk archive (select multiple) | 🔮 Future |
| Full-text message search | 🔮 Future |
| Date range filter | 🔮 Future |
| Auto-archive after X days | 🔮 Future |
| Delete conversation | 🔮 Future |
| Conversation tags/labels | 🔮 Future |
| Pin/star important chats | 🔮 Future |

---

## Implementation Recommendation

### Approach: Single PR (Recommended)

**Rationale:** Archive and filter are tightly coupled - the filter UI *needs* to handle archived state. Splitting would create partial features that don't work well alone.

**Size Estimate:** Medium (M)

**Suggested Breakdown:**

| Phase | Tasks | Est. Time |
|-------|-------|-----------|
| 1. Backend | Migration, model updates, component methods | 2-3 hours |
| 2. Frontend | UI for archive buttons, filter controls | 3-4 hours |
| 3. Polish | Empty states, transitions, responsive | 1-2 hours |
| **Total** | | **6-9 hours** |

### Recommended Builder

**Maya** - This is primarily a Livewire/Blade feature with database work. Maya just completed the chat reactivity fixes and has context on the component. The backend changes are straightforward (boolean column + scopes).

If Maya is unavailable, **Dave** could do backend + basic frontend, then Maya polishes UI.

---

## Technical Implementation Notes

### Query Performance

```php
// Optimized query for filtered sessions
ChatSession::with('teamMember:id,name,emoji')
    ->when($filterArchive === 'active', fn($q) => $q->active())
    ->when($filterArchive === 'archived', fn($q) => $q->archived())
    ->when($filterAgent, fn($q) => $q->where('team_member_id', $filterAgent))
    ->when($searchQuery, fn($q) => $q->where('title', 'LIKE', "%{$searchQuery}%"))
    ->orderBy('updated_at', $sortBy === 'oldest' ? 'asc' : 'desc')
    ->limit(50)
    ->get();
```

### Index Recommendation

With the `is_archived` index and existing `updated_at` index, queries should remain fast. Consider composite index if filtering by both archived + team_member becomes common:

```php
$table->index(['is_archived', 'team_member_id', 'updated_at']);
```

### Livewire Reactivity

The sidebar should update reactively when:
- User archives/unarchives a session → list refreshes
- User applies filter/search → list updates
- Session is selected → highlight in list

Use `wire:model.live` for instant filter/search updates.

---

## Testing Checklist

- [ ] Archive/unarchive persists across page reload
- [ ] Archived chats don't appear in "Active" filter
- [ ] Search finds partial title matches (case-insensitive)
- [ ] Agent filter correctly narrows list
- [ ] Sort options work correctly
- [ ] Session list updates reactively
- [ ] Empty states display correctly
- [ ] Mobile-responsive sidebar (if applicable)

---

## Open Questions

None - all decisions resolved above.

---

## References

- Current implementation: `app/Livewire/AgentChat.php`
- Model: `app/Models/ChatSession.php`
- View: `resources/views/livewire/agent-chat.blade.php`
- Migrations: `database/migrations/*chat*`