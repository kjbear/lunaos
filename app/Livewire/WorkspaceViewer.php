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
    public string $search = '';
    public string $filter = 'all';
    public array $stats = [];

    protected WorkspaceService $workspaceService;

    public function boot(WorkspaceService $workspaceService): void
    {
        $this->workspaceService = $workspaceService;
    }

    public function mount(): void
    {
        $this->loadFiles();
        $this->loadStats();
        
        // Select first file by default
        if (!empty($this->files) && !$this->selectedFile) {
            $this->selectFile($this->files[0]['path']);
        }
    }
    
    public function loadFiles(): void
    {
        $files = $this->workspaceService->listFiles();
        
        // Apply search filter
        if ($this->search) {
            $files = array_filter($files, function($file) {
                return stripos($file['name'], $this->search) !== false ||
                       stripos($file['path'], $this->search) !== false;
            });
        }
        
        // Apply type filter
        if ($this->filter !== 'all') {
            $files = array_filter($files, function($file) use ($files) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if ($this->filter === 'md') return $ext === 'md';
                if ($this->filter === 'json') return $ext === 'json';
                if ($this->filter === 'yaml') return in_array($ext, ['yaml', 'yml']);
                return true;
            });
        }
        
        $this->files = array_values($files);
    }
    
    public function loadStats(): void
    {
        $allFiles = $this->workspaceService->listFiles();
        $this->stats = [
            'total' => count($allFiles),
            'md' => count(array_filter($allFiles, fn($f) => pathinfo($f['name'], PATHINFO_EXTENSION) === 'md')),
            'json' => count(array_filter($allFiles, fn($f) => pathinfo($f['name'], PATHINFO_EXTENSION) === 'json')),
            'total_size' => array_sum(array_column($allFiles, 'size')),
        ];
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
    
    public function updatedSearch(): void
    {
        $this->loadFiles();
    }
    
    public function filterBy(string $filter): void
    {
        $this->filter = $filter;
        $this->loadFiles();
    }

    public function refresh(): void
    {
        $this->loadFiles();
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.workspace-viewer');
    }
}