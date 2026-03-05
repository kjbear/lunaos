<div class="kanban-board" x-data="{
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
    {{-- Polished Kanban Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        {{-- Subtle glow effect --}}
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-5">
                    {{-- Animated logo container --}}
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                        <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-amber-500 flex items-center justify-center text-3xl shadow-xl">
                            📋
                        </div>
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Development Pipeline</h1>
                        <p class="text-sm text-slate-400 font-medium mt-0.5">Agent-agnostic task management</p>
                    </div>
                </div>
                
                {{-- Live status badge --}}
                <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl {{ $autoRefresh ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-slate-500/10 border-slate-500/20' }} border">
                    <span class="relative flex h-2.5 w-2.5">
                        @if($autoRefresh)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        @endif
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $autoRefresh ? 'bg-emerald-500' : 'bg-slate-500' }}"></span>
                    </span>
                    <span class="text-sm font-semibold {{ $autoRefresh ? 'text-emerald-400' : 'text-slate-400' }}">
                        {{ $autoRefresh ? 'Auto-refresh ON' : 'Paused' }}
                    </span>
                </div>
            </div>
            
            {{-- Stats Row --}}
            <div class="flex flex-wrap gap-4">
                <div class="group relative flex-1 min-w-[180px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-blue-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Total Tasks</div>
                        <div class="text-3xl font-bold text-white">{{ $stats['total'] }}</div>
                    </div>
                </div>
                
                <div class="group relative flex-1 min-w-[180px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-blue-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Active</div>
                        <div class="text-3xl font-bold text-blue-400">{{ $stats['pending'] + $stats['in_progress'] }}</div>
                    </div>
                </div>
                
                <div class="group relative flex-1 min-w-[180px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-emerald-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Completed Today</div>
                        <div class="text-3xl font-bold text-emerald-400">{{ $stats['completed_today'] }}</div>
                    </div>
                </div>
                
                @if($stats['failed'] > 0)
                <div class="group relative flex-1 min-w-[180px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-red-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Failed</div>
                        <div class="text-3xl font-bold text-red-400">{{ $stats['failed'] }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 font-medium">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- Filters Bar --}}
    <section class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Filters</h2>
        </div>
        
        <div class="flex flex-wrap items-center gap-4 p-4 bg-slate-900/40 backdrop-blur-sm rounded-xl border border-white/10">
            {{-- Agent filter --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-slate-400 text-sm font-medium">Agent:</span>
                @foreach(['all', 'dave', 'sam', 'chen', 'security'] as $agentFilter)
                    <button 
                        wire:click="$set('selectedAgent', '{{ $agentFilter }}')"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $selectedAgent === $agentFilter ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/25' : 'bg-white/5 border border-white/10 text-slate-400 hover:bg-white/10 hover:border-white/20' }}"
                    >
                        @if($agentFilter === 'all')
                            All ({{ $agentCounts['all'] }})
                        @else
                            {{ ucfirst($agentFilter) }} ({{ $agentCounts[$agentFilter] ?? 0 }})
                        @endif
                    </button>
                @endforeach
            </div>
            
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search tasks..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-900/60 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-purple-500/50 focus:ring-2 focus:ring-purple-500/20 transition-all"
                />
            </div>
            
            {{-- Show completed toggle --}}
            <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer hover:text-white transition-colors">
                <div class="relative">
                    <input 
                        type="checkbox" 
                        wire:model.live="showCompleted"
                        class="sr-only peer"
                    />
                    <div class="w-10 h-6 bg-slate-700 rounded-full peer-focus:ring-2 peer-focus:ring-purple-500/20 peer-checked:bg-purple-500 transition-colors"></div>
                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-4"></div>
                </div>
                <span class="font-medium">Show Completed</span>
            </label>
        </div>
    </section>

    {{-- Kanban Columns --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Pipeline</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">5 stages</span>
        </div>
        
        <div class="flex gap-4 overflow-x-auto pb-6">
            @foreach(['develop', 'qa', 'security', 'staging', 'production'] as $step)
                <div class="min-w-[300px] max-w-[320px] flex-shrink-0">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between mb-3 p-3 bg-slate-900/40 backdrop-blur-sm rounded-xl border border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $stepIcons[$step] }}</span>
                            <h3 class="font-semibold text-white capitalize">{{ $stepLabels[$step] }}</h3>
                        </div>
                        <span class="px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-xs font-semibold text-slate-400">
                            {{ count($groupedTasks[$step]) }}
                        </span>
                    </div>
                    
                    {{-- Task Cards --}}
                    <div class="space-y-3 max-h-[calc(100vh-500px)] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                        @forelse($groupedTasks[$step] as $task)
                            <div 
                                draggable="true"
                                x-on:dragstart="handleDragStart"
                                x-on:drop="handleDrop(event, '{{ $task->status }}')"
                                x-on:dragover.prevent
                                data-task-id="{{ $task->id }}"
                                class="group relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border {{ $task->status === 'failed' ? 'border-red-500/30 shadow-[0_0_20px_rgba(239,68,68,0.1)]' : 'border-white/10' }} hover:border-purple-400/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 cursor-move"
                            >
                                {{-- Task Header --}}
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-xs font-mono text-slate-500">#{{ $task->id }}</span>
                                    <div class="flex items-center gap-2">
                                        {{-- Agent badge --}}
                                        <span class="text-[10px] px-2 py-1 rounded-full bg-{{ $agentColors[$task->assigned_to ?? 'unassigned'] }}-500/20 text-{{ $agentColors[$task->assigned_to ?? 'unassigned'] }}-400 border border-{{ $agentColors[$task->assigned_to ?? 'unassigned'] }}-500/30 font-medium">
                                            {{ $task->agent_display_name }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- Task Title --}}
                                <h4 class="text-white font-semibold mb-3 line-clamp-2">{{ $task->title }}</h4>
                                
                                {{-- Priority & Type --}}
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-[10px] px-2 py-1 rounded border font-medium {{ $task->priority_badge_class }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                    @if($task->task_type !== 'feature')
                                        <span class="text-[10px] px-2 py-1 rounded bg-white/5 text-slate-400 border border-white/10 font-medium">
                                            {{ $task->task_type }}
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- Created time --}}
                                <div class="text-xs text-slate-500 font-medium mb-3">
                                    {{ $task->created_at_human }}
                                </div>
                                
                                {{-- Actions (show on hover) --}}
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all pt-3 border-t border-white/10">
                                    @if($task->status !== 'complete')
                                        <button 
                                            wire:click="completeTask({{ $task->id }})"
                                            class="flex-1 px-3 py-1.5 bg-emerald-500/20 text-emerald-400 rounded-lg text-xs font-semibold hover:bg-emerald-500/30 transition-all shadow-lg shadow-emerald-500/10"
                                        >
                                            ✓ Complete
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="deleteTask({{ $task->id }})"
                                        class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg text-xs font-semibold hover:bg-red-500/30 transition-all shadow-lg shadow-red-500/10"
                                        onclick="return confirm('Delete this task?')"
                                    >
                                        ✕
                                    </button>
                                </div>
                                
                                {{-- Progress bar --}}
                                @if($task->status === 'in_progress')
                                    <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 animate-pulse" style="width: {{ $task->progress_percentage }}%"></div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-12 text-slate-500 bg-slate-900/40 rounded-xl border border-white/10">
                                <div class="text-4xl mb-2">🎯</div>
                                <div class="text-sm font-medium">No tasks in this stage</div>
                            </div>
                        @endforelse
                    </div>
                    
                    {{-- Scroll indicator for overflow --}}
                    @if(count($groupedTasks[$step]) > 3)
                        <div class="mt-3 text-center">
                            <span class="text-xs text-slate-500 font-medium">↓ {{ count($groupedTasks[$step]) - 3 }} more below</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    
    {{-- Recent Activity --}}
    <section class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-readable uppercase tracking-wider">Recent Activity</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-readable-dim">Last 10 actions</span>
        </div>
        
        <div class="bg-base-200 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
            {{-- MaryUI x-table component --}}
            <x-table 
                :headers="[
                    ['key' => 'created_at', 'label' => 'Time'],
                    ['key' => 'task_id', 'label' => 'Task'],
                    ['key' => 'agent_name', 'label' => 'Agent'],
                    ['key' => 'action', 'label' => 'Action'],
                ]"
                :rows="$recentActivity"
                striped
                no-pagination
            >
                {{-- Custom cell rendering --}}
                @scope('cell_created_at', $activity)
                    <span class="font-mono text-readable/80 text-sm">{{ $activity->created_at->diffForHumans() }}</span>
                @endscope
                
                @scope('cell_task_id', $activity)
                    <span class="text-readable text-sm font-medium">
                        #{{ $activity->task_id }} — {{ $activity->task?->title ?? 'Deleted' }}
                    </span>
                @endscope
                
                @scope('cell_agent_name', $activity)
                    <span class="text-purple-400 text-sm capitalize font-medium">{{ $activity->agent_name }}</span>
                @endscope
                
                @scope('cell_action', $activity)
                    <x-badge type="neutral">{{ $activity->action }}</x-badge>
                @endscope
            </x-table>
        </div>
    </section>

    @script
    <script>
        @if($autoRefresh)
        setInterval(() => {
            Livewire.dispatch('task-updated');
        }, {{ $refreshInterval * 1000 }});
        @endif
    </script>
    @endscript
</div>
