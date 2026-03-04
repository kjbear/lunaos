<div class="board-session-history">
    <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
        <!-- Header -->
        <div class="bg-white/[0.02] border-b border-white/10 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">Session History</h3>
                <p class="text-slate-400 text-sm mt-1">Previous board sessions and decisions</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    wire:click="sortByField('created_at')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $sortBy === 'created_at' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    Date {{ $sortBy === 'created_at' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' }}
                </button>
                <button 
                    wire:click="sortByField('status')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $sortBy === 'status' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    Status {{ $sortBy === 'status' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' }}
                </button>
            </div>
        </div>

        <!-- Sessions List -->
        <div class="divide-y divide-white/5">
            @forelse($this->sessions as $session)
            <a href="{{ route('tasks.executive.result', ['sessionId' => $session->id]) }}"
               class="block hover:bg-white/[0.02] transition-colors group">
                <div class="px-6 py-4 flex items-center gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        @if($session->status === 'decided')
                            ✅
                        @elseif($session->status === 'failed')
                            ❌
                        @elseif($session->status === 'debating')
                            🎙
                        @else
                            📋
                        @endif
                    </div>

                    <!-- Question Preview -->
                    <div class="flex-1 min-w-0 max-w-xl">
                        <p class="text-white font-medium truncate group-hover:text-purple-300 transition-colors" title="{{ $session->question }}">
                            {{ Str::limit($session->question, 80) }}
                        </p>
                        <div class="flex items-center gap-3 mt-1">
                            <p class="text-slate-500 text-sm">
                                {{ $session->created_at->format('M j, Y g:i A') }}
                            </p>
                            @if($session->responses_count ?? 0)
                            <span class="text-xs text-slate-600">•</span>
                            <p class="text-slate-500 text-sm">
                                {{ $session->responses_count ?? 0 }} response{{ ($session->responses_count ?? 0) !== 1 ? 's' : '' }}
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex-shrink-0">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $this->getStatusBadgeClass($session->status) }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>

                    <!-- Confidence Score (if available) -->
                    @if($session->confidence_score && $session->status === 'decided')
                    <div class="flex-shrink-0 text-right hidden lg:block">
                        <div class="text-xs text-slate-500 uppercase font-semibold">Confidence</div>
                        <div class="text-sm font-bold 
                            {{ $session->confidence_score >= 0.8 ? 'text-emerald-400' : ($session->confidence_score >= 0.6 ? 'text-amber-400' : 'text-red-400') }}">
                            {{ $session->confidence_percentage }}%
                        </div>
                    </div>
                    @endif

                    <!-- Arrow Icon -->
                    <div class="flex-shrink-0 text-slate-500 group-hover:text-purple-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
            @empty
            <div class="px-6 py-12 text-center">
                <div class="text-4xl mb-3 opacity-50">📋</div>
                <p class="text-slate-400 font-semibold">No sessions yet</p>
                <p class="text-slate-500 text-sm mt-1">Your board session history will appear here</p>
            </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if($this->sessions->count() > 0)
        <div class="bg-white/[0.02] border-t border-white/10 px-6 py-3">
            <p class="text-xs text-slate-500 text-center">
                Showing {{ $this->sessions->count() }} of {{ \App\Models\BoardSession::count() }} total sessions
            </p>
        </div>
        @endif
    </div>
</div>
