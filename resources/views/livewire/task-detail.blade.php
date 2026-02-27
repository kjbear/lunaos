<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header with Task Info --}}
    <header class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <a href="/activity" class="text-purple-400 hover:text-purple-300 hover:underline flex items-center gap-2">
                ← Back to Activity Feed
            </a>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $this->statusBadgeClass }}">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $this->priorityBadgeClass }}">
                    {{ ucfirst($task->priority) }} Priority
                </span>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 p-8 shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
            <div class="relative">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-4xl shadow-xl flex-shrink-0">
                        📋
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-white mb-2">{{ $task->title }}</h1>
                        <div class="flex items-center gap-4 text-sm text-slate-400">
                            <span>Task #{{ $task->id }}</span>
                            <span>•</span>
                            <span>{{ $task->created_at->format('M d, Y') }}</span>
                            <span>•</span>
                            <span>Step: {{ ucfirst($task->step ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>

                @if($agent)
                <div class="flex items-center gap-3 bg-white/5 rounded-xl p-4 border border-white/10">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-2xl">
                        @if($agent->name === 'jordan')
                            👔
                        @elseif($agent->name === 'dave')
                            👨‍💻
                        @elseif($agent->name === 'sam')
                            🔍
                        @elseif($agent->name === 'chen')
                            ⚙️
                        @else
                            🤖
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-white font-semibold">{{ ucfirst($agent->name) }}</div>
                        <div class="text-xs text-slate-400">{{ $agent->title ?? 'Agent' }} • {{ $agent->model }} @ {{ $agent->provider }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 uppercase">Capabilities</div>
                        <div class="text-xs text-slate-300">{{ implode(', ', json_decode($agent->capabilities ?? '[]', true) ?? []) }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </header>

    {{-- Task Description --}}
    <section class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span>📝</span> Description
        </h2>
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-6 text-slate-300 leading-relaxed">
            {{ $task->description }}
        </div>
    </section>

    {{-- Activity Timeline --}}
    <section>
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span>⚡</span> Activity History
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">
                {{ $activities->count() }} events
            </span>
        </h2>

        @forelse($activities as $activity)
        @php
            $actionIcons = [
                'reassigned' => '🔄',
                'assigned_by_jordan' => '📋',
                'escalated' => '⚠️',
                'started' => '🚀',
                'completed' => '✅',
                'failed' => '❌',
            ];
            $icon = $actionIcons[$activity->action] ?? '•';
        @endphp
        
        <div class="relative pl-8 mb-6 last:mb-0">
            {{-- Timeline Line --}}
            <div class="absolute left-3 top-0 bottom-0 w-px bg-gradient-to-b from-purple-500/30 to-transparent"></div>
            
            {{-- Timeline Dot --}}
            <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-purple-500/20 border-2 border-purple-500 shadow-lg flex items-center justify-center text-xs">
                {{ $icon }}
            </div>

            {{-- Activity Card --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-5">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-lg">
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
                        <div>
                            <div class="text-white font-semibold">{{ ucfirst($activity->agent_name) }}</div>
                            <div class="text-xs text-slate-400">{{ str_replace('_', ' ', ucfirst($activity->action)) }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-slate-300">{{ $activity->created_at->format('M d, Y \a\t H:i') }}</div>
                        <div class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                {{-- Action Details / Reasoning --}}
                @if($activity->metadata_json && count($activity->metadata_json) > 0)
                <div class="bg-slate-800/50 rounded-lg p-4 border border-white/5">
                    <div class="text-xs text-slate-500 uppercase mb-2 font-semibold">
                        🧠 Decision Context & Reasoning
                    </div>
                    
                    @if(isset($activity->metadata_json['from']) && isset($activity->metadata_json['to']))
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30 text-sm font-medium">
                            {{ $activity->metadata_json['from'] }}
                        </span>
                        <span class="text-slate-500">→</span>
                        <span class="px-2 py-1 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30 text-sm font-medium">
                            {{ $activity->metadata_json['to'] }}
                        </span>
                    </div>
                    @endif

                    @if(isset($activity->metadata_json['reason']))
                    <div class="mb-3">
                        <div class="text-xs text-slate-500 mb-1">Reasoning:</div>
                        <div class="text-slate-300 text-sm leading-relaxed">
                            {{ $activity->metadata_json['reason'] }}
                        </div>
                    </div>
                    @endif

                    @if(isset($activity->metadata_json['reasoning']))
                    <div class="mb-3">
                        <div class="text-xs text-slate-500 mb-1">Analysis:</div>
                        <div class="text-slate-300 text-sm leading-relaxed">
                            {{ $activity->metadata_json['reasoning'] }}
                        </div>
                    </div>
                    @endif

                    @if(isset($activity->metadata_json['assignee']))
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs font-medium">
                            Assigned to: {{ $activity->metadata_json['assignee'] }}
                        </span>
                        @if(isset($activity->metadata_json['priority']))
                        <span class="px-2 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-medium">
                            Priority: {{ ucfirst($activity->metadata_json['priority']) }}
                        </span>
                        @endif
                    </div>
                    @endif

                    {{-- Full Metadata (Expandable) --}}
                    <div class="mt-3 pt-3 border-t border-white/5">
                        <details class="text-xs">
                            <summary class="text-slate-500 hover:text-slate-300 cursor-pointer">
                                🔍 View Full Metadata (JSON)
                            </summary>
                            <div class="mt-2 bg-slate-900/80 rounded p-3 font-mono text-xs text-slate-400 overflow-x-auto">
                                <pre>{{ json_encode($activity->metadata_json, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </details>
                    </div>
                </div>
                @else
                <div class="text-slate-500 text-sm italic">No additional context provided</div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-12 bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-slate-400">No activity recorded for this task yet.</p>
        </div>
        @endforelse
    </section>

    {{-- Task Metadata --}}
    <section class="mt-8">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span>📊</span> Task Metadata
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4">
                <div class="text-xs text-slate-500 uppercase mb-1">Type</div>
                <div class="text-white font-semibold">{{ ucfirst($task->task_type ?? 'N/A') }}</div>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4">
                <div class="text-xs text-slate-500 uppercase mb-1">Workflow Step</div>
                <div class="text-white font-semibold">{{ ucfirst($task->step ?? 'N/A') }}</div>
            </div>
            @if($task->repository_id)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4">
                <div class="text-xs text-slate-500 uppercase mb-1">Repository</div>
                <div class="text-white font-semibold">#{{ $task->repository_id }}</div>
            </div>
            @endif
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4">
                <div class="text-xs text-slate-500 uppercase mb-1">Created</div>
                <div class="text-white font-semibold">{{ $task->created_at->format('M d, Y H:i') }}</div>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4">
                <div class="text-xs text-slate-500 uppercase mb-1">Last Updated</div>
                <div class="text-white font-semibold">{{ $task->updated_at->diffForHumans() }}</div>
            </div>
        </div>
    </section>
</div>
