<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">📅 Calendar</h1>
            <p class="text-sm text-[#6b6b80] mt-1">{{ $this->weekTitle }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                wire:click="goToToday"
                class="btn btn-secondary text-sm"
            >
                Today
            </button>
            <div class="flex items-center gap-1">
                <button
                    wire:click="previousWeek"
                    class="p-2 text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#252542] rounded-lg transition-colors"
                >
                    ←
                </button>
                <button
                    wire:click="nextWeek"
                    class="p-2 text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#252542] rounded-lg transition-colors"
                >
                    →
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-4 gap-6">
        <!-- Week View -->
        <div class="col-span-3">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 border-b border-[#2a2a40] ml-14">
                    @foreach($weekDays as $day)
                        <div class="p-3 text-center {{ $day['isToday'] ? 'bg-[#7c3aed]/10' : 'bg-[#12121f]' }}">
                            <div class="text-xs text-[#6b6b80] uppercase">{{ $day['dayName'] }}</div>
                            <div class="text-lg font-semibold {{ $day['isToday'] ? 'text-[#7c3aed]' : 'text-[#e4e4f0]' }}">
                                {{ $day['dayNum'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Time Grid -->
                <div class="flex" style="height: 660px; overflow-y: auto;">
                    <!-- Time Labels Column -->
                    <div class="w-14 flex-shrink-0 bg-[#12121f] border-r border-[#2a2a40]">
                        @for($hour = 8; $hour <= 18; $hour++)
                            <div class="h-[60px] border-b border-[#1f1f35] flex items-start justify-end pr-2 pt-0">
                                <span class="text-xs text-[#6b6b80]">
                                    {{ sprintf('%02d:00', $hour) }}
                                </span>
                            </div>
                        @endfor
                    </div>

                    <!-- Days Grid -->
                    <div class="flex-1 grid grid-cols-7 relative">
                        @foreach($weekDays as $dayIndex => $day)
                            <div class="relative border-r border-[#1f1f35] last:border-r-0" style="height: 660px;">
                                <!-- Hour lines -->
                                @for($hour = 8; $hour <= 18; $hour++)
                                    <div class="absolute w-full h-[60px] border-b border-[#1f1f35]" style="top: {{ ($hour - 8) * 60 }}px;"></div>
                                @endfor

                                <!-- Events for this day -->
                                @php
                                    $dayEvents = $events[$day['date']] ?? [];
                                @endphp
                                @foreach($dayEvents as $event)
                                    @php
                                        // Calculate position: each hour is 60px, starting at 8am
                                        $hour = (int) substr($event['time'], 0, 2);
                                        $minute = (int) substr($event['time'], 3, 2);
                                        $top = (($hour - 8) * 60) + $minute;
                                        $height = max($event['duration'], 30); // minimum 30px height
                                    @endphp
                                    <button
                                        wire:click="selectEvent({{ $event['id'] }})"
                                        class="absolute mx-1 rounded p-1.5 text-xs text-left overflow-hidden transition-transform hover:scale-105 hover:z-10"
                                        style="top: {{ $top }}px; height: {{ $height }}px; left: 2px; right: 2px;"
                                    >
                                        <div class="font-medium truncate event-{{ $event['color'] }}">{{ $event['title'] }}</div>
                                        <div class="text-[10px] opacity-75">{{ $event['time'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini Calendar & Legend -->
        <div class="col-span-1 space-y-4">
            <!-- Mini Month Calendar -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <h3 class="text-sm font-semibold text-[#e4e4f0] mb-3">{{ now()->format('F Y') }}</h3>
                <div class="grid grid-cols-7 gap-1 text-center text-xs text-[#6b6b80] mb-1">
                    <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                    @php
                        $monthStart = now()->startOfMonth();
                        $startDay = $monthStart->dayOfWeek;
                    @endphp
                    @for($i = 0; $i < $startDay; $i++)
                        <div></div>
                    @endfor
                    @for($day = 1; $day <= now()->daysInMonth; $day++)
                        @php
                            $date = $monthStart->copy()->addDays($day - 1);
                            $isToday = $date->isToday();
                            $isCurrentWeek = $date->between(
                                \Carbon\Carbon::parse($currentWeekStart),
                                \Carbon\Carbon::parse($currentWeekStart)->endOfWeek()
                            );
                        @endphp
                        <div class="w-6 h-6 flex items-center justify-center rounded {{ $isToday ? 'bg-[#7c3aed] text-white' : ($isCurrentWeek ? 'bg-[#252542] text-[#e4e4f0]' : 'text-[#6b6b80]') }}">
                            {{ $day }}
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Event Type Legend -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <h3 class="text-sm font-semibold text-[#e4e4f0] mb-3">Event Types</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-[#ef4444]"></div>
                        <span class="text-[#a0a0b8]">Deadline</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-[#f59e0b]"></div>
                        <span class="text-[#a0a0b8]">Reminder</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-[#10b981]"></div>
                        <span class="text-[#a0a0b8]">Meeting</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-[#8b5cf6]"></div>
                        <span class="text-[#a0a0b8]">Task</span>
                    </div>
                </div>
            </div>

            <!-- Priority Legend -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <h3 class="text-sm font-semibold text-[#e4e4f0] mb-3">Priority</h3>
                <div class="space-y-2 text-xs text-[#a0a0b8]">
                    <div>⭐⭐⭐ Critical</div>
                    <div>⭐⭐ High</div>
                    <div>⭐ Normal</div>
                    <div>◇ Low</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    @if($selectedEventId && $selectedEvent)
        <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50" wire:click="clearSelection">
            <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] shadow-xl max-w-md w-full mx-4 p-5" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $selectedEvent->icon }}</span>
                        <div>
                            <h3 class="font-semibold text-[#e4e4f0]">{{ $selectedEvent->title }}</h3>
                            <p class="text-xs text-[#6b6b80]">{{ ucfirst($selectedEvent->type) }}</p>
                        </div>
                    </div>
                    <button wire:click="clearSelection" class="p-1 text-[#6b6b80] hover:text-[#e4e4f0]">
                        ✕
                    </button>
                </div>
                
                <div class="space-y-3">
                    <div class="bg-[#252542] rounded-lg p-3">
                        <span class="text-xs text-[#6b6b80] uppercase">Time</span>
                        <div class="text-sm text-[#e4e4f0] mt-1">
                            {{ $selectedEvent->start_time->format('l, F j, Y') }}<br>
                            {{ $selectedEvent->start_time->format('H:i') }}
                            @if($selectedEvent->end_time)
                                — {{ $selectedEvent->end_time->format('H:i') }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-[#252542] rounded-lg p-3 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-[#6b6b80] uppercase">Priority</span>
                            <div class="text-sm text-[#e4e4f0] mt-1">{{ $selectedEvent->priority_stars }}</div>
                        </div>
                        <span class="badge {{ $selectedEvent->status === 'completed' ? 'badge-success' : ($selectedEvent->status === 'in_progress' ? 'badge-info' : 'badge-warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $selectedEvent->status)) }}
                        </span>
                    </div>
                    
                    @if($selectedEvent->notes)
                        <div class="border-t border-[#2a2a40] pt-3">
                            <span class="text-xs text-[#6b6b80] uppercase">Notes</span>
                            <p class="text-sm text-[#a0a0b8] mt-1">{{ $selectedEvent->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>