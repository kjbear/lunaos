<?php

namespace App\Livewire\Team;

use App\Models\TeamMember;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

class TeamIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url]
    public string $activeTab = 'all'; // all | workers | personas | board-members
    public string $search = '';
    public string $filter = 'all'; // all | active | inactive
    public string $statusFilter = 'all'; // alias for filter (test compatibility)
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public array $stats = [];
    public array $headers = [];
    
    public function delete(string $id): void
    {
        $member = TeamMember::find($id);
        if ($member) {
            $name = $member->name;
            $member->delete();
            $this->dispatch('toast-success', message: "Team member '{$name}' deleted successfully.");
            $this->loadStats();
        }
    }

    public function deleteMember(string $id): void
    {
        $this->delete($id);
    }

    protected $rules = [
        'memberName' => 'required|min:2|max:100',
        'memberEmail' => 'nullable|email|max:255',
        'memberTitle' => 'nullable|max:100',
        'memberType' => 'required|in:workers,personas,board-members',
        'memberRole' => 'required|in:worker,persona,board_member',
        'memberStatus' => 'required|in:active,inactive,online,offline,error,busy,archived',
        'memberModel' => 'nullable|max:100',
        'memberProvider' => 'nullable|max:100',
        'memberAvatar' => 'nullable|max:255',
        'memberEmoji' => 'nullable|max:10',
        'memberSystemPrompt' => 'nullable|string',
    ];

    public function mount(): void
    {
        // Read tab from URL query param when #[Url] doesn't auto-populate
        $this->activeTab = request('tab', 'all');
        
        // Initialize table headers for x-table component
        $this->headers = [
            ['key' => 'name', 'label' => 'Member', 'sortBy' => 'asc'],
            ['key' => 'type', 'label' => 'Type', 'sortBy' => 'asc'],
            ['key' => 'role', 'label' => 'Role', 'sortBy' => 'asc'],
            ['key' => 'status', 'label' => 'Status', 'sortBy' => 'asc'],
            ['key' => 'model', 'label' => 'Model', 'sortBy' => 'asc'],
            ['key' => 'created_at', 'label' => 'Created', 'sortBy' => 'desc'],
        ];
        
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total' => TeamMember::count(),
            'active' => TeamMember::where('status', 'active')->count(),
            'workers' => TeamMember::where('type', 'workers')->count(),
            'personas' => TeamMember::where('type', 'personas')->count(),
            'board_members' => TeamMember::where('type', 'board-members')->count(),
            'by_model' => TeamMember::select('model', DB::raw('COUNT(*) as count'))
                ->whereNotNull('model')
                ->groupBy('model')
                ->pluck('count', 'model')
                ->toArray(),
            'by_status' => [
                'active' => TeamMember::where('status', 'active')->count(),
                'online' => TeamMember::where('status', 'online')->count(),
                'offline' => TeamMember::where('status', 'offline')->count(),
                'busy' => TeamMember::where('status', 'busy')->count(),
                'error' => TeamMember::where('status', 'error')->count(),
            ],
        ];
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        // Update URL query params
        $this->js(<<<JS
            window.history.pushState({tab: '{$tab}'}, '', '?' + new URLSearchParams({tab: '{$tab}'}))
        JS);
        $this->dispatch('tabChanged', tab: $tab);
    }

    #[On('refresh')]
    public function refresh(): void
    {
        $this->loadStats();
    }

    #[On('tabChanged')]
    public function onTabChanged(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function filterBy(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        redirect()->route('team.create');
    }

    // Computed property for members (required for Livewire pagination)
    public function getMembersProperty()
    {
        $query = TeamMember::query();

        // Filter by active tab
        if ($this->activeTab === 'workers') {
            $query->where('type', 'workers');
        } elseif ($this->activeTab === 'personas') {
            $query->where('type', 'personas');
        } elseif ($this->activeTab === 'board-members') {
            $query->where('type', 'board-members');
        }
        // if 'all' or empty, show all members (no filter)

        // Apply status filter (support both filter and statusFilter)
        $statusFilterValue = $this->statusFilter !== 'all' ? $this->statusFilter : $this->filter;
        if ($statusFilterValue === 'active') {
            $query->where('status', 'active');
        } elseif ($statusFilterValue === 'inactive') {
            $query->where('status', 'inactive');
        } elseif ($statusFilterValue === 'online') {
            $query->where('status', 'online');
        }

        // Apply search
        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        // Paginate
        return $query->paginate($this->perPage);
    }

    public function render()
    {
        // Explicitly call the computed property to ensure pagination works
        return view('livewire.team.team-index', [
            'members' => $this->getMembersProperty()
        ]);
    }
}
