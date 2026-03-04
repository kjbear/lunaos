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
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@2.0.4" defer></script>
    
    <!-- Prevent flash of unstyled content -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('head')
</head>
<body class="antialiased min-h-screen overflow-hidden" x-cloak>
    
    <!-- Mobile Sidebar (Alpine.js slide-over) -->
    <div 
        x-data="{ mobileOpen: false }" 
        @open.window="mobileOpen = true"
        @close.window="mobileOpen = false"
        class="md:hidden"
    >
        <!-- Backdrop -->
        <div 
            x-show="mobileOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileOpen = false"
            class="fixed inset-0 bg-black/50 z-40"
        ></div>
        
        <!-- Sidebar Panel -->
        <div
            x-show="mobileOpen"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-base-200 border-r border-base-300 flex flex-col"
        >
            <!-- Header -->
            <div class="p-4 border-b border-base-300 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-xl">🌙</div>
                    <div>
                        <span class="font-semibold">LunaOS</span>
                        <p class="text-xs text-base-content/60">Dashboard</p>
                    </div>
                </div>
                <button @click="mobileOpen = false" class="btn btn-ghost btn-square btn-sm">✕</button>
            </div>
            
            <!-- Nav Items -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('tasks') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('tasks') ? 'btn-active' : '' }}">
                    <span class="text-xl">📋</span> Tasks
                </a>
                <a href="{{ route('org-chart') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('org-chart') ? 'btn-active' : '' }}">
                    <span class="text-xl">🏢</span> Org Chart
                </a>
                <a href="{{ route('team') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('team*') ? 'btn-active' : '' }}">
                    <span class="text-xl">👥</span> Team
                </a>
                <a href="{{ route('projects') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('projects*') ? 'btn-active' : '' }}">
                    <span class="text-xl">📊</span> Projects
                </a>
                <a href="{{ route('board') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('board*') ? 'btn-active' : '' }}">
                    <span class="text-xl">🎯</span> Board
                </a>
                <a href="{{ route('kanban.index') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('kanban*') ? 'btn-active' : '' }}">
                    <span class="text-xl">📋</span> Kanban
                </a>
            </nav>
            
            <!-- Footer -->
            <div class="p-4 border-t border-base-300">
                @include('layouts.partials.sidebar-footer')
            </div>
        </div>
    </div>
    
    <!-- Desktop Layout -->
    <div class="flex min-h-screen">
        <!-- Desktop Sidebar -->
        <aside 
            x-data="{ collapsed: localStorage.getItem('lunaos.sidebar.collapsed') === 'true' }"
            x-init="$watch('collapsed', value => localStorage.setItem('lunaos.sidebar.collapsed', value))"
            :class="collapsed ? 'w-20' : 'w-64'"
            class="hidden md:block fixed inset-y-0 left-0 z-30 bg-base-200 border-r border-base-300 flex flex-col transition-all duration-300 ease-in-out"
        >
            <!-- Header -->
            <div class="p-4 border-b border-base-300 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-xl flex-shrink-0">🌙</div>
                <div x-show="!collapsed" x-transition class="overflow-hidden">
                    <span class="font-semibold">LunaOS</span>
                    <p class="text-xs text-base-content/60">Dashboard</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <a href="{{ route('tasks') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('tasks') ? 'btn-active' : '' }}" :title="collapsed ? 'Tasks' : ''">
                    <span class="text-xl">📋</span>
                    <span x-show="!collapsed" x-transition>Tasks</span>
                </a>
                <a href="{{ route('org-chart') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('org-chart') ? 'btn-active' : '' }}" :title="collapsed ? 'Org Chart' : ''">
                    <span class="text-xl">🏢</span>
                    <span x-show="!collapsed" x-transition>Org Chart</span>
                </a>
                <a href="{{ route('team') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('team*') ? 'btn-active' : '' }}" :title="collapsed ? 'Team' : ''">
                    <span class="text-xl">👥</span>
                    <span x-show="!collapsed" x-transition>Team</span>
                </a>
                <a href="{{ route('projects') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('projects*') ? 'btn-active' : '' }}" :title="collapsed ? 'Projects' : ''">
                    <span class="text-xl">📊</span>
                    <span x-show="!collapsed" x-transition>Projects</span>
                </a>
                <a href="{{ route('board') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('board*') ? 'btn-active' : '' }}" :title="collapsed ? 'Board' : ''">
                    <span class="text-xl">🎯</span>
                    <span x-show="!collapsed" x-transition>Board</span>
                </a>
                <a href="{{ route('kanban.index') }}" class="btn btn-ghost justify-start gap-3 w-full {{ request()->routeIs('kanban*') ? 'btn-active' : '' }}" :title="collapsed ? 'Kanban' : ''">
                    <span class="text-xl">📋</span>
                    <span x-show="!collapsed" x-transition>Kanban</span>
                </a>
            </nav>
            
            <!-- Toggle -->
            <div class="p-3 border-t border-base-300">
                <button @click="collapsed = !collapsed" class="btn btn-sm btn-primary w-full">
                    <span x-text="collapsed ? '→' : '←'" class="font-bold"></span>
                    <span x-show="!collapsed" x-transition>Collapse</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main 
            x-data="{ getMargin() { return window.innerWidth >= 768 ? (localStorage.getItem('lunaos.sidebar.collapsed') === 'true' ? 'ml-20' : 'ml-64') : 'ml-0' } }"
            :class="getMargin()"
            class="flex-1 transition-all duration-300 ease-in-out min-h-screen"
        >
            <!-- Header -->
            <header class="h-16 bg-base-100 border-b border-base-300 flex items-center justify-between px-6 sticky top-0 z-10">
                <!-- Mobile Menu Button -->
                <button @click="$dispatch('open')" class="md:hidden btn btn-ghost btn-square">
                    ☰
                </button>
                
                <!-- Search -->
                @livewire('global-search')
                
                <!-- Right Side -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-base-200 rounded-lg border border-base-300">
                        <span>🌙</span>
                        <span id="current-time" class="text-sm font-medium font-mono"></span>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 border border-primary/30">
                                <img src="https://api.dicebear.com/9.x/avataaars/svg?seed=User" alt="User avatar">
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Livewire Scripts -->
    <script>
        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: '{{ csrf_token() }}'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/gh/livewire/livewire@v3.7.10/dist/livewire.min.js"></script>
    
    <script>
        // Update time
        setInterval(() => {
            const now = new Date();
            const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
            const el = document.getElementById('current-time');
            if (el) el.textContent = time;
        }, 60000);
        
        // HTMX
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        });
        
        // Keyboard shortcut
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('openSearch');
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
