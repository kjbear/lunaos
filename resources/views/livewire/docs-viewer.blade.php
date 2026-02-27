<div class="space-y-6">
    {{-- Header with Search Integration --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-indigo-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-indigo-500 to-purple-500 flex items-center justify-center text-3xl shadow-xl">📚</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Documentation</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">LunaOS docs and knowledge base</p>
                </div>
            </div>
            
            {{-- Quick Stats --}}
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-2xl font-bold text-cyan-400">{{ count($docs ?? []) }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Docs Indexed</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-400">500+</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Dynatrace</div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content: Sidebar + Viewer --}}
    <section class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar: Category Tree --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden sticky top-6">
                {{-- Search --}}
                <div class="p-4 border-b border-white/10">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search docs..."
                            class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-slate-300 placeholder-slate-500 focus:border-cyan-500/50 focus:outline-none"
                        >
                    </div>
                </div>

                {{-- Collections --}}
                <div class="p-4 border-b border-white/10">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-4 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Collections</h3>
                    </div>

                    <div class="space-y-1">
                        @foreach($collections ?? [] as $collection)
                        <button 
                            wire:click="selectCollection('{{ $collection['slug'] }}')"
                            class="w-full text-left px-3 py-2 rounded-lg {{ $collectionSlug === $collection['slug'] ? 'bg-purple-500/20 border border-purple-500/30 text-purple-300' : 'hover:bg-white/[0.02] text-slate-400' }} transition-all"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">
                                    @if(str_contains($collection['slug'], 'lunaos')) 🌙
                                    @elseif(str_contains($collection['slug'], 'dynatrace')) 📊
                                    @else 📚
                                    @endif
                                    {{ $collection['name'] }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $collection['file_count'] ?? 0 }}</span>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Categories --}}
                <div class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-1 h-4 bg-gradient-to-b from-cyan-400 to-indigo-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Categories</h3>
                    </div>

                    @if(count($categories ?? []) > 0)
                    <div class="space-y-1">
                        @foreach($categories ?? [] as $category)
                        <button 
                            wire:click="selectCategory('{{ $category['id'] }}')"
                            class="w-full text-left px-3 py-2 rounded-lg {{ $selectedCategory === $category['id'] ? 'bg-cyan-500/20 border border-cyan-500/30 text-cyan-300' : 'hover:bg-white/[0.02] text-slate-400' }} transition-all"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">
                                    @if($category['id'] === 'lunaos') 🌙
                                    @elseif($category['id'] === 'dynatrace') 📊
                                    @elseif($category['id'] === 'laravel') 🎨
                                    @elseif($category['id'] === 'livewire') ⚡
                                    @else 📄
                                    @endif
                                    {{ $category['name'] }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $category['count'] ?? 0 }}</span>
                            </div>
                        </button>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-slate-500 text-sm">
                        <div class="text-2xl mb-2">📂</div>
                        <div>Select a collection to view categories</div>
                    </div>
                    @endif

                    {{-- Tags --}}
                    @if(count($tags ?? []) > 0)
                    <div class="mt-6 pt-6 border-t border-white/10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-1 h-4 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Popular Tags</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($tags ?? [] as $tag)
                            <button 
                                wire:click="filterByTag('{{ $tag }}')"
                                class="px-2 py-1 bg-white/[0.02] border border-white/5 rounded-md text-xs text-slate-400 hover:border-purple-500/30 hover:text-purple-300 transition-all"
                            >
                                #{{ $tag }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main: Doc List / Viewer --}}
        <div class="lg:col-span-3">
            @if($this->selectedDocData)
            {{-- Document Viewer --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                {{-- Doc Header --}}
                <div class="p-6 border-b border-white/10 flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500/20 to-indigo-500/20 border border-cyan-500/30 flex items-center justify-center text-2xl">
                            @if(str_contains($this->selectedDocData['title'], 'LunaOS')) 🌙
                            @elseif(str_contains($this->selectedDocData['title'], 'Dynatrace')) 📊
                            @else 📄
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white mb-1">{{ $this->selectedDocData['title'] }}</h2>
                            <div class="flex items-center gap-3 text-sm text-slate-400">
                                <span class="flex items-center gap-1">
                                    📁 {{ $this->selectedDocData['category'] ?? 'Uncategorized' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    🕒 {{ $this->selectedDocData['updated_at'] ? \Carbon\Carbon::parse($this->selectedDocData['updated_at'])->diffForHumans() : 'Unknown' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <button 
                        wire:click="$set('selectedDoc', null)"
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                    >
                        ✕
                    </button>
                </div>

                {{-- Markdown Content --}}
                <div class="p-6 overflow-x-auto">
                    <div class="prose prose-invert max-w-none">
                        @if($this->selectedDocData['content'])
                            {!! Str::markdown($this->selectedDocData['content']) !!}
                        @else
                            <div class="text-slate-400 text-center py-8">
                                <div class="text-4xl mb-4 opacity-50">📄</div>
                                <p>No content available</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer with Tags --}}
                @if(isset($this->selectedDocData['tags']) && count($this->selectedDocData['tags']) > 0)
                <div class="px-6 py-4 border-t border-white/10 bg-white/[0.01]">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500 uppercase font-semibold">Tags:</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedDoc['tags'] as $tag)
                            <span class="px-2 py-1 bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded text-xs font-medium">
                                #{{ $tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @else
            {{-- Doc List --}}
            <div class="space-y-4">
                {{-- Header Bar --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-indigo-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                            {{ $selectedCategory ? ($categories->firstWhere('id', $selectedCategory)['name'] ?? 'All Docs') : 'All Documentation' }}
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">
                            {{ count($docs ?? []) }} docs
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <select 
                            wire:model.live="sortBy"
                            class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 focus:border-cyan-500/50 focus:outline-none"
                        >
                            <option value="updated_at">Recently Updated</option>
                            <option value="title">Title (A-Z)</option>
                            <option value="created_at">Date Added</option>
                        </select>
                    </div>
                </div>

                {{-- Doc Cards --}}
                @forelse($docs ?? [] as $doc)
                <div 
                    wire:click="selectDoc('{{ $doc['id'] }}')"
                    class="group bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-5 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 cursor-pointer"
                >
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500/20 to-indigo-500/20 border border-cyan-500/30 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition-transform">
                            @if(str_contains($doc['title'], 'LunaOS')) 🌙
                            @elseif(str_contains($doc['title'], 'Dynatrace')) 📊
                            @elseif(str_contains($doc['title'], 'Laravel')) 🎨
                            @else 📄
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-lg font-semibold text-white mb-1 group-hover:text-cyan-400 transition-colors">
                                {{ $doc['title'] }}
                            </h4>
                            @if($doc['excerpt'])
                            <p class="text-sm text-slate-400 mb-2 line-clamp-2">
                                {{ $doc['excerpt'] }}
                            </p>
                            @endif
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span>📁 {{ $doc['category'] ?? 'Uncategorized' }}</span>
                                <span>•</span>
                                <span>🕒 {{ $doc['updated_at'] ? \Carbon\Carbon::parse($doc['updated_at'])->diffForHumans() : 'Unknown' }}</span>
                                @if(isset($doc['tags']) && count($doc['tags']) > 0)
                                <span>•</span>
                                <div class="flex gap-1">
                                    @foreach(array_slice($doc['tags'], 0, 3) as $tag)
                                    <span class="text-purple-400">#{{ $tag }}</span>
                                    @endforeach
                                    @if(count($doc['tags']) > 3)
                                    <span class="text-slate-600">+{{ count($doc['tags']) - 3 }}</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-500 group-hover:text-cyan-400 group-hover:bg-cyan-500/20 transition-all">
                                →
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-5xl mb-4 opacity-50">📭</div>
                    <p class="text-slate-400 font-semibold">No documents found</p>
                    @if($search || $selectedCategory)
                    <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filters.</p>
                    @endif
                </div>
                @endforelse
            </div>
            @endif
        </div>
    </section>
</div>
