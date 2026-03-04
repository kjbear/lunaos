<div class="executive-board-wait-page"
     x-data="{
         elapsed: {{ $elapsed }},
         timer: null
     }"
     x-init="
         // Increment elapsed time every 2 seconds (matches Livewire polling interval)
         timer = setInterval(() => { elapsed += 2; }, 2000);
         
         // Cleanup on page unload
         $nextTick(() => {
             window.addEventListener('beforeunload', () => clearInterval(timer));
         });
     "
     wire:poll.2s="incrementElapsed"
>
    <div class="page-container max-w-2xl mx-auto">
        <!-- Header -->
        <header class="text-center mb-12">
            <div class="text-6xl mb-6 animate-bounce">🎙</div>
            <h1 class="text-3xl font-bold text-white mb-2">Board in Session</h1>
            <p class="text-slate-400">The executives are debating your question...</p>
            
            <!-- Session ID -->
            <div class="mt-4 text-xs text-slate-500 font-mono">
                Session: {{ $sessionId }}
            </div>
        </header>

        <!-- Progress -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-8 mb-8">
            <!-- Animated Dots -->
            <div class="flex items-center justify-center gap-3 mb-6">
                <div class="w-4 h-4 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-4 h-4 bg-orange-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-4 h-4 bg-pink-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
            
            <!-- Status Message -->
            <div class="text-center">
                <p class="text-white text-lg mb-2">Processing with AI executives</p>
                <p class="text-slate-400 text-sm">This typically takes 3-5 minutes</p>
                
                <!-- Session Status Badge -->
                @if($session)
                <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold
                    @if($session->status === 'pending') bg-amber-500/20 text-amber-400 border border-amber-500/30
                    @elseif($session->status === 'debating') bg-purple-500/20 text-purple-400 border border-purple-500/30
                    @elseif($session->status === 'decided') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                    @elseif($session->status === 'failed') bg-red-500/20 text-red-400 border border-red-500/30
                    @else bg-slate-500/20 text-slate-400 border border-slate-500/30
                    @endif">
                    @if($session->status === 'pending' || $session->status === 'debating')
                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    @endif
                    {{ ucfirst($session->status) }}
                </div>
                @endif
            </div>
            
            <!-- Timer -->
            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <p class="text-slate-400 text-sm">
                    Waiting time: <span class="text-amber-400 font-mono" x-text="elapsed + 's'">0s</span>
                </p>
            </div>
        </div>

        <!-- What's Happening -->
        <div class="bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-2xl border border-purple-500/20 p-6 mb-8">
            <h2 class="text-lg font-semibold text-purple-300 mb-4">What's happening:</h2>
            <ul class="space-y-3 text-slate-300 text-sm">
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>CEO</strong> is analyzing your question and forming initial recommendation</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>CFO, CTO, COO, CMO, CPO</strong> are providing their expert perspectives</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>Debate continues</strong> for 2-3 rounds to explore all angles</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>CEO synthesizes</strong> final recommendation with confidence score</span>
                </li>
            </ul>
        </div>

        <!-- Redirect Notice -->
        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="animate-spin h-5 w-5 text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-purple-200 text-sm">
                    <strong>Auto-redirect active</strong> — You'll be automatically redirected to the results page when ready
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="text-center mt-6">
            <button wire:click="cancel"
               class="text-slate-400 hover:text-white text-sm transition-colors">
                ← Cancel and return to board
            </button>
        </div>
    </div>
</div>
