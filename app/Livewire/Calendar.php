<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\ScheduledItem;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Calendar extends Component
{
    public string $currentWeekStart;
    public array $events = [];
    public array $weekDays = [];
    public ?int $selectedEventId = null;

    public function mount(): void
    {
        $this->currentWeekStart = now()->startOfWeek()->format('Y-m-d');
        $this->loadWeek();
    }

    public function loadWeek(): void
    {
        $startOfWeek = Carbon::parse($this->currentWeekStart);
        
        // Generate week days
        $this->weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $this->weekDays[] = [
                'date' => $day->format('Y-m-d'),
                'dayName' => $day->format('D'),
                'dayNum' => $day->format('j'),
                'month' => $day->format('M'),
                'isToday' => $day->isToday(),
            ];
        }

        // Load events for the week
        $events = ScheduledItem::forCalendar($this->currentWeekStart)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_time->format('Y-m-d'),
                    'time' => $event->start_time->format('H:i'),
                    'hour' => (int) $event->start_time->format('H'),
                    'duration' => $event->end_time 
                        ? $event->start_time->diffInMinutes($event->end_time)
                        : 60,
                    'color' => $event->color,
                    'icon' => $event->icon,
                    'priority' => $event->priority_stars,
                    'description' => $event->notes,
                    'type' => $event->type,
                ];
            })
            ->groupBy('date')
            ->toArray();

        $this->events = $events;
    }

    public function previousWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)
            ->subWeek()
            ->startOfWeek()
            ->format('Y-m-d');
        $this->loadWeek();
    }

    public function nextWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)
            ->addWeek()
            ->startOfWeek()
            ->format('Y-m-d');
        $this->loadWeek();
    }

    public function goToToday(): void
    {
        $this->currentWeekStart = now()->startOfWeek()->format('Y-m-d');
        $this->loadWeek();
    }

    public function selectEvent(int $eventId): void
    {
        $this->selectedEventId = $eventId;
    }

    public function clearSelection(): void
    {
        $this->selectedEventId = null;
    }

    public function getSelectedEventProperty(): ?ScheduledItem
    {
        return $this->selectedEventId 
            ? ScheduledItem::find($this->selectedEventId)
            : null;
    }

    public function getWeekTitleProperty(): string
    {
        $start = Carbon::parse($this->currentWeekStart);
        $end = $start->copy()->endOfWeek();
        
        if ($start->month === $end->month) {
            return $start->format('F Y');
        }
        
        return $start->format('F') . ' - ' . $end->format('F Y');
    }

    public function render()
    {
        return view('livewire.calendar');
    }
}