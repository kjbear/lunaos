<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            📋 Task Manager
        </h2>
        <div class="flex items-center space-x-3">
            <!-- Live Toggle -->
            <button
                wire:click="toggleLive"
                class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $isLive ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }}"
            >
                <span class="w-2 h-2 rounded-full {{ $isLive ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                <span>{{ $isLive ? 'Live' : 'Paused' }}</span>
            </button>

            <!-- Refresh Button -->
            <button
                wire:click="refresh"
                class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors"
            >
                🔄 Refresh
            </button>

            <!-- Last Refreshed -->
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Last: {{ $lastRefreshed }}
            </span>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalTasks }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Tasks</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $activeTasks }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Running</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $pendingTasks }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $completedTasks }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Completed</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalTokens) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Tokens Used</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">${{ $totalCost }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Cost</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                <select
                    wire:model.live="statusFilter"
                    class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="running">Running</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Agent:</label>
                <select
                    wire:model.live="agentFilter"
                    class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Priority:</label>
                <select
                    wire:model.live="priorityFilter"
                    class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <button
                wire:click="clearFilters"
                class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            >
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Task List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th wire:click="sortBy('status')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Status {{ $sortField === 'status' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Task Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Agent
                    </th>
                    <th wire:click="sortBy('created_at')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Created {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th wire:click="sortBy('tokens_used')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Tokens {{ $sortField === 'tokens_used' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th wire:click="sortBy('cost')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Cost {{ $sortField === 'cost' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'running' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                'completed' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'failed' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                            ];
                            $priorityColors = [
                                'low' => 'border-gray-300',
                                'normal' => 'border-blue-400',
                                'high' => 'border-orange-400',
                                'critical' => 'border-red-500',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border-l-2 {{ $statusColors[$task->status] ?? $statusColors['pending'] }} {{ $priorityColors[$task->priority] ?? $priorityColors['normal'] }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->name }}</div>
                        @if($task->description)
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($task->agent)
                            <span class="text-sm text-gray-900 dark:text-white">{{ $task->agent->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $task->agent->model }})</span>
                        @else
                            <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $task->created_at->diffForHumans() }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ number_format($task->tokens_used) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        ${{ number_format($task->cost, 4) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        No tasks found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>