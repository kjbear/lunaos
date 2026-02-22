<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            🏢 Organization Chart
        </h2>
        <div class="flex items-center space-x-4">
            <button
                wire:click="loadData"
                class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors"
            >
                🔄 Refresh
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Agents</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['online'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Online</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-400">{{ $stats['offline'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Offline</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Org Chart Tree -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Team Hierarchy</h3>

            <div class="space-y-6">
                @php
                    $roleColors = [
                        'ceo' => 'border-purple-500 bg-purple-50 dark:bg-purple-900/20',
                        'coordinator' => 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20',
                        'code_gen' => 'border-blue-500 bg-blue-50 dark:bg-blue-900/20',
                        'docs' => 'border-green-500 bg-green-50 dark:bg-green-900/20',
                        'qa' => 'border-orange-500 bg-orange-50 dark:bg-orange-900/20',
                    ];
                    $statusColors = [
                        'online' => 'bg-green-500',
                        'offline' => 'bg-gray-400',
                        'error' => 'bg-red-500',
                        'busy' => 'bg-yellow-500',
                    ];
                @endphp

                @foreach($tree as $rootAgent)
                    <!-- Root Agent (Kyle) -->
                    <div class="flex flex-col items-center">
                        <div
                            wire:click="selectAgent({{ $rootAgent['id'] }})"
                            class="w-48 p-4 rounded-lg border-l-4 {{ $roleColors[$rootAgent['role']] ?? 'border-gray-500' }} cursor-pointer hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full {{ $statusColors[$rootAgent['status']] ?? 'bg-gray-400' }}"></div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $rootAgent['name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $rootAgent['role'] }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Children (Luna) -->
                        @if(!empty($rootAgent['children']))
                            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>

                            @foreach($rootAgent['children'] as $level1)
                                <div class="flex flex-col items-center">
                                    <div
                                        wire:click="selectAgent({{ $level1['id'] }})"
                                        class="w-48 p-4 rounded-lg border-l-4 {{ $roleColors[$level1['role']] ?? 'border-gray-500' }} cursor-pointer hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 rounded-full {{ $statusColors[$level1['status']] ?? 'bg-gray-400' }} {{ $level1['status'] === 'online' ? 'animate-pulse' : '' }}"></div>
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $level1['name'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $level1['model'] }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grandchildren (Subagents) -->
                                    @if(!empty($level1['children']))
                                        <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>
                                        <div class="flex flex-wrap justify-center gap-4">
                                            @foreach($level1['children'] as $level2)
                                                <div
                                                    wire:click="selectAgent({{ $level2['id'] }})"
                                                    class="w-40 p-3 rounded-lg border-l-4 {{ $roleColors[$level2['role']] ?? 'border-gray-500' }} cursor-pointer hover:shadow-md transition-shadow"
                                                >
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-2 h-2 rounded-full {{ $statusColors[$level2['status']] ?? 'bg-gray-400' }}"></div>
                                                        <div>
                                                            <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $level2['name'] }}</div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $level2['model'] }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Agent Details Sidebar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            @if($selectedAgent)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $selectedAgent->name }}
                        </h3>
                        <button
                            wire:click="clearSelection"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Role</span>
                            <div class="text-gray-900 dark:text-white capitalize">{{ $selectedAgent->role }}</div>
                        </div>

                        @if($selectedAgent->model)
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Model</span>
                                <div class="text-gray-900 dark:text-white">{{ $selectedAgent->model }}</div>
                            </div>
                        @endif

                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Status</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full {{ $statusColors[$selectedAgent->status] ?? 'bg-gray-400' }}"></div>
                                <span class="text-gray-900 dark:text-white capitalize">{{ $selectedAgent->status }}</span>
                            </div>
                        </div>

                        @if($selectedAgent->model && isset($modelHealth[$selectedAgent->model]))
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Performance</span>
                                <div class="text-gray-900 dark:text-white">
                                    {{ $modelHealth[$selectedAgent->model]->tokens_per_sec }} tok/s
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($selectedAgent->tasks->count() > 0)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Recent Tasks</h4>
                            <div class="space-y-2">
                                @foreach($selectedAgent->tasks as $task)
                                    <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $task->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $task->status }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                    <div class="text-4xl mb-2">👤</div>
                    <div class="text-sm">Click an agent to see details</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Model Health -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Model Health</h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach(['GLM-5', 'Dolphin 3.0'] as $model)
                @php
                    $health = $modelHealth[$model] ?? null;
                @endphp
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $model }}</span>
                        @if($health)
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $health->status === 'healthy' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                {{ ucfirst($health->status) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">No data</span>
                        @endif
                    </div>
                    @if($health)
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <div>Speed: {{ $health->tokens_per_sec }} tok/s</div>
                            @if($health->vram_percent)
                                <div>VRAM: {{ $health->vram_percent }}%</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>