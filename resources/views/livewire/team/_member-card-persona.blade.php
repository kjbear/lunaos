{{-- Persona Card Component - List Layout --}}
<div class="persona-card group relative overflow-hidden bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-purple-500/40 hover:shadow-2xl hover:shadow-purple-500/10 transition-all duration-300">
    {{-- Background Glow Effect --}}
    <div class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:from-purple-500/15 group-hover:to-pink-500/15 transition-all duration-500"></div>
    
    <div class="relative flex justify-between items-start">
        {{-- Left: Avatar and Info --}}
        <div class="flex-1 flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 flex items-center justify-center text-3xl shadow-lg flex-shrink-0">
                {{ $member->emoji ?? '🎭' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-xl font-bold text-white">{{ $member->name }}</h3>
                    <x-badge type="info">Persona</x-badge>
                    <x-badge :type="in_array($member->status, ['active', 'online']) ? 'success' : 'neutral'">{{ ucfirst($member->status) }}</x-badge>
                </div>
                @if($member->title)
                    <p class="text-sm text-slate-400 mb-2">{{ $member->title }}</p>
                @endif
                @if($member->system_prompt)
                    <p class="text-sm text-slate-500 font-mono line-clamp-2">{{ Str::limit($member->system_prompt, 150) }}</p>
                @endif
                <div class="flex items-center gap-3 mt-3">
                    @if($member->model)
                        <x-badge type="primary">🧠 {{ ucfirst($member->model) }}</x-badge>
                    @endif
                    @if($member->parent)
                        <x-badge type="secondary">👤 Reports to: {{ $member->parent->name }}</x-badge>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Right: Actions --}}
        <div class="flex flex-col items-end gap-3">
            <div class="text-right">
                <p class="text-xs text-slate-400 mb-1">Assigned Tasks</p>
                <p class="text-2xl font-bold text-white">{{ $member->tasks->count() }}</p>
            </div>
            @if($member->children->count() > 0)
                <div class="text-right">
                    <p class="text-xs text-slate-400 mb-1">Sub-agents</p>
                    <p class="text-lg font-bold text-purple-400">{{ $member->children->count() }}</p>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Action Buttons --}}
    <div class="relative flex gap-2 mt-6 pt-4 border-t border-white/10">
        <a href="{{ route('team.show', $member->id) }}" 
           class="flex items-center gap-1.5 px-4 py-2 text-sm bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/20 font-medium">
            <span>📊</span>
            <span>View Details</span>
        </a>
        <button wire:click="edit('{{ $member->id }}')" 
                class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
            ✏️ Edit
        </button>
        @if(!in_array($member->name, ['dave', 'sam', 'chen']))
            <button wire:click="delete('{{ $member->id }}')" 
                    wire:confirm="Are you sure you want to delete {{ $member->name }}?"
                    class="px-4 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30 font-medium">
                ✗ Delete
            </button>
        @endif
    </div>
</div>
