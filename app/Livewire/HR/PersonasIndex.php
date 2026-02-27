<?php

namespace App\Livewire\HR;

use App\Models\Persona;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PersonasIndex extends Component
{
    public array $personas = [];
    public string $filter = 'all';
    public string $search = '';
    public bool $showEditModal = false;
    public bool $showCreateModal = false;
    public ?string $selectedPersonaId = null;
    
    // Stats
    public array $stats = [];
    
    // Form fields
    public string $personaName = '';
    public string $personaRole = 'custom';
    public string $personaModel = 'haiku';
    public string $personaAvatar = '🤖';
    public string $personaInspiration = '';
    public string $personaSystemPrompt = '';
    
    protected $rules = [
        'personaName' => 'required|min:2',
        'personaRole' => 'required|in:subagent,board_member,custom',
        'personaModel' => 'required|in:dolphin,haiku,glm-5',
        'personaAvatar' => 'nullable|string',
        'personaInspiration' => 'nullable|string',
        'personaSystemPrompt' => 'nullable|string',
    ];

    public function mount(): void
    {
        $this->loadPersonas();
        $this->loadStats();
    }

    public function loadPersonas(): void
    {
        $query = Persona::with('metrics');
        
        // Apply role filter
        if ($this->filter === 'active') {
            $query->active();
        } elseif ($this->filter === 'subagents') {
            $query->subagents();
        } elseif ($this->filter === 'board') {
            $query->boardMembers();
        } elseif ($this->filter === 'custom') {
            $query->custom();
        }
        
        // Apply search
        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('inspiration', 'like', "%{$search}%")
                  ->orWhere('system_prompt', 'like', "%{$search}%");
            });
        }
        
        $this->personas = $query->orderBy('name')->get()->map(function ($persona) {
            return [
                'id' => $persona->id,
                'name' => $persona->name,
                'role' => $persona->role,
                'model' => $persona->model,
                'avatar' => $persona->avatar ?? '🤖',
                'status' => $persona->status,
                'inspiration' => $persona->inspiration,
                'metrics' => $persona->metrics ? [
                    'projects_count' => $persona->metrics->projects_count,
                    'tasks_completed' => $persona->metrics->tasks_completed,
                    'success_rate' => round($persona->metrics->success_rate, 1),
                    'sessions_count' => $persona->metrics->sessions_count,
                    'decisions_count' => $persona->metrics->decisions_count,
                ] : null,
            ];
        })->toArray();
    }
    
    public function loadStats(): void
    {
        $this->stats = [
            'total' => Persona::count(),
            'active' => Persona::where('status', 'active')->count(),
            'subagents' => Persona::where('role', 'subagent')->count(),
            'board_members' => Persona::where('role', 'board_member')->count(),
            'by_model' => Persona::select('model', DB::raw('COUNT(*) as count'))
                ->groupBy('model')
                ->pluck('count', 'model')
                ->toArray(),
        ];
    }

    public function filterBy(string $filter): void
    {
        $this->filter = $filter;
        $this->loadPersonas();
    }
    
    public function updatedSearch(): void
    {
        $this->loadPersonas();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function edit(string $id): void
    {
        $persona = Persona::find($id);
        if (!$persona) return;
        
        $this->selectedPersonaId = $id;
        $this->personaName = $persona->name;
        $this->personaRole = $persona->role;
        $this->personaModel = $persona->model;
        $this->personaAvatar = $persona->avatar ?? '🤖';
        $this->personaInspiration = $persona->inspiration ?? '';
        $this->personaSystemPrompt = $persona->system_prompt ?? '';
        $this->showEditModal = true;
    }

    public function save(): void
    {
        $this->validate();
        
        $data = [
            'name' => $this->personaName,
            'role' => $this->personaRole,
            'model' => $this->personaModel,
            'avatar' => $this->personaAvatar,
            'inspiration' => $this->personaInspiration ?: null,
            'system_prompt' => $this->personaSystemPrompt ?: null,
        ];
        
        if ($this->showEditModal && $this->selectedPersonaId) {
            $persona = Persona::find($this->selectedPersonaId);
            $persona->update($data);
            $this->dispatch('toast-success', message: "Persona '{$this->personaName}' updated successfully.");
        } else {
            $persona = Persona::create($data);
            // Create metrics
            $persona->metrics()->create([]);
            $this->dispatch('toast-success', message: "Persona '{$this->personaName}' created successfully.");
        }
        
        $this->resetForm();
        $this->loadPersonas();
        $this->loadStats();
    }

    public function deactivate(string $id): void
    {
        $persona = Persona::find($id);
        if ($persona) {
            $name = $persona->name;
            $persona->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);
            $this->dispatch('toast-info', message: "Persona '{$name}' deactivated.");
            $this->loadPersonas();
            $this->loadStats();
        }
    }

    public function resetForm(): void
    {
        $this->selectedPersonaId = null;
        $this->personaName = '';
        $this->personaRole = 'custom';
        $this->personaModel = 'haiku';
        $this->personaAvatar = '🤖';
        $this->personaInspiration = '';
        $this->personaSystemPrompt = '';
        $this->showCreateModal = false;
        $this->showEditModal = false;
    }

    public function render()
    {
        return view('livewire.hr.personas-index');
    }
}