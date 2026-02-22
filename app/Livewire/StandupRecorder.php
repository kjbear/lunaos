<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Standup;
use App\Models\StandupDeliverable;
use App\Models\StandupActionItem;
use Carbon\Carbon;

class StandupRecorder extends Component
{
    // Form fields
    public string $date = '';
    public string $team = 'LunaOS Team';
    public string $facilitator = 'Luna';
    public string $transcript = '';
    
    // Lists
    public array $deliverables = [];
    public array $actionItems = [];
    
    // State
    public bool $isRecording = false;
    public int $recordingTime = 0;
    public ?int $editingStandupId = null;
    public bool $showHistory = false;
    
    // History
    public $recentStandups = [];

    protected $rules = [
        'date' => 'required|date',
        'team' => 'required|string|max:100',
        'facilitator' => 'nullable|string|max:100',
        'transcript' => 'nullable|string',
        'deliverables' => 'array',
        'deliverables.*.title' => 'required|string|max:255',
        'actionItems' => 'array',
        'actionItems.*.title' => 'required|string|max:255',
        'actionItems.*.assigned_to' => 'nullable|string|max:100',
    ];

    public function mount(): void
    {
        $this->date = Carbon::now()->format('Y-m-d');
        $this->loadRecentStandups();
    }

    public function loadRecentStandups(): void
    {
        $this->recentStandups = Standup::with(['deliverables', 'actionItems'])
            ->recent(30)
            ->get();
    }

    public function addDeliverable(): void
    {
        $this->deliverables[] = [
            'title' => '',
            'order' => count($this->deliverables),
        ];
    }

    public function removeDeliverable(int $index): void
    {
        unset($this->deliverables[$index]);
        $this->deliverables = array_values($this->deliverables);
    }

    public function addActionItem(): void
    {
        $this->actionItems[] = [
            'title' => '',
            'assigned_to' => '',
            'completed' => false,
        ];
    }

    public function removeActionItem(int $index): void
    {
        unset($this->actionItems[$index]);
        $this->actionItems = array_values($this->actionItems);
    }

    public function toggleRecording(): void
    {
        $this->isRecording = !$this->isRecording;
        
        if ($this->isRecording) {
            $this->recordingTime = 0;
        }
    }

    public function save(): void
    {
        $this->validate();

        $standup = Standup::create([
            'date' => $this->date,
            'team' => $this->team,
            'facilitator' => $this->facilitator ?: null,
            'transcript' => $this->transcript ?: null,
            'status' => Standup::STATUS_COMPLETED,
        ]);

        foreach ($this->deliverables as $index => $deliverable) {
            if (!empty($deliverable['title'])) {
                StandupDeliverable::create([
                    'standup_id' => $standup->id,
                    'title' => $deliverable['title'],
                    'order' => $index,
                ]);
            }
        }

        foreach ($this->actionItems as $item) {
            if (!empty($item['title'])) {
                StandupActionItem::create([
                    'standup_id' => $standup->id,
                    'title' => $item['title'],
                    'assigned_to' => $item['assigned_to'] ?: null,
                    'completed' => false,
                ]);
            }
        }

        $this->reset(['deliverables', 'actionItems', 'transcript']);
        $this->date = Carbon::now()->format('Y-m-d');
        $this->loadRecentStandups();
        
        session()->flash('message', 'Standup saved successfully!');
    }

    public function loadStandup(int $id): void
    {
        $standup = Standup::with(['deliverables', 'actionItems'])->findOrFail($id);
        
        $this->editingStandupId = $id;
        $this->date = $standup->date->format('Y-m-d');
        $this->team = $standup->team;
        $this->facilitator = $standup->facilitator ?? 'Luna';
        $this->transcript = $standup->transcript ?? '';
        $this->deliverables = $standup->deliverables->map(fn($d) => [
            'title' => $d->title,
            'order' => $d->order,
        ])->toArray();
        $this->actionItems = $standup->actionItems->map(fn($a) => [
            'title' => $a->title,
            'assigned_to' => $a->assigned_to ?? '',
            'completed' => $a->completed,
        ])->toArray();
        
        $this->showHistory = false;
    }

    public function toggleHistory(): void
    {
        $this->showHistory = !$this->showHistory;
    }

    public function render()
    {
        return view('livewire.standup-recorder');
    }
}