<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            🏢 Organization Chart
        </h2>
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span>Online</span>
            </div>
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

    <!-- Org Chart Tree -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-8 overflow-x-auto">
        @php
            $roleColors = [
                'ceo' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'border' => 'border-purple-400', 'avatar' => 'bg-purple-500', 'text' => 'text-purple-700 dark:text-purple-300'],
                'coordinator' => ['bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'border' => 'border-indigo-400', 'avatar' => 'bg-indigo-500', 'text' => 'text-indigo-700 dark:text-indigo-300'],
                'code_gen' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'border' => 'border-blue-400', 'avatar' => 'bg-blue-500', 'text' => 'text-blue-700 dark:text-blue-300'],
                'docs' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'border' => 'border-green-400', 'avatar' => 'bg-green-500', 'text' => 'text-green-700 dark:text-green-300'],
                'qa' => ['bg' => 'bg-orange-100 dark:bg-orange-900/30', 'border' => 'border-orange-400', 'avatar' => 'bg-orange-500', 'text' => 'text-orange-700 dark:text-orange-300'],
            ];
            $roleDescriptions = [
                'ceo' => 'Product owner & decision maker',
                'coordinator' => 'Orchestrates tasks & manages team',
                'code_gen' => 'Generates code & implementations',
                'docs' => 'Creates documentation & guides',
                'qa' => 'Tests & validates outputs',
            ];
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
        @endphp

        @foreach($tree as $rootAgent)
            <!-- Level 0: CEO/Owner -->
            <div class="flex flex-col items-center">
                <div
                    wire:click="selectAgent({{ $rootAgent['id'] }})"
                    class="relative cursor-pointer transform transition-transform hover:scale-105"
                >
                    @php $colors = $roleColors[$rootAgent['role']] ?? $roleColors['coordinator']; @endphp
                    <!-- Card -->
                    <div class="{{ $colors['bg'] }} {{ $colors['border'] }} border-2 rounded-xl p-4 w-56 shadow-sm hover:shadow-md transition-shadow">
                        <!-- Avatar and Status -->
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="relative">
                                <div class="w-12 h-12 {{ $colors['avatar'] }} rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {{ strtoupper(substr($rootAgent['name'], 0, 1)) }}
                                </div>
                                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 {{ $statusColors[$rootAgent['status']] ?? 'bg-gray-400' }} rounded-full border-2 border-white dark:border-gray-800 {{ $rootAgent['status'] === 'online' ? 'animate-pulse ring-2' : '' }}"></div>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $rootAgent['name'] }}</div>
                                <div class="text-xs {{ $colors['text'] }} uppercase font-medium">{{ $rootAgent['role'] }}</div>
                            </div>
                        </div>
                        <!-- Description -->
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                            {{ $roleDescriptions[$rootAgent['role']] ?? 'Team member' }}
                        </p>
                        <!-- Model Pill -->
                        @if($rootAgent['model'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $modelColors[$rootAgent['model']] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $rootAgent['model'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                Human
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Connector Line to Level 1 -->
                @if(!empty($rootAgent['children']))
                    <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                @endif

                <!-- Level 1: Coordinator -->
                @foreach($rootAgent['children'] as $level1)
                    <div class="flex flex-col items-center">
                        <div
                            wire:click="selectAgent({{ $level1['id'] }})"
                            class="relative cursor-pointer transform transition-transform hover:scale-105"
                        >
                            @php $colors1 = $roleColors[$level1['role']] ?? $roleColors['coordinator']; @endphp
                            <!-- Card -->
                            <div class="{{ $colors1['bg'] }} {{ $colors1['border'] }} border-2 rounded-xl p-4 w-56 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="relative">
                                        <div class="w-12 h-12 {{ $colors1['avatar'] }} rounded-full flex items-center justify-center text-white font-bold text-lg">
                                            {{ strtoupper(substr($level1['name'], 0, 1)) }}
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 {{ $statusColors[$level1['status']] ?? 'bg-gray-400' }} rounded-full border-2 border-white dark:border-gray-800 {{ $level1['status'] === 'online' ? 'animate-pulse ring-2' : '' }}"></div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $level1['name'] }}</div>
                                        <div class="text-xs {{ $colors1['text'] }} uppercase font-medium">{{ $level1['role'] }}</div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                    {{ $roleDescriptions[$level1['role']] ?? 'Team member' }}
                                </p>
                                @if($level1['model'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $modelColors[$level1['model']] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $level1['model'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Connector Line to Level 2 -->
                        @if(!empty($level1['children']))
                            <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                        @endif

                        <!-- Level 2: Subagents (horizontal) -->
                        @if(!empty($level1['children']))
                            <div class="flex items-start space-x-4">
                                @foreach($level1['children'] as $level2)
                                    <div class="flex flex-col items-center">
                                        <!-- Horizontal connector -->
                                        <div class="w-px h-4 bg-gray-300 dark:bg-gray-600"></div>
                                        <div
                                            wire:click="selectAgent({{ $level2['id'] }})"
                                            class="relative cursor-pointer transform transition-transform hover:scale-105"
                                        >
                                            @php $colors2 = $roleColors[$level2['role']] ?? $roleColors['coordinator']; @endphp
                                            <!-- Smaller Card -->
                                            <div class="{{ $colors2['bg'] }} {{ $colors2['border'] }} border-2 rounded-lg p-3 w-44 shadow-sm hover:shadow-md transition-shadow">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <div class="relative">
                                                        <div class="w-8 h-8 {{ $colors2['avatar'] }} rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                            {{ strtoupper(substr($level2['name'], 0, 1)) }}
                                                        </div>
                                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 {{ $statusColors[$level2['status']] ?? 'bg-gray-400' }} rounded-full border-2 border-white dark:border-gray-800"></div>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $level2['name'] }}</div>
                                                        <div class="text-xs {{ $colors2['text'] }} uppercase">{{ $level2['role'] }}</div>
                                                    </div>
                                                </div>
                                                @if($level2['model'])
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $modelColors[$level2['model']] ?? 'bg-gray-100 text-gray-700' }}">
                                                        {{ $level2['model'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <!-- Selected Agent Details -->
    @if($selectedAgent)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="clearSelection">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $selectedAgent->name }}</h3>
                    <button wire:click="clearSelection" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
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
                    @if($selectedAgent->tasks->count() > 0)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Recent Tasks</span>
                            <div class="mt-2 space-y-2">
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
            </div>
        </div>
    @endif
</div>