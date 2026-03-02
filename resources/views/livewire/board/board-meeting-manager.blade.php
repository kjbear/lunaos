<div class="space-y-6">
    {{-- Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                        <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">🎯</div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Board Meeting Manager</h1>
                        <p class="text-sm text-slate-400 font-medium mt-0.5">Executive debate & decision-making</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($currentSessionId)
                    <span class="px-3 py-1.5 rounded-lg {{ $isDebating ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : ($isDecided ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30') }} text-sm font-semibold">
                        {{ $isDebating ? '🔴 In Session' : ($isDecided ? '✅ Decided' : '🟢 Ready') }}
                    </span>
                    @endif
                    <button 
                        wire:click="resetManager"
                        class="px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all text-sm font-medium"
                    >
                        🔄 Reset
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Question & Session Controls --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Question Input --}}
            @if(!$currentSessionId)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Strategic Question</h3>
                    @if(!$apiConfigured)
                    <span class="px-2.5 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold">
                        ⚠️ API Required
                    </span>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">What should the board discuss?</label>
                        <textarea 
                            wire:model="question" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-purple-500/50 focus:outline-none resize-none"
                            rows="4"
                            placeholder="e.g., Should we prioritize LunaOS development or the Status Page Aggregator first? What are the trade-offs?"
                        ></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button 
                            wire:click="askQuestion"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25 {{ $isDebating ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $isDebating ? 'disabled' : '' }}
                        >
                            🎙 Ask the Board
                        </button>
                    </div>
                </div>
            </div>

            {{-- Board Members --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Board Members (All use GLM-5)</h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    @foreach(['COO' => ['name' => 'Gwynne', 'emoji' => '👔'], 'CFO' => ['name' => 'Warren', 'emoji' => '💰'], 'CTO' => ['name' => 'Werner', 'emoji' => '💻'], 'CMO' => ['name' => 'Bozoma', 'emoji' => '📢'], 'CPO' => ['name' => 'Fidji', 'emoji' => '📦']] as $role => $info)
                    <div class="p-4 bg-white/[0.02] rounded-xl border border-white/5 text-center hover:border-purple-500/30 hover:bg-purple-500/10 transition-all">
                        <div class="text-3xl mb-2">{{ $info['emoji'] }}</div>
                        <div class="font-bold text-white text-xs">{{ $role }}</div>
                        <div class="text-xs text-slate-500">{{ $info['name'] }}</div>
                        <div class="mt-2 inline-block px-2 py-0.5 rounded text-xs font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30">GLM-5</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Active Session Info --}}
            @if($currentSessionId && $this->sessionInfo)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Session Info</h3>
                    </div>
                    <span class="text-xs text-slate-500">ID: {{ Str::substr($currentSessionId, 0, 8) }}...</span>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Question</label>
                        <p class="text-sm text-slate-300">{{ $this->sessionInfo['question'] }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Status</label>
                            <p class="text-sm font-semibold text-purple-400">{{ ucfirst($this->sessionInfo['status']) }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Round</label>
                            <p class="text-sm font-semibold text-slate-300">{{ $currentRound }} / {{ $maxRounds }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Started</label>
                            <p class="text-sm text-slate-300">{{ $this->sessionInfo['started_at'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Debate Controls --}}
            @if($isDebating && !$isDecided)
            <div class="bg-gradient-to-r from-amber-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-amber-500/30 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                        <h3 class="text-lg font-semibold text-amber-300">Debate in Progress</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <button 
                            wire:click="getNextDebateRound"
                            class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 text-white font-semibold rounded-xl hover:from-amber-500 hover:to-orange-500 transition-all shadow-lg shadow-amber-500/25"
                        >
                            {{ $currentRound > 0 ? "▶️ Next Round" : "▶️ Start Round 1" }}
                        </button>
                        <button 
                            wire:click="closeSession"
                            class="px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all font-medium"
                            title="End Session"
                        >
                            ⏹ End
                        </button>
                    </div>
                </div>
            </div>
            @endif
            @endif

            {{-- Transcript --}}
            @if(count($this->transcriptDisplay) > 0)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                <div class="bg-white/[0.02] border-b border-white/10 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-5 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Debate Transcript</h3>
                    </div>
                    <span class="text-xs text-slate-500">{{ count($this->transcriptDisplay) }} rounds</span>
                </div>
                <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto">
                    @foreach($this->transcriptDisplay as $round)
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs font-semibold uppercase">Round {{ $loop->iteration }}</span>
                            <div class="flex-1 h-px bg-white/10"></div>
                        </div>
                        @foreach($round['entries'] as $entry)
                        <div class="p-4 bg-white/[0.02] rounded-xl border border-white/5">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">{{ $entry['emoji'] }}</span>
                                <div>
                                    <span class="text-sm font-bold text-white">{{ $entry['name'] }}</span>
                                    <span class="text-xs text-slate-500 ml-2">{{ $entry['role'] }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $entry['response'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Decision --}}
        <div class="space-y-6">
            @if($this->decision)
            <div class="bg-gradient-to-br from-emerald-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-emerald-500/30 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">✅ Final Decision</h3>
                </div>

                <div class="prose prose-invert max-w-none mb-4">
                    <p class="text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $this->decision['text'] }}</p>
                </div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="px-3 py-1.5 rounded-lg {{ $this->decision['confidence'] >= 0.7 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($this->decision['confidence'] >= 0.5 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30') }} text-xs font-semibold">
                        Confidence: {{ $this->decision['confidence_level'] }} ({{ number_format($this->decision['confidence'] * 100, 0) }}%)
                    </div>
                    <span class="text-xs text-slate-500">{{ $this->decision['created_at'] }}</span>
                </div>

                @if($this->decision['reasoning'])
                <div class="mt-4 p-4 bg-white/[0.02] rounded-xl border border-white/5">
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-2">Reasoning</div>
                    <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $this->decision['reasoning'] }}</p>
                </div>
                @endif

                <button class="w-full mt-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-500 hover:to-teal-500 transition-all shadow-lg shadow-emerald-500/25">
                    📋 Create Project from Decision
                </button>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-12 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                <div class="text-5xl mb-4 opacity-50">💭</div>
                <p class="text-slate-400 font-semibold">No decision yet</p>
                <p class="text-slate-500 text-sm mt-2">Start a debate to reach a decision</p>
            </div>
            @endif
        </div>
    </div>
</div>
