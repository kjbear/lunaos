<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="lunaos">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LunaOS')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@2.0.4" defer></script>
    
    <!-- Alpine.js Sidebar App (define BEFORE Alpine CDN) -->
    <script>
        function sidebarApp() {
            return {
                collapsed: false,      // false = 256px expanded, true = 64px collapsed
                mobileOpen: false,
                expandedGroups: ['work'], // Track which nav groups are expanded
                
                initApp() {
                    // Restore sidebar collapsed state from localStorage
                    const stored = localStorage.getItem('lunaos.sidebar.collapsed');
                    if (stored !== null) {
                        this.collapsed = (stored === 'true');
                    }
                    
                    // Restore expanded nav groups from localStorage
                    const storedGroups = localStorage.getItem('lunaos.nav.groups');
                    if (storedGroups !== null) {
                        this.expandedGroups = JSON.parse(storedGroups);
                    }
                    
                    // Escape key closes mobile overlay
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.mobileOpen) {
                            this.mobileOpen = false;
                        }
                    });
                    
                    // Update time immediately to avoid flash
                    const now = new Date();
                    const time = now.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: false 
                    });
                    const el = document.getElementById('current-time');
                    if (el) el.textContent = time;
                },
                
                toggleSidebar() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('lunaos.sidebar.collapsed', this.collapsed);
                },
                
                toggleGroup(groupName) {
                    const index = this.expandedGroups.indexOf(groupName);
                    if (index > -1) {
                        // Collapse: remove from array
                        this.expandedGroups.splice(index, 1);
                    } else {
                        // Expand: add to array
                        this.expandedGroups.push(groupName);
                    }
                    localStorage.setItem('lunaos.nav.groups', JSON.stringify(this.expandedGroups));
                },
                
                isGroupExpanded(groupName) {
                    return this.expandedGroups.includes(groupName);
                },
                
                openMobile() {
                    this.mobileOpen = true;
                },
                
                closeMobile() {
                    this.mobileOpen = false;
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Prevent flash of unstyled content -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('head')
</head>
<body class="antialiased bg-base-100 text-base-content min-h-screen overflow-hidden" x-data="sidebarApp()" x-init="initApp()" x-cloak>
    
    <!-- Mobile Sidebar Overlay (visible only when mobileOpen is true) -->
    <div x-show="mobileOpen" class="md:hidden" style="display: none;">
        <!-- Backdrop -->
        <div 
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileOpen = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-40"
        ></div>
        
        <!-- Mobile Sidebar (slide-in overlay) -->
        <aside 
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#12121f] border-r border-[#1f1f35] flex flex-col"
            aria-label="Mobile navigation"
        >
            <!-- Mobile Header -->
            <div class="p-4 border-b border-[#1f1f35] flex items-center justify-between">
                <a href="{{ route('tasks') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-xl flex-shrink-0">
                        🌙
                    </div>
                    <span class="font-semibold text-[#e4e4f0]">LunaOS</span>
                </a>
                <button 
                    @click="mobileOpen = false"
                    class="p-2 text-[#a0a0b8] hover:text-[#e4e4f0] focus:outline-none focus:ring-2 focus:ring-[#7c3aed] rounded-lg"
                    aria-label="Close navigation menu"
                >
                    ✕
                </button>
            </div>
            
            <!-- Mobile Nav Items -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <a href="{{ route('tasks') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tasks') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📋</span>
                    <span class="font-medium">Tasks</span>
                </a>
                <a href="{{ route('org-chart') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('org-chart') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">🏢</span>
                    <span class="font-medium">Org Chart</span>
                </a>
                <a href="{{ route('team') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('team*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">👥</span>
                    <span class="font-medium">Team</span>
                </a>
                <a href="{{ route('projects') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('projects*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📊</span>
                    <span class="font-medium">Projects</span>
                </a>
                <a href="{{ route('board') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('board*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">🎯</span>
                    <span class="font-medium">Board</span>
                </a>
                <a href="{{ route('kanban.index') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('kanban*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📋</span>
                    <span class="font-medium">Kanban</span>
                </a>
            </nav>
            
            <!-- Mobile Footer -->
            <div class="p-4 border-t border-[#1f1f35]">
                @include('layouts.partials.sidebar-footer')
            </div>
        </aside>
    </div>
    
    <!-- Desktop Layout -->
    <div class="flex min-h-screen">
        <!-- Desktop Sidebar (always visible, just changes width) -->
        <aside 
            :class="collapsed ? 'w-16' : 'w-64'"
            class="hidden md:block fixed inset-y-0 left-0 z-30 bg-[#12121f] border-r border-[#1f1f35] flex flex-col transition-all duration-300 ease-in-out overflow-hidden"
            :aria-expanded="!collapsed"
            aria-label="Main navigation"
        >
            <!-- Collapse/Expand Toggle Button -->
            <button
                @click="toggleSidebar()"
                :class="collapsed ? 'left-8' : 'right-0'"
                class="absolute top-8 w-6 h-6 rounded-full bg-[#7c3aed] text-white shadow-lg hover:bg-[#6d28d9] transition-all focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 focus:ring-offset-[#12121f] flex items-center justify-center z-50"
                aria-label="Toggle navigation"
                :aria-expanded="!collapsed"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <span x-text="collapsed ? '→' : '←'" class="text-xs font-bold"></span>
            </button>
            
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-[#1f1f35]">
                <a href="{{ route('tasks') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-xl flex-shrink-0">
                        🌙
                    </div>
                    <div x-show="!collapsed" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-200" class="overflow-hidden">
                        <span class="font-semibold text-[#e4e4f0]">LunaOS</span>
                        <p class="text-xs text-[#6b6b80]">Dashboard</p>
                    </div>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-4 overflow-y-auto">
                
                {{-- 📋 WORK --}}
                <div>
                    <button 
                        @click="toggleGroup('work')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Work' : ''">
                        <span class="flex items-center gap-2">
                            <span>📋</span>
                            <span x-show="!collapsed">Work</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('work') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('work')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        <a href="{{ route('tasks') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tasks') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">✓</span>
                            <span class="text-sm font-medium">Tasks</span>
                        </a>
                        <a href="{{ route('board') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('board*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">◆</span>
                            <span class="text-sm font-medium">Board</span>
                        </a>
                    </div>
                </div>
                
                {{-- 👥 TEAM --}}
                <div>
                    <button 
                        @click="toggleGroup('team')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Team' : ''">
                        <span class="flex items-center gap-2">
                            <span>👥</span>
                            <span x-show="!collapsed">Team</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('team') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('team')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        <a href="{{ route('org-chart') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('org-chart') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">🏢</span>
                            <span class="text-sm font-medium">Org Chart</span>
                        </a>
                    </div>
                </div>
                
                {{-- 📊 PROJECTS --}}
                <div>
                    <button 
                        @click="toggleGroup('projects')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Projects' : ''">
                        <span class="flex items-center gap-2">
                            <span>📊</span>
                            <span x-show="!collapsed">Projects</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('projects') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('projects')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        <a href="{{ route('projects') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('projects*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">📊</span>
                            <span class="text-sm font-medium">Projects</span>
                        </a>
                    </div>
                </div>
                
                {{--  WORKSPACE --}}
                <div>
                    <button 
                        @click="toggleGroup('workspace')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Workspace' : ''">
                        <span class="flex items-center gap-2">
                            <span>🏢</span>
                            <span x-show="!collapsed">Workspace</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('workspace') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('workspace')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        <a href="{{ route('workspace') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('workspace') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">📁</span>
                            <span class="text-sm font-medium">Files</span>
                        </a>
                        <a href="{{ route('activity') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('activity') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">📄</span>
                            <span class="text-sm font-medium">Activity Feed</span>
                        </a>
                    </div>
                </div>
                
                {{-- 📅 CALENDAR & EVENTS --}}
                <div>
                    <button 
                        @click="toggleGroup('calendar')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Calendar' : ''">
                        <span class="flex items-center gap-2">
                            <span>📅</span>
                            <span x-show="!collapsed">Calendar</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('calendar') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('calendar')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        <a href="{{ route('calendar') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('calendar') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">📅</span>
                            <span class="text-sm font-medium">Calendar</span>
                        </a>
                        {{-- Standup - route doesn't exist yet, will add later --}}
                    </div>
                </div>
                
                {{-- 📈 INSIGHTS --}}
                <div>
                    <button 
                        @click="toggleGroup('insights')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Insights' : ''">
                        <span class="flex items-center gap-2">
                            <span>📈</span>
                            <span x-show="!collapsed">Insights</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('insights') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('insights')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        {{-- Move Activity Feed to Workspace, keep this section ready for future items --}}
                        <div class="px-3 py-2 text-xs text-[#6b6b80] italic">
                            <span x-show="!collapsed">More insights coming soon...</span>
                        </div>
                    </div>
                </div>
                
                {{-- 🧪 DEVELOPMENT --}}
                <div>
                    <button 
                        @click="toggleGroup('development')"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider hover:text-[#a0a0b8] focus:outline-none"
                        :title="collapsed ? 'Development' : ''">
                        <span class="flex items-center gap-2">
                            <span>🧪</span>
                            <span x-show="!collapsed">Development</span>
                        </span>
                        <span x-show="!collapsed" 
                              x-text="isGroupExpanded('development') ? '▼' : '▶'"
                              class="text-xs transition-transform"></span>
                    </button>
                    <div x-show="!collapsed && isGroupExpanded('development')" 
                         x-collapse
                         class="mt-1 space-y-1 pl-2">
                        <a href="{{ route('tests') }}" 
                           class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tests') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                            <span class="text-sm">🧪</span>
                            <span class="text-sm font-medium">Tests</span>
                        </a>
                    </div>
                </div>
                
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-[#1f1f35]" x-show="!collapsed" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-200">
                @include('layouts.partials.sidebar-footer')
            </div>
        </aside>
        
        <!-- Main Content -->
        <main 
            :class="collapsed ? 'ml-16' : 'ml-64'"
            class="flex-1 transition-all duration-300 ease-in-out min-h-screen overflow-y-auto"
        >
            <!-- Header -->
            <header class="h-16 bg-[#0f0f1a] border-b border-[#1f1f35] flex items-center justify-between px-6 sticky top-0 z-10">
                <!-- Mobile Menu Button (visible only on mobile) -->
                <button 
                    @click="mobileOpen = true"
                    class="md:hidden p-2 rounded-lg bg-[#1a1a2e] border border-[#2a2a40] text-[#a0a0b8] hover:text-[#e4e4f0] focus:outline-none focus:ring-2 focus:ring-[#7c3aed]"
                    aria-label="Open navigation menu"
                >
                    ☰
                </button>
                
                <!-- Search -->
                @livewire('global-search')
                
                <!-- Right Side: Time, User -->
                <div class="flex items-center gap-4">
                    <!-- Clock -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-[#1a1a2e] rounded-lg border border-[#2a2a40]">
                        <span class="text-[#6b6b80]">🌙</span>
                        <span id="current-time" class="text-sm font-medium text-[#a0a0b8] font-mono"></span>
                    </div>
                    
                    <!-- User Avatar (daisyUI) -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 bg-gradient-to-br from-primary/20 to-secondary/20">
                                <img src="https://api.dicebear.com/9.x/avataaars/svg?seed=User" alt="User avatar">
                            </div>
                        </label>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Livewire Config & Script -->
    <script>
        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: '{{ csrf_token() }}'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/gh/livewire/livewire@v3.7.10/dist/livewire.min.js"></script>
    
    <script>
        // Update time every minute
        setInterval(() => {
            const now = new Date();
            const time = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
            const el = document.getElementById('current-time');
            if (el) el.textContent = time;
        }, 60000);
        
        // HTMX configuration
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + K for search focus
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                // Dispatch Livewire event to open global search
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('openSearch');
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
