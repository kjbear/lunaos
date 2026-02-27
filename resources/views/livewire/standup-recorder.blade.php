<div class="space-y-6">
    {{-- Header with Meeting Context --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">🎤</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Team Standup</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Daily agent syncs with blockers, wins, and action items</p>
                </div>
            </div>
            
            {{-- Quick Stats --}}
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-400">{{ $standupsCount ?? 12 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">This Month</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-amber-400">{{ $activeBlockers ?? 3 }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Blockers</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <button 
                    wire:click="showNewForm"
                    class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25"
                >
                    ➕ New Standup
                </button>
            </div>
        </div>
    </header>

    {{-- View: Archive --}}
    @if($view === 'archive')
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Standups List --}}
        <div class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Recent Meetings</h3>
            </div>

            <div class="space-y-4">
                @forelse($recentStandups ?? [] as $standup)
                <div 
                    wire:click="viewStandup({{ $standup->id }})"
                    class="group bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-5 hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300 cursor-pointer"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 flex items-center justify-center text-2xl flex-shrink-0">
                                📋
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-white mb-1 group-hover:text-purple-400 transition-colors">
                                    {{ $standup->team }} Standup
                                </h4>
                                <div class="flex items-center gap-3 text-sm text-slate-400">
                                    <span>📅 {{ \Carbon\Carbon::parse($standup->date)->format('l, M j, Y') }}</span>
                                    <span>•</span>
                                    <span>👥 {{ $standup->agentUpdates->count() }} agents</span>
                                    @if($standup->agentUpdates->filter(fn($u) => $u->hasBlockers())->count() > 0)
                                    <span class="flex items-center gap-1 text-red-400">
                                        ⚠️ {{ $standup->agentUpdates->filter(fn($u) => $u->hasBlockers())->count() }} blockers
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-500 group-hover:text-purple-400 group-hover:bg-purple-500/20 transition-all">
                                →
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-5xl mb-4 opacity-50">📭</div>
                    <p class="text-slate-400 font-semibold">No standups yet</p>
                    <p class="text-sm text-slate-500 mt-2">Click "New Standup" to record your first meeting</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar: Today's Focus --}}
        <div class="space-y-4">
            {{-- Today's Date Card --}}
            <div class="bg-gradient-to-br from-purple-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="text-center">
                    <div class="text-4xl mb-2">📆</div>
                    <div class="text-3xl font-bold text-white mb-1">{{ now()->format('j') }}</div>
                    <div class="text-slate-400 text-sm">{{ now()->format('F Y') }}</div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/10">
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-2">Quick Actions</div>
                    <button wire:click="showNewForm" class="w-full py-2 bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 border border-purple-500/30 rounded-lg text-sm font-semibold transition-all">
                        Record Standup
                    </button>
                </div>
            </div>

            {{-- Active Blockers --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-5 bg-gradient-to-b from-red-400 to-orange-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Active Blockers</h3>
                </div>

                <div class="space-y-2">
                    @foreach($allActiveBlockers ?? [] as $blocker)
                    <div class="p-3 bg-red-500/10 border border-red-500/30 rounded-lg">
                        <div class="text-xs text-red-400 font-semibold mb-1">{{ $blocker['agent'] }}</div>
                        <div class="text-sm text-red-300">{{ Str::limit($blocker['text'], 50) }}</div>
                    </div>
                    @endforeach
                    @if(empty($allActiveBlockers ?? []))
                    <div class="text-center py-4">
                        <div class="text-3xl mb-2 opacity-50">🟢</div>
                        <p class="text-sm text-slate-500">No active blockers</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Team Members --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-5 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Team</h3>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @foreach(['🌙 Luna', '🔧 Dave', '🎨 Maya', '☁️ Chen', '✨ Sam', '🔌 Alex'] as $member)
                    <div class="p-2 bg-white/[0.02] rounded-lg border border-white/5 flex items-center gap-2">
                        <div class="text-xl">{{ Str::before($member, ' ') }}</div>
                        <div class="text-xs text-slate-300">{{ Str::after($member, ' ') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- View: Meeting Details --}}
    @if($view === 'meeting' && $selectedStandup)
    <div class="flex items-center gap-4 mb-4">
        <button wire:click="showArchive" class="p-2 text-slate-400 hover:text-white transition-colors">
            ← Back
        </button>
        <div class="flex-1"></div>
        <button wire:click="deleteStandup({{ $selectedStandup->id }})" class="p-2 text-slate-400 hover:text-red-400 transition-colors" title="Delete">
            🗑️
        </button>
    </div>

    {{-- Header Card --}}
    <div class="bg-gradient-to-br from-purple-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-white/10 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-4xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $selectedStandup->team }} Standup</h2>
                        <p class="text-slate-400">
                            {{ $selectedStandup->date->format('l, F j, Y') }} 
                            <span class="text-slate-600">•</span>
                            Facilitator: {{ $selectedStandup->facilitator }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @foreach($selectedStandup->agentUpdates as $update)
                <span 
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold"
                    style="background-color: {{ $update->agent_color }}20; color: {{ $update->agent_color }}; border: 1px solid {{ $update->agent_color }}40;"
                >
                    {{ $update->agent_name }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Transcript: Speaker Cards --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Meeting Transcript</h3>
        </div>

        <div class="space-y-4">
            @foreach($selectedStandup->agentUpdates as $update)
            <div 
                class="bg-slate-900/60 backdrop-blur-sm rounded-xl border-l-4 p-5 hover:shadow-lg transition-all"
                style="border-left-color: {{ $update->agent_color }};"
            >
                {{-- Speaker Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div 
                        class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-xl"
                        style="background: linear-gradient(135deg, {{ $update->agent_color }}, {{ $update->agent_color }}dd);"
                    >
                        {{ substr($update->agent_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-base font-bold text-white">{{ $update->agent_name }}</div>
                        <div class="text-sm text-slate-400">{{ $update->agent_role }}</div>
                    </div>
                </div>

                {{-- Content Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($update->done_yesterday)
                    <div class="p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/30">
                        <div class="text-xs text-emerald-400 font-semibold uppercase mb-2">✅ Done Yesterday</div>
                        <p class="text-sm text-emerald-100">{{ $update->done_yesterday }}</p>
                    </div>
                    @endif

                    @if($update->doing_today)
                    <div class="p-4 bg-cyan-500/10 rounded-xl border border-cyan-500/30">
                        <div class="text-xs text-cyan-400 font-semibold uppercase mb-2">🎯 Doing Today</div>
                        <p class="text-sm text-cyan-100">{{ $update->doing_today }}</p>
                    </div>
                    @endif

                    @if($update->hasBlockers())
                    <div class="p-4 bg-red-500/10 rounded-xl border border-red-500/30 md:col-span-1">
                        <div class="text-xs text-red-400 font-semibold uppercase mb-2">⚠️ Blockers</div>
                        <p class="text-sm text-red-100">{{ $update->blockers }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Action Items --}}
    @if($selectedStandup->actionItems->count() > 0)
    <section class="mt-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                Action Items
                <span class="text-slate-500 ml-2 font-normal">
                    {{ $selectedStandup->actionItems->where('completed', true)->count() }} / {{ $selectedStandup->actionItems->count() }} completed
                </span>
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($selectedStandup->actionItems as $item)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-4 {{ $item->completed ? 'opacity-60' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="text-xl {{ $item->completed ? 'text-emerald-400' : 'text-slate-500' }}">
                        {{ $item->completed ? '✅' : '⬜' }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-slate-300 {{ $item->completed ? 'line-through' : '' }}">{{ $item->title }}</p>
                        @if($item->assigned_to)
                        <p class="text-xs text-slate-500 mt-1">Assigned to: {{ $item->assigned_to }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
    @endif

    {{-- View: New Standup Form --}}
    @if($view === 'new')
    <div class="flex items-center gap-4 mb-4">
        <button wire:click="showArchive" class="p-2 text-slate-400 hover:text-white transition-colors">
            ← Cancel
        </button>
        <div class="flex-1"></div>
        <button 
            wire:click="generateAgentUpdates"
            class="px-4 py-2 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-lg text-sm font-semibold hover:bg-emerald-500/30 transition-all"
        >
            🔄 Auto-fill from Activity
        </button>
    </div>

    {{-- Meeting Setup --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Form Fields --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Info --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">Meeting Details</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Date</label>
                        <input type="date" wire:model="date" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-300 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Team</label>
                        <input type="text" wire:model="team" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-300 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-2">Facilitator</label>
                        <input type="text" wire:model="facilitator" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-300 focus:border-purple-500 focus:outline-none">
                    </div>
                </div>
            </div>

            {{-- Agent Updates --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Agent Updates</h3>
                    <button wire:click="addAgent('NewAgent')" class="text-xs text-purple-400 hover:text-purple-300">+ Add Agent</button>
                </div>

                @foreach($agentUpdates ?? [] as $index => $update)
                <div class="mb-6 last:mb-0">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-sm" style="background: {{ $update['agent_color'] }}">
                                {{ substr($update['agent_name'], 0, 1) }}
                            </div>
                            <input type="text" wire:model="agentUpdates.{{ $index }}.agent_name" class="bg-transparent text-sm font-semibold text-white focus:outline-none" placeholder="Agent Name">
                        </div>
                        <button wire:click="removeAgent({{ $index }})" class="p-2 text-slate-500 hover:text-red-400 transition-colors">×</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pl-11">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Done Yesterday</label>
                            <textarea wire:model="agentUpdates.{{ $index }}.done_yesterday" rows="3" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-300 focus:border-purple-500 focus:outline-none resize-none" placeholder="What was completed?"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Doing Today</label>
                            <textarea wire:model="agentUpdates.{{ $index }}.doing_today" rows="3" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-300 focus:border-purple-500 focus:outline-none resize-none" placeholder="Current focus"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Blockers</label>
                            <textarea wire:model="agentUpdates.{{ $index }}.blockers" rows="3" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-300 focus:border-purple-500 focus:outline-none resize-none" placeholder="Any blockers?"></textarea>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Action Items & Save --}}
        <div class="space-y-6">
            {{-- Action Items --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Action Items</h3>
                    <button wire:click="addActionItem" class="text-xs text-purple-400 hover:text-purple-300">+ Add</button>
                </div>

                <div class="space-y-2">
                    @forelse($actionItems ?? [] as $index => $item)
                    <div class="flex items-center gap-2">
                        <button wire:click="toggleActionItem({{ $index }})" class="text-lg {{ $item['completed'] ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $item['completed'] ? '✅' : '⬜' }}
                        </button>
                        <input type="text" wire:model="actionItems.{{ $index }}.title" class="flex-1 bg-white/5 border border-white/10 rounded-lg px-2 py-1.5 text-xs text-slate-300 focus:border-purple-500 focus:outline-none {{ $item['completed'] ? 'line-through opacity-60' : '' }}" placeholder="Action item...">
                        <button wire:click="removeActionItem({{ $index }})" class="p-1 text-slate-500 hover:text-red-400">×</button>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-500">No action items yet</p>
                        <p class="text-xs text-slate-600 mt-1">Blockers auto-generate items</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Save Button --}}
            @if(count($agentUpdates ?? []) > 0)
            <button wire:click="save" class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25">
                💾 Save Standup
            </button>
            @endif
        </div>
    </div>
    @endif
</div>
