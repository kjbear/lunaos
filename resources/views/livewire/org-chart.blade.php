<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            🏢 Organization Chart
        </h2>
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span>Online</span>
            </div>
            <button
                wire:click="loadData"
                class="px-2 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-xs font-medium transition-colors"
            >
                🔄 Refresh
            </button>
        </div>
    </div>

    <!-- Stats Row - Left Aligned, Fixed 150px Width -->
    <div class="flex flex-wrap gap-3 justify-start">
        <div class="bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-700" style="width: 150px;">
            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Agents</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-700" style="width: 150px;">
            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $stats['online'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Online</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-700" style="width: 150px;">
            <div class="text-lg font-bold text-gray-400">{{ $stats['offline'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Offline</div>
        </div>
    </div>

    <!-- Org Chart Tree with Grid Background -->
    <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-slate-100 dark:bg-slate-900">
        <!-- Grid Background -->
        <div class="absolute inset-0 opacity-20 dark:opacity-10" style="background-image: linear-gradient(to right, #64748b 1px, transparent 1px), linear-gradient(to bottom, #64748b 1px, transparent 1px); background-size: 50px 50px;"></div>
        
        <div class="relative p-12">
            @php
                $statusColors = [
                    'online' => 'bg-green-500 ring-green-300',
                    'offline' => 'bg-gray-400',
                    'error' => 'bg-red-500 ring-red-300',
                    'busy' => 'bg-yellow-500 ring-yellow-300',
                ];
                $modelColors = [
                    'GLM-5' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300',
                    'Dolphin 3.0' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-300',
                    'Claude Haiku' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300',
                    'GPT-4o Mini' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                ];
                $roleDescriptions = [
                    'ceo' => 'Product owner & decision maker',
                    'coordinator' => 'Orchestrates tasks & manages team',
                    'code_gen' => 'Generates code & implementations',
                    'docs' => 'Creates documentation & guides',
                    'qa' => 'Tests & validates outputs',
                ];
            @endphp

            @foreach($tree as $rootAgent)
                <!-- ROOT LEVEL: CEO -->
                <div class="flex flex-col items-center">
                    <!-- CEO Card - Light blue card -->
                    <div wire:click="selectAgent({{ $rootAgent['id'] }})" class="cursor-pointer transform transition-transform hover:scale-105">
                        <div class="bg-slate-200/80 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-500 rounded-lg p-3 w-52 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center space-x-2 mb-1.5">
                                <div class="relative">
                                    <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($rootAgent['name'], 0, 1)) }}
                                    </div>
                                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 {{ $statusColors[$rootAgent['status']] ?? 'bg-gray-400' }} rounded-full border-2 border-slate-200 dark:border-slate-700 {{ $rootAgent['status'] === 'online' ? 'animate-pulse ring-2' : '' }}"></div>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $rootAgent['name'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase">{{ $rootAgent['role'] }}</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">{{ $roleDescriptions[$rootAgent['role']] ?? 'Team member' }}</p>
                            @if($rootAgent['model'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $modelColors[$rootAgent['model']] ?? 'bg-slate-100 text-slate-700' }}">{{ $rootAgent['model'] }}</span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-600 dark:text-slate-400">Human</span>
                            @endif
                        </div>
                    </div>

                    <!-- Vertical connector to Level 1 -->
                    @if(!empty($rootAgent['children']))
                        <div class="w-0.5 h-12 bg-slate-400 dark:bg-slate-500"></div>
                    @endif

                    <!-- LEVEL 1: Coordinator(s) -->
                    @if(!empty($rootAgent['children']))
                        <div class="flex flex-col items-center">
                            @foreach($rootAgent['children'] as $level1)
                                <!-- Coordinator Card -->
                                <div wire:click="selectAgent({{ $level1['id'] }})" class="cursor-pointer transform transition-transform hover:scale-105 mb-8">
                                    <div class="bg-slate-200/80 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-500 rounded-lg p-3 w-52 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex items-center space-x-2 mb-1.5">
                                            <div class="relative">
                                                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                    {{ strtoupper(substr($level1['name'], 0, 1)) }}
                                                </div>
                                                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 {{ $statusColors[$level1['status']] ?? 'bg-gray-400' }} rounded-full border-2 border-slate-200 dark:border-slate-700 {{ $level1['status'] === 'online' ? 'animate-pulse ring-2' : '' }}"></div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $level1['name'] }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 uppercase">{{ $level1['role'] }}</div>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">{{ $roleDescriptions[$level1['role']] ?? 'Team member' }}</p>
                                        @if($level1['model'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $modelColors[$level1['model']] ?? 'bg-slate-100 text-slate-700' }}">{{ $level1['model'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Vertical connector to Level 2 -->
                                @if(!empty($level1['children']))
                                    <div class="w-0.5 h-12 bg-slate-400 dark:bg-slate-500 mb-8"></div>
                                @endif

                                <!-- LEVEL 2: Subagents (horizontal row) -->
                                @if(!empty($level1['children']))
                                    <div class="flex justify-center items-start gap-10">
                                        @foreach($level1['children'] as $level2)
                                            <div class="flex flex-col items-center">
                                                <!-- Vertical connector from above -->
                                                <div class="w-0.5 h-8 bg-slate-400 dark:bg-slate-500"></div>
                                                <!-- Subagent Card -->
                                                <div wire:click="selectAgent({{ $level2['id'] }})" class="cursor-pointer transform transition-transform hover:scale-105">
                                                    <div class="bg-slate-200/80 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-500 rounded-lg p-2.5 w-36 shadow-sm hover:shadow-md transition-shadow">
                                                        <div class="flex items-center space-x-2 mb-1">
                                                            <div class="relative">
                                                                <div class="w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                                                    {{ strtoupper(substr($level2['name'], 0, 1)) }}
                                                                </div>
                                                                <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 {{ $statusColors[$level2['status']] ?? 'bg-gray-400' }} rounded-full border-2 border-slate-200 dark:border-slate-700"></div>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $level2['name'] }}</div>
                                                                <div class="text-xs text-slate-500 dark:text-slate-400 uppercase">{{ $level2['role'] }}</div>
                                                            </div>
                                                        </div>
                                                        @if($level2['model'])
                                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium {{ $modelColors[$level2['model']] ?? 'bg-slate-100 text-slate-700' }}">{{ $level2['model'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Selected Agent Modal -->
    @if($selectedAgent)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="clearSelection">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-4" wire:click.stop>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $selectedAgent->name }}</h3>
                    <button wire:click="clearSelection" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Role</span>
                        <div class="text-xs text-gray-900 dark:text-white capitalize">{{ $selectedAgent->role }}</div>
                    </div>
                    @if($selectedAgent->model)
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Model</span>
                            <div class="text-xs text-gray-900 dark:text-white">{{ $selectedAgent->model }}</div>
                        </div>
                    @endif
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Status</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full {{ $statusColors[$selectedAgent->status] ?? 'bg-gray-400' }}"></div>
                            <span class="text-xs text-gray-900 dark:text-white capitalize">{{ $selectedAgent->status }}</span>
                        </div>
                    </div>
                    @if($selectedAgent->tasks->count() > 0)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Recent Tasks</span>
                            <div class="mt-1.5 space-y-1">
                                @foreach($selectedAgent->tasks as $task)
                                    <div class="p-1.5 bg-gray-50 dark:bg-gray-700 rounded text-xs">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $task->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $task->status }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>