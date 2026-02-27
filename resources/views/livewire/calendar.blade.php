<div class="space-y-6">
    {{-- Polished Page Header with Calendar Context --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-blue-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">📅</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Team Calendar</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Deadlines, standups, and agent task due dates</p>
                </div>
            </div>
            
            {{-- Quick Stats --}}
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-2xl font-bold text-white">{{ count($events[0] ?? []) }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">This Month</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-amber-400">{{ collect($events[0] ?? [])->where('type', 'deadline')->count() }}</div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Deadlines</div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Calendar Grid --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Month Grid (2 columns) --}}
        <div class="lg:col-span-2">
            {{-- Month Navigation --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <button wire:click="previousMonth" class="p-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
                        ◀
                    </button>
                    <button wire:click="goToToday" class="px-3 py-1.5 text-sm rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
                        Today
                    </button>
                    <button wire:click="nextMonth" class="p-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
                        ▶
                    </button>
                </div>
                <div class="text-lg font-bold text-white">
                    {{ $currentMonth }} {{ $currentYear }}
                </div>
            </div>

            {{-- Calendar Card --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                {{-- Weekday Headers --}}
                <div class="grid grid-cols-7 border-b border-white/10">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        {{ $day }}
                    </div>
                    @endforeach
                </div>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7">
                    @foreach($days as $day)
                    <div 
                        @if($day['month'] !== $currentMonth)
                        class="min-h-[100px] p-2 bg-white/[0.01] border-r border-b border-white/5"
                        @else
                        class="min-h-[100px] p-2 border-r border-b border-white/5 hover:bg-white/[0.02] transition-colors cursor-pointer group {{ $day['is_today'] ? 'bg-purple-500/10' : '' }}"
                        wire:click="selectDay({{ $day['day'] }})"
                        @endif
                    >
                        {{-- Day Number --}}
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold {{ $day['is_today'] ? 'text-purple-400' : 'text-slate-300' }}">
                                {{ $day['day'] }}
                            </span>
                            @if($day['is_today'])
                            <span class="px-2 py-0.5 rounded bg-purple-500/30 text-xs text-purple-200 font-semibold">Today</span>
                            @endif
                        </div>

                        {{-- Events for this day --}}
                        @if(isset($day['events']) && count($day['events']) > 0)
                        <div class="space-y-1">
                            @foreach(array_slice($day['events'], 0, 3) as $event)
                            <div class="px-2 py-1 rounded text-xs truncate {{ $event['type'] === 'deadline' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : ($event['type'] === 'standup' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30') }}">
                                {{ $event['title'] }}
                            </div>
                            @endforeach
                            @if(count($day['events']) > 3)
                            <div class="text-xs text-slate-500 pl-2">
                                +{{ count($day['events']) - 3 }} more
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: Event Details & Quick Actions (1 column) --}}
        <div class="space-y-4">
            {{-- Selected Day Panel --}}
            @if($selectedDay)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">
                        {{ \Carbon\Carbon::create($currentYear, $currentMonth, $selectedDay)->format('M j') }}
                    </h3>
                    <button wire:click="$set('selectedDay', null)" class="text-slate-400 hover:text-white">×</button>
                </div>

                @php
                $dayEvents = collect($events[0] ?? [])->filter(function($event) use ($selectedDay, $currentMonth) {
                    return \Carbon\Carbon::parse($event['date'])->day == $selectedDay && 
                           \Carbon\Carbon::parse($event['date'])->month == $currentMonth;
                })->values();
                @endphp

                @if($dayEvents->count() > 0)
                <div class="space-y-3">
                    @foreach($dayEvents as $event)
                    <div class="p-3 bg-white/[0.02] rounded-xl border border-white/5">
                        <div class="flex items-start gap-3">
                            <div class="text-2xl">{{ $event['type'] === 'deadline' ? '⚠️' : ($event['type'] === 'standup' ? '🔄' : '📅') }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-white mb-1">{{ $event['title'] }}</div>
                                <div class="text-xs text-slate-400">{{ $event['time'] ?? 'All day' }}</div>
                                @if(isset($event['description']))
                                <div class="text-xs text-slate-500 mt-1">{{ Str::limit($event['description'], 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="text-4xl mb-2 opacity-50">📭</div>
                    <p class="text-slate-400 text-sm">No events scheduled</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Upcoming Deadlines --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-5 bg-gradient-to-b from-red-400 to-orange-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Upcoming Deadlines</h3>
                </div>

                @php
                $upcomingDeadlines = collect($events[0] ?? [])->filter(function($event) {
                    return ($event['type'] ?? '') === 'deadline' && 
                           \Carbon\Carbon::parse($event['date'])->isFuture();
                })->sortBy('date')->take(5)->values();
                @endphp

                @if($upcomingDeadlines->count() > 0)
                <div class="space-y-2">
                    @foreach($upcomingDeadlines as $deadline)
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/[0.02] transition-all">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-300 truncate">{{ $deadline['title'] }}</div>
                            <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($deadline['date'])->format('M j') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500 text-center py-4">No upcoming deadlines</p>
                @endif
            </div>

            {{-- Recurring Events --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-5 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Recurring</h3>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/[0.02] transition-all">
                        <div class="w-2 h-2 rounded-full bg-cyan-500"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-slate-300">Team Standup</div>
                            <div class="text-xs text-slate-500">Daily at 10:00 AM</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/[0.02] transition-all">
                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-slate-300">Heartbeat Check</div>
                            <div class="text-xs text-slate-500">Every 30 min</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
