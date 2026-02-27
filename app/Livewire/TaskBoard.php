<?php

namespace App\Livewire;

use Livewire\Component;

class TaskBoard extends Component
{
    public ?string $projectId = null;
    public array $columns = ['todo', 'in_progress', 'blocked', 'done'];
    public array $tasks = [];
    public array $projects = [];
    public bool $canEdit = false;

    public function mount(): void
    {
        $this->loadProjects();
        $this->loadTasks();
    }

    public function loadProjects(): void
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/projects.db';
        
        if (file_exists($dbPath)) {
            $pdo = new \PDO('sqlite:' . $dbPath);
            $stmt = $pdo->query("SELECT * FROM projects WHERE status = 'active'");
            $this->projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (!$this->projectId && count($this->projects) > 0) {
                $this->projectId = $this->projects[0]['id'] ?? null;
            }
        }
    }

    public function loadTasks(): void
    {
        if (!$this->projectId) {
            $this->tasks = [];
            return;
        }

        $dbPath = '/Users/kobear/.openclaw/workspace/projects.db';
        
        if (file_exists($dbPath)) {
            $pdo = new \PDO('sqlite:' . $dbPath);
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC");
            $stmt->execute([$this->projectId]);
            $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group by status
            $this->tasks = [];
            foreach ($this->columns as $column) {
                $this->tasks[$column] = array_filter($tasks, fn($t) => ($t['status'] ?? 'todo') === $column);
            }
        }
    }

    public function selectProject(string $projectId): void
    {
        $this->projectId = $projectId;
        $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.task-board');
    }
}
