<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WorkspaceService;

class WorkspaceViewer extends Component
{
    public ?string $selectedFile = null;
    public ?string $fileContent = null;
    public ?string $filePath = null;
    public ?string $fileModified = null;
    public array $files = [];

    protected WorkspaceService $workspaceService;

    public function boot(WorkspaceService $workspaceService): void
    {
        $this->workspaceService = $workspaceService;
    }

    public function mount(): void
    {
        $this->files = $this->workspaceService->listFiles();
        
        // Select first file by default
        if (!empty($this->files) && !$this->selectedFile) {
            $this->selectFile($this->files[0]['path']);
        }
    }

    public function selectFile(string $path): void
    {
        $file = $this->workspaceService->readFile($path);

        if ($file) {
            $this->selectedFile = $file['name'];
            $this->filePath = $file['path'];
            $this->fileContent = $file['content'];
            $this->fileModified = $file['modified'];
        }
    }

    public function refresh(): void
    {
        $this->files = $this->workspaceService->listFiles();
    }

    public function render()
    {
        return view('livewire.workspace-viewer');
    }
}