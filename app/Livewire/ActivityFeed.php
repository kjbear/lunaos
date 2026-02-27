<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class ActivityFeed extends Component
{
    // Filters
    public string $agent = '';
    public string $actionType = '';
    public string $impact = '';
    public string $status = '';
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
        $this->agents = ActivityLog::query()
            ->select('agent', DB::raw('COUNT(*) as count'))
            ->groupBy('agent')
            ->orderByDesc('count')
            ->pluck('agent')
            ->toArray();
    }

    public function loadActionTypes(): void
    {
        $this->actionTypes = ActivityLog::query()
            ->select('action_type', DB::raw('COUNT(*) as count'))
            ->groupBy('action_type')
            ->orderByDesc('count')
            ->pluck('action_type')
            ->toArray();
    }

    public function loadActivities(): void
    {
        $query = ActivityLog::query()
            ->orderBy('created_at', 'desc')
            ->limit($this->limit);

        // Apply filters
        if (!empty($this->agent)) {
            $query->where('agent', $this->agent);
        }

        if (!empty($this->actionType)) {
            $query->where('action_type', $this->actionType);
        }

        if (!empty($this->impact)) {
            $query->where('impact', $this->impact);
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        // Date range filter
        $query = $this->applyDateRange($query);

        // Search (using FTS5)
        if (!empty($this->search)) {
            $searchTerm = $this->search . '*';
            $query->whereRaw("id IN (SELECT rowid FROM activity_logs_fts WHERE activity_logs_fts MATCH ?)", [$searchTerm]);
        }

        $this->activities = $query->get();
    }

    public function loadStats(): void
    {
        $query = ActivityLog::query();
        $query = $this->applyDateRange($query);

        $activities = $query->get();

        $this->stats = [
            'total' => $activities->count(),
            'by_agent' => $activities->groupBy('agent')->map->count()->toArray(),
            'by_type' => $activities->groupBy('action_type')->map->count()->toArray(),
            'by_impact' => $activities->groupBy('impact')->map->count()->toArray(),
            'by_status' => $activities->groupBy('status')->map->count()->toArray(),
            'high_impact' => $activities->where('impact', 'high')->count(),
            'failed' => $activities->where('status', 'failed')->count(),
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
        $this->selectedActivity = ActivityLog::findOrFail($id);
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

    /**
     * Poll OpenClaw for activity (fallback when webhook not active)
     */
    private function pollOpenClaw(): void
    {
        if (!config('lunaos.polling_enabled', true)) {
            return;
        }

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $client->post(url('/api/activity/poll'));
        } catch (\Exception $e) {
            // Silently fail - polling is just a fallback
            \Log::debug('OpenClaw polling failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.activity-feed');
    }
}