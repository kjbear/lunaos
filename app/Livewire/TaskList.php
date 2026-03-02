<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Task;

#[Layout('components.layouts.app')]
class TaskList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';
    #[Url]
    public string $search = '';
    
    // Additional filters for blade compatibility
    public string $selectedAgent = 'all';
    public ?string $selectedStatus = null;

    // Sorting
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // View settings
    public int $perPage = 20;
    
    // View mode
    #[Url]
    public string $viewMode = 'list';
    
    // Agent counts for filtering
    public array $agentCounts = [];

    protected $listeners = ['refreshTasks' => 'refresh'];

    public function mount(): void
    {
        // Set up agent counts
        $this->agentCounts = [
            'all' => Task::count(),
            'dave' => Task::where('assigned_to', 'dave')->count(),
            'sam' => Task::where('assigned_to', 'sam')->count(),
            'chen' => Task::where('assigned_to', 'chen')->count(),
            'security' => Task::where('assigned_to', 'security')->count(),
        ];
        
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
    
    public function updatedSelectedAgent(): void
    {
        $this->resetPage();
    }
    
    public function updatedSelectedStatus(): void
    {
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
    
    public function setSort(string $field): void
    {
        $this->sortBy($field);
    }
    
    public function getSortDirection(string $field): string
    {
        if ($this->sortField !== $field) {
            return '';
        }
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    public function getSortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return '↕️';
        }
        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }
    
    public function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'complete' => 'Complete',
            'blocked' => 'Blocked',
            'failed' => 'Failed',
            default => ucfirst($status),
        };
    }
    
    // Computed properties for blade
    public function getTaskStatsProperty(): array
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

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->priorityFilter = 'all';
        $this->search = '';
        $this->selectedAgent = 'all';
        $this->selectedStatus = null;
        $this->resetPage();
    }
    
    public function viewTask(int $taskId): void
    {
        redirect()->route('tasks.show', $taskId);
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
        // Re-calculate agent counts on each render to ensure they're always available
        $agentCounts = [
            'all' => Task::count(),
            'dave' => Task::where('assigned_to', 'dave')->count(),
            'sam' => Task::where('assigned_to', 'sam')->count(),
            'chen' => Task::where('assigned_to', 'chen')->count(),
            'security' => Task::where('assigned_to', 'security')->count(),
        ];
        
        return view('livewire.task-list', [
            'stats' => $this->stats,
            'tasks' => $this->tasks,
            'agentCounts' => $agentCounts,
        ]);
    }
}
