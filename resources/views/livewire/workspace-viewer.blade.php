<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">📁 Workspace</h1>
            <p class="text-sm text-[#6b6b80] mt-1">View configuration and memory files</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                wire:click="refresh"
                class="btn btn-secondary text-sm"
            >
                🔄 Refresh
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-4 gap-6">
        <!-- File Tree Sidebar -->
        <div class="col-span-1">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden">
                <div class="bg-[#12121f] border-b border-[#2a2a40] px-4 py-3">
                    <h3 class="text-sm font-medium text-[#a0a0b8]">Files</h3>
                </div>
                <div class="p-2">
                    @forelse($files as $file)
                        <button
                            wire:click="selectFile('{{ $file['path'] }}')"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#252542] transition-colors {{ $filePath === $file['path'] ? 'bg-[#7c3aed]/10 text-[#e4e4f0]' : '' }}"
                        >
                            <span class="text-lg">{{ $file['icon'] }}</span>
                            <span class="truncate">{{ $file['name'] }}</span>
                        </button>
                    @empty
                        <div class="px-4 py-6 text-center text-[#6b6b80] text-sm">
                            No files found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- File Content Panel -->
        <div class="col-span-3">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden min-h-[600px]">
                @if($fileContent)
                    <!-- File Header -->
                    <div class="bg-[#12121f] border-b border-[#2a2a40] px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">{{ $files[array_search($filePath, array_column($files, 'path'))]['icon'] ?? '📄' }}</span>
                            <span class="font-medium text-[#e4e4f0]">{{ $selectedFile }}</span>
                        </div>
                        <div class="text-xs text-[#6b6b80]">
                            Last updated: {{ $fileModified }}
                        </div>
                    </div>

                    <!-- File Content -->
                    <div class="p-6 markdown-content overflow-auto max-h-[540px]">
                        {{-- Parse and render markdown --}}
                        @php
                            $parsed = $fileContent;
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
                            // Convert paragraphs (lines separated by blank lines)
                            $parsed = preg_replace('/^(?!<[a-z]|$)(.+)$/m', '<p>$1</p>', $parsed);
                            // Convert horizontal rules
                            $parsed = preg_replace('/^---$/m', '<hr>', $parsed);
                            // Convert blockquotes
                            $parsed = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $parsed);
                        @endphp
                        {!! $parsed !!}
                    </div>
                @else
                    <div class="flex items-center justify-center h-full min-h-[600px]">
                        <div class="text-center text-[#6b6b80]">
                            <div class="text-4xl mb-2">📄</div>
                            <div>Select a file to view its contents</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>