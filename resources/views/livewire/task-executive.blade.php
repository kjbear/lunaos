<div class="task-executive" x-data="{
    selectedPeriod: '7d'
}">
    {{-- Executive Dashboard Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">
                        🎯
                    </div>
                </div>
                
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Executive Dashboard</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Strategic task overview and metrics</p>
                </div>
            </div>
            
            <button 
                wire:click="refreshMetrics"
                class="group relative p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all duration-200"
            >
                <span class="group-hover:rotate-180 transition-transform duration-500 block">↻</span>
            </button>
        </div>
    </header>

    {{-- Key Metrics Overview --}}
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-amber-400 to-orange-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Key Metrics</h2>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total Tasks --}}
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10 hover:border-blue-400/30 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center text-xl">📊</div>
                        <div>
                            <div class="text-sm text-slate-400 font-medium">Total Tasks</div>
                            <div class="text-3xl font-bold text-white">{{ $metrics['overview']['total'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Completion Rate --}}
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10 hover:border-emerald-400/30 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center text-xl">✅</div>
                        <div>
                            <div class="text-sm text-slate-400 font-medium">Completion Rate</div>
                            <div class="text-3xl font-bold text-emerald-400">{{ $metrics['overview']['completion_rate'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Avg Cycle Time --}}
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10 hover:border-purple-400/30 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center text-xl">⏱</div>
                        <div>
                            <div class="text-sm text-slate-400 font-medium">Avg Cycle Time</div>
                            <div class="text-3xl font-bold text-purple-400">{{ $metrics['overview']['avg_cycle_time'] }}h</div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Today's Completions --}}
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10 hover:border-cyan-400/30 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center text-xl">🚀</div>
                        <div>
                            <div class="text-sm text-slate-400 font-medium">Today</div>
                            <div class="text-3xl font-bold text-cyan-400">{{ $metrics['overview']['today_completions'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Work in Progress & Status Breakdown --}}
    <section class="mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- In Progress by Step --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Work in Progress</h3>
                </div>
                
                <div class="space-y-4">
                    @foreach($metrics['byStep'] as $step => $stepMetrics)
                    <div class="group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $stepMetrics['icon'] }}</span>
                                <span class="text-sm font-medium text-white">{{ $stepMetrics['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-white">{{ $stepMetrics['count'] }}</span>
                                @if($stepMetrics['avg_age_hours'] > 24)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 border border-red-500/30">
                                    {{ $stepMetrics['avg_age_hours'] }}h
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-500
                                    @if($step === 'develop') bg-gradient-to-r from-cyan-500 to-blue-500
                                    @elseif($step === 'qa') bg-gradient-to-r from-emerald-500 to-green-500
                                    @elseif($step === 'security') bg-gradient-to-r from-orange-500 to-red-500
                                    @elseif($step === 'staging') bg-gradient-to-r from-purple-500 to-pink-500
                                    @else bg-gradient-to-r from-slate-500 to-slate-400
                                    @endif
                                "
                                style="width: {{ min($stepMetrics['count'] * 10, 100) }}%"
                            ></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Current Status Distribution --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-gradient-to-b from-pink-400 to-amber-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Status Distribution</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-slate-500"></div>
                            <span class="text-sm font-medium text-white">Pending</span>
                        </div>
                        <span class="text-lg font-bold text-white">{{ $metrics['overview']['pending'] }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="text-sm font-medium text-white">In Progress</span>
                        </div>
                        <span class="text-lg font-bold text-white">{{ $metrics['overview']['in_progress'] }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-medium text-white">Completed</span>
                        </div>
                        <span class="text-lg font-bold text-white">{{ $metrics['overview']['completed'] }}</span>
                    </div>
                    
                    @if($metrics['overview']['blocked'] > 0)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                            <span class="text-sm font-medium text-white">Blocked</span>
                        </div>
                        <span class="text-lg font-bold text-orange-400">{{ $metrics['overview']['blocked'] }}</span>
                    </div>
                    @endif
                    
                    @if($metrics['overview']['failed'] > 0)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span class="text-sm font-medium text-white">Failed</span>
                        </div>
                        <span class="text-lg font-bold text-red-400">{{ $metrics['overview']['failed'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Performance by Agent --}}
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-blue-400 to-cyan-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Team Performance</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($metrics['byAgent'] as $agent => $agentMetrics)
            <div class="group relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-5 border border-white/10 hover:border-cyan-400/30 transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500/20 to-purple-500/20 flex items-center justify-center text-xl">
                        @if($agent === 'dave')💻
                        @elseif($agent === 'sam')🧪
                        @elseif($agent === 'chen')⚙️
                        @else🔒
                        @endif
                    </div>
                    <div>
                        <div class="font-semibold text-white">{{ ucfirst($agent) }}</div>
                        <div class="text-xs text-slate-400">{{ $agentMetrics['total'] }} tasks</div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mb-2 text-sm">
                    <span class="text-slate-400">Completed</span>
                    <span class="font-bold text-emerald-400">{{ $agentMetrics['completed'] }}</span>
                </div>
                <div class="flex items-center justify-between mb-2 text-sm">
                    <span class="text-slate-400">In Progress</span>
                    <span class="font-bold text-blue-400">{{ $agentMetrics['in_progress'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400">Completion Rate</span>
                    <span class="font-bold {{ $agentMetrics['completion_rate'] >= 80 ? 'text-emerald-400' : ($agentMetrics['completion_rate'] >= 50 ? 'text-yellow-400' : 'text-red-400') }}">
                        {{ $agentMetrics['completion_rate'] }}%
                    </span>
                </div>
                
                {{-- Progress bar --}}
                <div class="mt-3 h-1.5 rounded-full bg-slate-800 overflow-hidden">
                    <div 
                        class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500 transition-all duration-500"
                        style="width: {{ $agentMetrics['completion_rate'] }}%"
                    ></div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Bottlenecks --}}
    @if(count($metrics['bottlenecks']) > 0)
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-red-400 to-orange-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Bottlenecks Alert</h3>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-red-500/20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($metrics['bottlenecks'] as $step => $bottleneck)
                <div class="flex items-center gap-3 p-3 rounded-lg {{ $this->getSeverityClass($bottleneck['severity']) }}">
                    <span class="text-xl">
                        @if($step === 'develop')🔧
                        @elseif($step === 'qa')🧪
                        @elseif($step === 'security')🔒
                        @elseif($step === 'staging')🚀
                        @else✅
                        @endif
                    </span>
                    <div>
                        <div class="text-sm font-bold">{{ ucfirst($step) }}</div>
                        <div class="text-xs opacity-80">{{ $bottleneck['count'] }} tasks > 48h</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Completion Trend Chart --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">7-Day Trend</h3>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
            <div class="flex items-end justify-between h-32 gap-2">
                @foreach($metrics['trends'] as $day)
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <div class="w-full relative">
                        <div 
                            class="w-full rounded-t-lg bg-gradient-to-t from-emerald-500/30 to-emerald-400 group-hover:from-emerald-400 group-hover:to-emerald-300 transition-all cursor-pointer"
                            style="height: {{ max($day['completed'] * 10, 4) }}px;"
                        ></div>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">{{ $day['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    
    {{-- Executive Board Meeting Manager --}}
    <section class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-amber-400 to-orange-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Executive Board</h3>
            <a 
                href="{{ route('tasks.executive.board') }}"
                class="text-xs text-amber-400 hover:text-amber-300 font-medium ml-2"
            >
                View Full Board →
            </a>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
            <livewire:board-meeting-manager />
        </div>
    </section>
</div>
