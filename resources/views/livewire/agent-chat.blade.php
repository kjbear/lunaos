<div class="flex h-screen bg-slate-950">
    <!-- Sidebar -->
    <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-slate-800">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="text-2xl">💬</span>
                Agent Chat
            </h2>
            <p class="text-xs text-slate-400 mt-1">Chat with AI team members</p>
        </div>

        <!-- Team Member Selector -->
        <div class="p-3 border-b border-slate-800">
            <label class="text-xs font-medium text-slate-400 mb-1 block">Select Agent</label>
            <select 
                wire:model="selectedMemberId"
                wire:change="selectMember($event.target.value)"
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
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Recent</h3>
            <div class="space-y-1">
                @forelse ($recentSessions as $session)
                    <button
                        wire:click="loadSession('{{ $session['id'] }}')"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors {{ $this->session?->id === $session['id'] ? 'bg-slate-800' : '' }}"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $session['emoji'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $session['title'] }}</p>
                                <p class="text-xs text-slate-400">{{ $session['member'] }} • {{ $session['updated'] }}</p>
                            </div>
                        </div>
                    </button>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">No conversations yet</p>
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
        @if ($selectedMember)
            <!-- Chat Header -->
            <header class="bg-slate-900 border-b border-slate-800 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xl">
                        {{ $selectedMember->emoji ?? '🤖' }}
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-white">{{ $selectedMember->name }}</h1>
                        <p class="text-sm text-slate-400">{{ $selectedMember->title }}</p>
                    </div>
                    @if ($selectedMember->role)
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ 
                            $selectedMember->role === 'executive' ? 'bg-purple-500/20 text-purple-400' : 
                            ($selectedMember->role === 'board_member' ? 'bg-blue-500/20 text-blue-400' : 
                            'bg-green-500/20 text-green-400') 
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $selectedMember->role)) }}
                        </span>
                    @endif
                </div>
            </header>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4" id="messages-container" x-data x-ref="container" x-init="$nextTick(() => $refs.container.scrollTop = $refs.container.scrollHeight)">
                @forelse ($messages as $message)
                    <div class="flex gap-3 {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        @if ($message['role'] === 'assistant')
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm flex-shrink-0">
                                {{ $selectedMember->emoji ?? '🤖' }}
                            </div>
                        @endif
                        
                        <div class="max-w-2xl {{ $message['role'] === 'user' ? 'bg-purple-600' : 'bg-slate-800' }} rounded-2xl px-4 py-3">
                            <p class="text-white text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                            <p class="text-xs {{ $message['role'] === 'user' ? 'text-purple-200' : 'text-slate-500' }} mt-1">{{ $message['timestamp'] }}</p>
                            
                            @if ($message['role'] === 'assistant' && !empty($message['metadata']))
                                @php
                                    $meta = $message['metadata'];
                                    $model = $meta['model'] ?? 'unknown';
                                    $promptTokens = $meta['prompt_tokens'] ?? 0;
                                    $completionTokens = $meta['completion_tokens'] ?? 0;
                                    $latencyMs = $meta['latency_ms'] ?? 0;
                                    $latencySec = $latencyMs > 0 ? number_format($latencyMs / 1000, 1) : '0.0';
                                @endphp
                                <div class="text-xs text-slate-500 mt-1 border-t border-slate-700 pt-1 mt-2">
                                    <span class="text-purple-400">{{ $model }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $promptTokens }} in / {{ $completionTokens }} out</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $latencySec }}s</span>
                                </div>
                            @endif
                        </div>

                        @if ($message['role'] === 'user')
                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-sm flex-shrink-0">
                                👤
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-500">
                        <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-3xl mb-4">
                            {{ $selectedMember->emoji ?? '🤖' }}
                        </div>
                        <p class="text-lg font-medium text-slate-300">Start a conversation with {{ $selectedMember->name }}</p>
                        <p class="text-sm mt-1">Ask questions, get help, or collaborate on tasks</p>
                    </div>
                @endforelse

                @if ($isTyping)
                    {{-- Streaming response container --}}
                    <div class="flex gap-3 justify-start" id="streaming-container" x-data x-intersect="$el.scrollIntoView({ behavior: 'smooth', block: 'end' })">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm flex-shrink-0">
                            {{ $selectedMember->emoji ?? '🤖' }}
                        </div>
                        <div class="max-w-2xl bg-slate-800 rounded-2xl px-4 py-3">
                            <p class="text-white text-sm whitespace-pre-wrap">
                                <span wire:stream="stream-response"></span>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Message Input -->
            <div class="border-t border-slate-800 bg-slate-900 p-4">
                <form wire:submit="sendMessage" class="flex gap-3">
                    <input
                        type="text"
                        wire:model="newMessage"
                        placeholder="Type a message..."
                        class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        wire:disabled="{{ $isTyping ? 'true' : 'false' }}"
                    >
                    <button
                        type="submit"
                        wire:disabled="{{ empty($newMessage) || $isTyping ? 'true' : 'false' }}"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl px-6 py-3 font-medium hover:from-purple-500 hover:to-pink-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        Send
                    </button>
                </form>
            </div>
        @else
            <!-- Empty State -->
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