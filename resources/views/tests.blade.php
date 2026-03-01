<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Status - LunaOS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@2.0.4" defer></script>
    
    <!-- Livewire Styles (CDN) -->
    <link href="https://cdn.jsdelivr.net/gh/livewire/livewire@v3.7.10/dist/livewire.min.css" rel="stylesheet">
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
                <a href="{{ route('tasks') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35]">
                    <span class="text-lg">📋</span>
                    <span class="font-medium">Tasks</span>
                </a>
                <a href="{{ route('tests') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#e4e4f0] bg-[#1f1f35]">
                    <span class="text-lg">🧪</span>
                    <span class="font-medium">Tests</span>
                </a>
                <a href="{{ route('org-chart') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35]">
                    <span class="text-lg">🏢</span>
                    <span class="font-medium">Org Chart</span>
                </a>
                <a href="{{ route('agents.index') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35]">
                    <span class="text-lg">🤖</span>
                    <span class="font-medium">Agents</span>
                </a>
                <a href="{{ route('board') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35]">
                    <span class="text-lg">🎯</span>
                    <span class="font-medium">Board</span>
                </a>
                <a href="{{ route('docs') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35]">
                    <span class="text-lg">📄</span>
                    <span class="font-medium">Docs</span>
                </a>
                <a href="{{ route('activity') }}" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35]">
                    <span class="text-lg">📊</span>
                    <span class="font-medium">Activity</span>
                </a>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-[#1f1f35]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#252542] flex items-center justify-center text-sm">👤</div>
                    <div>
                        <p class="text-sm font-medium text-[#e4e4f0]">Kyle</p>
                        <p class="text-xs text-[#6b6b80]">Admin</p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Header -->
            <header class="h-16 bg-[#0f0f1a] border-b border-[#1f1f35] flex items-center justify-between px-6 sticky top-0 z-10">
                <h1 class="text-lg font-semibold text-[#e4e4f0]">LunaOS</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-[#6b6b80]">{{ now()->format('H:i') }}</span>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Page Header -->
                    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
                        <div class="relative flex items-center justify-between p-6">
                            <div class="flex items-center gap-5">
                                <div class="group relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">🧪</div>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-white tracking-tight">Test Status</h1>
                                    <p class="text-sm text-slate-400 font-medium mt-0.5">PHPUnit test suite overview and coverage</p>
                                </div>
                            </div>
                        </div>
                    </header>

                    {{-- Summary Cards --}}
                    <div class="grid grid-cols-4 gap-6">
                        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-[#6b6b80]">Total Tests</span>
                                <span class="text-2xl">📝</span>
                            </div>
                            <p class="text-3xl font-bold text-[#e4e4f0]">19</p>
                            <p class="text-xs text-[#6b6b80] mt-2">11 unit + 8 feature</p>
                        </div>
                        
                        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-[#6b6b80]">Coverage</span>
                                <span class="text-2xl">📊</span>
                            </div>
                            <p class="text-3xl font-bold text-[#f59e0b]">60%</p>
                            <p class="text-xs text-[#6b6b80] mt-2">Target: 80%</p>
                        </div>
                        
                        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-[#6b6b80]">Status</span>
                                <span class="text-2xl">✅</span>
                            </div>
                            <p class="text-3xl font-bold text-[#10b981]">Written</p>
                            <p class="text-xs text-[#6b6b80] mt-2">Config pending</p>
                        </div>
                        
                        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-[#6b6b80]">Last Run</span>
                                <span class="text-2xl">🕐</span>
                            </div>
                            <p class="text-lg font-bold text-[#e4e4f0]">Mar 1</p>
                            <p class="text-xs text-[#6b6b80] mt-2">17:37 EST</p>
                        </div>
                    </div>

                    {{-- Test Files - Unit Tests --}}
                    <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#2a2a40]">
                            <h3 class="text-lg font-semibold text-[#e4e4f0]">Unit Tests (Models)</h3>
                        </div>
                        <div class="divide-y divide-[#2a2a40]">
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-[#1f1f35] transition-colors">
                                <div>
                                    <p class="font-mono text-sm font-medium text-[#e4e4f0]">AgentModelTest.php</p>
                                    <p class="text-xs text-[#6b6b80] mt-1">Agent creation, relationships, strategy</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">3 tests</span>
                                    <span class="px-3 py-1 text-xs font-medium bg-yellow-500/20 text-yellow-400 rounded-full">Written</span>
                                </div>
                            </div>
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-[#1f1f35] transition-colors">
                                <div>
                                    <p class="font-mono text-sm font-medium text-[#e4e4f0]">TaskModelTest.php</p>
                                    <p class="text-xs text-[#6b6b80] mt-1">Task CRUD, agent FK, status transitions</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">3 tests</span>
                                    <span class="px-3 py-1 text-xs font-medium bg-yellow-500/20 text-yellow-400 rounded-full">Written</span>
                                </div>
                            </div>
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-[#1f1f35] transition-colors">
                                <div>
                                    <p class="font-mono text-sm font-medium text-[#e4e4f0]">ActivityLogModelTest.php</p>
                                    <p class="text-xs text-[#6b6b80] mt-1">Activity logging, JSON metadata</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">2 tests</span>
                                    <span class="px-3 py-1 text-xs font-medium bg-yellow-500/20 text-yellow-400 rounded-full">Written</span>
                                </div>
                            </div>
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-[#1f1f35] transition-colors">
                                <div>
                                    <p class="font-mono text-sm font-medium text-[#e4e4f0]">StandupModelTest.php</p>
                                    <p class="text-xs text-[#6b6b80] mt-1">Standups, deliverables, action items</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">3 tests</span>
                                    <span class="px-3 py-1 text-xs font-medium bg-yellow-500/20 text-yellow-400 rounded-full">Written</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Test Files - Feature Tests --}}
                    <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#2a2a40]">
                            <h3 class="text-lg font-semibold text-[#e4e4f0]">Feature Tests (Livewire)</h3>
                        </div>
                        <div class="divide-y divide-[#2a2a40]">
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-[#1f1f35] transition-colors">
                                <div>
                                    <p class="font-mono text-sm font-medium text-[#e4e4f0]">ModuleTests.php</p>
                                    <p class="text-xs text-[#6b6b80] mt-1">All 8 core modules load testing</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-xs font-medium bg-purple-500/20 text-purple-400 rounded-full">8 tests</span>
                                    <span class="px-3 py-1 text-xs font-medium bg-yellow-500/20 text-yellow-400 rounded-full">Written</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Known Issue Alert --}}
                    <div class="bg-yellow-500/10 border-l-4 border-yellow-500/50 p-4 rounded-lg">
                        <div class="flex gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h4 class="font-semibold text-yellow-400 mb-1">Known Issue: Multi-Database Testing</h4>
                                <p class="text-sm text-yellow-200/80">
                                    Tests are properly written but cannot execute due to SQLite multi-database configuration. 
                                    Phase 2 fix required.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Livewire Scripts (CDN) -->
    <script src="https://cdn.jsdelivr.net/gh/livewire/livewire@v3.7.10/dist/livewire.min.js"></script>
    <script>
        if (typeof Livewire !== 'undefined') {
            Livewire.start();
        }
    </script>
</body>
</html>
