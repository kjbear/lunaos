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
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('head')
</head>
<body class="antialiased bg-[#0f0f1a] text-[#e4e4f0] min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#12121f] border-r border-[#1f1f35] flex flex-col fixed h-full">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-[#1f1f35]">
                <a href="{{ route('tasks') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-xl">
                        🌙
                    </div>
                    <div>
                        <span class="font-semibold text-[#e4e4f0]">LunaOS</span>
                        <p class="text-xs text-[#6b6b80]">Dashboard</p>
                    </div>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1">
                <a href="{{ route('tasks') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tasks') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📋</span>
                    <span class="font-medium">Tasks</span>
                </a>
                <a href="{{ route('org-chart') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('org-chart') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">🏢</span>
                    <span class="font-medium">Org Chart</span>
                </a>
                <a href="{{ route('team') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('team*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">👥</span>
                    <span class="font-medium">Team</span>
                </a>
                <a href="{{ route('projects') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('projects*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📊</span>
                    <span class="font-medium">Projects</span>
                </a>
                <a href="{{ route('board') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('board*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">🎯</span>
                    <span class="font-medium">Board</span>
                </a>
                <a href="{{ route('kanban.index') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('kanban*') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📋</span>
                    <span class="font-medium">Kanban</span>
                </a>
                <a href="{{ route('workspace') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('workspace') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📁</span>
                    <span class="font-medium">Workspace</span>
                </a>
                <a href="{{ route('docs') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('docs') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📄</span>
                    <span class="font-medium">Docs</span>
                </a>
                <a href="{{ route('calendar') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('calendar') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📅</span>
                    <span class="font-medium">Calendar</span>
                </a>
                <a href="{{ route('standup') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('standup') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">🎤</span>
                    <span class="font-medium">Standup</span>
                </a>
                <a href="{{ route('activity') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('activity') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">📊</span>
                    <span class="font-medium">Activity</span>
                </a>
                <a href="{{ route('tests') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] {{ request()->routeIs('tests') ? 'bg-[#1f1f35] text-[#e4e4f0]' : '' }}">
                    <span class="text-lg">🧪</span>
                    <span class="font-medium">Tests</span>
                </a>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-[#1f1f35]">
                @auth
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-400 to-purple-500 flex items-center justify-center text-sm shadow-lg">
                        👤
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-[#e4e4f0] truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#6b6b80] truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="text-[#6b6b80] hover:text-red-400 transition-colors"
                       title="Logout">
                        🚪
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
                @else
                <div class="flex items-center justify-center">
                    <a href="{{ route('login') }}" class="text-sm text-[#a0a0b8] hover:text-[#e4e4f0] transition-colors">
                        🔐 Login to LunaOS
                    </a>
                </div>
                @endauth
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Header -->
            <header class="h-16 bg-[#0f0f1a] border-b border-[#1f1f35] flex items-center justify-between px-6 sticky top-0 z-10">
                <!-- Search -->
                <div class="flex-1 max-w-xl">
                    <livewire:global-search />
                </div>
                
                <!-- Header Actions -->
                <div class="flex items-center gap-4">
                    <!-- Live Toggle -->
                    <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#10b981]/20 text-[#10b981] text-sm font-medium hover:bg-[#10b981]/30 transition-colors">
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
        // Update time every minute
        setInterval(() => {
            const now = new Date();
            const time = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
            document.getElementById('current-time').textContent = time;
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
                Livewire.dispatch('openSearch');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>