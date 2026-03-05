<div class="space-y-6">
    {{-- Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">
                    ⚡
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Activity Feed</h1>
                    <p class="text-sm text-slate-400 mt-0.5">Real-time agent actions and decisions</p>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-2xl font-bold text-white">{{ $this->activities->count() }}</span>
                    </div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Recent Events</div>
                </div>
                <button wire:click="$refresh" class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                    🔄
                </button>
            </div>
        </div>
    </header>

    {{-- Filters --}}
    <section class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="$set('actionType', '')" 
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all {{ empty($actionType) ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                    All
                </button>
                @foreach(['reassigned', 'assigned_by_jordan', 'escalated', 'started', 'completed', 'failed'] as $action)
                <button wire:click="$set('actionType', '{{ $action }}')" 
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all {{ $actionType === $action ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                    {{ str_replace('_', ' ', ucfirst($action)) }}
                </button>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <select wire:model.live="agent" class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:border-purple-500/50 focus:outline-none">
                    <option value="">All Agents</option>
                    @foreach($this->agents as $agent)
                    <option value="{{ $agent }}">{{ $agent }}</option>
                    @endforeach
                </select>
                
                <select wire:model.live="dateRange" class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:border-purple-500/50 focus:outline-none">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="all">All Time</option>
                </select>
                
                <button wire:click="clearFilters" class="px-3 py-1.5 text-sm text-slate-500 hover:text-white transition-colors">
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
                {{ $this->activities->count() }} events
            </span>
        </div>

        <div class="space-y-4">
            @forelse($this->activities as $activity)
            @php
                $actionIcons = [
                    'reassigned' => '🔄',
                    'assigned_by_jordan' => '📋',
                    'escalated' => '⚠️',
                    'started' => '🚀',
                    'completed' => '✅',
                    'failed' => '❌',
                ];
                $actionColors = [
                    'reassigned' => 'border-blue-500/50 bg-blue-500/10 text-blue-400',
                    'assigned_by_jordan' => 'border-purple-500/50 bg-purple-500/10 text-purple-400',
                    'escalated' => 'border-amber-500/50 bg-amber-500/10 text-amber-400',
                    'started' => 'border-emerald-500/50 bg-emerald-500/10 text-emerald-400',
                    'completed' => 'border-green-500/50 bg-green-500/10 text-green-400',
                    'failed' => 'border-red-500/50 bg-red-500/10 text-red-400',
                ];
                $icon = $actionIcons[$activity->action] ?? '•';
                $colorClass = $actionColors[$activity->action] ?? 'border-slate-500/50 bg-slate-500/10 text-slate-400';
            @endphp
            
            <div class="relative pl-8 group">
                {{-- Timeline Line --}}
                <div class="absolute left-3 top-0 bottom-0 w-px bg-gradient-to-b from-purple-500/30 to-transparent"></div>
                
                {{-- Timeline Dot --}}
                <div class="absolute left-1 top-4 w-4 h-4 rounded-full {{ $colorClass }} border-2 border-slate-800 shadow-lg flex items-center justify-center text-xs">
                    {{ $icon }}
                </div>

                {{-- Activity Card --}}
                <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4 hover:border-purple-500/30 hover:shadow-lg transition-all">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 flex-1">
                            {{-- Agent Avatar --}}
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-lg flex-shrink-0">
                                @if($activity->agent_name === 'jordan')
                                    👔
                                @elseif($activity->agent_name === 'dave')
                                    👨‍💻
                                @elseif($activity->agent_name === 'sam')
                                    🔍
                                @elseif($activity->agent_name === 'chen')
                                    ⚙️
                                @else
                                    🤖
                                @endif
                            </div>
                            
                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="font-semibold text-white">{{ ucfirst($activity->agent_name) }}</span>
                                    <span class="text-xs text-slate-500">•</span>
                                    <span class="text-xs text-slate-400">{{ str_replace('_', ' ', ucfirst($activity->action)) }}</span>
                                    <span class="text-xs text-slate-500">•</span>
                                    <span class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                                
                                @if($activity->task)
                                <div class="text-sm text-slate-300 mb-2">
                                    <span class="text-slate-400">Task</span>
                                    <a href="/tasks/{{ $activity->task->id }}" class="font-medium text-purple-400 hover:text-purple-300 hover:underline">
                                        #{{ $activity->task->id }} - {{ Str::limit($activity->task->title, 60) }}
                                    </a>
                                </div>
                                @endif

                                {{-- Metadata --}}
                                @if($activity->metadata_json)
                                <div class="flex flex-wrap items-center gap-2">
                                    @if(isset($activity->metadata_json['from']) && isset($activity->metadata_json['to']))
                                    <x-badge type="info">{{ $activity->metadata_json['from'] }} → {{ $activity->metadata_json['to'] }}</x-badge>
                                    @endif
                                    @if(isset($activity->metadata_json['assignee']))
                                    <x-badge type="primary">Assigned to: {{ $activity->metadata_json['assignee'] }}</x-badge>
                                    @endif
                                    @if(isset($activity->metadata_json['priority']))
                                    <x-badge type="warning">Priority: {{ ucfirst($activity->metadata_json['priority']) }}</x-badge>
                                    @endif
                                    @if(isset($activity->metadata_json['reason']))
                                    <x-badge type="neutral">{{ Str::limit($activity->metadata_json['reason'], 50) }}</x-badge>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Details Button --}}
                        <button wire:click="showActivity({{ $activity->id }})" class="p-2 text-slate-500 hover:text-white transition-colors" title="View details">
                            🔍
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📭</div>
                <p class="text-slate-400">No activities found. Change filters or wait for agent actions.</p>
            </div>
            @endforelse
        </div>
    </section>

    {{-- Modal --}}
    @if($showModal && $selectedActivity)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-white/10 rounded-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Activity Details</h3>
                <button wire:click="closeModal" class="text-slate-400 hover:text-white text-2xl">×</button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 uppercase">Agent</div>
                        <div class="text-white font-semibold">{{ ucfirst($selectedActivity->agent_name) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase">Action</div>
                        <div class="text-white">{{ str_replace('_', ' ', ucfirst($selectedActivity->action)) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase">Time</div>
                        <div class="text-white">{{ $selectedActivity->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @if($selectedActivity->task)
                    <div>
                        <div class="text-xs text-slate-500 uppercase">Task</div>
                        <div class="text-purple-400">#{{ $selectedActivity->task->id }} - {{ $selectedActivity->task->title }}</div>
                    </div>
                    @endif
                </div>
                
                @if($selectedActivity->metadata_json)
                <div>
                    <div class="text-xs text-slate-500 uppercase mb-2">Metadata</div>
                    <div class="bg-slate-800/50 rounded-lg p-3 font-mono text-sm text-slate-300">
                        <pre>{{ json_encode($selectedActivity->metadata_json, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="p-4 border-t border-white/10 bg-slate-800/30">
                <button wire:click="closeModal" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold hover:opacity-90 transition-opacity">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
