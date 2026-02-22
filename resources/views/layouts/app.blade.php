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
                <a href="{{ route('home') }}" class="flex items-center gap-3">
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
                <a href="{{ route('home') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] {{ request()->routeIs('home') ? 'active' : '' }}">
                    <span class="text-lg">📋</span>
                    <span class="font-medium">Tasks</span>
                </a>
                <a href="{{ route('org-chart') }}" 
                   class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] {{ request()->routeIs('org-chart') ? 'active' : '' }}">
                    <span class="text-lg">🏢</span>
                    <span class="font-medium">Org Chart</span>
                </a>
                <a href="#" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0]">
                    <span class="text-lg">📁</span>
                    <span class="font-medium">Workspace</span>
                </a>
                <a href="#" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0]">
                    <span class="text-lg">📄</span>
                    <span class="font-medium">Docs</span>
                </a>
                <a href="#" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0]">
                    <span class="text-lg">📅</span>
                    <span class="font-medium">Calendar</span>
                </a>
                <a href="#" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0]">
                    <span class="text-lg">🎤</span>
                    <span class="font-medium">Standup</span>
                </a>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-[#1f1f35]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#252542] flex items-center justify-center text-sm">
                        👤
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-[#e4e4f0] truncate">Kyle</p>
                        <p class="text-xs text-[#6b6b80]">Admin</p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Header -->
            <header class="h-16 bg-[#0f0f1a] border-b border-[#1f1f35] flex items-center justify-between px-6 sticky top-0 z-10">
                <!-- Search -->
                <div class="flex-1 max-w-xl">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b6b80]">🔍</span>
                        <input type="text" 
                               placeholder="Search tasks, docs, agents..." 
                               class="w-full bg-[#1a1a2e] border border-[#2a2a40] rounded-lg pl-10 pr-4 py-2 text-sm text-[#e4e4f0] placeholder-[#6b6b80] focus:outline-none focus:border-[#7c3aed]">
                    </div>
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
            </div>
        </main>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
    
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
                document.querySelector('input[placeholder*="Search"]').focus();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>