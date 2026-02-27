<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Task;
use App\Models\Agent;
use App\Models\ActivityLog;

#[Layout('layouts.app')]
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

    // Highlight from search
    public ?int $highlight = null;
    
    // Modal
    public bool $showModal = false;
    public ?int $selectedTaskId = null;
    public $selectedTask = null;
    public $taskActivities = [];

    protected $listeners = ['refreshTasks' => 'refresh'];

    public function mount(): void
    {
        $this->lastRefreshed = now()->format('H:i:s');
        $this->highlight = request()->query('highlight');
        
        // Open task from query parameter
        if (request()->query('task')) {
            $this->showTask((int) request()->query('task'));
        }
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

    /**
     * Show task detail modal
     */
    public function showTask(int $id): void
    {
        $this->selectedTaskId = $id;
        $this->selectedTask = Task::with('agent')->findOrFail($id);
        $this->taskActivities = $this->getRelatedActivities($this->selectedTask);
        $this->showModal = true;
    }

    /**
     * Close task detail modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedTaskId = null;
        $this->selectedTask = null;
        $this->taskActivities = [];
    }

    /**
     * Get activities related to this task
     */
    private function getRelatedActivities(Task $task): array
    {
        // Look for activities from the same agent within the task timeframe
        $query = ActivityLog::query()
            ->where('agent', $task->agent->name ?? 'Luna')
            ->orderBy('created_at', 'desc');

        // If task has started_at, use that as a starting point
        if ($task->started_at) {
            $query->where('created_at', '>=', $task->started_at);
        } else {
            // Otherwise, get activities from the task creation date
            $query->where('created_at', '>=', $task->created_at);
        }

        // If task is completed, limit to activities before completion
        if ($task->completed_at) {
            $query->where('created_at', '<=', $task->completed_at);
        }

        return $query->limit(20)->get()->toArray();
    }

    public function render()
    {
        return view('livewire.task-manager', [
            'stats' => $this->stats,
            'agents' => $this->agents,
            'tasks' => $this->tasks,
        ]);
    }
}