{{-- Board Member Card Component - List Layout --}}
<div class="board-member-card group relative overflow-hidden bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-amber-500/40 hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300">
    {{-- Background Glow Effect --}}
    <div class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-br from-amber-500/10 to-orange-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:from-amber-500/15 group-hover:to-orange-500/15 transition-all duration-500"></div>
    
    <div class="relative flex justify-between items-start">
        {{-- Left: Avatar and Info --}}
        <div class="flex-1 flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 border border-amber-500/30 flex items-center justify-center text-3xl shadow-lg flex-shrink-0">
                {{ $member->emoji ?? '🎯' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-xl font-bold text-white">{{ $member->name }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        Board Member
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $member->status === 'active' || $member->status === 'online' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30' }}">
                        {{ ucfirst($member->status) }}
                    </span>
                </div>
                @if($member->title)
                    <p class="text-sm text-slate-400 mb-2">{{ $member->title }}</p>
                @endif
                @if($member->system_prompt)
                    <p class="text-sm text-slate-500 font-mono line-clamp-2">{{ Str::limit($member->system_prompt, 150) }}</p>
                @endif
                @if($member->email)
                    <p class="text-sm text-slate-500 mt-2 flex items-center gap-2">
                        <span>📧</span>
                        <span>{{ $member->email }}</span>
                    </p>
                @endif
            </div>
        </div>
        
        {{-- Right: Stats --}}
        <div class="flex flex-col items-end gap-3">
            <div class="text-right">
                <p class="text-xs text-slate-400 mb-1">Oversight</p>
                <p class="text-2xl font-bold text-white">{{ $member->children->count() }}</p>
            </div>
            @if($member->tasks->count() > 0)
                <div class="text-right">
                    <p class="text-xs text-slate-400 mb-1">Tasks</p>
                    <p class="text-lg font-bold text-amber-400">{{ $member->tasks->count() }}</p>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Action Buttons --}}
    <div class="relative flex gap-2 mt-6 pt-4 border-t border-white/10">
        <a href="{{ route('team.show', $member->id) }}" 
           class="flex items-center gap-1.5 px-4 py-2 text-sm bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-lg hover:from-amber-500 hover:to-orange-500 transition-all shadow-lg shadow-amber-500/20 font-medium">
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
