# Agent Chat Debug Report

**Date:** March 8, 2026
**Issue:** Agent Chat not responding when users send messages
**Reporter:** Dave (Backend Developer)

## Summary

The Agent Chat feature has **three critical issues** preventing messages from working properly:

1. **Model name not being mapped** - TeamMember has `ai_model = 'glm-5'` but Ollama needs `'glm-5:cloud'`
2. **Blade view Error** - Undefined variable `$selectedMember` causing view compilation failure
3. **Cached/Old View** - Compiled view cache may contain outdated code

---

## Issue #1: AI Model Not Found (404 Error)

### Problem
Log entries show:
```
[2026-03-08 22:33:09] dusk.ERROR: Ollama API error {"status":404,"body":"{\"error\":\"model 'glm-5' not found\"}"}
```

### Root Cause
The TeamMember model has a default `ai_model` value of `'glm-5'`:

**File:** `app/Models/TeamMember.php` (line ~80)
```php
protected $attributes = [
    'ai_model' => 'glm-5',  // ← Wrong! Should be 'glm-5:cloud'
    // ...
];
```

### Why The Mapping Isn't Working
While `ChatService::getOllamaModel()` has a mapping for `'glm-5'` → `'glm-5:cloud'`:
```php
protected function getOllamaModel(?string $model): string
{
    $modelMap = [
        'glm-5' => 'glm-5:cloud',  // Mapping exists
        'haiku' => 'haiku:cloud',
        'dolphin' => 'dolphin:cloud',
    ];
    return $modelMap[$model] ?? $model;
}
```

The logs show the model was passed as `'glm-5'` directly to Ollama without mapping. This indicates either:
1. Old code without the mapping was used (logs from 22:33)
2. Or there's a caching issue
3. Or an existing TeamMember in database has `'glm-5'` and wasn't being mapped correctly

### Fix Required
**Option A (Recommended):** Change the TeamMember default to match what Ollama expects:
```php
// File: app/Models/TeamMember.php
protected $attributes = [
    'ai_model' => 'glm-5:cloud',  // ← Fixed
    // ...
];
```

**Option B:** Verify getOllamaModel() is called in ALL code paths in ChatService.

**Option C:** Update existing database records:
```sql
UPDATE team_members SET ai_model = 'glm-5:cloud' WHERE ai_model = 'glm-5';
```

---

## Issue #2: Blade View Error - Undefined Variable

### Problem
Log entry shows:
```
[2026-03-08 23:17:23] dusk.ERROR: Undefined variable $selectedMember (View: .../agent-chat.blade.php)
```

### Analysis
The current blade file (`agent-chat.blade.php`) uses:
- `$selectedMemberId` - the selected member's ID
- `$selectedMemberData` - array with member details

But the error references `$selectedMember` which doesn't exist in the current code. This suggests:
1. A cached compiled view from an older version
2. OR an included partial that references the old variable

### Fix Required
Clear the view cache:
```bash
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## Issue #3: Livewire View Binding Issues

### Problem
The blade template has incorrect Livewire syntax that may cause issues:

**File:** `resources/views/livewire/agent-chat.blade.php`

#### Problem 1: Invalid wire:disabled syntax
```blade
wire:disabled="{{ empty($newMessage) || $isTyping ? 'true' : 'false' }}"
```

This uses Blade PHP syntax inside a Livewire directive. In Livewire 3, this should be:
```blade
wire:disabled="$isTyping || empty($newMessage)"
```

#### Problem 2: Invalid .entangle modifier
```blade
wire:disabled.entangle="isTyping"
```

The `.entangle` modifier is for Alpine.js x-model binding, NOT for Livewire directives. Correct:
```blade
wire:disabled="isTyping"
```

### Fix Required
Replace the form section in `agent-chat.blade.php`:

**Before:**
```blade
<form wire:submit="sendMessage" class="flex gap-3">
    <input
        type="text"
        wire:model.live="newMessage"
        placeholder="Type a message..."
        class="..."
        wire:disabled.entangle="isTyping"
    >
    <button
        type="submit"
        wire:disabled="{{ empty($newMessage) || $isTyping ? 'true' : 'false' }}"
        class="..."
    >
        Send
    </button>
</form>
```

**After:**
```blade
<form wire:submit="sendMessage" class="flex gap-3">
    <input
        type="text"
        wire:model.live="newMessage"
        placeholder="Type a message..."
        class="..."
        wire:disabled="isTyping"
    >
    <button
        type="submit"
        wire:disabled="isTyping || !newMessage"
        class="..."
    >
        Send
    </button>
</form>
```

---

## Database Status

| Table | Count | Status |
|-------|-------|--------|
| chat_sessions | 8 | ✅ Working |
| chat_messages | 24 | ✅ Working |

The models exist and are working correctly. The issue is in the code, not the database.

---

## Ollama Connection Status

✅ **Ollama is accessible** at `http://192.168.2.2:11434`

Available models:
- `qwen3.5:9b` (local)
- `glm-5:cloud` ✅ (remote - expected model)
- `haiku:cloud` (remote)
- And others...

---

## Code Flow Analysis

### Livewire `sendMessage()` Method (AgentChat.php)
```php
public function sendMessage(): void
{
    // 1. Check for empty message or no member selected
    if (empty($this->newMessage) || !$this->selectedMemberId) {
        return;  // ← Silent fail if no member selected!
    }

    // 2. Create session if needed
    if (!$this->session) {
        $this->session = app(ChatService::class)->createSession($this->selectedMemberId);
        $this->sessionId = $this->session->id;
    }

    // 3. Stream from ChatService
    foreach (app(ChatService::class)->streamMessage($this->session, $userMessage) as $chunk) {
        // Process chunks...
    }
}
```

### ChatService `streamMessage()` Method
```php
public function streamMessage(ChatSession $session, string $userMessage): \Generator
{
    // 1. Get team member
    $teamMember = $session->teamMember;
    
    // 2. Build prompt
    $prompt = $this->buildPrompt($session, $teamMember, $userMessage);
    
    // 3. Get correct model name
    $model = $this->getOllamaModel($teamMember->ai_model);  // ← Mapping here
    
    // 4. Call Ollama
    $url = "{$this->ollamaUrl}/api/chat";
    // ... curl to Ollama ...
}
```

---

## Additional Findings

### WebSocket Status
The UI shows WebSocket is connected (green dot). The WebSocket server (Reverb) had port conflicts which were logged:
```
[2026-03-08 22:58:19] dusk.ERROR: Failed to listen on "tcp://0.0.0.0:8080": Address already in use
```

This means at some point the WebSocket server couldn't start. Make sure Reverb is running:
```bash
php artisan reverb:start
```

---

## Recommended Fix Order

1. **Clear all caches** (immediate):
   ```bash
   cd /Users/kobear/.openclaw/workspace/lunaos
   php artisan view:clear
   php artisan cache:clear
   php artisan optimize:clear
   ```

2. **Fix TeamMember default ai_model** (important):
   ```php
   // app/Models/TeamMember.php
   protected $attributes = [
       'ai_model' => 'glm-5:cloud',  // Change from 'glm-5'
   ```

3. **Fix the blade template syntax** (important):
   - Remove `.entangle` from `wire:disabled`
   - Use proper Livewire expressions instead of Blade PHP

4. **Update existing database records** (if needed):
   ```php
   TeamMember::where('ai_model', 'glm-5')->update(['ai_model' => 'glm-5:cloud']);
   ```

5. **Ensure Reverb is running**:
   ```bash
   php artisan reverb:start
   ```

---

## Files Reviewed

| File | Status |
|------|--------|
| `app/Livewire/AgentChat.php` | ✅ Code OK |
| `app/Services/ChatService.php` | ✅ Code OK |
| `app/Models/TeamMember.php` | ⚠️ Default ai_model needs fix |
| `app/Models/ChatSession.php` | ✅ OK |
| `app/Models/ChatMessage.php` | ✅ OK |
| `resources/views/livewire/agent-chat.blade.php` | ⚠️ Livewire syntax issues |
| `config/chat.php` | ✅ OK (default_model: glm-5:cloud) |

---

## Test Verification

After fixes, test by:
1. Clear caches
2. Navigate to http://lunaos.test/chat
3. Select an agent from dropdown
4. Type a message and click Send
5. Check `storage/logs/laravel.log` for successful Ollama calls:
   ```
   Calling Ollama {"model":"glm-5:cloud",...}
   Ollama response {"status":200,...}
   ```

---

**End of Report**