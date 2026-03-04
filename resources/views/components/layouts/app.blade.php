<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
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
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('head')
</head>
<body class="antialiased bg-[#0f0f1a] text-[#e4e4f0] min-h-screen" x-data="sidebarApp()" x-init="initApp()">
    
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
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
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
                <a href="{{ route('workspace') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('workspace') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📁</span>
                    <span class="font-medium">Workspace</span>
                </a>
                <a href="{{ route('docs') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('docs') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📄</span>
                    <span class="font-medium">Docs</span>
                </a>
                <a href="{{ route('calendar') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('calendar') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📅</span>
                    <span class="font-medium">Calendar</span>
                </a>
                <a href="{{ route('standup') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('standup') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">🎤</span>
                    <span class="font-medium">Standup</span>
                </a>
                <a href="{{ route('activity') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('activity') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">📊</span>
                    <span class="font-medium">Activity</span>
                </a>
                <a href="{{ route('tests') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tests') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg flex-shrink-0">🧪</span>
                    <span class="font-medium">Tests</span>
                </a>
            </nav>
            
            <!-- Mobile Footer -->
            <div class="p-4 border-t border-[#1f1f35]">
                @include('layouts.partials.sidebar-footer')
            </div>
        </aside>
    </div>
    
    <div class="flex min-h-screen">
        <!-- Desktop Sidebar (push mode, hidden on mobile) -->
        <aside 
            :class="collapsed ? 'w-16' : 'w-64'"
            class="hidden md:block fixed inset-y-0 left-0 z-30 bg-[#12121f] border-r border-[#1f1f35] flex flex-col transition-all duration-300 ease-in-out"
            :aria-expanded="!collapsed"
            aria-label="Main navigation"
        >
            <!-- Toggle Button -->
            <button
                @click="toggleSidebar()"
                :class="collapsed ? 'left-16 -ml-3' : 'right-0 -mr-3'"
                class="absolute top-8 w-6 h-6 rounded-full bg-[#7c3aed] text-white shadow-lg hover:bg-[#6d28d9] transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 focus:ring-offset-[#12121f] flex items-center justify-center z-50"
                aria-label="Toggle navigation menu"
                :aria-expanded="!collapsed"
                title="Toggle navigation"
            >
                <span x-text="collapsed ? '→' : '←'" class="text-xs font-bold"></span>
            </button>
            
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-[#1f1f35]">
                <a href="{{ route('tasks') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-xl flex-shrink-0">
                        🌙
                    </div>
                    <div x-show="!collapsed" class="overflow-hidden">
                        <span class="font-semibold text-[#e4e4f0]">LunaOS</span>
                        <p class="text-xs text-[#6b6b80]">Dashboard</p>
                    </div>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <a href="{{ route('tasks') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tasks') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Tasks">
                    <span class="text-lg flex-shrink-0">📋</span>
                    <span x-show="!collapsed" class="font-medium">Tasks</span>
                </a>
                <a href="{{ route('org-chart') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('org-chart') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Org Chart">
                    <span class="text-lg flex-shrink-0">🏢</span>
                    <span x-show="!collapsed" class="font-medium">Org Chart</span>
                </a>
                <a href="{{ route('team') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('team*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Team">
                    <span class="text-lg flex-shrink-0">👥</span>
                    <span x-show="!collapsed" class="font-medium">Team</span>
                </a>
                <a href="{{ route('projects') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('projects*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Projects">
                    <span class="text-lg flex-shrink-0">📊</span>
                    <span x-show="!collapsed" class="font-medium">Projects</span>
                </a>
                <a href="{{ route('board') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('board*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Board">
                    <span class="text-lg flex-shrink-0">🎯</span>
                    <span x-show="!collapsed" class="font-medium">Board</span>
                </a>
                <a href="{{ route('kanban.index') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('kanban*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Kanban">
                    <span class="text-lg flex-shrink-0">📋</span>
                    <span x-show="!collapsed" class="font-medium">Kanban</span>
                </a>
                <a href="{{ route('workspace') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('workspace') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Workspace">
                    <span class="text-lg flex-shrink-0">📁</span>
                    <span x-show="!collapsed" class="font-medium">Workspace</span>
                </a>
                <a href="{{ route('docs') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('docs') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Docs">
                    <span class="text-lg flex-shrink-0">📄</span>
                    <span x-show="!collapsed" class="font-medium">Docs</span>
                </a>
                <a href="{{ route('calendar') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('calendar') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Calendar">
                    <span class="text-lg flex-shrink-0">📅</span>
                    <span x-show="!collapsed" class="font-medium">Calendar</span>
                </a>
                <a href="{{ route('standup') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('standup') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Standup">
                    <span class="text-lg flex-shrink-0">🎤</span>
                    <span x-show="!collapsed" class="font-medium">Standup</span>
                </a>
                <a href="{{ route('activity') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('activity') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Activity">
                    <span class="text-lg flex-shrink-0">📊</span>
                    <span x-show="!collapsed" class="font-medium">Activity</span>
                </a>
                <a href="{{ route('tests') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tests') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}"
                   title="Tests">
                    <span class="text-lg flex-shrink-0">🧪</span>
                    <span x-show="!collapsed" class="font-medium">Tests</span>
                </a>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-[#1f1f35]">
                @include('layouts.partials.sidebar-footer')
            </div>
        </aside>
        
        <!-- Main Content -->
        <main 
            :class="collapsed ? 'ml-16' : 'ml-64'"
            class="flex-1 transition-all duration-300 ease-in-out"
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
                <div class="flex-1 max-w-xl">
                    <livewire:global-search />
                </div>
                
                <!-- Header Actions -->
                <div class="flex items-center gap-4">
                    <!-- Live Toggle -->
                    <button class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#10b981]/20 text-[#10b981] text-sm font-medium hover:bg-[#10b981]/30 transition-colors">
                        <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                        Live
                    </button>
                    
                    <!-- Refresh -->
                    <button class="p-2 rounded-lg bg-[#1a1a2e] border border-[#2a2a40] text-[#a0a0b8] hover:text-[#e4e4f0] hover:border-[#7c3aed] transition-colors">
                        🔄
                    </button>
                    
                    <!-- Time -->
                    <span class="text-sm text-[#6b6b80]" id="current-time">
                        {{ now()->format('H:i') }}
                    </span>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>
    
    {{-- Toast Notifications --}}
    <livewire:toast-container />
    
    <!-- Livewire Config & Script -->
    <script>
        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: '{{ csrf_token() }}'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/gh/livewire/livewire@v3.7.10/dist/livewire.min.js"></script>
    <script>
        // Manually start Livewire after script loads
        if (typeof Livewire !== 'undefined') {
            Livewire.start();
        }
    </script>
    
    <script>
        // Register Alpine.js component BEFORE Alpine initializes
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebarApp', () => ({
                collapsed: false,
                mobileOpen: false,
                
                initApp() {
                    // Restore state from localStorage
                    const stored = localStorage.getItem('lunaos.sidebar.collapsed');
                    if (stored !== null) {
                        this.collapsed = (stored === 'true');
                    }
                    
                    // Escape key closes mobile overlay
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.mobileOpen) {
                            this.mobileOpen = false;
                        }
                    });
                },
                
                toggleSidebar() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('lunaos.sidebar.collapsed', this.collapsed);
                },
                
                openMobile() {
                    this.mobileOpen = true;
                },
                
                closeMobile() {
                    this.mobileOpen = false;
                }
            }));
        });
        
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
