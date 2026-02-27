# DESIGN_SYSTEM.md - LunaOS Design System

**Version:** 1.0  
**Last Updated:** February 26, 2026  
**Status:** Production

---

## Overview

LunaOS uses a **gradient glassmorphism** design system with a dark theme, built on Tailwind CSS v4. The system emphasizes depth, translucency, and vibrant accent colors against a dark slate background.

**Design Principles:**
1. **Clarity** - High contrast, readable typography
2. **Depth** - Layered surfaces with shadows and borders
3. **Translucency** - Backdrop blur for glassmorphic effects
4. **Vibrancy** - Purple/cyan gradients as primary brand
5. **Consistency** - Reusable patterns across all views

---

## Color Palette

### Base Colors

```css
/* Backgrounds */
--bg-primary: #0f0f1a;       /* Main background */
--bg-secondary: #12121f;     /* Cards, sidebars */
--bg-tertiary: #1a1a2e;      /* Nested cards */
--bg-elevated: #252542;      /* Hover states */

/* Text */
--text-primary: #e4e4f0;     /* Headings, important */
--text-secondary: #a0a0b8;   /* Body text */
--text-muted: #6b6b80;       /* Captions, metadata */
--text-disabled: #4a4a5a;    /* Disabled states */

/* Borders */
--border-subtle: rgba(255, 255, 255, 0.05);   /* Light borders */
--border-default: rgba(255, 255, 255, 0.1);   /* Standard borders */
--border-strong: rgba(255, 255, 255, 0.2);    /* Active/hover */
```

### Brand Colors

```css
/* Primary Gradient */
--gradient-primary: linear-gradient(135deg, #7c3aed, #06b6d4, #ec4899);
--gradient-primary-subtle: linear-gradient(135deg, 
  rgba(124, 58, 237, 0.1), 
  rgba(6, 182, 212, 0.1), 
  rgba(236, 72, 153, 0.1));

/* Accent Colors */
--accent-purple: #7c3aed;
--accent-cyan: #06b6d4;
--accent-pink: #ec4899;
--accent-indigo: #6366f1;
```

### Semantic Colors

```css
/* Success */
--success-bg: rgba(16, 185, 129, 0.1);
--success-border: rgba(16, 185, 129, 0.3);
--success-text: #10b981;
--success-bright: #34d399;

/* Error */
--error-bg: rgba(239, 68, 68, 0.1);
--error-border: rgba(239, 68, 68, 0.3);
--error-text: #ef4444;
--error-bright: #f87171;

/* Warning */
--warning-bg: rgba(245, 158, 11, 0.1);
--warning-border: rgba(245, 158, 11, 0.3);
--warning-text: #f59e0b;
--warning-bright: #fbbf24;

/* Info */
--info-bg: rgba(59, 130, 246, 0.1);
--info-border: rgba(59, 130, 246, 0.3);
--info-text: #3b82f6;
--info-bright: #60a5fa;
```

---

## Typography

### Font Stack

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
font-family-mono: 'JetBrains Mono', 'Fira Code', monospace;
```

### Type Scale

| Name | Size | Weight | Line Height | Usage |
|------|------|--------|-------------|-------|
| `text-xs` | 0.75rem (12px) | 400 | 1.5 | Captions, metadata |
| `text-sm` | 0.875rem (14px) | 400/500 | 1.5 | Body, labels |
| `text-base` | 1rem (16px) | 400 | 1.6 | Default text |
| `text-lg` | 1.125rem (18px) | 500/600 | 1.5 | Subheadings |
| `text-xl` | 1.25rem (20px) | 600/700 | 1.4 | Card titles |
| `text-2xl` | 1.5rem (24px) | 700 | 1.3 | Page headers |
| `text-3xl` | 1.875rem (30px) | 700 | 1.2 | Hero text |

### Font Weights

```css
font-weight-normal: 400;
font-weight-medium: 500;
font-weight-semibold: 600;
font-weight-bold: 700;
```

---

## Spacing & Layout

### Spacing Scale (4px Grid)

```css
--space-1: 0.25rem (4px)
--space-2: 0.5rem (8px)
--space-3: 0.75rem (12px)
--space-4: 1rem (16px)
--space-5: 1.25rem (20px)
--space-6: 1.5rem (24px)
--space-8: 2rem (32px)
--space-10: 2.5rem (40px)
--space-12: 3rem (48px)
--space-16: 4rem (64px)
```

### Container Widths

```css
--container-sm: 640px;
--container-md: 768px;
--container-lg: 1024px;
--container-xl: 1280px;
--container-2xl: 1536px;
```

### Grid System

**Default Layout:**
```html
<!-- Two-column: Sidebar + Main -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
  <div class="lg:col-span-1">Sidebar</div>
  <div class="lg:col-span-3">Main Content</div>
</div>

<!-- Three-column: 2/3 + 1/3 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">Primary</div>
  <div class="lg:col-span-1">Secondary</div>
</div>
```

---

## Components

### Cards

**Standard Card:**
```html
<div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
  <!-- Content -->
</div>
```

**Elevated Card (Hover):**
```html
<div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6 
            hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/10 
            transition-all duration-300">
  <!-- Content -->
</div>
```

**Header Card (Gradient):**
```html
<header class="relative overflow-hidden rounded-2xl 
               bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 
               backdrop-blur-xl border border-white/10 shadow-2xl">
  <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
  <div class="relative p-6">
    <!-- Content -->
  </div>
</header>
```

---

### Buttons

**Primary Button:**
```html
<button class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 
               text-white font-semibold rounded-xl 
               hover:from-purple-500 hover:to-pink-500 
               transition-all shadow-lg shadow-purple-500/25">
  Action
</button>
```

**Secondary Button:**
```html
<button class="px-4 py-2 bg-white/5 border border-white/10 
               text-slate-300 rounded-lg 
               hover:bg-white/10 transition-all">
  Action
</button>
```

**Icon Button:**
```html
<button class="p-2.5 rounded-xl bg-white/5 border border-white/10 
               text-slate-400 hover:text-white hover:bg-white/10 
               transition-all">
  <span class="text-lg">🔄</span>
</button>
```

---

### Inputs

**Text Input:**
```html
<input 
  type="text" 
  class="w-full bg-white/5 border border-white/10 rounded-lg 
         px-4 py-2 text-sm text-slate-300 placeholder-slate-500 
         focus:border-purple-500/50 focus:outline-none focus:ring-2 focus:ring-purple-500/20 
         transition-all"
  placeholder="Search..."
/>
```

**Textarea:**
```html
<textarea 
  class="w-full bg-white/5 border border-white/10 rounded-xl 
         px-4 py-3 text-slate-300 
         focus:border-purple-500/50 focus:outline-none 
         resize-none"
  rows="4"
></textarea>
```

**Select:**
```html
<select 
  class="bg-white/5 border border-white/10 rounded-lg 
         px-3 py-2 text-sm text-slate-300 
         focus:border-purple-500/50 focus:outline-none"
>
  <option>Option 1</option>
</select>
```

---

### Badges & Tags

**Badge (Status):**
```html
<span class="px-3 py-1 rounded-full text-xs font-semibold 
             bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
  Active
</span>
```

**Tag (Filter):**
```html
<button class="px-2 py-1 bg-purple-500/20 text-purple-300 
               border border-purple-500/30 rounded text-xs font-medium">
  #lunaos
</button>
```

---

### Icons

**Emoji Icons (2xl):**
```html
<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 
            flex items-center justify-center text-3xl shadow-xl">
  📁
</div>
```

**Icon Containers:**
```html
<!-- Small -->
<div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-500">
  →
</div>

<!-- Medium -->
<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 
            border border-purple-500/30 flex items-center justify-center text-lg">
  📄
</div>

<!-- Large -->
<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 
            flex items-center justify-center text-3xl shadow-xl">
  🎯
</div>
```

---

## Layout Patterns

### Page Structure

```html
<div class="space-y-6">
  <!-- Header -->
  <header class="...gradient glassmorphism...">
    <!-- Title + Stats -->
  </header>
  
  <!-- Main Content -->
  <section class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Sidebar -->
    <div class="lg:col-span-1">
      <!-- Sticky navigation/filters -->
    </div>
    
    <!-- Main -->
    <div class="lg:col-span-3">
      <!-- Primary content -->
    </div>
  </section>
</div>
```

### Section Headers

```html
<div class="flex items-center gap-3 mb-4">
  <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
  <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
    Section Title
  </h3>
  <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">
    12 items
  </span>
</div>
```

---

## Interaction States

### Hover

```css
/* Cards */
.card:hover {
  border-color: rgba(124, 58, 237, 0.3); /* purple-500/30 */
  box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.1);
}

/* Buttons */
.button:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Links */
.link:hover {
  color: #a78bfa; /* purple-400 */
}
```

### Active/Focus

```css
/* Inputs */
.input:focus {
  border-color: rgba(124, 58, 237, 0.5);
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.2);
}

/* Buttons */
.button:active {
  transform: scale(0.98);
}
```

### Disabled

```css
.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
```

---

## Animations

### Transitions

```css
/* Standard Transition */
transition-all duration-300;

/* Fast Transition */
transition-all duration-150;

/* Slow Transition */
transition-all duration-500;
```

### Hover Effects

```css
/* Scale on Hover */
.hover\:scale-110 {
  transform: scale(1.1);
}

/* Fade In */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Pulse (Live Indicator) */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

---

## Accessibility

### Color Contrast

All text meets **WCAG 2.1 AA** standards:
- Normal text: 4.5:1 minimum contrast ratio
- Large text: 3:1 minimum contrast ratio
- UI components: 3:1 minimum contrast ratio

### Focus Indicators

All interactive elements have visible focus states:
```html
<button class="focus:outline-none focus:ring-2 focus:ring-purple-500/20">
```

### ARIA Labels

```html
<button aria-label="Refresh data" class="...">
  🔄
</button>

<nav aria-label="Documentation navigation">
```

---

## Responsive Breakpoints

```css
/* Mobile First */
sm: 640px   /* Small tablets */
md: 768px   /* Tablets */
lg: 1024px  /* Laptops */
xl: 1280px  /* Desktops */
2xl: 1536px /* Large screens */
```

### Layout Changes

```html
<!-- Mobile: Stack, Desktop: Side-by-side -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
```

---

## Code Standards

### Blade Templates

**Always:**
- Use `?? []` for null arrays: `@foreach($items ?? [] as $item)`
- Use `@forelse` for empty states
- Use full `@php ... @endphp` blocks (not short syntax)
- Add `wire:key` for dynamic lists

**Never:**
- Inline `on*` event handlers (use Alpine.js: `x-on:`)
- Short `@php()` syntax (causes parse errors)
- Hardcode colors (use Tailwind classes)

### Livewire Components

**Required:**
- Try/catch in all public methods
- Proper data loading in `mount()` or lifecycle hooks
- Loading states for async operations
- Error handling with flash messages

**Example:**
```php
public function loadData()
{
    try {
        $this->items = Item::all()->toArray();
    } catch (\Exception $e) {
        $this->dispatchBrowserEvent('toast', [
            'message' => 'Failed to load data',
            'type' => 'error'
        ]);
        $this->items = [];
    }
}
```

---

## File Structure

```
resources/
├── css/
│   └── app.css (Tailwind imports + custom utilities)
├── js/
│   └── app.js (Alpine.js setup)
└── views/
    ├── components/
    │   └── layouts/
    │       └── app.blade.php (Main layout)
    ├── livewire/
    │   ├── calendar.blade.php
    │   ├── activity-feed.blade.php
    │   ├── docs-viewer.blade.php
    │   └── ... (11 views total)
    └── pages/
        └── ... (page wrappers)
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Feb 26, 2026 | Initial release - all 11 views complete |

---

## Related Files

- `POLISH_STATUS.md` - Project status and progress tracker
- `UI_STANDARDS.md` - Coding standards and best practices
- Tailwind CSS v4 Docs: https://tailwindcss.com/docs

---

**Maintained by:** Luna  
**Last Review:** February 26, 2026  
**Next Review:** March 26, 2026
