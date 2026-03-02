<div class="task-board-unified" x-data="{
    handleDragStart(e) { 
        e.dataTransfer.setData('taskId', e.target.dataset.taskId); 
        e.target.style.opacity = '0.4'; 
    },
    handleDrop(e, newStep) { 
        e.preventDefault(); 
        const taskId = e.dataTransfer.getData('taskId'); 
        $wire.$call('moveTask', taskId, newStep);
    }
}">
    {{-- Task Board Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-5">
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                        <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-amber-500 flex items-center justify-center text-3xl shadow-xl">
                            📊
                        </div>
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Task Board</h1>
                        <p class="text-sm text-slate-400 font-medium mt-0.5">Kanban-style workflow visualization</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    @if($autoRefresh)
                    <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-semibold text-emerald-400">Auto-refresh ON</span>
                    </div>
                    @endif
                    
                    <button 
                        wire:click="$refresh"
                        class="group relative p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all duration-200"
                    >
                        <span class="group-hover:rotate-180 transition-transform duration-500 block">↻</span>
                    </button>
                </div>
            </div>
            
            {{-- Stats Row --}}
            <div class="flex flex-wrap gap-4">
                @foreach(['total' => 'Total', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed_today' => 'Today'] as $key => $label)
                <div class="group relative flex-1 min-w-[150px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-blue-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">{{ $label }}</div>
                        <div class="text-2xl font-bold text-white">{{ $stats[$key] ?? 0 }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </header>

    {{-- Agent Filter --}}
    <section class="mb-6">
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <div class="flex items-center gap-6 overflow-x-auto">
                <span class="text-sm font-medium text-slate-400">Filter by Agent:</span>
                @foreach(['all', 'dave', 'sam', 'chen', 'security'] as $agent)
                <button 
                    wire:click="$set('selectedAgent', '{{ $agent }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap
                        {{ $selectedAgent === $agent 
                            ? 'bg-gradient-to-r from-cyan-500/20 to-purple-500/20 text-white border border-cyan-400/50' 
                            : 'bg-slate-800/50 text-slate-400 border border-white/10 hover:border-white/20 hover:text-white'
                        }}"
                >
                    {{ $agent === 'all' ? 'All' : ucfirst($agent) }}
                    <span class="ml-2 px-2 py-0.5 rounded-full bg-white/10 text-xs">
                        {{ $agentCounts[$agent] ?? 0 }}
                    </span>
                </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Kanban Columns --}}
    <section class="overflow-x-auto">
        <div class="flex gap-4 min-w-[1200px]">
            @foreach($groupedTasks as $step => $tasks)
            <div class="flex-1 min-w-[280px]">
                {{-- Column Header --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xl">{{ $stepIcons[$step] ?? '📋' }}</span>
                    <h3 class="font-semibold text-white uppercase tracking-wide">{{ $columns[$step] }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full bg-white/10 text-xs font-mono text-slate-400">
                        {{ count($tasks) }}
                    </span>
                </div>
                
                {{-- Column Content --}}
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl p-3 border border-white/5 min-h-[400px] max-h-[700px] overflow-y-auto">
                    <div class="space-y-3">
                        @forelse($tasks as $task)
                        <div 
                            class="group relative bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-cyan-400/30 transition-all duration-200 cursor-move hover:shadow-lg hover:-translate-y-0.5"
                            draggable="true"
                            x-on:dragstart="handleDragStart"
                            x-data="{ taskId: '{{ $task->id }}' }"
                            :data-task-id="taskId"
                        >
                            {{-- Priority Indicator --}}
                            <div class="absolute left-0 top-3 bottom-3 w-1 rounded-r
                                @if($task->priority === 'critical') bg-red-500
                                @elseif($task->priority === 'high') bg-orange-500
                                @elseif($task->priority === 'medium') bg-yellow-500
                                @else bg-slate-500
                                @endif
                            "></div>
                            
                            {{-- Task Content --}}
                            <div class="ml-2">
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-xs font-mono text-slate-500">#{{ $task->id }}</span>
                                    
                                    @if($task->assigned_to)
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        @if($task->assigned_to === 'dave') bg-blue-500/20 text-blue-400
                                        @elseif($task->assigned_to === 'sam') bg-emerald-500/20 text-emerald-400
                                        @elseif($task->assigned_to === 'chen') bg-purple-500/20 text-purple-400
                                        @elseif($task->assigned_to === 'security') bg-orange-500/20 text-orange-400
                                        @else bg-slate-500/20 text-slate-400
                                        @endif
                                    ">
                                        {{ ucfirst($task->assigned_to) }}
                                    </span>
                                    @endif
                                </div>
                                
                                <h4 class="text-sm font-semibold text-white mb-2 group-hover:text-cyan-400 transition-colors">
                                    {{ $task->title }}
                                </h4>
                                
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        @if($task->priority === 'critical') bg-red-500/20 text-red-400
                                        @elseif($task->priority === 'high') bg-orange-500/20 text-orange-400
                                        @elseif($task->priority === 'medium') bg-yellow-500/20 text-yellow-400
                                        @else bg-slate-500/20 text-slate-400
                                        @endif
                                    ">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                    
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        @if($task->status === 'in_progress') bg-blue-500/20 text-blue-400
                                        @elseif($task->status === 'pending') bg-slate-500/20 text-slate-400
                                        @else bg-slate-500/20 text-slate-400
                                        @endif
                                    ">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                </div>
                                
                                <div class="text-xs text-slate-500 flex items-center gap-2">
                                    <span>🕐 {{ $task->created_at->diffForHumans(short: true) }}</span>
                                </div>
                            </div>
                            
                            {{-- Quick Actions (on hover) --}}
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                <button 
                                    wire:click="viewTask({{ $task->id }})"
                                    class="p-1.5 rounded-lg bg-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-600 transition-all"
                                    title="View Details"
                                >
                                    👁
                                </button>
                                @if($task->getNextStep())
                                <button 
                                    wire:click="completeTask({{ $task->id }})"
                                    class="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 transition-all"
                                    title="Advance to Next Step"
                                >
                                    →
                                </button>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-slate-500 text-sm">
                            No tasks in {{ strtolower($columns[$step]) }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>
