<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Task;
use App\Models\Doc;
use App\Models\ActivityLog;
use App\Models\Standup;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class GlobalSearch extends Component
{
    public string $query = '';
    public bool $isOpen = false;
    public array $results = [];
    public int $totalResults = 0;
    
    protected $listeners = ['openSearch' => 'openSearch'];

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 2) {
            $this->search();
            $this->isOpen = true;
        } else {
            $this->results = [];
            $this->totalResults = 0;
            $this->isOpen = false;
        }
    }

    public function search(): void
    {
        $this->results = [];
        $this->totalResults = 0;
        
        if (strlen($this->query) < 2) {
            return;
        }

        $searchTerm = $this->query . '*';

        // Search Tasks
        $tasks = Task::query()
            ->where('name', 'LIKE', "%{$this->query}%")
            ->orWhere('description', 'LIKE', "%{$this->query}%")
            ->limit(3)
            ->get()
            ->map(fn($task) => [
                'type' => 'task',
                'id' => $task->id,
                'title' => $task->name,
                'subtitle' => ucfirst($task->status ?? 'pending') . ' · ' . ucfirst($task->priority ?? 'normal') . ' priority',
                'url' => route('tasks') . '?task=' . $task->id,
                'icon' => '📋',
            ]);
        
        $this->results['tasks'] = $tasks;
        $this->totalResults += $tasks->count();

        // Search Docs (using FTS5)
        try {
            $docs = Doc::query()
                ->whereRaw("id IN (SELECT rowid FROM docs_fts WHERE docs_fts MATCH ?)", [$searchTerm])
                ->limit(3)
                ->get()
                ->map(fn($doc) => [
                    'type' => 'doc',
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'subtitle' => $doc->section ?? 'Document',
                    'url' => route('docs') . '?doc=' . $doc->slug,
                    'icon' => '📄',
                ]);
            $this->results['docs'] = $docs;
            $this->totalResults += $docs->count();
        } catch (\Exception $e) {
            // FTS might not work, skip
        }

        // Search Activity Logs (using FTS5)
        try {
            $activities = ActivityLog::query()
                ->whereRaw("id IN (SELECT rowid FROM activity_logs_fts WHERE activity_logs_fts MATCH ?)", [$searchTerm])
                ->limit(5)
                ->get()
                ->map(fn($activity) => [
                    'type' => 'activity',
                    'id' => $activity->id,
                    'title' => $activity->action_name,
                    'subtitle' => $activity->agent . ' · ' . $activity->created_at->diffForHumans(),
                    'url' => route('activity') . '?activity=' . $activity->id,
                    'icon' => '📊',
                ]);
            $this->results['activity'] = $activities;
            $this->totalResults += $activities->count();
        } catch (\Exception $e) {
            // FTS might not work, skip
        }

        // Search Standups
        $standups = Standup::query()
            ->where('team', 'LIKE', "%{$this->query}%")
            ->orWhere('transcript', 'LIKE', "%{$this->query}%")
            ->limit(2)
            ->get()
            ->map(fn($standup) => [
                'type' => 'standup',
                'id' => $standup->id,
                'title' => $standup->team . ' - ' . $standup->date->format('M j, Y'),
                'subtitle' => $standup->agentUpdates->count() . ' participants',
                'url' => route('standup') . '?standup=' . $standup->id,
                'icon' => '🎤',
            ]);
        
        $this->results['standups'] = $standups;
        $this->totalResults += $standups->count();
    }

    public function openSearch(): void
    {
        $this->isOpen = true;
        $this->dispatch('focus-search-input');
    }

    public function closeSearch(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
        $this->totalResults = 0;
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}