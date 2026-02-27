<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\AgentActivity;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class ActivityFeed extends Component
{
    // Filters
    public string $agent = '';
    public string $actionType = '';
    public string $dateRange = 'today';
    public string $search = '';
    
    // State
    public bool $isLive = true;
    public int $pollInterval = 30;
    public int $limit = 50;
    
    // Modal
    public bool $showModal = false;
    public ?int $selectedActivityId = null;
    public $selectedActivity = null;
    
    // Data
    public $activities = [];
    public array $stats = [];
    public array $agents = [];
    public array $actionTypes = [];
    
    // Refresh listener for live mode
    protected $listeners = ['refreshFeed' => 'loadActivities'];

    public function mount(): void
    {
        $this->loadAgents();
        $this->loadActionTypes();
        $this->loadActivities();
        $this->loadStats();
        
        // Open activity from query parameter
        if (request()->query('activity')) {
            $this->showActivity((int) request()->query('activity'));
        }
    }

    public function loadAgents(): void
    {
        $this->agents = AgentActivity::query()
            ->select('agent_name')
            ->groupBy('agent_name')
            ->orderBy('agent_name')
            ->pluck('agent_name')
            ->toArray();
    }

    public function loadActionTypes(): void
    {
        $this->actionTypes = AgentActivity::query()
            ->select('action', DB::raw('COUNT(*) as count'))
            ->groupBy('action')
            ->orderByDesc('count')
            ->pluck('action')
            ->toArray();
    }

    public function loadActivities(): void
    {
        $query = AgentActivity::query()
            ->with('task')
            ->orderBy('created_at', 'desc')
            ->limit($this->limit);

        // Apply filters
        if (!empty($this->agent)) {
            $query->where('agent_name', $this->agent);
        }

        if (!empty($this->actionType)) {
            $query->where('action', $this->actionType);
        }

        // Date range filter
        $query = $this->applyDateRange($query);

        // Search
        if (!empty($this->search)) {
            $query->whereHas('task', function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $this->activities = $query->get();
    }

    public function loadStats(): void
    {
        $query = AgentActivity::query();
        $query = $this->applyDateRange($query);

        $activities = $query->get();

        $this->stats = [
            'total' => $activities->count(),
            'by_agent' => $activities->groupBy('agent_name')->map->count()->toArray(),
            'by_type' => $activities->groupBy('action')->map->count()->toArray(),
            'jordan_actions' => $activities->where('agent_name', 'jordan')->count(),
        ];
    }

    private function applyDateRange($query)
    {
        return match($this->dateRange) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', yesterday()),
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            'all' => $query,
            default => $query->whereDate('created_at', today()),
        };
    }

    public function toggleLive(): void
    {
        $this->isLive = !$this->isLive;
    }

    public function updated($property): void
    {
        // Reload when filters change
        if (in_array($property, ['agent', 'actionType', 'impact', 'status', 'dateRange', 'search'])) {
            $this->loadActivities();
            $this->loadStats();
        }
    }

    public function clearFilters(): void
    {
        $this->agent = '';
        $this->actionType = '';
        $this->impact = '';
        $this->status = '';
        $this->dateRange = 'today';
        $this->search = '';
        $this->loadActivities();
        $this->loadStats();
    }

    public function refresh(): void
    {
        // Trigger polling fallback (webhook is primary)
        $this->pollOpenClaw();
        
        $this->loadActivities();
        $this->loadStats();
    }

    /**
     * Show activity detail modal
     */
    public function showActivity(int $id): void
    {
        $this->selectedActivityId = $id;
        $this->selectedActivity = AgentActivity::with('task')->findOrFail($id);
        $this->showModal = true;
    }

    /**
     * Close activity detail modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedActivityId = null;
        $this->selectedActivity = null;
    }

    public function render()
    {
        return view('livewire.activity-feed');
    }
}