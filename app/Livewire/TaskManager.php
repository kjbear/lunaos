<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Task;
use App\Models\Agent;

class TaskManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

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

    // Pagination
    public int $perPage = 20;

    protected $listeners = ['refreshTasks' => 'refresh'];

    public function mount(): void
    {
        $this->lastRefreshed = now()->format('H:i:s');
    }

    public function updated($property): void
    {
        // Reset pagination when filters change
        $this->resetPage();
    }

    public function refresh(): void
    {
        $this->lastRefreshed = now()->format('H:i:s');
    }

    public function toggleLive(): void
    {
        $this->isLive = !$this->isLive;

        if ($this->isLive) {
            $this->lastRefreshed = now()->format('H:i:s');
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->agentFilter = null;
        $this->priorityFilter = 'all';
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => Task::count(),
            'running' => Task::running()->count(),
            'pending' => Task::pending()->count(),
            'completed' => Task::completed()->count(),
            'totalTokens' => Task::sum('tokens_used'),
            'totalCost' => number_format(Task::sum('cost'), 2),
        ];
    }

    public function getAgentsProperty()
    {
        return Agent::select('id', 'name')->get();
    }

    public function getTasksProperty()
    {
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

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.task-manager');
    }
}