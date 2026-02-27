<?php

namespace App\Livewire\HR;

use App\Models\Persona;
use App\Models\PersonaWorkspace as PersonaWorkspaceModel;
use Livewire\Component;

class PersonaWorkspaceViewer extends Component
{
    public string $id;
    public string $personaId;
    public array $persona = [];
    public array $files = [];
    public string $selectedFile = 'AGENTS.md';
    public string $content = '';
    
    public function mount(): void
    {
        $this->personaId = $this->id;
        $this->loadPersona();
        $this->loadFiles();
    }

    public function loadPersona(): void
    {
        $persona = Persona::find($this->personaId);
        if ($persona) {
            $this->persona = [
                'id' => $persona->id,
                'name' => $persona->name,
                'avatar' => $persona->avatar ?? '🤖',
                'role' => $persona->role,
                'model' => $persona->model,
            ];
        }
    }

    public function loadFiles(): void
    {
        $workspaces = PersonaWorkspaceModel::where('persona_id', $this->personaId)->get();
        $this->files = $workspaces->pluck('file_name')->toArray();
        
        if (empty($this->files)) {
            $this->files = ['AGENTS.md', 'TOOLS.md'];
        }
        
        $this->selectFile($this->selectedFile);
    }

    public function selectFile(string $fileName): void
    {
        $this->selectedFile = $fileName;
        $workspace = PersonaWorkspaceModel::where('persona_id', $this->personaId)
            ->where('file_name', $fileName)
            ->first();
        
        $this->content = $workspace?->content ?? '# No content yet';
    }

    public function sync(): void
    {
        $persona = Persona::find($this->personaId);
        if (!$persona || !$persona->workspace_path) {
            return;
        }

        foreach (['AGENTS.md', 'TOOLS.md'] as $fileName) {
            $filePath = rtrim($persona->workspace_path, '/') . '/' . $fileName;
            
            if (file_exists($filePath)) {
                PersonaWorkspaceModel::updateOrCreate(
                    ['persona_id' => $this->personaId, 'file_name' => $fileName],
                    ['content' => file_get_contents($filePath), 'last_synced_at' => now()]
                );
            }
        }
        
        $this->loadFiles();
    }

    public function render()
    {
        return view('livewire.hr.persona-workspace');
    }
}