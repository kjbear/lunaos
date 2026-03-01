# Livewire v3 Setup - Critical Configuration

## ⚠️ IMPORTANT: Manual `Livewire.start()` Required

**Problem:** Livewire v3 with CDN does NOT auto-initialize components in certain layouts, even when:
- ✅ `window.livewireScriptConfig` is defined BEFORE the script
- ✅ Script loads without errors
- ✅ Components render with `wire:id` and `wire:snapshot` attributes
- ✅ `typeof Livewire === 'object'`

**Symptom:** `window.Livewire.components.components` is `undefined` - components never registered.

**Solution:** Manually call `Livewire.start()` after the script loads:

```blade
<!-- Livewire Config & Script -->
<script>
    window.livewireScriptConfig = {
        uri: '/livewire/update',
        csrf: '{{ csrf_token() }}'
    };
</script>
<script src="https://cdn.jsdelivr.net/gh/livewire/livewire@v3.7.10/dist/livewire.min.js"></script>
<script>
    // CRITICAL: Manually start Livewire to initialize components
    // Without this, components are not registered and forms do page refresh instead of AJAX
    if (typeof Livewire !== 'undefined') {
        Livewire.start();
    }
</script>
```

## Why This Happens

**Root Cause:** When using Livewire v3 with CDN (instead of `@livewireScripts`), the auto-initialization may fail if:
1. Script loads before DOM is fully parsed
2. Layout uses non-standard structure
3. CDN version differs from Composer package
4. Script timing/defer issues

**The Fix:** `Livewire.start()` forces Livewire to:
1. Scan the DOM for all elements with `wire:id`
2. Initialize each component
3. Bind event listeners (including `wire:submit`)
4. Register components in `window.Livewire.components.components`

## Without This Fix

- Forms with `wire:submit` do **traditional page refresh** instead of AJAX POST
- No POST to `/livewire/update` in Network tab
- `Livewire.find(componentId)` returns `undefined`
- Component methods never execute

## With This Fix

- Forms intercept submission and POST to `/livewire/update`
- Component methods (e.g., `save()`) execute properly
- Page redirects work as expected
- Full Livewire reactivity enabled

---

**Date Documented:** February 28, 2026  
**LunaOS Version:** Phase 1  
**Livewire Version:** v3.7.10
