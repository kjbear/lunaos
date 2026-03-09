@php
    $sessionId = $session['id'] ?? null;
@endphp

<div class="flex h-screen bg-slate-950" 
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
    <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col">
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
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Recent</h3>
            <div class="space-y-1">
                @forelse ($recentSessions as $sess)
                    <button
                        wire:click="loadSession('{{ $sess['id'] }}')"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors {{ $sessionId === $sess['id'] ? 'bg-slate-800' : '' }}"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $sess['emoji'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $sess['title'] }}</p>
                                <p class="text-xs text-slate-400">{{ $sess['member'] }} • {{ $sess['updated'] }}</p>
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
        @if ($selectedMemberId && !empty($selectedMemberData))
            <!-- Chat Header -->
            <header class="bg-slate-900 border-b border-slate-800 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xl">
                        {{ $selectedMemberData['emoji'] ?? '🤖' }}
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-white">{{ $selectedMemberData['name'] ?? 'Select Agent' }}</h1>
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
                            <p class="text-white text-sm whitespace-pre-wrap">{{ $msg['content'] }}</p>
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

@script
<script>
    // Auto-scroll to bottom when messages update
    document.addEventListener('livewire:init', () => {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                queueMicrotask(() => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            });
        });
    });
</script>
@endscript