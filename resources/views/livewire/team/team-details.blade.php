<div class="team-details space-y-6">
    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('team') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to Team</span>
        </a>
    </div>
    
    @if($member)
    @php
        $gradients = [
            'workers' => ['from-blue-500/20 to-cyan-500/20', 'from-blue-500/10 to-cyan-500/10', 'border-blue-500/30', 'from-blue-600 to-cyan-600', 'shadow-blue-500/20'],
            'personas' => ['from-purple-500/20 to-pink-500/20', 'from-purple-500/10 to-pink-500/10', 'border-purple-500/30', 'from-purple-600 to-pink-600', 'shadow-purple-500/20'],
            'board-members' => ['from-amber-500/20 to-orange-500/20', 'from-amber-500/10 to-orange-500/10', 'border-amber-500/30', 'from-amber-600 to-orange-600', 'shadow-amber-500/20'],
        ];
        $g = $gradients[$member->type] ?? $gradients['workers'];
    @endphp
    
    {{-- Member Card --}}
    <div class="group relative overflow-hidden bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 hover:border-purple-500/30 transition-all duration-300">
        {{-- Background Glow --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br {{ $g[1] }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        
        {{-- Header --}}
        <div class="relative flex items-start gap-6 p-6 border-b border-white/10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br {{ $g[0] }} {{ $g[2] }} flex items-center justify-center text-4xl shadow-lg flex-shrink-0">
                {{ $member->emoji ?? ($member->type === 'workers' ? '🤖' : ($member->type === 'personas' ? '🎭' : '👔')) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-white">{{ $member->name }}</h1>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">
                        {{ ucfirst(str_replace('-', ' ', $member->type)) }}
                    </span>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ in_array($member->status, ['active', 'online']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30' }}">
                        {{ ucfirst($member->status) }}
                    </span>
                </div>
                @if($member->title)
                    <p class="text-slate-400 text-lg">{{ $member->title }}</p>
                @endif
                @if($member->email)
                    <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                        <span>📧</span>
                        <span>{{ $member->email }}</span>
                    </p>
                @endif
            </div>
            
            {{-- Actions --}}
            <div class="flex gap-2">
                <a href="{{ route('team.edit', $member->id) }}" 
                   class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
                    ✏️ Edit
                </a>
                <button type="button" wire:click="toggleStatus" 
                        class="px-4 py-2 text-sm bg-emerald-500/20 text-emerald-400 rounded-lg hover:bg-emerald-500/30 transition-all border border-emerald-500/30 font-medium">
                    Toggle Status
                </button>
                <button type="button" wire:click="confirmDelete" 
                        class="px-4 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30 font-medium">
                    🗑️ Delete
                </button>
            </div>
        </div>
        
        {{-- Details Grid --}}
        <div class="relative grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
            {{-- Column 1: Basic Info --}}
            <div class="space-y-4">
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Information</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-slate-500">Role</span>
                        <span class="text-white font-medium">{{ ucfirst($member->role) }}</span>
                    </div>
                    @if($member->model)
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-slate-500">Model</span>
                        <span class="text-white font-medium">{{ $member->model }}</span>
                    </div>
                    @endif
                    @if($member->provider)
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-slate-500">Provider</span>
                        <span class="text-white font-medium">{{ $member->provider }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-slate-500">Member Since</span>
                        <span class="text-white font-medium">{{ $member->created_at?->diffForHumans() ?? 'Recently' }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Column 2: Stats --}}
            <div class="space-y-4">
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Stats</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Tasks</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ $member->tasks->count() }}</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Reports</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ $member->children->count() }}</p>
                    </div>
                </div>
                @if($member->parent)
                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Reports To</p>
                    <a href="{{ route('team.show', $member->parent->id) }}" class="text-purple-400 hover:text-purple-300 font-medium mt-1 block">
                        {{ $member->parent->name }}
                    </a>
                </div>
                @endif
            </div>
            
            {{-- Column 3: Direct Reports or Tasks --}}
            <div class="space-y-4">
                @if($member->children->count() > 0)
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Direct Reports</h2>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($member->children as $child)
                    <a href="{{ route('team.show', $child->id) }}" 
                       class="flex items-center gap-3 p-3 bg-white/5 rounded-lg border border-white/10 hover:bg-white/10 transition-colors">
                        <span class="text-lg">{{ $child->emoji ?? '👤' }}</span>
                        <span class="text-white font-medium">{{ $child->name }}</span>
                        <span class="ml-auto text-xs text-slate-500">{{ ucfirst(str_replace('-', ' ', $child->type)) }}</span>
                    </a>
                    @endforeach
                </div>
                @else
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Recent Tasks</h2>
                @if($member->tasks->count() > 0)
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($member->tasks->take(5) as $task)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10">
                        <span class="text-white text-sm truncate flex-1">{{ $task->title }}</span>
                        <span class="text-xs px-2 py-1 rounded ml-2 {{ $task->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </div>
                    @endforeach
                    @if($member->tasks->count() > 5)
                    <p class="text-xs text-slate-500 text-center">+{{ $member->tasks->count() - 5 }} more</p>
                    @endif
                </div>
                @else
                <div class="text-center py-6 text-slate-500">
                    <p>No tasks assigned</p>
                </div>
                @endif
                @endif
            </div>
        </div>
        
        {{-- Metadata Section --}}
        @if(!empty($metadata))
        <div class="relative p-6 border-t border-white/10">
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Metadata</h2>
            <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($metadata as $key => $value)
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-slate-500">{{ $key }}</span>
                        <span class="text-white font-medium text-sm">{{ is_array($value) ? json_encode($value) : $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        {{-- Settings Section --}}
        @if(!empty($settings))
        <div class="relative p-6 border-t border-white/10">
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Settings</h2>
            <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($settings as $key => $value)
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-slate-500">{{ $key }}</span>
                        <span class="text-white font-medium text-sm">{{ is_bool($value) ? ($value ? '✓' : '✗') : $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        {{-- Activity History Section --}}
        @if(!empty($activityHistory))
        <div class="relative p-6 border-t border-white/10">
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Activity History</h2>
            <div class="space-y-2">
                @foreach($activityHistory as $activity)
                <div class="flex items-center gap-4 p-3 bg-white/5 rounded-lg border border-white/10">
                    <span class="text-xs text-slate-500">{{ $activity['timestamp'] }}</span>
                    <span class="text-white">{{ ucfirst($activity['action']) }}: {{ $activity['item'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
        <div class="text-5xl mb-4 opacity-50">🔍</div>
        <p class="text-slate-400 font-semibold">Team member not found</p>
        <a href="{{ route('team') }}" class="mt-4 text-purple-400 hover:text-purple-300">Return to Team</a>
    </div>
    @endif
</div>