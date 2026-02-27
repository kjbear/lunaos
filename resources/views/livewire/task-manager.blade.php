<div class="space-y-6">
    <!-- Polished Page Header -->
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">📋</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Task Manager</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Monitor and manage agent tasks in real-time</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button
                    wire:click="toggleLive"
                    class="flex items-center gap-2.5 px-4 py-2 rounded-xl {{ $isLive ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-white/5 border border-white/10' }} transition-all"
                >
                    <span class="relative flex h-2.5 w-2.5">
                        @if($isLive)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        @endif
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $isLive ? 'bg-emerald-500' : 'bg-slate-500' }}"></span>
                    </span>
                    <span class="text-sm font-semibold {{ $isLive ? 'text-emerald-400' : 'text-slate-400' }}">{{ $isLive ? 'Live' : 'Paused' }}</span>
                </button>
                
                <button
                    wire:click="refresh"
                    class="group p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all duration-200"
                >
                    <span class="group-hover:rotate-180 transition-transform duration-500 block">🔄</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Polished Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
        <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-500/10 to-purple-500/10 backdrop-blur-sm rounded-2xl p-5 border border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-xl">📊</div>
                    <div>
                        <p class="text-xs text-indigo-300 font-semibold uppercase tracking-wider mb-0.5">Total Tasks</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-sm rounded-2xl p-5 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-xl">⚡</div>
                    <div>
                        <p class="text-xs text-amber-300 font-semibold uppercase tracking-wider mb-0.5">Running</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['running'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm rounded-2xl p-5 border border-blue-500/20 hover:border-blue-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-xl">⏳</div>
                    <div>
                        <p class="text-xs text-blue-300 font-semibold uppercase tracking-wider mb-0.5">Pending</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['pending'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-500/10 to-green-500/10 backdrop-blur-sm rounded-2xl p-5 border border-emerald-500/20 hover:border-emerald-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl">✅</div>
                    <div>
                        <p class="text-xs text-emerald-300 font-semibold uppercase tracking-wider mb-0.5">Completed</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['completed'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500/10 to-blue-500/10 backdrop-blur-sm rounded-2xl p-5 border border-cyan-500/20 hover:border-cyan-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/20 border border-cyan-500/30 flex items-center justify-center text-xl">🪙</div>
                    <div>
                        <p class="text-xs text-cyan-300 font-semibold uppercase tracking-wider mb-0.5">Tokens Used</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($stats['totalTokens']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm rounded-2xl p-5 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-xl">💰</div>
                    <div>
                        <p class="text-xs text-purple-300 font-semibold uppercase tracking-wider mb-0.5">Total Cost</p>
                        <p class="text-2xl font-bold text-purple-400">${{ $stats['totalCost'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Filters</h2>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-400">Status:</label>
                    <select
                        wire:model.live="statusFilter"
                        class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-300 focus:outline-none focus:border-cyan-400/50 transition-colors"
                    >
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                        <option value="running">Running</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-400">Agent:</label>
                    <select
                        wire:model.live="agentFilter"
                        class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-300 focus:outline-none focus:border-cyan-400/50 transition-colors"
                    >
                        <option value="">All Agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-400">Priority:</label>
                    <select
                        wire:model.live="priorityFilter"
                        class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-300 focus:outline-none focus:border-cyan-400/50 transition-colors"
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
                    class="px-3 py-1.5 text-sm text-slate-500 hover:text-white transition-colors"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </section>

    <!-- Task List Section -->
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Task List</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">{{ count($tasks) }} tasks</span>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
            <!-- Table Header -->
            <div class="bg-white/[0.02] border-b border-white/10">
                <div class="grid grid-cols-12 gap-4 px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">
                    <div class="col-span-1">
                        <button wire:click="sortBy('status')" class="hover:text-white transition-colors">
                            Status {{ $sortField === 'status' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </button>
                    </div>
                    <div class="col-span-4">Task Name</div>
                    <div class="col-span-2">Agent</div>
                    <div class="col-span-1">
                        <button wire:click="sortBy('created_at')" class="hover:text-white transition-colors">
                            Created {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </button>
                    </div>
                    <div class="col-span-2">
                        <button wire:click="sortBy('tokens_used')" class="hover:text-white transition-colors">
                            Tokens {{ $sortField === 'tokens_used' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </button>
                    </div>
                    <div class="col-span-1">
                        <button wire:click="sortBy('cost')" class="hover:text-white transition-colors">
                            Cost {{ $sortField === 'cost' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </button>
                    </div>
                    <div class="col-span-1">Actions</div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-white/5">
                @forelse($tasks as $task)
                @php
                    $statusColors = [
                        'pending' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                        'running' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        'completed' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'failed' => 'bg-red-500/20 text-red-400 border-red-500/30',
                    ];
                @endphp
                <div 
                    class="group grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-white/[0.03] transition-all duration-200 cursor-pointer {{ $highlight === $task->id ? 'bg-purple-500/10 border-l-4 border-purple-500' : '' }}"
                    id="task-{{ $task->id }}"
                    wire:click="showTask({{ $task->id }})"
                >
                    <!-- Status -->
                    <div class="col-span-1">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $statusColors[$task->status] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30' }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </div>
                    
                    <!-- Task Name -->
                    <div class="col-span-4">
                        <div class="text-sm font-semibold text-white">{{ $task->name }}</div>
                        @if($task->description)
                            <div class="text-xs text-slate-500 truncate mt-0.5">{{ $task->description }}</div>
                        @endif
                    </div>
                    
                    <!-- Agent -->
                    <div class="col-span-2">
                        @if($task->agent)
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $task->agent->emoji ?? '🤖' }}</span>
                                <div>
                                    <div class="text-sm text-slate-300 font-medium">{{ $task->agent->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $task->agent->model }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-slate-500">—</span>
                        @endif
                    </div>
                    
                    <!-- Created -->
                    <div class="col-span-1 text-sm text-slate-400 font-mono">
                        {{ $task->created_at->diffForHumans() }}
                    </div>
                    
                    <!-- Tokens -->
                    <div class="col-span-2 text-sm text-slate-300 font-mono">
                        {{ number_format($task->tokens_used) }}
                    </div>
                    
                    <!-- Cost -->
                    <div class="col-span-1 text-sm text-purple-400 font-mono font-semibold">
                        ${{ number_format($task->cost, 4) }}
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-span-1">
                        <button class="p-1.5 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 transition-all opacity-0 group-hover:opacity-100">
                            ⋯
                        </button>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="text-5xl mb-4 opacity-50">📭</div>
                    <p class="text-slate-400 font-medium">No tasks found</p>
                    <p class="text-slate-600 text-sm mt-1">Adjust your filters or create a new task</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Pagination -->
    {{ $tasks->links('vendor.pagination.livewire-default') }}

    <!-- Task Detail Modal -->
    @if($showModal && $selectedTask)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="task-modal-{{ $selectedTask->id }}">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/80 backdrop-blur-md" wire:click="closeModal"></div>
            
            <!-- Modal -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-gradient-to-br from-slate-900 via-purple-900/20 to-slate-900 rounded-2xl border border-white/10 shadow-2xl max-w-3xl w-full max-h-[80vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-white/10 bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-purple-500 flex items-center justify-center text-xl shadow-lg">📋</div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $selectedTask->name }}</h3>
                                <p class="text-xs text-slate-400 font-medium">Task Details</p>
                            </div>
                        </div>
                        <button 
                            wire:click="closeModal"
                            class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all"
                        >
                            ✕
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-5 overflow-y-auto max-h-[60vh] space-y-6">
                        <!-- Basic Info Grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Status</label>
                                @php
                                    $statusBadge = [
                                        'completed' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                        'running' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                        'pending' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                        'failed' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                    ][$selectedTask->status] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                                @endphp
                                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-semibold border {{ $statusBadge }}">
                                    {{ ucfirst($selectedTask->status) }}
                                </span>
                            </div>
                            <div class="bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Priority</label>
                                <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-semibold bg-slate-500/20 text-slate-400 border border-slate-500/30">
                                    {{ ucfirst($selectedTask->priority) }}
                                </span>
                            </div>
                            <div class="bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Agent</label>
                                <div class="text-sm text-slate-300 font-semibold">{{ $selectedTask->agent->name ?? 'Unassigned' }}</div>
                            </div>
                            <div class="bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Created</label>
                                <div class="text-sm text-slate-300 font-mono">{{ $selectedTask->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                            <div class="bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Tokens Used</label>
                                <div class="text-sm text-slate-300 font-mono">{{ number_format($selectedTask->tokens_used) }}</div>
                            </div>
                            <div class="bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Cost</label>
                                <div class="text-sm text-purple-400 font-mono font-bold">${{ number_format($selectedTask->cost, 4) }}</div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($selectedTask->description)
                            <div>
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Description</label>
                                <div class="text-sm text-slate-300 bg-white/[0.02] rounded-xl p-4 border border-white/5">
                                    {{ $selectedTask->description }}
                                </div>
                            </div>
                        @endif

                        <!-- Related Activities -->
                        @if(!empty($taskActivities))
                            <div>
                                <label class="block text-xs text-slate-500 font-semibold uppercase tracking-wider mb-3">Related Activities ({{ count($taskActivities) }})</label>
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($taskActivities as $activity)
                                        <div class="flex items-start gap-3 p-3 bg-white/[0.02] rounded-xl border border-white/5 hover:border-white/10 transition-all">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-sm">
                                                @if($activity['action_type'] === 'create') ⚡
                                                @elseif($activity['action_type'] === 'update') ✏️
                                                @else 📝
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm text-slate-300 font-semibold">{{ $activity['action_name'] }}</span>
                                                    <span class="text-xs text-slate-500 bg-white/5 px-1.5 py-0.5 rounded">{{ $activity['action_type'] }}</span>
                                                </div>
                                                <div class="text-xs text-slate-500 font-mono">{{ \Carbon\Carbon::parse($activity['created_at'])->format('H:i') }}</div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded {{ $activity['impact'] === 'high' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                                {{ ucfirst($activity['impact']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end px-6 py-4 border-t border-white/10 bg-white/[0.02]">
                        <button 
                            wire:click="closeModal"
                            class="px-4 py-2 bg-white/5 text-slate-300 rounded-xl text-sm hover:bg-white/10 transition-all border border-white/10 font-medium"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>