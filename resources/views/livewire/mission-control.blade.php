<div class="mission-control" x-data x-on:activity-received.window="handleActivity($event.detail)">
    <!-- Mission Control Header -->
    <header class="border-b border-gray-800 bg-gray-900/50 backdrop-blur-sm mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center text-xl">
                    🚀
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Mission Control</h1>
                    <p class="text-sm text-gray-400">LunaOS Subagent Dashboard</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-400">
                    <span class="text-green-400">{{ $stats['active_agents'] ?? 1 }}</span> agents online
                </div>
                <button wire:click="loadAgents" class="p-2 rounded-lg hover:bg-gray-800 transition-colors text-gray-400 hover:text-white">
                    🔄
                </button>
            </div>
        </div>
    </header>

    <!-- Top Row: Agent Grid -->
    <section class="mb-6">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Agent Grid</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
            @foreach($agents as $agent)
            <div class="bg-[#1a1a2e] rounded-xl p-3 border border-[#2a2a40] hover:border-purple-500/50 transition-all {{ $agent['status'] === 'running' ? 'border-green-500/50 shadow-[0_0_20px_rgba(16,185,129,0.15)]' : '' }}">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xl">{{ $agent['avatar'] }}</span>
                    @if($agent['status'] === 'running')
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    @endif
                </div>
                <div class="font-medium text-white text-sm mb-1">{{ $agent['name'] }}</div>
                <div class="text-xs text-gray-400 mb-2">{{ $agent['role'] }}</div>
                <div class="flex items-center justify-between">
                    <span class="text-xs px-2 py-0.5 rounded {{ $agent['model'] === 'GLM-5' ? 'bg-purple-500/20 text-purple-400' : 'bg-cyan-500/20 text-cyan-400' }}">
                        {{ $agent['model'] }}
                    </span>
                    <span class="text-xs text-gray-500">D{{ $agent['depth'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Middle Row: Tasks + Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Task Pipeline -->
        <div class="lg:col-span-2">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Task Pipeline</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach(['todo', 'in_progress', 'blocked', 'done'] as $column)
                <div class="bg-[#1a1a2e] rounded-xl p-3 border border-[#2a2a40]">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-white text-sm capitalize">{{ str_replace('_', ' ', $column) }}</h3>
                        <span class="text-xs text-gray-400 bg-[#12121f] px-2 py-0.5 rounded-full">
                            {{ count($tasks[$column] ?? []) }}
                        </span>
                    </div>
                    <div class="space-y-2 min-h-[120px]" 
                         data-status="{{ $column }}"
                         ondragover="event.preventDefault()"
                         ondrop="handleDrop(event, '{{ $column }}')">
                        @forelse(($tasks[$column] ?? []) as $task)
                        <div class="bg-[#12121f] rounded-lg p-2 border border-[#2a2a40] hover:border-purple-500 transition-colors cursor-grab"
                             draggable="true"
                             data-task-id="{{ $task['id'] }}"
                             ondragstart="handleDragStart(event)">
                            <div class="text-xs text-white font-medium mb-1">{{ $task['title'] ?? 'Untitled' }}</div>
                            @if(isset($task['assigned_to']))
                            <div class="text-xs text-cyan-400">{{ $task['assigned_to'] }}</div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center text-gray-600 text-xs py-8">Empty</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Activity Feed -->
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Activity Feed</h2>
            <div class="bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40] h-[320px] overflow-y-auto space-y-2" id="activity-feed">
                @forelse($activity as $item)
                <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-[#252542] transition-colors {{ ($item['status'] ?? '') === 'running' ? 'border-l-2 border-yellow-500' : 'border-l-2 border-green-500' }}">
                    <div class="text-lg">
                        @if(($item['status'] ?? '') === 'running')
                        ⚡
                        @elseif(($item['status'] ?? '') === 'done')
                        ✅
                        @else
                        ❌
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-white text-sm">{{ $item['agent_name'] ?? 'Unknown' }}</span>
                            <span class="text-xs text-gray-500">{{ $item['action'] ?? '' }}</span>
                        </div>
                        @if($item['task'] ?? '')
                        <div class="text-xs text-gray-400 truncate">{{ $item['task'] }}</div>
                        @endif
                        <div class="text-xs text-gray-600 mt-1">{{ $item['created_at'] ?? '' }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-600 py-8">
                    No activity yet
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Row: Workload Chart + Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Workload Distribution -->
        <div class="lg:col-span-2">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Workload Distribution</h2>
            <div class="bg-[#1a1a2e] rounded-xl p-6 border border-[#2a2a40]">
                <div id="workload-chart" class="h-[200px]"></div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Stats</h2>
            <div class="bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40] space-y-3">
                <div class="flex items-center justify-between p-3 bg-[#12121f] rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📊</span>
                        <span class="text-gray-400 text-sm">Total Tasks</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $stats['total_tasks'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-[#12121f] rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">🪙</span>
                        <span class="text-gray-400 text-sm">Tokens Used</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ number_format($stats['total_tokens'] ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-[#12121f] rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">💰</span>
                        <span class="text-gray-400 text-sm">Est. Cost</span>
                    </div>
                    <span class="text-2xl font-bold text-green-400">$0.00</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ApexCharts Workload Chart
            const workloadData = @json($workload);
            const options = {
                series: [{
                    name: 'Tasks',
                    data: workloadData.series?.[0]?.data || [5, 3, 2, 4, 1]
                }],
                chart: {
                    type: 'bar',
                    height: 200,
                    toolbar: { show: false },
                    background: 'transparent',
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '60%',
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: workloadData.labels || ['Luna', 'Jordan', 'Dave', 'Maya', 'Chen', 'Sam', 'Alex'],
                    labels: { style: { colors: '#9ca3af' } }
                },
                yaxis: {
                    labels: { style: { colors: '#e5e7eb' } }
                },
                grid: {
                    borderColor: '#374151',
                    strokeDashArray: 3,
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.3,
                        gradientToColors: ['#06b6d4'],
                        stops: [0, 100]
                    }
                },
                colors: ['#7c3aed'],
            };
            
            const chart = new ApexCharts(document.querySelector("#workload-chart"), options);
            chart.render();
        });

        // Drag and Drop Handlers
        function handleDragStart(e) {
            e.dataTransfer.setData('taskId', e.target.dataset.taskId);
            e.target.classList.add('opacity-50');
        }

        function handleDrop(e, newStatus) {
            e.preventDefault();
            const taskId = e.dataTransfer.getData('taskId');
            e.target.classList.remove('opacity-50');
            console.log('Task moved:', taskId, '→', newStatus);
            // Livewire.dispatch('task-moved', { taskId, newStatus });
        }
    </script>
    @endpush
</div>
