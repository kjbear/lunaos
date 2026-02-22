<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">📋 Task Manager</h1>
            <p class="text-sm text-[#6b6b80] mt-1">Monitor and manage agent tasks in real-time</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Live Toggle -->
            <button
                wire:click="toggleLive"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $isLive ? 'bg-[#10b981]/20 text-[#10b981]' : 'bg-[#252542] text-[#6b6b80]' }}"
            >
                <span class="w-2 h-2 rounded-full {{ $isLive ? 'bg-[#10b981] animate-pulse' : 'bg-[#6b6b80]' }}"></span>
                <span>{{ $isLive ? 'Live' : 'Paused' }}</span>
            </button>

            <!-- Refresh Button -->
            <button
                wire:click="refresh"
                class="btn btn-secondary text-sm"
            >
                🔄 Refresh
            </button>

            <!-- Last Refreshed -->
            <span class="text-xs text-[#6b6b80]">
                Last: {{ $lastRefreshed }}
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow">
            <div class="text-2xl font-bold text-[#e4e4f0]">{{ $stats['total'] }}</div>
            <div class="text-xs text-[#6b6b80]">Total Tasks</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow">
            <div class="text-2xl font-bold text-[#f59e0b]">{{ $stats['running'] }}</div>
            <div class="text-xs text-[#6b6b80]">Running</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow">
            <div class="text-2xl font-bold text-[#3b82f6]">{{ $stats['pending'] }}</div>
            <div class="text-xs text-[#6b6b80]">Pending</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow">
            <div class="text-2xl font-bold text-[#10b981]">{{ $stats['completed'] }}</div>
            <div class="text-xs text-[#6b6b80]">Completed</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow">
            <div class="text-2xl font-bold text-[#e4e4f0]">{{ number_format($stats['totalTokens']) }}</div>
            <div class="text-xs text-[#6b6b80]">Tokens Used</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow">
            <div class="text-2xl font-bold text-[#7c3aed]">${{ $stats['totalCost'] }}</div>
            <div class="text-xs text-[#6b6b80]">Total Cost</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40]">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-[#a0a0b8]">Status:</label>
                <select
                    wire:model.live="statusFilter"
                    class="px-3 py-1.5 bg-[#252542] border border-[#2a2a40] rounded-lg text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                >
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="running">Running</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-[#a0a0b8]">Agent:</label>
                <select
                    wire:model.live="agentFilter"
                    class="px-3 py-1.5 bg-[#252542] border border-[#2a2a40] rounded-lg text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                >
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-[#a0a0b8]">Priority:</label>
                <select
                    wire:model.live="priorityFilter"
                    class="px-3 py-1.5 bg-[#252542] border border-[#2a2a40] rounded-lg text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
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
                class="px-3 py-1.5 text-sm text-[#6b6b80] hover:text-[#e4e4f0] transition-colors"
            >
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Task List -->
    <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden">
        <!-- Table Header -->
        <div class="bg-[#12121f] border-b border-[#2a2a40]">
            <div class="grid grid-cols-12 gap-4 px-4 py-3 text-xs font-medium text-[#6b6b80] uppercase tracking-wider">
                <div class="col-span-1">
                    <button wire:click="sortBy('status')" class="hover:text-[#e4e4f0]">
                        Status {{ $sortField === 'status' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </button>
                </div>
                <div class="col-span-4">Task Name</div>
                <div class="col-span-2">Agent</div>
                <div class="col-span-1">
                    <button wire:click="sortBy('created_at')" class="hover:text-[#e4e4f0]">
                        Created {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </button>
                </div>
                <div class="col-span-2">
                    <button wire:click="sortBy('tokens_used')" class="hover:text-[#e4e4f0]">
                        Tokens {{ $sortField === 'tokens_used' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </button>
                </div>
                <div class="col-span-1">
                    <button wire:click="sortBy('cost')" class="hover:text-[#e4e4f0]">
                        Cost {{ $sortField === 'cost' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                    </button>
                </div>
                <div class="col-span-1">Actions</div>
            </div>
        </div>

        <!-- Table Body -->
        <div class="divide-y divide-[#1f1f35]">
            @forelse($tasks as $task)
            <div class="grid grid-cols-12 gap-4 px-4 py-3 items-center hover:bg-[#252542] transition-colors">
                <!-- Status -->
                <div class="col-span-1">
                    @php
                        $statusColors = [
                            'pending' => 'badge-info',
                            'running' => 'badge-warning',
                            'completed' => 'badge-success',
                            'failed' => 'badge-error',
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$task->status] ?? 'badge-info' }}">
                        {{ ucfirst($task->status) }}
                    </span>
                </div>
                
                <!-- Task Name -->
                <div class="col-span-4">
                    <div class="text-sm font-medium text-[#e4e4f0]">{{ $task->name }}</div>
                    @if($task->description)
                        <div class="text-xs text-[#6b6b80] truncate">{{ $task->description }}</div>
                    @endif
                </div>
                
                <!-- Agent -->
                <div class="col-span-2">
                    @if($task->agent)
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $task->agent->emoji ?? '🤖' }}</span>
                            <div>
                                <div class="text-sm text-[#e4e4f0]">{{ $task->agent->name }}</div>
                                <div class="text-xs text-[#6b6b80]">{{ $task->agent->model }}</div>
                            </div>
                        </div>
                    @else
                        <span class="text-sm text-[#6b6b80]">—</span>
                    @endif
                </div>
                
                <!-- Created -->
                <div class="col-span-1 text-sm text-[#a0a0b8]">
                    {{ $task->created_at->diffForHumans() }}
                </div>
                
                <!-- Tokens -->
                <div class="col-span-2 text-sm text-[#e4e4f0]">
                    {{ number_format($task->tokens_used) }}
                </div>
                
                <!-- Cost -->
                <div class="col-span-1 text-sm text-[#7c3aed] font-medium">
                    ${{ number_format($task->cost, 4) }}
                </div>
                
                <!-- Actions -->
                <div class="col-span-1">
                    <button class="p-1 text-[#6b6b80] hover:text-[#e4e4f0]">
                        ⋯
                    </button>
                </div>
            </div>
            @empty
            <div class="px-4 py-12 text-center text-[#6b6b80]">
                <div class="text-4xl mb-2">📭</div>
                <div>No tasks found</div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    {{ $tasks->links('vendor.pagination.livewire-default') }}
</div>