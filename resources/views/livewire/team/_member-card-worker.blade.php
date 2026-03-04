{{-- Worker Card Component - Grid Layout --}}
<div class="worker-card group relative overflow-hidden bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-blue-500/40 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300">
    {{-- Background Glow Effect --}}
    <div class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:from-blue-500/15 group-hover:to-cyan-500/15 transition-all duration-500"></div>
    
    {{-- Header with Avatar and Name --}}
    <div class="relative flex items-start gap-4 mb-4">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/30 flex items-center justify-center text-3xl shadow-lg flex-shrink-0">
            {{ $member->emoji ?? '🛠️' }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-lg font-bold text-white truncate">{{ $member->name }}</h3>
                <x-badge type="primary" class="flex-shrink-0">Worker</x-badge>
            </div>
            @if($member->title)
                <p class="text-sm text-slate-400">{{ $member->title }}</p>
            @endif
            <div class="flex items-center gap-2 mt-2">
                <x-badge :type="in_array($member->status, ['active', 'online']) ? 'success' : 'neutral'">{{ ucfirst($member->status) }}</x-badge>
                @if($member->model)
                    <x-badge type="info">{{ ucfirst($member->model) }}</x-badge>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Stats Row --}}
    <div class="relative grid grid-cols-3 gap-2 mb-4 pt-4 border-t border-white/10">
        <div class="text-center">
            <p class="text-xs text-slate-400 mb-1">Tasks</p>
            <p class="text-lg font-bold text-white">{{ $member->tasks->count() }}</p>
        </div>
        <div class="text-center border-l border-white/10">
            <p class="text-xs text-slate-400 mb-1">Subtasks</p>
            <p class="text-lg font-bold text-white">{{ $member->children->count() }}</p>
        </div>
        <div class="text-center border-l border-white/10">
            <p class="text-xs text-slate-400 mb-1">ID</p>
            <p class="text-xs font-mono text-slate-500 truncate" title="{{ $member->id }}">
                {{ substr($member->id, 0, 8) }}
            </p>
        </div>
    </div>
    
    {{-- Action Buttons --}}
    <div class="relative flex gap-2">
        <a href="{{ route('team.show', $member->id) }}" 
           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-sm bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-500 hover:to-cyan-500 transition-all shadow-lg shadow-blue-500/20 font-medium">
            <span>📊</span>
            <span>View</span>
        </a>
        <button wire:click="edit('{{ $member->id }}')" 
                class="px-3 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
            ✏️
        </button>
        @if(!in_array($member->name, ['dave', 'sam', 'chen']))
            <button wire:click="delete('{{ $member->id }}')" 
                    wire:confirm="Are you sure you want to delete {{ $member->name }}?"
                    class="px-3 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30 font-medium">
                🗑️
            </button>
        @endif
    </div>
</div>
