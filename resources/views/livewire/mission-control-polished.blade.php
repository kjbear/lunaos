<div class="mission-control" x-data="{
    handleActivity(detail) { console.log('Activity received:', detail); },
    handleDragStart(e) { e.dataTransfer.setData('taskId', e.target.dataset.taskId); e.target.style.opacity = '0.4'; },
    handleDrop(e, newStatus) { e.preventDefault(); const taskId = e.dataTransfer.getData('taskId'); console.log('Task moved:', taskId, '→', newStatus); }
}">
    {{-- Polished Mission Control Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        {{-- Subtle glow effect --}}
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                {{-- Animated logo container --}}
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">
                        🚀
                    </div>
                </div>
                
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Mission Control</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Real-time subagent orchestration</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                {{-- Live status badge --}}
                <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-sm font-semibold text-emerald-400">{{ $stats['active_agents'] ?? 1 }} Active</span>
                </div>
                
                {{-- Action buttons --}}
                <div class="flex items-center gap-2">
                    <button 
                        wire:click="loadAgents" 
                        wire:loading.attr="disabled"
                        class="group relative p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all duration-200 disabled:opacity-50"
                        title="Refresh"
                    >
                        <span class="group-hover:rotate-180 transition-transform duration-500 block">↻</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Section 1: Agent Grid (Enhanced) --}}
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Agent Fleet</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">{{ count($agents) }} agents</span>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            @foreach($agents as $agent)
            <div class="group relative">
                {{-- Card background with hover glow --}}
                <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                
                {{-- Main card --}}
                <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border {{ $agent['status'] === 'running' ? 'border-emerald-500/30 shadow-[0_0_30px_rgba(16,185,129,0.1)]' : 'border-white/10' }} hover:border-cyan-400/30 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5">
                    {{-- Status indicator --}}
                    @if($agent['status'] === 'running')
                    <div class="absolute top-3 right-3">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                    </div>
                    @endif
                    
                    {{-- Avatar with gradient ring --}}
                    <div class="relative inline-block mb-3">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-xl blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
                        <div class="relative w-12 h-12 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-2xl border border-white/10">
                            {{ $agent['avatar'] }}
                        </div>
                    </div>
                    
                    {{-- Agent info --}}
                    <div class="space-y-1.5">
                        <div class="font-semibold text-white text-sm truncate" title="{{ $agent['name'] }}">{{ $agent['name'] }}</div>
                        <div class="text-xs text-slate-400 font-medium truncate">{{ $agent['role'] }}</div>
                    </div>
                    
                    {{-- Badges --}}
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                        <span class="text-xs px-2.5 py-1 rounded-lg {{ $agent['model'] === 'GLM-5' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' }} font-medium">
                            {{ $agent['model'] }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono">D{{ $agent['depth'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Section 2: Task Pipeline + Activity Feed --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Task Pipeline (Enhanced) --}}
        <div class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Task Pipeline</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">
                    {{ collect($tasks)->flatten()->count() }} total
                </span>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @php
                $columnColors = [
                    'todo' => ['from' => 'from-slate-600', 'to' => 'to-slate-700', 'bg' => 'bg-slate-500'],
                    'in_progress' => ['from' => 'from-cyan-600', 'to' => 'to-blue-700', 'bg' => 'bg-cyan-500'],
                    'blocked' => ['from' => 'from-amber-600', 'to' => 'to-orange-700', 'bg' => 'bg-amber-500'],
                    'done' => ['from' => 'from-emerald-600', 'to' => 'to-green-700', 'bg' => 'bg-emerald-500'],
                ];
                @endphp
                
                @foreach(['todo', 'in_progress', 'blocked', 'done'] as $column)
                @php
                    $colors = $columnColors[$column] ?? ['from' => 'from-slate-600', 'to' => 'to-slate-700', 'bg' => 'bg-slate-500'];
                @endphp
                <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10 hover:border-white/20 transition-all">
                    {{-- Column header --}}
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-gradient-to-br {{ $colors['from'] }} {{ $colors['to'] }}"></span>
                            <h3 class="font-semibold text-white text-sm capitalize">{{ str_replace('_', ' ', $column) }}</h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-400 bg-white/5 px-2.5 py-1 rounded-lg border border-white/10">
                            {{ count($tasks[$column] ?? []) }}
                        </span>
                    </div>
                    
                    {{-- Task list --}}
                    <div class="space-y-2 min-h-[140px]" 
                         data-status="{{ $column }}"
                         x-on:dragover.prevent
                         x-on:drop="handleDrop($event, '{{ $column }}')">
                        @php $columnTasks = $tasks[$column] ?? []; @endphp
                        @forelse($columnTasks as $task)
                        <div class="group bg-gradient-to-br from-white/5 to-transparent rounded-xl p-3 border border-white/10 hover:border-cyan-400/30 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-200 cursor-grab active:cursor-grabbing"
                             draggable="true"
                             data-task-id="{{ $task['id'] }}"
                             x-on:dragstart="handleDragStart($event)">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="text-xs text-white font-medium line-clamp-2">{{ $task['title'] ?? 'Untitled' }}</div>
                            </div>
                            @if(isset($task['assigned_to']))
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-cyan-400 font-medium">{{ $task['assigned_to'] }}</span>
                            </div>
                            @endif
                            @if(isset($task['priority']))
                            <div class="mt-2">
                                <span class="text-xs px-2 py-0.5 rounded {{ $task['priority'] === 'high' ? 'bg-red-500/20 text-red-400' : 'bg-slate-500/20 text-slate-400' }}">
                                    {{ ucfirst($task['priority']) }}
                                </span>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="flex items-center justify-center h-[140px] rounded-xl border-2 border-dashed border-white/5 bg-white/[0.02]">
                            <span class="text-sm text-slate-600 font-medium">No tasks</span>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Activity Feed (Enhanced) --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-cyan-500 rounded-full"></div>
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Activity Feed</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">Real-time</span>
            </div>
            
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10 h-[400px] overflow-y-auto space-y-2 custom-scrollbar" id="activity-feed">
                @forelse($activity as $item)
                <div class="group flex items-start gap-3 p-3 rounded-xl hover:bg-white/[0.03] transition-all duration-200 {{ ($item['status'] ?? '') === 'running' ? 'border-l-2 border-amber-500 bg-amber-500/5' : 'border-l-2 border-emerald-500' }}">
                    {{-- Status icon --}}
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-lg {{ ($item['status'] ?? '') === 'running' ? 'bg-amber-500/20' : 'bg-emerald-500/20' }}">
                        @if(($item['status'] ?? '') === 'running')
                        ⚡
                        @elseif(($item['status'] ?? '') === 'done')
                        ✅
                        @else
                        ❌
                        @endif
                    </div>
                    
                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white text-sm">{{ $item['agent_name'] ?? 'Unknown' }}</span>
                            <span class="text-xs text-slate-500 bg-white/5 px-1.5 py-0.5 rounded">{{ $item['action'] ?? '' }}</span>
                        </div>
                        @if($item['task'] ?? '')
                        <div class="text-xs text-slate-400 line-clamp-2 mb-1.5">{{ $item['task'] }}</div>
                        @endif
                        <div class="text-xs text-slate-600 font-mono">{{ $item['created_at'] ?? '' }}</div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-[400px] text-center">
                    <div class="text-4xl mb-3 opacity-50">📭</div>
                    <p class="text-sm text-slate-500 font-medium">No activity yet</p>
                    <p class="text-xs text-slate-600 mt-1">Actions will appear here in real-time</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Section 3: Workload Chart + Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Workload Distribution (Enhanced) --}}
        <div class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-pink-400 to-rose-500 rounded-full"></div>
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Workload Distribution</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">Last 7 days</span>
            </div>
            
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                <div id="workload-chart" class="h-[240px]"></div>
            </div>
        </div>

        {{-- Quick Stats (Enhanced) --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-amber-400 to-orange-500 rounded-full"></div>
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Quick Stats</h2>
            </div>
            
            <div class="space-y-3">
                {{-- Total Tasks Card --}}
                <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-500/10 to-purple-500/10 backdrop-blur-sm rounded-2xl p-5 border border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-xl">
                                📊
                            </div>
                            <div>
                                <p class="text-xs text-indigo-300 font-semibold uppercase tracking-wider mb-0.5">Total Tasks</p>
                                <p class="text-2xl font-bold text-white">{{ $stats['total_tasks'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tokens Used Card --}}
                <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500/10 to-blue-500/10 backdrop-blur-sm rounded-2xl p-5 border border-cyan-500/20 hover:border-cyan-500/40 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-cyan-500/20 border border-cyan-500/30 flex items-center justify-center text-xl">
                                🪙
                            </div>
                            <div>
                                <p class="text-xs text-cyan-300 font-semibold uppercase tracking-wider mb-0.5">Tokens Used</p>
                                <p class="text-2xl font-bold text-white">{{ number_format($stats['total_tokens'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cost Card --}}
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-500/10 to-green-500/10 backdrop-blur-sm rounded-2xl p-5 border border-emerald-500/20 hover:border-emerald-500/40 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl">
                                💰
                            </div>
                            <div>
                                <p class="text-xs text-emerald-300 font-semibold uppercase tracking-wider mb-0.5">Est. Cost</p>
                                <p class="text-2xl font-bold text-emerald-400">$0.00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- System Status --}}
            <div class="mt-4 bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">System Status</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">WebSocket</span>
                        <span class="flex items-center gap-1.5 text-xs text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Connected
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Database</span>
                        <span class="flex items-center gap-1.5 text-xs text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Healthy
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Uptime</span>
                        <span class="text-xs text-slate-300 font-mono">24h 13m</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Scripts --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const workloadData = @json($workload);
            
            const options = {
                series: [{
                    name: 'Tasks',
                    data: workloadData.series?.[0]?.data || [5, 3, 2, 4, 1]
                }],
                chart: {
                    type: 'bar',
                    height: 240,
                    toolbar: { show: false },
                    background: 'transparent',
                    fontFamily: 'Inter, sans-serif',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: { enabled: true, delay: 150 },
                        dynamicAnimation: { enabled: true, speed: 350 }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 8,
                        barHeight: '70%',
                        distributed: true,
                        dataLabels: { position: 'bottom' }
                    }
                },
                dataLabels: { 
                    enabled: true,
                    style: {
                        colors: ['#e2e8f0'],
                        fontSize: '12px',
                        fontWeight: 600
                    },
                    formatter: function (val) {
                        return val + " tasks"
                    }
                },
                colors: ['#22d3ee', '#06b6d4', '#0891b2', '#0e7490', '#155e75'],
                xaxis: {
                    categories: workloadData.labels || ['Luna', 'Jordan', 'Dave', 'Maya', 'Chen', 'Sam', 'Alex'],
                    labels: { 
                        style: { 
                            colors: '#94a3b8',
                            fontSize: '11px',
                            fontWeight: 500
                        } 
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { 
                        style: { 
                            colors: '#f1f5f9',
                            fontSize: '12px',
                            fontWeight: 600
                        } 
                    }
                },
                grid: {
                    borderColor: '#334155',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.4,
                        gradientToColors: ['#0891b2'],
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 0.8,
                        stops: [0, 100]
                    }
                },
                tooltip: {
                    theme: 'dark',
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Inter, sans-serif'
                    },
                    onDatasetHover: {
                        highlightDataSeries: true
                    }
                }
            };
            
            const chart = new ApexCharts(document.querySelector("#workload-chart"), options);
            chart.render();
        });

        // Drag and Drop Handlers
        function handleDragStart(e) {
            e.dataTransfer.setData('taskId', e.target.dataset.taskId);
            e.target.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDrop(e, newStatus) {
            e.preventDefault();
            const taskId = e.dataTransfer.getData('taskId');
            const draggedElement = e.target.closest('[data-task-id]');
            if (draggedElement) {
                draggedElement.style.opacity = '1';
            }
            
            console.log('Task moved:', taskId, '→', newStatus);
            
            // @todo: Implement Livewire dispatch
            // Livewire.dispatch('task-moved', { taskId, newStatus });
        }
    </script>
    
    {{-- Custom scrollbar styles --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
    @endpush
</div>
