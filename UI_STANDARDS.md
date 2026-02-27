# LunaOS UI/UX Standards

## Lessons Learned (Feb 26, 2026)

### ✅ What Works

**Polished Mission Control Pattern:**
- Gradient glassmorphism headers with backdrop-blur
- Card grids with hover states and subtle glows
- Real-time activity feeds with status indicators
- Empty states with dashed borders
- Stats cards with gradient backgrounds and icons

**Technical Fixes:**
1. **ALWAYS use full `@php` blocks** — never `@php($var = ...)` short syntax
   ```blade
   {{-- ✅ Good --}}
   @php
       $colors = $columnColors[$column] ?? ['from' => 'from-slate-600'];
   @endphp
   
   {{-- ❌ Bad — causes parse errors --}}
   @php($colors = $columnColors[$column])
   ```

2. **Use Alpine.js for drag/drop** — not inline `on*` handlers
   ```blade
   {{-- ✅ Good --}}
   <div x-on:dragover.prevent x-on:drop="handleDrop($event, 'status')">
   
   {{-- ❌ Old way --}}
   <div ondragover="event.preventDefault()" ondrop="handleDrop(event, 'status')">
   ```

3. **Always handle empty arrays in blade** — use `?? []` everywhere
   ```blade
   @forelse($tasks[$column] ?? [] as $task)
   ```

4. **Add try/catch in Livewire mount()** — prevent silent failures
   ```php
   public function loadWorkload(): void
   {
       try {
           $stats = ActivityLogger::getStatsByAgent(7);
       } catch (\Exception $e) {
           $stats = [];
       }
   }
   ```

5. **Clear caches after changes** — always run:
   ```bash
   php artisan view:clear
   php artisan config:clear
   npm run build  # if CSS/JS changed
   ```

---

## Design System Components

### Headers
```blade
<header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
    <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
    <div class="relative flex items-center justify-between p-6">
        {{-- Content --}}
    </div>
</header>
```

### Section Headers
```blade
<div class="flex items-center gap-3 mb-4">
    <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Section Title</h2>
    <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">Badge</span>
</div>
```

### Cards
```blade
<div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10 hover:border-cyan-400/30 transition-all duration-300 hover:shadow-xl">
    {{-- Content --}}
</div>
```

### Stats Cards
```blade
<div class="group relative overflow-hidden bg-gradient-to-br from-indigo-500/10 to-purple-500/10 backdrop-blur-sm rounded-2xl p-5 border border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300">
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
    <div class="relative flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-xl">📊</div>
            <div>
                <p class="text-xs text-indigo-300 font-semibold uppercase tracking-wider mb-0.5">Label</p>
                <p class="text-2xl font-bold text-white">123</p>
            </div>
        </div>
    </div>
</div>
```

### Status Badges
```blade
<span class="relative flex h-2.5 w-2.5">
    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
</span>
```

### Empty States
```blade
<div class="flex items-center justify-center h-[140px] rounded-xl border-2 border-dashed border-white/5 bg-white/[0.02]">
    <span class="text-sm text-slate-600 font-medium">No items yet</span>
</div>
```

---

## Files to Update

- [ ] `livewire/task-manager.blade.php`
- [ ] `livewire/projects-index.blade.php`
- [ ] `livewire/docs-viewer.blade.php`
- [ ] `livewire/calendar.blade.php`
- [ ] `livewire/standup.blade.php`
- [ ] `livewire/activity-feed.blade.php`
- [ ] `livewire/org-chart.blade.php`
- [ ] `livewire/workspace-viewer.blade.php`
- [ ] `livewire/board/executive-board.blade.php`
- [ ] `livewire/global-search.blade.php`

---

## Color Palette Reference

| Token | Value | Usage |
|-------|-------|-------|
| `bg-primary` | `#0f0f1a` | Main background |
| `bg-secondary` | `#1a1a2e` | Cards, panels |
| `bg-tertiary` | `#252542` | Hover states |
| `fg-primary` | `#e4e4f0` | Primary text |
| `fg-secondary` | `#a0a0b8` | Subtitles |
| `fg-muted` | `#6b6b80` | Meta text |
| `accent` | `#7c3aed` | Interactive elements |
| `success` | `#10b981` | Positive status |
| `warning` | `#f59e0b` | Warnings |
| `error` | `#ef4444` | Errors |

---

**Last Updated:** Feb 26, 2026  
**Applied In:** Mission Control Polished
