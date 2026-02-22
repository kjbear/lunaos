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
                <div class="grid grid-cols-7 border-b border-[#2a2a40]">
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
                <div class="relative" style="height: 600px; overflow-y: auto;">
                    <!-- Hour Lines -->
                    <div class="absolute inset-0">
                        @for($hour = 8; $hour <= 18; $hour++)
                            <div class="h-[60px] border-b border-[#1f1f35] relative">
                                <span class="absolute left-0 -top-2 text-xs text-[#6b6b80] w-12 text-right pr-2">
                                    {{ sprintf('%02d:00', $hour) }}
                                </span>
                            </div>
                        @endfor
                    </div>

                    <!-- Events Grid -->
                    <div class="grid grid-cols-7 ml-12 relative">
                        @foreach($weekDays as $dayIndex => $day)
                            <div class="col-span-1 relative" style="height: 660px;">
                                @php
                                    $dayEvents = $events[$day['date']] ?? [];
                                @endphp
                                @foreach($dayEvents as $event)
                                    @php
                                        $top = (($event['hour'] - 8) * 60) + (int)substr($event['time'], 3, 2);
                                        $height = min($event['duration'], 60);
                                    @endphp
                                    <button
                                        wire:click="selectEvent({{ $event['id'] }})"
                                        class="absolute left-1 right-1 rounded-md p-2 text-xs text-left transition-transform hover:scale-105 event-{{ $event['color'] }}"
                                        style="top: {{ $top }}px; min-height: {{ $height }}px;"
                                    >
                                        <div class="font-medium truncate">{{ $event['title'] }}</div>
                                        <div class="text-xs opacity-75">{{ $event['time'] }}</div>
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
                        <div class="w-3 h-3 rounded-sm event-cron"></div>
                        <span class="text-[#a0a0b8]">Cron Jobs</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm event-reminder"></div>
                        <span class="text-[#a0a0b8]">Reminders</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm event-calendar"></div>
                        <span class="text-[#a0a0b8]">Calendar Events</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm event-email"></div>
                        <span class="text-[#a0a0b8]">Email Tasks</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm event-task"></div>
                        <span class="text-[#a0a0b8]">Plane Tasks</span>
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
                            <p class="text-xs text-[#6b6b80]">{{ ucfirst($selectedEvent->source_type) }}</p>
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
                            {{ $selectedEvent->starts_at->format('l, F j, Y') }}<br>
                            {{ $selectedEvent->starts_at->format('H:i') }}
                            @if($selectedEvent->ends_at)
                                — {{ $selectedEvent->ends_at->format('H:i') }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-[#252542] rounded-lg p-3 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-[#6b6b80] uppercase">Priority</span>
                            <div class="text-sm text-[#e4e4f0] mt-1">{{ $selectedEvent->priority_stars }}</div>
                        </div>
                        <span class="badge {{ $selectedEvent->status === 'completed' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($selectedEvent->status) }}
                        </span>
                    </div>
                    
                    @if($selectedEvent->description)
                        <div class="border-t border-[#2a2a40] pt-3">
                            <span class="text-xs text-[#6b6b80] uppercase">Description</span>
                            <p class="text-sm text-[#a0a0b8] mt-1">{{ $selectedEvent->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>