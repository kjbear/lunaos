<div class="space-y-6">
    {{-- Header with Real-time Stats --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">⚡</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Activity Feed</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Real-time agent actions and system events</p>
                </div>
            </div>
            
            {{-- Live Stats --}}
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-2xl font-bold text-white">{{ $activities->count() ?? 0 }}</span>
                    </div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Recent Events</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <button 
                    wire:click="$refresh"
                    class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                    title="Refresh"
                >
                    🔄
                </button>
            </div>
        </div>
    </header>

    {{-- Filters and Search --}}
    <section class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            {{-- Filter Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                @foreach(['all', 'create', 'update', 'delete', 'complete'] as $filter)
                <button 
                    wire:click="$set('actionType', '{{ $filter === 'all' ? '' : $filter }}')"
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all {{ (empty($actionType) && $filter === 'all') || $actionType === $filter ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}"
                >
                    {{ ucfirst($filter) }}
                </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="flex items-center gap-3">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search activities..."
                        class="bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-1.5 text-sm text-slate-300 placeholder-slate-500 focus:border-purple-500/50 focus:outline-none w-64"
                    >
                </div>
                <select 
                    wire:model.live="agentFilter"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:border-purple-500/50 focus:outline-none"
                >
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
                <button 
                    wire:click="clearFilters"
                    class="px-3 py-1.5 text-sm text-slate-500 hover:text-white transition-colors"
                >
                    Clear
                </button>
            </div>
        </div>
    </section>

    {{-- Timeline --}}
    <section>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Timeline</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">
                {{ $activities->count() }} events
            </span>
        </div>

        <div class="space-y-4">
            @forelse($activities as $activity)
            @php
                $actionColors = [
                    'create' => 'border-emerald-500/50 bg-emerald-500/10 text-emerald-400',
                    'update' => 'border-blue-500/50 bg-blue-500/10 text-blue-400',
                    'delete' => 'border-red-500/50 bg-red-500/10 text-red-400',
                    'complete' => 'border-purple-500/50 bg-purple-500/10 text-purple-400',
                ];
                $actionIcons = [
                    'create' => '✨',
                    'update' => '✏️',
                    'delete' => '🗑️',
                    'complete' => '✅',
                ];
                $impactColors = [
                    'high' => 'text-red-400 bg-red-500/20',
                    'medium' => 'text-amber-400 bg-amber-500/20',
                    'low' => 'text-slate-400 bg-slate-500/20',
                ];
            @endphp
            
            <div class="relative pl-8 group">
                {{-- Timeline Line --}}
                <div class="absolute left-3 top-0 bottom-0 w-px bg-gradient-to-b from-purple-500/30 via-cyan-500/30 to-transparent"></div>
                
                {{-- Timeline Dot --}}
                <div class="absolute left-1 top-4 w-4 h-4 rounded-full {{ $actionColors[$activity->action_type] ?? 'bg-slate-500/30' }} border-2 border-slate-800 shadow-lg flex items-center justify-center text-xs">
                    {{ $actionIcons[$activity->action_type] ?? '•' }}
                </div>

                {{-- Activity Card --}}
                <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4 hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 flex-1">
                            {{-- Agent Avatar --}}
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-lg flex-shrink-0">
                                {{ ($agents->firstWhere('id', $activity->agent_id) ?? (object)['emoji' => '🤖'])->emoji }}
                            </div>
                            
                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-white">{{ ($agents->firstWhere('id', $activity->agent_id) ?? (object)['name' => 'Unknown'])->name }}</span>
                                    <span class="text-xs text-slate-500">•</span>
                                    <span class="text-xs text-slate-400">{{ $activity->action_type }}</span>
                                    <span class="text-xs text-slate-500">•</span>
                                    <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</span>
                                </div>
                                
                                <div class="text-sm text-slate-300 mb-2">
                                    <span class="font-medium text-white">{{ $activity->action_name }}</span>
                                    @if($activity->description)
                                    <span class="text-slate-400"> - {{ Str::limit($activity->description, 100) }}</span>
                                    @endif
                                </div>

                                {{-- Metadata Tags --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($activity->task_id)
                                    <span class="px-2 py-1 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs font-medium">
                                        #Task-{{ $activity->task_id }}
                                    </span>
                                    @endif
                                    <span class="px-2 py-1 rounded {{ $impactColors[$activity->impact] ?? 'text-slate-400 bg-slate-500/20' }} text-xs font-medium border border-white/5">
                                        Impact: {{ ucfirst($activity->impact) }}
                                    </span>
                                    @if($activity->cost_impact > 0)
                                    <span class="px-2 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-medium">
                                        +${{ number_format($activity->cost_impact, 4) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Expand Button --}}
                        <button 
                            wire:click="toggleExpand('{{ $activity->id }}')"
                            class="p-2 text-slate-500 hover:text-white transition-colors"
                        >
                            {{ $expandedActivities[$activity->id] ? '▼' : '▶' }}
                        </button>
                    </div>

                    {{-- Expanded Details --}}
                    @if($expandedActivities[$activity->id])
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-slate-500">Agent Model:</span>
                                <span class="ml-2 text-slate-300">{{ ($agents->firstWhere('id', $activity->agent_id) ?? (object)['model' => 'Unknown'])->model }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Duration:</span>
                                <span class="ml-2 text-slate-300">{{ $activity->duration ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Tokens Used:</span>
                                <span class="ml-2 text-cyan-300 font-mono">{{ number_format($activity->tokens_used ?? 0) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Cost:</span>
                                <span class="ml-2 text-purple-300 font-mono">${{ number_format($activity->cost ?? 0, 4) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                <div class="text-5xl mb-4 opacity-50">📭</div>
                <p class="text-slate-400 font-semibold">No activities found</p>
                @if(!empty($actionType) || $search || !empty($agent))
                <p class="text-sm text-slate-500 mt-2">Try adjusting your filters.</p>
                @endif
            </div>
            @endforelse
        </div>

        {{-- Load More --}}
        @if($activities->count() > 0 && $hasMore)
        <div class="flex justify-center mt-8">
            <button 
                wire:click="loadMore"
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25"
            >
                Load More Activities
            </button>
        </div>
        @endif
    </section>
</div>
