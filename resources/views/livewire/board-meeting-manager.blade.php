<div class="board-meeting-manager" x-data="{
    questionText: @entangle('question'),
    contextText: @entangle('context'),
    isSubmitting: @entangle('isDebating'),
    activeSpeakerId: @entangle('activeSpeakerId'),
    currentRound: @entangle('currentRound'),
    maxRounds: @entangle('maxRounds'),
    apiConfigured: @entangle('apiConfigured'),
    
    // Auto-scroll transcript to bottom
    scrollToBottom() {
        this.$nextTick(() => {
            const transcript = document.getElementById('transcript-container');
            if (transcript) {
                transcript.scrollTop = transcript.scrollHeight;
            }
        });
    }
}">
    {{-- Board Meeting Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-950/80 via-orange-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-6 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 via-orange-500/5 to-pink-500/5"></div>
        
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">
                        🎯
                    </div>
                </div>
                
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Executive Board</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Strategic decision-making with AI executives</p>
                </div>
            </div>
            
            {{-- Quick Stats --}}
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-2xl font-bold text-amber-400">{{ $stats['total_sessions'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Sessions</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ $stats['decisions'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Decisions</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-slate-400">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Pending</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <button 
                    wire:click="resetSession"
                    class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                    title="New Session"
                >
                    🔄
                </button>
            </div>
        </div>
    </header>

    {{-- Main Content Grid --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Question Input & Persona Cards --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Question Input Panel --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-amber-400 to-orange-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Ask the Board</h3>
                    @if(!$apiConfigured)
                    <span class="px-2.5 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold">
                        ⚠️ API Required
                    </span>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Your Strategic Question</label>
                        <textarea 
                            wire:model="question"
                            x-model="questionText"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-amber-500/50 focus:outline-none resize-none"
                            rows="4"
                            placeholder="e.g., Should we prioritize LunaOS development or the Status Page Aggregator first?"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Additional Context (Optional)</label>
                        <textarea 
                            wire:model="context"
                            x-model="contextText"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-amber-500/50 focus:outline-none resize-none"
                            rows="2"
                            placeholder="Add any relevant background, constraints, or considerations..."
                        ></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            Round <span x-text="currentRound">1</span> of <span x-text="maxRounds">3</span>
                        </div>
                        <button 
                            wire:click="conveneBoard"
                            wire:loading.attr="disabled"
                            wire:target="conveneBoard"
                            x-bind:disabled="isSubmitting || !apiConfigured"
                            class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 text-white font-semibold rounded-xl hover:from-amber-500 hover:to-orange-500 transition-all shadow-lg shadow-amber-500/25 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="conveneBoard" x-show="!isSubmitting">🎙 Convene Board</span>
                            <span wire:loading wire:target="conveneBoard">
                                <svg class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Convening...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Persona Cards --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Board Members</h3>
                    <span class="text-xs text-slate-500 ml-auto">{{ count($boardMembers) }} executives</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($boardMembers as $member)
                    <div 
                        class="group p-4 rounded-xl border transition-all duration-300 cursor-pointer
                            @if($activeSpeakerId === $member['id'])
                                bg-amber-500/10 border-amber-500/40 shadow-lg shadow-amber-500/10
                            @else
                                bg-white/[0.02] border-white/5 hover:border-amber-500/30 hover:bg-amber-500/10
                            @endif"
                        x-data="{ expanded: false }"
                    >
                        <div class="text-center">
                            {{-- Avatar with Status Indicator --}}
                            <div class="relative inline-block mb-3">
                                <div class="text-4xl transition-transform duration-300 group-hover:scale-110">
                                    {{ $member['avatar'] }}
                                </div>
                                @if($isDebating && $activeSpeakerId === $member['id'])
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-amber-500 border-2 border-slate-900 animate-pulse"></div>
                                @elseif($isDebating)
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-slate-500/50 border-2 border-slate-900"></div>
                                @else
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500/50 border-2 border-slate-900" title="Ready"></div>
                                @endif
                            </div>
                            
                            <div class="font-bold text-white text-sm">{{ $member['title'] }}</div>
                            <div class="text-xs text-slate-500 mb-2">{{ $member['name'] }}</div>
                            
                            <div class="inline-block px-2 py-1 rounded text-xs font-semibold border {{ $this->getModelBadgeClass($member['model']) }}">
                                {{ $member['model'] }}
                            </div>
                            
                            {{-- Expandable Inspiration --}}
                            @if($member['inspiration'] ?? null)
                            <div class="mt-3 pt-3 border-t border-white/5">
                                <div 
                                    x-show="expanded"
                                    x-collapse
                                    class="text-xs text-slate-400 leading-relaxed"
                                >
                                    {{ $member['inspiration'] }}
                                </div>
                                <button 
                                    @click="expanded = !expanded"
                                    class="text-xs text-amber-400 hover:text-amber-300 mt-1"
                                >
                                    <span x-show="!expanded">→ More info</span>
                                    <span x-show="expanded">↑ Less info</span>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right Column: Transcript & Decision --}}
        <div class="space-y-6">
            {{-- Round Progress Indicator --}}
            @if($isDebating)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-semibold uppercase">Session Progress</span>
                    <span class="text-xs text-amber-400 font-bold">Round {{ $currentRound }}/{{ $maxRounds }}</span>
                </div>
                <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-500"
                        style="width: {{ ($currentRound / $maxRounds) * 100 }}%"
                    ></div>
                </div>
            </div>
            @endif

            {{-- Live Transcript --}}
            @if(count($transcript ?? []) > 0)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden"
                 x-data="{
                     messages: @entangle('transcript'),
                     init() {
                         this.$watch('messages', () => this.scrollToBottom());
                         this.scrollToBottom();
                     },
                     scrollToBottom() {
                         this.$nextTick(() => {
                             const container = document.getElementById('transcript-container');
                             if (container) container.scrollTop = container.scrollHeight;
                         });
                     }
                 }">
                <div class="bg-white/[0.02] border-b border-white/10 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-5 bg-gradient-to-b from-amber-400 to-orange-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Debate Transcript</h3>
                    </div>
                    <span class="text-xs text-slate-500">{{ count($transcript ?? []) }} contributions</span>
                </div>
                
                <div 
                    id="transcript-container"
                    class="p-4 max-h-[500px] overflow-y-auto space-y-4 scroll-smooth"
                >
                    @foreach($transcript as $entry)
                    <div class="p-4 bg-white/[0.02] rounded-xl border border-white/5 animate-fade-in">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">{{ $entry['avatar'] }}</span>
                            <div>
                                <span class="text-sm font-bold text-white">{{ $entry['member_name'] }}</span>
                                <span class="text-xs text-slate-500 ml-2">{{ $entry['member_role'] }}</span>
                                <span class="text-xs text-slate-600 ml-2">• {{ $entry['timestamp'] }}</span>
                            </div>
                            @if($isDebating && $loop->last)
                            <span class="flex items-center gap-1 text-xs text-amber-400 ml-auto">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                Latest
                            </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $entry['response'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            {{-- Empty Transcript State --}}
            <div class="flex flex-col items-center justify-center py-12 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                <div class="text-5xl mb-4 opacity-50">💬</div>
                <p class="text-slate-400 font-semibold">No discussion yet</p>
                <p class="text-slate-500 text-sm mt-2">Ask the board a question to start the debate</p>
            </div>
            @endif

            {{-- Final Decision Panel --}}
            @if($finalDecision)
            <div class="bg-gradient-to-br from-emerald-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-emerald-500/30 p-6"
                 x-data="{ expanded: true }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">✅ Board Decision</h3>
                    </div>
                    @if($confidenceScore !== null)
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold uppercase">Confidence</div>
                        <div class="text-xl font-bold {{ $this->getConfidenceColor() }}">{{ number_format($confidenceScore, 0) }}%</div>
                    </div>
                    @endif
                </div>

                <div class="prose prose-invert max-w-none mb-4">
                    <p class="text-slate-300 leading-relaxed">{{ $finalDecision }}</p>
                </div>

                @if($risksBenefits)
                <div 
                    x-show="expanded"
                    x-collapse
                    class="mt-4 p-4 bg-white/[0.02] rounded-xl border border-white/5"
                >
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-2">Risks & Benefits</div>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $risksBenefits }}</p>
                </div>
                
                <button 
                    @click="expanded = !expanded"
                    class="text-xs text-emerald-400 hover:text-emerald-300 mt-2"
                >
                    <span x-show="!expanded">Show risks & benefits</span>
                    <span x-show="expanded">Hide risks & benefits</span>
                </button>
                @endif

                <button 
                    wire:click="$dispatch('create-project-from-decision', { decision: '{{ addslashes($finalDecision) }}' })"
                    class="w-full mt-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-500 hover:to-teal-500 transition-all shadow-lg shadow-emerald-500/25"
                >
                    📋 Create Project from Decision
                </button>
            </div>
            @endif
        </div>
    </section>

    {{-- Loading Overlay (shown during debate) --}}
    @if($isDebating)
    <div 
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center"
        x-data="{ show: $wire.entangle('isDebating') }"
        x-show="show"
        x-cloak
    >
        <div class="bg-slate-900 border border-white/10 rounded-2xl p-8 max-w-md text-center">
            <div class="text-5xl mb-4 animate-bounce">🎙</div>
            <h3 class="text-xl font-bold text-white mb-2">Board in Session</h3>
            <p class="text-slate-400 text-sm mb-4">The executives are debating your question...</p>
            <button 
                wire:click="cancelSession"
                class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-medium rounded-lg transition-colors"
            >
                Cancel Session
            </button>
            <div class="mt-6 flex items-center justify-center gap-2">
                <div class="w-3 h-3 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-3 h-3 bg-orange-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-3 h-3 bg-pink-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Custom Styles for Animations --}}
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        
        /* Custom scrollbar for transcript */
        #transcript-container::-webkit-scrollbar {
            width: 6px;
        }
        
        #transcript-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 3px;
        }
        
        #transcript-container::-webkit-scrollbar-thumb {
            background: rgba(251, 191, 36, 0.3);
            border-radius: 3px;
        }
        
        #transcript-container::-webkit-scrollbar-thumb:hover {
            background: rgba(251, 191, 36, 0.5);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Listen for toast events from Livewire
        @if (app()->environment('local'))
        Livewire.on('toast', (event) => {
            const { type, message } = event;
            console.log(`[Toast] ${type}: ${message}`);
        });
        @endif
    </script>
    @endpush
</div>
