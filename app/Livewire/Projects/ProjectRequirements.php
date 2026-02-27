<?php

namespace App\Livewire\Projects;

use App\Models\Requirement;
use App\Models\Project;
use Livewire\Component;

class ProjectRequirements extends Component
{
    public string $id;
    public array $project = [];
    public array $requirements = [];
    
    public bool $showAddModal = false;
    public string $newTitle = '';
    public string $newDescription = '';
    public string $newPriority = 'medium';

    public function mount(): void
    {
        $this->loadProject();
        $this->loadRequirements();
    }

    public function loadProject(): void
    {
        $project = Project::find($this->id);
        if ($project) {
            $this->project = [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
            ];
        }
    }

    public function loadRequirements(): void
    {
        $requirements = Requirement::where('project_id', $this->id)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->requirements = $requirements->map(function ($req) {
            return [
                'id' => $req->id,
                'title' => $req->title,
                'description' => $req->description,
                'priority' => $req->priority,
                'status' => $req->status,
                'created_at' => $req->created_at?->diffForHumans() ?? 'Just now',
            ];
        })->toArray();
    }

    public function add(): void
    {
        $this->validate([
            'newTitle' => 'required|min:3',
        ]);

        Requirement::create([
            'project_id' => $this->id,
            'title' => $this->newTitle,
            'description' => $this->newDescription,
            'priority' => $this->newPriority,
            'status' => 'draft',
        ]);

        $this->resetAddForm();
        $this->loadRequirements();
    }

    public function approve(string $reqId): void
    {
        $req = Requirement::find($reqId);
        if ($req) {
            $req->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            $this->loadRequirements();
        }
    }

    public function prioritize(string $reqId, string $priority): void
    {
        $req = Requirement::find($reqId);
        if ($req) {
            $req->update(['priority' => $priority]);
            $this->loadRequirements();
        }
    }

    public function resetAddForm(): void
    {
        $this->newTitle = '';
        $this->newDescription = '';
        $this->newPriority = 'medium';
        $this->showAddModal = false;
    }

    public function render()
    {
        return view('livewire.projects.project-requirements');
    }
}