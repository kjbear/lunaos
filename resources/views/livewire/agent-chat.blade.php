@php
    $sessionId = $session['id'] ?? null;
@endphp

<div class="chat-page flex h-screen bg-slate-950"
     x-data="{ 
         wsConnected: false, 
         wsConnecting: true,
         init() {
                 window.addEventListener('lunaos:websocket-connected', () => { 
                     this.wsConnected = true; 
                     this.wsConnecting = false; 
                 });
                 window.addEventListener('lunaos:websocket-disconnected', () => { 
                     this.wsConnected = false; 
                     this.wsConnecting = false; 
                 });
                 window.addEventListener('lunaos:websocket-connecting', () => { 
                     this.wsConnecting = true; 
                 });
             }
         }">
        
        <!-- Sidebar -->
        <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col" wire:key="sidebar">
        <!-- Header -->
        <div class="p-4 border-b border-slate-800">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="text-2xl">💬</span>
                Agent Chat
                <span x-show="wsConnected" class="inline-block w-2 h-2 rounded-full bg-green-500 ml-1"></span>
                <span x-show="wsConnecting" class="inline-block w-2 h-2 rounded-full bg-yellow-500 ml-1"></span>
                <span x-show="!wsConnected && !wsConnecting" class="inline-block w-2 h-2 rounded-full bg-red-500 ml-1"></span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">Chat with AI team members</p>
        </div>

        <!-- Team Member Selector -->
        <div class="p-3 border-b border-slate-800">
            <label class="text-xs font-medium text-slate-400 mb-1 block">Select Agent</label>
            <select 
                wire:model.change="selectedMemberId"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
                <option value="">Choose an agent...</option>
                @foreach ($teamMembers as $member)
                    <option value="{{ $member['id'] }}">
                        {{ $member['emoji'] }} {{ $member['name'] }} - {{ $member['title'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Recent Conversations -->
        <div class="flex-1 overflow-y-auto p-3">
            <!-- Search and Filters -->
            <div class="mb-3 space-y-2">
                <!-- Search -->
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Search conversations..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
                
                <!-- Filter Row -->
                <div class="flex gap-2">
                    <!-- Agent Filter -->
                    <select
                        wire:model.live="filterAgent"
                        class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All Agents</option>
                        @foreach ($teamMembers as $member)
                            <option value="{{ $member['id'] }}">{{ $member['emoji'] }} {{ $member['name'] }}</option>
                        @endforeach
                    </select>
                    
                    <!-- Sort -->
                    <select
                        wire:model.live="sortBy"
                        class="bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="recent">Recent</option>
                        <option value="oldest">Oldest</option>
                        <option value="alpha">A-Z</option>
                    </select>
                </div>
                
                <!-- Archive Filter Toggle -->
                <div class="flex gap-2">
                    <button
                        wire:click="$set('filterArchive', 'active')"
                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $filterArchive === 'active' ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}"
                    >
                        Active
                    </button>
                    <button
                        wire:click="$set('filterArchive', 'archived')"
                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $filterArchive === 'archived' ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}"
                    >
                        Archived
                    </button>
                    <button
                        wire:click="$set('filterArchive', 'all')"
                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $filterArchive === 'all' ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}"
                    >
                        All
                    </button>
                </div>
                
                <!-- Reset Filters -->
                @if ($searchQuery || $filterAgent || $filterArchive !== 'active' || $sortBy !== 'recent')
                    <button
                        wire:click="resetFilters"
                        class="w-full text-xs text-purple-400 hover:text-purple-300 transition-colors"
                    >
                        Reset Filters
                    </button>
                @endif
            </div>
            
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Recent</h3>
            <div class="space-y-1">
                @forelse ($recentSessions as $sess)
                    <div class="relative w-full group/item">
                        <div
                            wire:click="loadSession('{{ $sess['id'] }}')"
                            class="text-left px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer {{ $sessionId === $sess['id'] ? 'bg-slate-800' : '' }}"
                            role="button"
                            tabindex="0"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $sess['emoji'] }}</span>
                                @if ($sess['is_archived'])
                                    <span class="text-xs" title="Archived">📦</span>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate {{ $sess['is_archived'] ? 'opacity-60' : '' }}">{{ $sess['title'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $sess['member'] }} • {{ $sess['updated'] }}</p>
                                </div>
                            </div>
                        </div>
                        <button
                            wire:click.stop="{{ $sess['is_archived'] ? 'unarchiveSession' : 'archiveSession' }}('{{ $sess['id'] }}')"
                            onclick="event.stopPropagation()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 z-10 opacity-0 group-hover/item:opacity-100 transition-opacity p-1.5 hover:bg-slate-700 rounded cursor-pointer"
                            title="{{ $sess['is_archived'] ? 'Unarchive' : 'Archive' }} conversation"
                        >
                            @if ($sess['is_archived'])
                                <span class="text-xs text-green-400 hover:text-green-300">↩️</span>
                            @else
                                <span class="text-xs text-slate-400 hover:text-slate-300">📦</span>
                            @endif
                        </button>
                    </div>
                @empty
                    <div class="text-xs text-slate-500 text-center py-4">
                        @if ($filterArchive === 'archived')
                            No archived conversations
                        @elseif ($searchQuery || $filterAgent)
                            No matching conversations
                        @else
                            No conversations yet
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- New Chat Button -->
        <div class="p-3 border-t border-slate-800">
            <button
                wire:click="newChat"
                wire:disabled="!selectedMemberId"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:from-purple-500 hover:to-pink-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
                New Conversation
            </button>
        </div>
    </aside>

    <!-- Main Chat Area -->
    <main class="flex-1 flex flex-col">
        @if ($selectedMemberId && !empty($selectedMemberData))
            <!-- Chat Header -->
            <header class="bg-slate-900 border-b border-slate-800 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xl">
                        {{ $selectedMemberData['emoji'] ?? '🤖' }}
                    </div>
                    <div class="flex-1">
                        <h1 class="text-lg font-semibold text-white">
                            {{ $selectedMemberData['name'] ?? 'Select Agent' }}
                            @if ($session && $session->is_archived)
                                <span class="text-xs text-slate-400 ml-2">📦 Archived</span>
                            @endif
                        </h1>
                        <p class="text-sm text-slate-400">{{ $selectedMemberData['title'] ?? '' }}</p>
                    </div>
                    @if (!empty($selectedMemberData['role']))
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ 
                            $selectedMemberData['role'] === 'executive' ? 'bg-purple-500/20 text-purple-400' : 
                            ($selectedMemberData['role'] === 'board_member' ? 'bg-blue-500/20 text-blue-400' : 
                            'bg-green-500/20 text-green-400') 
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $selectedMemberData['role'])) }}
                        </span>
                    @endif
                    @if ($session)
                        <button
                            wire:click="{{ $session->is_archived ? 'unarchiveSession' : 'archiveSession' }}('{{ $session->id }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $session->is_archived ? 'bg-green-600 hover:bg-green-500 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-300' }}"
                            title="{{ $session->is_archived ? 'Unarchive' : 'Archive' }} this conversation"
                        >
                            @if ($session->is_archived)
                                ↩️ Unarchive
                            @else
                                📦 Archive
                            @endif
                        </button>
                    @endif
                </div>
            </header>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4" id="messages-container"
                x-init="$el.scrollTop = $el.scrollHeight">
                
                @forelse ($messages as $msg)
                    <div class="flex gap-3 {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        @if ($msg['role'] === 'assistant')
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm flex-shrink-0">
                                {{ $selectedMemberData['emoji'] ?? '🤖' }}
                            </div>
                        @endif
                        
                        <div class="max-w-2xl {{ $msg['role'] === 'user' ? 'bg-purple-600' : 'bg-slate-800' }} rounded-2xl px-4 py-3">
                            @if ($msg['role'] === 'assistant')
                                <div class="markdown-content text-white text-sm prose prose-invert prose-sm max-w-none" data-raw="{{ $msg['content'] }}">{{ $msg['content'] }}</div>
                            @else
                                <p class="text-white text-sm whitespace-pre-wrap">{{ $msg['content'] }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs {{ $msg['role'] === 'user' ? 'text-purple-200' : 'text-slate-500' }}">
                                    {{ $msg['timestamp'] }}
                                </p>
                                @if ($msg['role'] === 'assistant' && !empty($msg['metadata']))
                                    @php
                                        $meta = $msg['metadata'];
                                        $model = $meta['model'] ?? 'unknown';
                                        $promptTokens = $meta['prompt_tokens'] ?? 0;
                                        $completionTokens = $meta['completion_tokens'] ?? 0;
                                        $latencyMs = $meta['latency_ms'] ?? 0;
                                        $latencySec = $latencyMs > 0 ? number_format($latencyMs / 1000, 1) : '0.0';
                                    @endphp
                                    <p class="text-xs text-slate-500">
                                        <span class="text-purple-400">{{ $model }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ $promptTokens }}/{{ $completionTokens }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ $latencySec }}s</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if ($msg['role'] === 'user')
                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-sm flex-shrink-0">
                                👤
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-500">
                        <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-3xl mb-4">
                            {{ $selectedMemberData['emoji'] ?? '🤖' }}
                        </div>
                        <p class="text-lg font-medium text-slate-300">Start a conversation</p>
                        <p class="text-sm mt-1">Ask questions, get help, or collaborate</p>
                    </div>
                @endforelse

                @if ($isTyping)
                    <div class="flex gap-3 justify-start animate-pulse">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm flex-shrink-0">
                            {{ $selectedMemberData['emoji'] ?? '🤖' }}
                        </div>
                        <div class="max-w-2xl bg-slate-800 rounded-2xl px-4 py-3">
                            <div class="flex gap-1">
                                <div class="w-2 h-2 bg-slate-500 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-slate-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-slate-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Message Input -->
            <div class="border-t border-slate-800 bg-slate-900 p-4">
                <form wire:submit="sendMessage" class="flex gap-3">
                    <input
                        type="text"
                        wire:model.live="newMessage"
                        placeholder="Type a message..."
                        class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        wire:disabled="isTyping"
                    >
                    <button
                        type="submit"
                        wire:disabled="isTyping || !newMessage"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl px-6 py-3 font-medium hover:from-purple-500 hover:to-pink-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        Send
                    </button>
                </form>
            </div>
        @else
            <!-- Empty State - No Agent Selected -->
            <div class="flex-1 flex flex-col items-center justify-center text-slate-500">
                <div class="w-20 h-20 rounded-full bg-slate-800 flex items-center justify-center text-4xl mb-4">
                    💬
                </div>
                <h2 class="text-xl font-semibold text-slate-300 mb-2">Select an Agent to Chat</h2>
                <p class="text-sm">Choose a team member from the sidebar to start a conversation</p>
            </div>
        @endif
    </main>
    </div>
</div>

@script
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    // Configure marked for safe rendering
    marked.setOptions({
        breaks: true,
        gfm: true
    });
    
    // Markdown rendering function with XSS protection
    window.renderMarkdown = function(text) {
        if (!text) return '';
        // Basic XSS protection
        const escaped = text
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        return marked.parse(escaped);
    };
    
    // Render markdown in all assistant messages
    window.renderAllMarkdown = function() {
        document.querySelectorAll('.markdown-content[data-raw]').forEach(el => {
            const raw = el.getAttribute('data-raw');
            try {
                el.innerHTML = window.renderMarkdown(raw);
            } catch (e) {
                el.textContent = raw;
            }
        });
    };
    
    // Auto-scroll to bottom when messages update
    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        }
    }
    
    document.addEventListener('livewire:init', () => {
        // Scroll on commit (general updates)
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                queueMicrotask(() => {
                    renderAllMarkdown();
                    scrollToBottom();
                });
            });
        });
    });
    
    // Initial render on page load
    document.addEventListener('DOMContentLoaded', () => {
        renderAllMarkdown();
    });
</script>
<style>
    /* Markdown content styles for agent messages */
    .markdown-content {
        line-height: 1.6;
    }
    .markdown-content p {
        margin-bottom: 0.75rem;
    }
    .markdown-content p:last-child {
        margin-bottom: 0;
    }
    .markdown-content strong {
        font-weight: 600;
        color: #e2e8f0;
    }
    .markdown-content em {
        font-style: italic;
        color: #cbd5e1;
    }
    .markdown-content a {
        color: #a78bfa;
        text-decoration: underline;
        transition: color 0.15s;
    }
    .markdown-content a:hover {
        color: #c4b5fd;
    }
    .markdown-content ul, .markdown-content ol {
        margin-left: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .markdown-content ul {
        list-style-type: disc;
    }
    .markdown-content ol {
        list-style-type: decimal;
    }
    .markdown-content li {
        margin-bottom: 0.25rem;
    }
    .markdown-content code {
        background: #1e293b;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.875em;
        color: #f472b6;
    }
    .markdown-content pre {
        background: #1e293b;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin-bottom: 0.75rem;
    }
    .markdown-content pre code {
        background: transparent;
        padding: 0;
        color: #e2e8f0;
    }
    .markdown-content blockquote {
        border-left: 3px solid #a78bfa;
        padding-left: 1rem;
        margin-left: 0;
        margin-bottom: 0.75rem;
        color: #94a3b8;
        font-style: italic;
    }
    .markdown-content h1, .markdown-content h2, .markdown-content h3, 
    .markdown-content h4, .markdown-content h5, .markdown-content h6 {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #f1f5f9;
    }
    .markdown-content h1 { font-size: 1.25rem; }
    .markdown-content h2 { font-size: 1.125rem; }
    .markdown-content h3 { font-size: 1rem; }
    .markdown-content hr {
        border-color: #475569;
        margin: 0.75rem 0;
    }
</style>
@endscript