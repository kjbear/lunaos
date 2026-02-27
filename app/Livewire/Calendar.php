<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class Calendar extends Component
{
    public int $currentMonth;
    public int $currentYear;
    public array $days = [];
    public array $events = [];
    public ?int $selectedDay = null;

    public function mount(): void
    {
        $today = Carbon::now();
        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;
        $this->loadMonth();
    }

    public function loadMonth(): void
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startGrid = $firstDay->copy()->startOfWeek();
        $endGrid = $lastDay->copy()->endOfWeek();

        // Sample events (no database dependency)
        $this->events = [
            [
                [
                    'id' => 1,
                    'title' => 'Team Standup',
                    'date' => Carbon::now()->format('Y-m-d'),
                    'time' => '10:00',
                    'type' => 'standup',
                    'description' => 'Daily team sync',
                ],
                [
                    'id' => 2,
                    'title' => 'LunaOS Sprint Review',
                    'date' => Carbon::now()->format('Y-m-d'),
                    'time' => '14:00',
                    'type' => 'meeting',
                    'description' => 'Review UI redesign',
                ],
            ],
        ];

        // Generate calendar grid
        $this->days = [];
        $currentDay = $startGrid->copy();
        
        while ($currentDay <= $endGrid) {
            $dayEvents = [];
            foreach ($this->events[0] ?? [] as $event) {
                if (Carbon::parse($event['date'])->format('j-n-Y') === $currentDay->format('j-n-Y')) {
                    $dayEvents[] = $event;
                }
            }
            
            $this->days[] = [
                'day' => (int) $currentDay->format('j'),
                'month' => (int) $currentDay->format('n'),
                'is_today' => $currentDay->isToday(),
                'events' => $dayEvents,
            ];
            $currentDay->addDay();
        }
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->loadMonth();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->loadMonth();
    }

    public function goToToday(): void
    {
        $today = Carbon::now();
        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;
        $this->loadMonth();
    }

    public function selectDay(int $day): void
    {
        $this->selectedDay = $day;
    }

    public function getMonthNameProperty(): string
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F');
    }

    public function render()
    {
        return view('livewire.calendar');
    }
}