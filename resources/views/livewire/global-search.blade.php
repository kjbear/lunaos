<div 
    x-data="{ open: @entangle('isOpen') }"
    x-on:keydown.escape.window="closeSearch()"
    class="relative"
>
    {{-- Search Input with Keyboard Shortcut --}}
    <div class="relative group">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-purple-400 transition-colors">🔍</span>
        <input 
            type="text" 
            wire:model.live.debounce.200ms="query"
            x-ref="searchInput"
            x-on:keydown.k.prevent="$wire.openSearch()"
            placeholder="Search tasks, docs, agents..." 
            class="w-full bg-slate-900/60 backdrop-blur-sm border border-white/10 rounded-xl pl-10 pr-12 py-2.5 text-sm text-slate-300 placeholder-slate-500 focus:border-purple-500/50 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all"
        >
        <span class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
            <kbd class="hidden md:inline-block px-2 py-0.5 bg-white/5 border border-white/10 rounded text-xs text-slate-500">⌘K</kbd>
        </span>
    </div>

    {{-- Search Results Dropdown --}}
    @if($isOpen && $totalResults > 0)
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-on:click.away="closeSearch()"
        class="absolute top-full left-0 right-0 mt-3 bg-slate-900/90 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl shadow-purple-500/10 z-50 max-h-[70vh] overflow-y-auto"
    >
        {{-- Results Header --}}
        <div class="px-5 py-3 border-b border-white/10 flex items-center justify-between sticky top-0 bg-slate-900/90 backdrop-blur-xl">
            <div class="flex items-center gap-3">
                <span class="text-purple-400">🔍</span>
                <span class="text-sm text-slate-400">
                    <span class="font-semibold text-white">{{ $totalResults }}</span> results for "<span class="text-purple-300">{{ $query }}</span>"
                </span>
            </div>
            <button 
                wire:click="closeSearch"
                class="text-xs text-slate-500 hover:text-slate-300 transition-colors"
            >
                ESC to close
            </button>
        </div>

        {{-- Results by Type --}}
        <div class="py-2">
            @foreach(['tasks', 'docs', 'agents', 'projects', 'sessions'] as $type)
                @if(isset($results[$type]) && $results[$type]->count() > 0)
                <div class="py-2">
                    <div class="px-5 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        {{ $type === 'docs' ? '📚 Documentation' : ($type === 'tasks' ? '✅ Tasks' : ($type === 'agents' ? '🤖 Agents' : ($type === 'projects' ? '📁 Projects' : '💬 Sessions'))) }}
                    </div>
                    @foreach($results[$type] as $item)
                    <a 
                        href="{{ $item['url'] }}"
                        wire:click="closeSearch"
                        class="flex items-center gap-4 px-5 py-3 hover:bg-white/[0.02] hover:border-white/5 border border-transparent transition-all group"
                    >
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                            {{ $item['icon'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">{{ $item['title'] }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ $item['subtitle'] }}</div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-600 group-hover:text-purple-400 group-hover:bg-purple-500/20 transition-all">
                                →
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @if(!$loop->last && count(array_filter(array_map(fn($r) => $r->count() > 0, $results))) > 1)
                <div class="border-t border-white/10 mx-5"></div>
                @endif
                @endif
            @endforeach
        </div>

        {{-- View All Link --}}
        <div class="px-5 py-3 border-t border-white/10 bg-white/[0.01]">
            <button class="text-sm text-purple-400 hover:text-purple-300 font-semibold transition-colors">
                View all results →
            </button>
        </div>
    </div>
    @endif

    {{-- No Results State --}}
    @if($isOpen && strlen($query ?? '') >= 2 && $totalResults === 0)
    <div 
        x-show="open"
        x-transition
        class="absolute top-full left-0 right-0 mt-3 bg-slate-900/90 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl p-8 text-center"
    >
        <div class="text-5xl mb-4 opacity-50">🔍</div>
        <p class="text-slate-400 font-semibold">No results found</p>
        <p class="text-sm text-slate-500 mt-2">Try adjusting your search for "<span class="text-purple-300">{{ $query }}</span>"</p>
    </div>
    @endif
</div>

@script
<script>
    // Focus search input when opened
    $wire.on('focus-search-input', () => {
        setTimeout(() => {
            document.querySelector('input[wire\\\\:model\\\\.live\\\\.debounce\\\\.200ms\\\\=query]')?.focus();
        }, 50);
    });
</script>
@endscript
