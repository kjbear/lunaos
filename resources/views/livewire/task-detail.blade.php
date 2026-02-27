<div>
    <h1 class="text-3xl font-bold text-white mb-4">Task #{{ $task->id }}</h1>
    <div class="bg-slate-900/60 rounded-xl p-6 border border-white/10 mb-6">
        <h2 class="text-2xl font-semibold text-white mb-2">{{ $task->title }}</h2>
        <p class="text-slate-300">{{ $task->description }}</p>
        <div class="mt-4 flex gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                {{ ucfirst($task->status) }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                {{ ucfirst($task->priority) }}
            </span>
        </div>
    </div>

    @if($agent)
    <div class="bg-slate-900/60 rounded-xl p-6 border border-white/10 mb-6">
        <h3 class="text-xl font-semibold text-white mb-4">Assigned Agent</h3>
        <div class="flex items-center gap-4">
            <div class="text-4xl">
                @if($agent->name === 'jordan')👔
                @elseif($agent->name === 'dave')👨‍💻
                @elseif($agent->name === 'sam')🔍
                @elseif($agent->name === 'chen')⚙️
                @else🤖
                @endif
            </div>
            <div>
                <div class="text-white font-semibold">{{ ucfirst($agent->name) }}</div>
                <div class="text-sm text-slate-400">{{ $agent->title }} • {{ $agent->model }} @ {{ $agent->provider }}</div>
            </div>
        </div>
    </div>
    @endif

    <div>
        <h3 class="text-xl font-semibold text-white mb-4">Activity History ({{ $activities->count() }} events)</h3>
        @forelse($activities as $activity)
        <div class="bg-slate-800/50 rounded-lg p-4 mb-3 border border-white/5">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">
                        @if($activity->agent_name === 'jordan')👔
                        @elseif($activity->agent_name === 'dave')👨‍💻
                        @elseif($activity->agent_name === 'sam')🔍
                        @elseif($activity->agent_name === 'chen')⚙️
                        @else🤖
                        @endif
                    </span>
                    <div>
                        <div class="text-white font-semibold">{{ ucfirst($activity->agent_name) }}</div>
                        <div class="text-xs text-slate-400">{{ str_replace('_', ' ', ucfirst($activity->action)) }}</div>
                    </div>
                </div>
                <div class="text-sm text-slate-400">{{ $activity->created_at->diffForHumans() }}</div>
            </div>
            
            @if($activity->metadata_json && count($activity->metadata_json) > 0)
            <div class="mt-3 bg-slate-900/80 rounded p-3 text-sm">
                <div class="text-xs text-slate-500 mb-2 uppercase">Decision Context:</div>
                @if(isset($activity->metadata_json['reason']))
                <div class="text-slate-300 mb-2">{{ $activity->metadata_json['reason'] }}</div>
                @endif
                @if(isset($activity->metadata_json['from']) && isset($activity->metadata_json['to']))
                <div class="text-slate-300">Reassigned: {{ $activity->metadata_json['from'] }} → {{ $activity->metadata_json['to'] }}</div>
                @endif
                @if(isset($activity->metadata_json['assignee']))
                <div class="text-slate-300">Assigned to: {{ $activity->metadata_json['assignee'] }} (Priority: {{ $activity->metadata_json['priority'] }})</div>
                @endif
                <details class="mt-2">
                    <summary class="text-xs text-slate-500 cursor-pointer">View Full JSON</summary>
                    <pre class="mt-1 text-xs text-slate-400 overflow-x-auto">{{ json_encode($activity->metadata_json, JSON_PRETTY_PRINT) }}</pre>
                </details>
            </div>
            @endif
        </div>
        @empty
        <div class="text-slate-400 text-center py-8">No activity recorded yet</div>
        @endforelse
    </div>
</div>
