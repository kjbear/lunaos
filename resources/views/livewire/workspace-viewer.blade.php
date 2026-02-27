<div class="space-y-6">
    @push('styles')
    <style>
        /* Markdown Content Styling */
        .markdown-content h1, .markdown-content h2, .markdown-content h3, .markdown-content h4 { margin-top: 1.5em; margin-bottom: 0.5em; font-weight: 600; color: #e4e4f0; }
        .markdown-content h1 { font-size: 2em; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.3em; }
        .markdown-content h2 { font-size: 1.5em; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.3em; }
        .markdown-content h3 { font-size: 1.25em; }
        .markdown-content h4 { font-size: 1em; }
        .markdown-content p { margin-bottom: 1em; color: #a1a1b8; line-height: 1.7; }
        .markdown-content a { color: #7c3aed; text-decoration: underline; }
        .markdown-content a:hover { color: #a78bfa; }
        .markdown-content code { background: rgba(124, 58, 237, 0.15); padding: 0.2em 0.4em; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 0.9em; color: #e4e4f0; }
        .markdown-content pre { background: #0d0d1a; padding: 1em; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); overflow-x: auto; margin-bottom: 1em; }
        .markdown-content pre code { background: transparent; padding: 0; color: #a1a1b8; font-size: 0.85em; }
        .markdown-content ul, .markdown-content ol { margin-bottom: 1em; padding-left: 1.5em; }
        .markdown-content li { margin-bottom: 0.5em; color: #a1a1b8; }
        .markdown-content blockquote { border-left: 4px solid #7c3aed; padding-left: 1em; color: #6b6b80; font-style: italic; }
        .markdown-content table { border-collapse: collapse; width: 100%; margin-bottom: 1em; }
        .markdown-content th, .markdown-content td { border: 1px solid rgba(255,255,255,0.1); padding: 0.5em 1em; text-align: left; }
        .markdown-content th { background: rgba(124, 58, 237, 0.1); font-weight: 600; }
        .markdown-content img { max-width: 100%; border-radius: 8px; }
    </style>
    @endpush
    
    {{-- Header with Workspace Context --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-indigo-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-indigo-500 to-purple-500 flex items-center justify-center text-3xl shadow-xl">📁</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Workspace Files</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Memory, config, and documentation files</p>
                </div>
            </div>
            
            {{-- Quick Stats --}}
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-2xl font-bold text-cyan-400">{{ $stats['total'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Files</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ $stats['md'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Markdown</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-400">{{ number_format(($stats['total_size'] ?? 0) / 1024, 1) }}KB</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Total Size</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <button wire:click="refresh" class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all" title="Refresh">
                    🔄
                </button>
            </div>
        </div>
    </header>

    {{-- Main Grid: Sidebar + Viewer --}}
    <section class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar: File Tree --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden sticky top-6">
                {{-- Search --}}
                <div class="p-4 border-b border-white/10">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search files..."
                            class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-slate-300 placeholder-slate-500 focus:border-cyan-500/50 focus:outline-none"
                        >
                    </div>
                </div>

                {{-- Filter Tabs --}}
                <div class="p-4 border-b border-white/10">
                    <div class="flex flex-wrap gap-2">
                        @foreach(['all', 'md', 'json', 'yaml'] as $filterType)
                        <button 
                            wire:click="filterBy('{{ $filterType }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterType === $filter ? 'bg-gradient-to-r from-cyan-600 to-indigo-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}"
                        >
                            {{ strtoupper($filterType) }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- File List --}}
                <div class="p-2 max-h-[600px] overflow-y-auto">
                    @forelse($files as $file)
                    <button
                        wire:click="selectFile('{{ $file['path'] }}')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ $filePath === $file['path'] ? 'bg-cyan-500/20 border border-cyan-500/30 text-cyan-300' : 'text-slate-400 hover:bg-white/[0.02] hover:text-slate-300' }}"
                    >
                        <span class="text-lg">{{ $file['icon'] }}</span>
                        <span class="truncate font-medium">{{ $file['name'] }}</span>
                    </button>
                    @empty
                    <div class="text-center py-8">
                        <div class="text-3xl mb-3 opacity-50">📭</div>
                        <p class="text-slate-500 text-sm">No files match your search</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Main: File Viewer --}}
        <div class="lg:col-span-3">
            @if($selectedFile)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                {{-- Header --}}
                <div class="bg-white/[0.02] border-b border-white/10 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/20 to-indigo-500/20 border border-cyan-500/30 flex items-center justify-center text-xl">
                            📄
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ basename($selectedFile) }}</h3>
                            <p class="text-xs text-slate-400">{{ dirname($selectedFile) }}</p>
                        </div>
                    </div>
                    @if($fileModified)
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Last Modified</div>
                        <div class="text-sm text-slate-300">{{ \Carbon\Carbon::parse($fileModified)->diffForHumans() }}</div>
                    </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="p-6">
                    @php
                        $ext = pathinfo($selectedFile ?? '', PATHINFO_EXTENSION);
                        $isMarkdown = in_array(strtolower($ext), ['md', 'markdown', 'mdown']);
                    @endphp
                    
                    @if($isMarkdown)
                        {{-- Render as Markdown --}}
                        <div class="prose prose-invert max-w-none">
                            <div class="markdown-content bg-black/30 rounded-xl border border-white/5 p-6 overflow-auto max-h-[700px] prose prose-invert prose-sm sm:prose lg:prose-lg">
                                {!! (new League\CommonMark\GithubFlavoredMarkdownConverter())->convert($fileContent) !!}
                            </div>
                        </div>
                    @else
                        {{-- Plain text for code/config files --}}
                        <div class="bg-black/30 rounded-xl border border-white/5 p-4 overflow-auto max-h-[700px]">
                            <pre class="text-sm text-slate-300 font-mono whitespace-pre-wrap break-words">{{ $fileContent }}</pre>
                        </div>
                    @endif
                </div>

                {{-- Footer Actions --}}
                <div class="px-6 py-4 border-t border-white/10 bg-white/[0.01] flex items-center justify-between">
                    <div class="flex items-center gap-4 text-sm text-slate-400">
                        <span>{{ strlen($fileContent) }} characters</span>
                        <span>•</span>
                        <span>{{ count(explode("\n", $fileContent)) }} lines</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-300 hover:bg-white/10 transition-all">
                            📋 Copy
                        </button>
                        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg text-sm hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25">
                            ✏️ Edit
                        </button>
                    </div>
                </div>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                <div class="text-6xl mb-4 opacity-50">📄</div>
                <p class="text-slate-400 font-semibold text-lg">Select a file to view</p>
                <p class="text-slate-500 text-sm mt-2">Use the search or filters to find what you need</p>
            </div>
            @endif
        </div>
    </section>
</div>
