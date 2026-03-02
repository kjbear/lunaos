<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Task;

#[Layout('components.layouts.app')]
#[\Livewire\Attributes\Url]
class TaskList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';
    public string $search = '';

    // Sorting
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // View settings
    public int $perPage = 20;
    
    // View mode
    public string $viewMode = 'list';

    protected $listeners = ['refreshTasks' => 'refresh'];

    public function mount(): void
    {
        // Load any query parameters
        if ($search = request()->query('search')) {
            $this->search = $search;
        }
        
        // Load view_mode from query string
        if ($viewMode = request()->query('view_mode')) {
            $this->viewMode = $viewMode;
        }
    }

    public function updateViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function updated($property): void
    {
        // Reset pagination when filters change
        $this->resetPage();
    }

    public function refresh(): void
    {
        // Triggered on refresh
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
        $this->priorityFilter = 'all';
        $this->search = '';
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed' => Task::where('status', 'complete')->count(),
            'failed' => Task::where('status', 'failed')->count(),
            'blocked' => Task::where('status', 'blocked')->count(),
        ];
    }

    public function getTasksProperty()
    {
        $query = Task::query();

        // Apply search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        // Apply filters
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function __invoke()
    {
        return $this->render();
    }

    public function render()
    {
        return view('livewire.task-list', [
            'stats' => $this->stats,
            'tasks' => $this->tasks,
        ]);
    }
}
