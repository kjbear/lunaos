<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectAssignment;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectsIndex extends Component
{
    public array $projects = [];
    public string $search = '';
    public string $filter = 'all';
    public array $stats = [];
    
    public bool $showNewProjectModal = false;
    public string $newProjectName = '';
    public string $newProjectDescription = '';
    public string $newProjectRepo = '';
    public string $newProjectStatus = 'planning';
    
    // Edit modal properties
    public bool $showEditModal = false;
    public ?string $editingProjectId = null;
    public string $editName = '';
    public string $editDescription = '';
    public string $editRepoUrl = '';
    public string $editStatus = 'active';
    public string $editHealth = 'healthy';
    public string $editArchitectureType = '';
    public string $editTechnologiesStr = '';

    public function mount(): void
    {
        $this->loadProjects();
        $this->loadStats();
        
        // Check if redirecting from board session with decision data
        $boardDecision = session('board_decision');
        if ($boardDecision) {
            $this->newProjectName = $boardDecision['title'] ?? '';
            $this->newProjectDescription = $boardDecision['description'] ?? '';
            $this->showNewProjectModal = true;
            
            // Clear the session so it doesn't persist on refresh
            session()->forget('board_decision');
            
            $this->dispatch('toast-success', message: 'Board decision loaded! Create a project from this recommendation.');
        }
    }

    public function loadProjects(): void
    {
        try {
            $query = Project::with('assignments');
            
            // Apply status filter
            if ($this->filter === 'active') {
                $query->where('status', 'active');
            } elseif ($this->filter === 'planning') {
                $query->where('status', 'planning');
            } elseif ($this->filter === 'completed') {
                $query->where('status', 'completed');
            } elseif ($this->filter === 'archived') {
                $query->whereNotNull('archived_at');
            } else {
                $query->whereNull('archived_at');
            }
            
            // Apply search
            if ($this->search) {
                $search = $this->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $this->projects = $query->orderBy('created_at', 'desc')->get()->map(function ($project) {
                $assignments = $project->assignments->map(function($a) {
                    return [
                        'persona_id' => $a->persona_id,
                        'role' => $a->role,
                    ];
                })->toArray();
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'repo_url' => $project->repo_url,
                    'health' => $project->health,
                    'progress' => $project->progress ?? 0,
                    'status' => $project->status,
                    'owner' => $project->owner,
                    'assignments' => $assignments,
                    'requirements_count' => $project->artifacts()->where('type', 'requirement')->count(),
                    'created_at' => $project->created_at?->diffForHumans() ?? 'Just now',
                ];
            })->toArray();
        } catch (\Illuminate\Database\QueryException $e) {
            // Tables don't exist - use sample data
            $this->projects = [
                [
                    'id' => 1,
                    'name' => 'LunaOS Dashboard',
                    'description' => 'Unified dashboard for Luna and subagent team visibility',
                    'repo_url' => 'https://github.com/lunaos/lunaos',
                    'health' => 'green',
                    'progress' => 64,
                    'status' => 'active',
                    'owner' => 'Luna',
                    'assignments' => [
                        ['persona_id' => 1, 'role' => 'PM'],
                        ['persona_id' => 2, 'role' => 'Backend Dev'],
                    ],
                    'requirements_count' => 23,
                    'created_at' => '2 weeks ago',
                ],
                [
                    'id' => 2,
                    'name' => 'Status Page Aggregator',
                    'description' => 'Multi-tenant SaaS status page aggregator + VRM',
                    'repo_url' => 'https://github.com/onewatch/cloud',
                    'health' => 'yellow',
                    'progress' => 41,
                    'status' => 'active',
                    'owner' => 'Kyle',
                    'assignments' => [
                        ['persona_id' => 1, 'role' => 'PM'],
                        ['persona_id' => 5, 'role' => 'API Dev'],
                    ],
                    'requirements_count' => 25,
                    'created_at' => '1 week ago',
                ],
            ];
        }
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total' => Project::whereNull('archived_at')->count(),
            'active' => Project::where('status', 'active')->whereNull('archived_at')->count(),
            'planning' => Project::where('status', 'planning')->whereNull('archived_at')->count(),
            'completed' => Project::where('status', 'completed')->whereNull('archived_at')->count(),
            'by_health' => [
                'healthy' => Project::where('health', 'healthy')->whereNull('archived_at')->count(),
                'at_risk' => Project::where('health', 'at_risk')->whereNull('archived_at')->count(),
                'blocked' => Project::where('health', 'blocked')->whereNull('archived_at')->count(),
            ],
        ];
    }

    public function filterBy(string $filter): void
    {
        $this->filter = $filter;
        $this->loadProjects();
    }

    public function updatedSearch(): void
    {
        $this->loadProjects();
    }

    public function createProject(): void
    {
        $this->validate([
            'newProjectName' => 'required|min:3',
        ]);

        $project = Project::create([
            'name' => $this->newProjectName,
            'description' => $this->newProjectDescription,
            'repo_url' => $this->newProjectRepo,
            'status' => $this->newProjectStatus,
            'health' => 'healthy',
            'progress' => 0,
        ]);

        $this->dispatch('toast-success', message: "Project '{$this->newProjectName}' created successfully.");
        $this->resetNewProjectForm();
        $this->loadProjects();
        $this->loadStats();
    }

    public function archiveProject(string $id): void
    {
        $project = Project::find($id);
        if ($project) {
            $name = $project->name;
            $project->update(['archived_at' => now()]);
            $this->dispatch('toast-info', message: "Project '{$name}' archived.");
            $this->loadProjects();
            $this->loadStats();
        }
    }

    public function editProject(string $id): void
    {
        $project = Project::find($id);
        if (!$project) return;
        
        $this->editingProjectId = $id;
        $this->editName = $project->name;
        $this->editDescription = $project->description ?? '';
        $this->editRepoUrl = $project->repo_url ?? '';
        $this->editStatus = $project->status;
        $this->editHealth = $project->health;
        $this->editArchitectureType = $project->architecture_type ?? '';
        $this->editTechnologiesStr = is_array($project->technologies) ? implode(', ', $project->technologies) : '';
        $this->showEditModal = true;
    }

    public function updateProject(): void
    {
        if (!$this->editingProjectId) return;
        
        $this->validate([
            'editName' => 'required|min:3|max:255',
            'editStatus' => 'required|in:planning,active,completed,archived',
            'editHealth' => 'required|in:healthy,at_risk,blocked',
            'editRepoUrl' => 'nullable|url',
            'editTechnologiesStr' => 'nullable|string',
        ]);

        $project = Project::find($this->editingProjectId);
        if (!$project) {
            $this->dispatch('toast-error', message: 'Project not found');
            return;
        }
        
        $project->update([
            'name' => $this->editName,
            'description' => $this->editDescription,
            'repo_url' => $this->editRepoUrl,
            'status' => $this->editStatus,
            'health' => $this->editHealth,
            'architecture_type' => $this->editArchitectureType,
            'technologies' => $this->editTechnologiesStr ? array_map('trim', explode(',', $this->editTechnologiesStr)) : null,
        ]);

        $this->dispatch('toast-success', message: "Project '{$this->editName}' updated successfully.");
        $this->closeEditModal();
        $this->loadProjects();
        $this->loadStats();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingProjectId = null;
        $this->editName = '';
        $this->editDescription = '';
        $this->editRepoUrl = '';
        $this->editStatus = 'active';
        $this->editHealth = 'healthy';
        $this->editArchitectureType = '';
        $this->editTechnologiesStr = '';
    }

    public function resetNewProjectForm(): void
    {
        $this->newProjectName = '';
        $this->newProjectDescription = '';
        $this->newProjectRepo = '';
        $this->newProjectStatus = 'planning';
        $this->showNewProjectModal = false;
    }

    public function render()
    {
        return view('livewire.projects.projects-index', [
            'projects' => $this->projects,
            'search' => $this->search,
            'filter' => $this->filter,
            'stats' => $this->stats,
            'showNewProjectModal' => $this->showNewProjectModal,
            'newProjectName' => $this->newProjectName,
            'newProjectDescription' => $this->newProjectDescription,
            'newProjectRepo' => $this->newProjectRepo,
            'newProjectStatus' => $this->newProjectStatus,
            'showEditModal' => $this->showEditModal,
            'editingProject' => $this->editingProjectId,
            'editName' => $this->editName,
            'editDescription' => $this->editDescription,
            'editRepoUrl' => $this->editRepoUrl,
            'editStatus' => $this->editStatus,
            'editHealth' => $this->editHealth,
            'editArchitectureType' => $this->editArchitectureType,
            'editTechnologiesStr' => $this->editTechnologiesStr,
        ]);
    }
}