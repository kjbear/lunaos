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
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
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
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Comprehensive task management with filters</p>
                </div>
            </div>
            
            {{-- View Mode Toggle --}}
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center px-3 py-1.5 bg-white/5 border border-white/10 rounded-xl">
                    <label class="text-xs font-medium text-slate-400 mr-3 uppercase tracking-wider">View</label>
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
                    class="group relative p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all duration-200"
                >
                    <span class="group-hover:rotate-180 transition-transform duration-500 block">↻</span>
                </button>
            </div>
        </div>
        
        {{-- Stats Row --}}
        <div class="relative px-6 pb-6">
            <div class="flex flex-wrap gap-4">
                <div class="group relative flex-1 min-w-[150px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-blue-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Total</div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
                    </div>
                </div>
                
                <div class="group relative flex-1 min-w-[150px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-yellow-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Pending</div>
                        <div class="text-2xl font-bold text-yellow-400">{{ $stats['pending'] }}</div>
                    </div>
                </div>
                
                <div class="group relative flex-1 min-w-[150px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-blue-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">In Progress</div>
                        <div class="text-2xl font-bold text-blue-400">{{ $stats['in_progress'] }}</div>
                    </div>
                </div>
                
                <div class="group relative flex-1 min-w-[150px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-emerald-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1">Completed</div>
                        <div class="text-2xl font-bold text-emerald-400">{{ $stats['completed'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Filters Bar --}}
    <section class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Filters</h2>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-2">Search</label>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tasks..."
                        class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all"
                    >
                </div>
                
                {{-- Agent Filter --}}
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-2">Agent</label>
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
                    <label class="block text-xs font-medium text-slate-400 mb-2">Status</label>
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
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-white/10 bg-slate-800/30">
                            <th 
                                wire:click="setSort('id')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                ID @if($sortField === 'id'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                            <th 
                                wire:click="setSort('title')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                Task @if($sortField === 'title'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                            <th 
                                wire:click="setSort('assigned_to')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                Agent @if($sortField === 'assigned_to'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                            <th 
                                wire:click="setSort('step')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                Step @if($sortField === 'step'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                            <th 
                                wire:click="setSort('priority')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                Priority @if($sortField === 'priority'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                            <th 
                                wire:click="setSort('status')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                Status @if($sortField === 'status'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                            <th 
                                wire:click="setSort('created_at')"
                                class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                            >
                                Created @if($sortField === 'created_at'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else ↕️ @endif
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody class="divide-y divide-white/5">
                        @forelse($tasks as $task)
                        <tr 
                            class="group hover:bg-white/5 transition-colors cursor-pointer"
                            wire:click="viewTask({{ $task->id }})"
                        >
                            <td class="px-6 py-4 text-sm font-mono text-slate-400 group-hover:text-white">
                                #{{ $task->id }}
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-white group-hover:text-cyan-400 transition-colors">
                                    {{ $task->title }}
                                </div>
                                @if($task->description)
                                <div class="text-xs text-slate-500 mt-1 line-clamp-1">
                                    {{ Str::limit($task->description, 60) }}
                                </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4">
                                @if($task->assigned_to)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                                    @if($task->assigned_to === 'dave') bg-blue-500/20 text-blue-400 border border-blue-500/30
                                    @elseif($task->assigned_to === 'sam') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @elseif($task->assigned_to === 'chen') bg-purple-500/20 text-purple-400 border border-purple-500/30
                                    @elseif($task->assigned_to === 'security') bg-orange-500/20 text-orange-400 border border-orange-500/30
                                    @else bg-slate-500/20 text-slate-400 border border-slate-500/30
                                    @endif
                                ">
                                    {{ $task->agent_display_name ?? ucfirst($task->assigned_to) }}
                                </span>
                                @else
                                <span class="text-sm text-slate-500">Unassigned</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/50">
                                    {{ $task->step === 'develop' ? '🔧' : ($task->step === 'qa' ? '🧪' : ($task->step === 'security' ? '🔒' : ($task->step === 'staging' ? '🚀' : '✅'))) }}
                                    {{ ucfirst($task->step) }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                    @if($task->priority === 'critical') bg-red-500/20 text-red-400 border border-red-500/30
                                    @elseif($task->priority === 'high') bg-orange-500/20 text-orange-400 border border-orange-500/30
                                    @elseif($task->priority === 'medium') bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                                    @else bg-slate-500/20 text-slate-400 border border-slate-500/30
                                    @endif
                                ">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                    @if($task->status === 'in_progress') bg-blue-500/20 text-blue-400 border border-blue-500/30
                                    @elseif($task->status === 'complete') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @elseif($task->status === 'blocked') bg-red-500/20 text-red-400 border border-red-500/30
                                    @elseif($task->status === 'failed') bg-red-500/20 text-red-400 border border-red-500/30
                                    @else bg-slate-500/20 text-slate-400 border border-slate-500/30
                                    @endif
                                ">
                                    @if($task->status === 'pending')
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400 border border-gray-400/30">Pending</span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-400 border border-blue-400/30">In Progress</span>
                                    @elseif($task->status === 'complete')
                                        <span class="px-2 py-1 text-xs rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-400/30">Complete</span>
                                    @elseif($task->status === 'blocked')
                                        <span class="px-2 py-1 text-xs rounded-full bg-orange-500/20 text-orange-400 border border-orange-400/30">Blocked</span>
                                    @elseif($task->status === 'failed')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-500/20 text-red-400 border border-red-400/30">Failed</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-slate-500/20 text-slate-400 border border-slate-400/30">{{ ucfirst($task->status) }}</span>
                                    @endif
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 text-sm text-slate-400">
                                {{ $task->created_at->format('M j, Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-slate-400 text-lg mb-2">No tasks found</div>
                                <div class="text-slate-500 text-sm">Try adjusting your filters or search terms</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-white/10 bg-slate-800/30">
                {{ $tasks->links() }}
            </div>
        </div>
    </section>
</div>
