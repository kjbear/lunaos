<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Requirement;
use App\Models\ProjectAssignment;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ProjectDetail extends Component
{
    public Project $project;
    public array $projectData = [];
    public array $requirements = [];
    public array $assignments = [];
    public array $activities = [];
    public array $stats = [];
    
    public bool $showNewRequirementModal = false;
    public string $newRequirementTitle = '';
    public string $newRequirementDescription = '';
    public string $newRequirementPriority = 'medium';
    
    public function mount(string $id): void
    {
        $this->project = Project::with(['assignments.persona', 'requirements'])->findOrFail($id);
        $this->loadProjectData();
        $this->loadRequirements();
        $this->loadAssignments();
        $this->loadActivities();
        $this->loadStats();
    }
    
    public function loadProjectData(): void
    {
        $this->projectData = [
            'id' => $this->project->id,
            'name' => $this->project->name,
            'description' => $this->project->description,
            'repo_url' => $this->project->repo_url,
            'health' => $this->project->health,
            'progress' => $this->project->progress ?? 0,
            'status' => $this->project->status,
            'owner' => $this->project->owner,
            'created_at' => $this->project->created_at?->format('M j, Y'),
            'updated_at' => $this->project->updated_at?->diffForHumans(),
        ];
    }
    
    public function loadRequirements(): void
    {
        try {
            $this->requirements = $this->project->requirements()
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'priority' => $r->priority,
                    'status' => $r->status,
                    'created_by' => $r->created_by,
                    'created_at' => $r->created_at?->diffForHumans(),
                ])
                ->toArray();
        } catch (\Exception $e) {
            $this->requirements = [];
        }
    }
    
    public function loadAssignments(): void
    {
        try {
            $this->assignments = $this->project->assignments()
                ->with('persona')
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'persona_name' => $a->persona?->name ?? 'Unknown',
                    'role' => $a->role,
                    'assigned_at' => $a->assigned_at ? 
                        ($a->assigned_at instanceof \Carbon\Carbon ? $a->assigned_at->diffForHumans() : $a->assigned_at) 
                        : 'Unknown',
                ])
                ->toArray();
        } catch (\Exception $e) {
            $this->assignments = [];
        }
    }
    
    public function loadActivities(): void
    {
        // Activity feed disabled until AgentActivity model/workflow integration is complete
        $this->activities = [];
    }
    
    public function loadStats(): void
    {
        $this->stats = [
            'total_requirements' => count($this->requirements),
            'completed_requirements' => collect($this->requirements)->where('status', 'completed')->count(),
            'in_progress' => collect($this->requirements)->where('status', 'in_progress')->count(),
            'ready' => collect($this->requirements)->where('status', 'ready')->count(),
            'team_size' => count($this->assignments),
        ];
    }
    
    public function createRequirement(): void
    {
        $this->validate([
            'newRequirementTitle' => 'required|string|max:255',
            'newRequirementDescription' => 'nullable|string',
            'newRequirementPriority' => 'required|in:low,medium,high,critical',
        ]);
        
        try {
            Requirement::create([
                'project_id' => $this->project->id,
                'title' => $this->newRequirementTitle,
                'description' => $this->newRequirementDescription,
                'priority' => $this->newRequirementPriority,
                'status' => 'draft',
                'created_by' => 'kyle',
            ]);
            
            $this->showNewRequirementModal = false;
            $this->newRequirementTitle = '';
            $this->newRequirementDescription = '';
            $this->newRequirementPriority = 'medium';
            
            $this->loadRequirements();
            $this->loadStats();
            
            $this->dispatch('notify', 
                title: 'Requirement Created', 
                message: 'New requirement added successfully',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                title: 'Error', 
                message: 'Failed to create requirement: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    public function updatedStatus(string $status): void
    {
        $this->project->update(['status' => $status]);
        
        $this->dispatch('notify', 
            title: 'Status Updated', 
            message: "Project status changed to {$status}",
            type: 'success'
        );
    }
    
    public function updatedProgress(int $progress): void
    {
        $this->project->update(['progress' => $progress]);
        
        $this->dispatch('notify', 
            title: 'Progress Updated', 
            message: "Progress set to {$progress}%",
            type: 'success'
        );
    }

    public function render()
    {
        return view('livewire.projects.project-detail');
    }
}
