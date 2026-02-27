<div class="space-y-6">
    {{-- Header with Strategic Context --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">🎯</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Executive Board</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Strategic decision-making with AI executives</p>
                </div>
            </div>
            
            {{-- Quick Stats --}}
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-400">{{ $stats['total_sessions'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Sessions</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ $stats['decisions'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Decisions</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-amber-400">{{ $stats['pending'] ?? 0 }}</div>
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

    {{-- Main Grid --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Question Input & Board Members --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Question Input --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Ask the Board</h3>
                    @if(!$apiConfigured)
                    <span class="px-2.5 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold">
                        ⚠️ Add API Key
                    </span>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Your Strategic Question</label>
                        <textarea 
                            wire:model="question" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-purple-500/50 focus:outline-none resize-none"
                            rows="4"
                            placeholder="e.g., Should we prioritize LunaOS development or the Status Page Aggregator first?"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Additional Context (Optional)</label>
                        <textarea 
                            wire:model="context" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-purple-500/50 focus:outline-none resize-none"
                            rows="2"
                            placeholder="Add any relevant background, constraints, or considerations..."
                        ></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button 
                            wire:click="conveneBoard" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25 {{ $isDebating ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $isDebating ? 'disabled' : '' }}
                        >
                            {{ $isDebating ? '🎙 Board in Session...' : '🎙 Convene Board' }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Board Members --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Board Members</h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($boardMembers ?? [] as $member)
                    <div class="group p-4 bg-white/[0.02] rounded-xl border border-white/5 hover:border-purple-500/30 hover:bg-purple-500/10 transition-all">
                        <div class="text-center">
                            <div class="text-4xl mb-3">{{ $member['avatar'] }}</div>
                            <div class="font-bold text-white text-sm">{{ $member['title'] }}</div>
                            <div class="text-xs text-slate-500 mb-2">{{ $member['name'] }}</div>
                            <div class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $member['model'] === 'dolphin' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : ($member['model'] === 'haiku' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30') }}">
                                {{ $member['model'] }}
                            </div>
                        </div>
                        @if($member['inspiration'] ?? null)
                        <div class="mt-3 pt-3 border-t border-white/5">
                            <p class="text-xs text-slate-400 leading-relaxed">{{ Str::limit($member['inspiration'], 60) }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: Transcript & Decision --}}
        <div class="space-y-6">
            {{-- Live Transcript --}}
            @if(count($transcript ?? []) > 0)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                <div class="bg-white/[0.02] border-b border-white/10 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-5 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Transcript</h3>
                    </div>
                    <span class="text-xs text-slate-500">{{ count($transcript) }} contributions</span>
                </div>
                <div class="p-4 max-h-[500px] overflow-y-auto space-y-4">
                    @foreach($transcript as $entry)
                    <div class="p-4 bg-white/[0.02] rounded-xl border border-white/5">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">{{ $entry['avatar'] }}</span>
                            <div>
                                <span class="text-sm font-bold text-white">{{ $entry['member_name'] }}</span>
                                <span class="text-xs text-slate-500 ml-2">{{ $entry['member_role'] }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $entry['response'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Final Decision --}}
            @if($finalDecision)
            <div class="bg-gradient-to-br from-emerald-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-emerald-500/30 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">✅ Board Decision</h3>
                </div>

                <div class="prose prose-invert max-w-none mb-4">
                    <p class="text-slate-300 leading-relaxed">{!! nl2br(e($finalDecision)) !!}</p>
                </div>

                @if($risksBenefits)
                <div class="mt-4 p-4 bg-white/[0.02] rounded-xl border border-white/5">
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-2">Risks & Benefits</div>
                    <p class="text-sm text-slate-300 leading-relaxed">{!! nl2br(e($risksBenefits)) !!}</p>
                </div>
                @endif

                <button class="w-full mt-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-500 hover:to-teal-500 transition-all shadow-lg shadow-emerald-500/25">
                    📋 Create Project from Decision
                </button>
            </div>
            @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-12 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                <div class="text-5xl mb-4 opacity-50">💭</div>
                <p class="text-slate-400 font-semibold">No decision yet</p>
                <p class="text-slate-500 text-sm mt-2">Ask the board a question to begin</p>
            </div>
            @endif
        </div>
    </section>
</div>
