<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Agent;

class TaskManager extends Component
{
    // Filters
    public string $statusFilter = 'all';
    public ?int $agentFilter = null;
    public string $priorityFilter = 'all';

    // Sorting
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Live updates
    public bool $isLive = true;
    public ?string $lastRefreshed = null;

    // Stats
    public int $totalTasks = 0;
    public int $activeTasks = 0;
    public int $pendingTasks = 0;
    public int $completedTasks = 0;
    public int $totalTokens = 0;
    public string $totalCost = '0.00';

    // Data
    public $tasks;
    public $agents;

    protected $listeners = ['refreshTasks' => '$refresh'];

    public function mount(): void
    {
        $this->loadData();
        $this->lastRefreshed = now()->format('H:i:s');
    }

    public function updated($property): void
    {
        // Reload data when filters change
        $this->loadData();
    }

    public function loadData(): void
    {
        // Load stats
        $this->totalTasks = Task::count();
        $this->activeTasks = Task::running()->count();
        $this->pendingTasks = Task::pending()->count();
        $this->completedTasks = Task::completed()->count();
        $this->totalTokens = Task::sum('tokens_used');
        $this->totalCost = number_format(Task::sum('cost'), 2);

        // Load agents for filter
        $this->agents = Agent::select('id', 'name')->get();

        // Build query
        $query = Task::with('agent');

        // Apply filters
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->agentFilter) {
            $query->where('agent_id', $this->agentFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $this->tasks = $query->limit(50)->get();
        $this->lastRefreshed = now()->format('H:i:s');
    }

    public function toggleLive(): void
    {
        $this->isLive = !$this->isLive;

        if ($this->isLive) {
            $this->loadData();
        }
    }

    public function refresh(): void
    {
        $this->loadData();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }

        $this->loadData();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->agentFilter = null;
        $this->priorityFilter = 'all';
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.task-manager');
    }
}