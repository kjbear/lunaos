<div class="task-list" x-data="{
    handleDragStart(e) { 
        e.dataTransfer.setData('taskId', e.target.dataset.taskId); 
        e.target.style.opacity = '0.4'; 
    },
    handleDrop(e, newStatus) { 
        e.preventDefault(); 
        const taskId = e.dataTransfer.getData('taskId'); 
        console.log('Task moved:', taskId, '→', newStatus); 
    }
}">
    {{-- Task List Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-base-300 via-base-200 to-base-100 backdrop-blur-xl border border-base-content/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-secondary/5 to-accent/5"></div>
        
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">
                        📋
                    </div>
                </div>
                
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Task List</h1>
                    <p class="text-sm text-readable font-medium mt-0.5">Comprehensive task management with filters</p>
                </div>
            </div>
            
            {{-- View Mode Toggle --}}
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center px-3 py-1.5 bg-white/5 border border-white/10 rounded-xl">
                    <label class="text-xs font-medium text-readable mr-3 uppercase tracking-wider">View</label>
                    <select 
                        wire:model.live="viewMode"
                        class="bg-transparent text-sm font-medium text-white focus:outline-none cursor-pointer"
                    >
                        <option value="list">📋 List</option>
                        <option value="board">📊 Board</option>
                        <option value="executive">📈 Executive</option>
                    </select>
                </div>
                
                <button 
                    wire:click="$refresh"
                    class="group relative p-2.5 rounded-xl bg-base-content/5 border border-base-content/10 text-readable/70 hover:text-readable hover:bg-base-content/10 hover:border-base-content/20 transition-all duration-200"
                >
                    <span class="group-hover:rotate-180 transition-transform duration-500 block">↻</span>
                </button>
            </div>
        </div>
        
        {{-- Stats Row (daisyUI) --}}
        <div class="relative px-6 pb-6">
            <div class="stats stats-vertical lg:stats-horizontal shadow-2xl bg-base-300 border border-base-300 w-full">
                <div class="stat group">
                    <div class="stat-title text-readable">Total</div>
                    <div class="stat-value text-primary text-3xl">{{ $stats['total'] }}</div>
                    <div class="stat-desc text-readable">All tasks</div>
                </div>
                
                <div class="stat group">
                    <div class="stat-title text-readable">Pending</div>
                    <div class="stat-value text-warning text-3xl">{{ $stats['pending'] }}</div>
                    <div class="stat-desc text-readable">Awaiting start</div>
                </div>
                
                <div class="stat group">
                    <div class="stat-title text-readable">In Progress</div>
                    <div class="stat-value text-info text-3xl">{{ $stats['in_progress'] }}</div>
                    <div class="stat-desc text-readable">Active work</div>
                </div>
                
                <div class="stat group">
                    <div class="stat-title text-readable">Completed</div>
                    <div class="stat-value text-success text-3xl">{{ $stats['completed'] }}</div>
                    <div class="stat-desc text-readable">Done</div>
                </div>
            </div>
        </div>
    </header>

    {{-- Filters Bar --}}
    <section class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-primary to-secondary rounded-full"></div>
            <h2 class="text-sm font-semibold text-readable uppercase tracking-wider">Filters</h2>
        </div>
        
        <div class="bg-base-200/80 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-readable mb-2">Search</label>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tasks..."
                        class="luna-input rounded-lg px-4 py-2.5 transition-all"
                    >
                </div>
                
                {{-- Agent Filter --}}
                <div>
                    <label class="block text-xs font-medium text-readable-dim mb-2">Agent</label>
                    <select 
                        wire:model.live="selectedAgent"
                        class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="all">All Agents ({{ $agentCounts['all'] }})</option>
                        <option value="dave">Dave ({{ $agentCounts['dave'] }})</option>
                        <option value="sam">Sam ({{ $agentCounts['sam'] }})</option>
                        <option value="chen">Chen ({{ $agentCounts['chen'] }})</option>
                        <option value="security">Security ({{ $agentCounts['security'] }})</option>
                    </select>
                </div>
                
                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-medium text-readable-dim mb-2">Status</label>
                    <select 
                        wire:model.live="selectedStatus"
                        class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="complete">Complete</option>
                        <option value="blocked">Blocked</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    {{-- Task Table --}}
    <section>
        <div class="bg-base-200 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
            {{-- MaryUI x-table component --}}
            <x-table 
                :headers="$headers" 
                :rows="$tasks"
                striped 
                with-pagination
                pagination-per-page="{{ $perPage }}"
                wire:sort="setSort($event.detail.field, $event.detail.direction)"
                class="cursor-pointer"
                @row-click="viewTask($event.detail.id)"
            >
                {{-- Custom cell rendering --}}
                
                {{-- ID column --}}
                @scope('cell_id', $task)
                    <span class="font-mono text-readable/80">#{{ $task->id }}</span>
                @endscope
                
                {{-- Task column with description --}}
                @scope('cell_title', $task)
                    <div>
                        <div class="font-medium text-readable group-hover:text-primary transition-colors">
                            {{ $task->title }}
                        </div>
                        @if($task->description)
                        <div class="text-xs text-readable/70 mt-1 line-clamp-1">
                            {{ Str::limit($task->description, 60) }}
                        </div>
                        @endif
                    </div>
                @endscope
                
                {{-- Agent column with dynamic badge --}}
                @scope('cell_assigned_to', $task)
                    @if($task->assigned_to)
                    @php
                        $badgeType = match($task->assigned_to) {
                            'dave' => 'info',
                            'sam' => 'success',
                            'chen' => 'primary',
                            'security' => 'warning',
                            default => 'neutral'
                        };
                    @endphp
                    <x-badge :type="$badgeType">{{ $task->agent_display_name ?? ucfirst($task->assigned_to) }}</x-badge>
                    @else
                    <span class="text-readable-dim">Unassigned</span>
                    @endif
                @endscope
                
                {{-- Step column with icon --}}
                @scope('cell_step', $task)
                    @php
                        $stepIcons = [
                            'develop' => '🔧',
                            'qa' => '🧪',
                            'security' => '🔒',
                            'staging' => '🚀',
                            'complete' => '✅'
                        ];
                    @endphp
                    <x-badge type="neutral">
                        {{ $stepIcons[$task->step] ?? '📋' }} {{ ucfirst($task->step) }}
                    </x-badge>
                @endscope
                
                {{-- Priority column --}}
                @scope('cell_priority', $task)
                    @php
                        $priorityTypes = [
                            'critical' => 'error',
                            'high' => 'warning',
                            'medium' => 'info',
                            'low' => 'neutral'
                        ];
                    @endphp
                    <x-badge :type="$priorityTypes[$task->priority] ?? 'neutral'">{{ ucfirst($task->priority) }}</x-badge>
                @endscope
                
                {{-- Status column --}}
                @scope('cell_status', $task)
                    @php
                        $statusTypes = [
                            'pending' => 'info',
                            'in_progress' => 'warning',
                            'complete' => 'success',
                            'blocked' => 'error',
                            'failed' => 'error'
                        ];
                    @endphp
                    <x-badge :type="$statusTypes[$task->status] ?? 'neutral'">{{ ucfirst($task->status) }}</x-badge>
                @endscope
                
                {{-- Created column --}}
                @scope('cell_created_at', $task)
                    <span class="text-readable-dim">{{ $task->created_at->format('M j, Y') }}</span>
                @endscope
            </x-table>
        </div>
    </section>
</div>
