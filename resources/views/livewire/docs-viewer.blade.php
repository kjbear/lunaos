<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">📄 Documentation</h1>
            <p class="text-sm text-[#6b6b80] mt-1">Browse LunaOS documentation</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b6b80]">🔍</span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    wire:keyup="search"
                    placeholder="Search docs..."
                    class="w-64 bg-[#1a1a2e] border border-[#2a2a40] rounded-lg pl-10 pr-4 py-2 text-sm text-[#e4e4f0] placeholder-[#6b6b80] focus:outline-none focus:border-[#7c3aed]"
                >
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-4 gap-6">
        <!-- Docs Sidebar -->
        <div class="col-span-1">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden">
                <div class="bg-[#12121f] border-b border-[#2a2a40] px-4 py-3">
                    <h3 class="text-sm font-medium text-[#a0a0b8]">Contents</h3>
                </div>
                <div class="p-2 max-h-[600px] overflow-y-auto">
                    @forelse($sections as $section)
                        <div class="mb-2">
                            <div class="px-3 py-2 text-xs font-semibold text-[#6b6b80] uppercase tracking-wider">
                                {{ $section['name'] }}
                            </div>
                            @foreach($section['docs'] as $doc)
                                <button
                                    wire:click="selectDoc('{{ $doc['slug'] }}')"
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#252542] transition-colors {{ $selectedSlug === $doc['slug'] ? 'bg-[#7c3aed]/10 text-[#e4e4f0]' : '' }}"
                                >
                                    <span>📄</span>
                                    <span class="truncate">{{ $doc['title'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-[#6b6b80] text-sm">
                            No docs found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Doc Content Panel -->
        <div class="col-span-3">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden min-h-[600px]">
                @if($selectedDoc)
                    <!-- Doc Header -->
                    <div class="bg-[#12121f] border-b border-[#2a2a40] px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📄</span>
                            <div>
                                <h2 class="text-lg font-semibold text-[#e4e4f0]">{{ $selectedDoc->title }}</h2>
                                <p class="text-xs text-[#6b6b80]">{{ ucfirst($selectedDoc->section) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Doc Content -->
                    <div class="p-6 markdown-content overflow-auto max-h-[540px]">
                        @php
                            $parsed = $selectedDoc->content;
                            // Convert headers
                            $parsed = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $parsed);
                            $parsed = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $parsed);
                            $parsed = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $parsed);
                            // Convert bold
                            $parsed = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $parsed);
                            // Convert italic
                            $parsed = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $parsed);
                            // Convert inline code
                            $parsed = preg_replace('/`(.+?)`/', '<code>$1</code>', $parsed);
                            // Convert code blocks
                            $parsed = preg_replace('/```(\w+)?\n([\s\S]+?)```/', '<pre><code class="language-$1">$2</code></pre>', $parsed);
                            // Convert links
                            $parsed = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $parsed);
                            // Convert lists
                            $parsed = preg_replace('/^- (.+)$/m', '<li>$1</li>', $parsed);
                            $parsed = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $parsed);
                            // Convert paragraphs
                            $parsed = preg_replace('/^(?!<[a-z]|$)(.+)$/m', '<p>$1</p>', $parsed);
                        @endphp
                        {!! $parsed !!}
                    </div>
                @else
                    <div class="flex items-center justify-center h-full min-h-[600px]">
                        <div class="text-center text-[#6b6b80]">
                            <div class="text-4xl mb-2">📄</div>
                            <div>Select a document to view</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>