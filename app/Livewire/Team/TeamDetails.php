<?php

namespace App\Livewire\Team;

use App\Models\TeamMember;
use App\Models\Task;
use Livewire\Component;

class TeamDetails extends Component
{
    public ?TeamMember $member = null;
    public array $activityHistory = [];
    public array $stats = [];
    public array $metadata = [];
    public array $settings = [];

    public function loadMember(?string $memberId = null): void
    {
        if ($memberId) {
            $this->member = TeamMember::with(['parent', 'children', 'tasks'])->findOrFail($memberId);
        }
    }

    public function mount(?string $memberId = null): void
    {
        $this->loadMember($memberId);
        $this->loadActivityHistory();
        $this->loadStats();
        $this->loadMetadata();
        $this->loadSettings();
    }

    public function loadActivityHistory(): void
    {
        if (!$this->member) {
            $this->activityHistory = [];
            return;
        }
        
        // Get recent tasks and their status changes
        $recentTasks = Task::where('assigned_to', $this->member->name)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        
        $this->activityHistory = $recentTasks->map(function($task) {
            return [
                'type' => 'task',
                'action' => match($task->status) {
                    'completed' => 'completed',
                    'in_progress' => 'started working on',
                    'blocked' => 'was blocked on',
                    default => 'updated',
                },
                'item' => $task->title,
                'timestamp' => $task->updated_at?->diffForHumans() ?? 'Recently',
            ];
        })->toArray();
    }

    public function loadStats(): void
    {
        if (!$this->member) {
            $this->stats = [];
            return;
        }
        
        $this->stats = [
            'total_tasks' => $this->member->tasks->count(),
            'completed_tasks' => $this->member->tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $this->member->tasks->where('status', 'in_progress')->count(),
            'blocked_tasks' => $this->member->tasks->where('status', 'blocked')->count(),
            'subordinates' => $this->member->children->count(),
        ];
    }

    public function loadMetadata(): void
    {
        if (!$this->member) {
            $this->metadata = [];
            return;
        }
        
        $this->metadata = $this->member->metadata_json ?? [];
    }

    public function loadSettings(): void
    {
        if (!$this->member) {
            $this->settings = [];
            return;
        }
        
        $this->settings = $this->member->settings ?? [];
    }

    public function toggleStatus(): void
    {
        if (!$this->member) {
            return;
        }
        
        $this->member->status = $this->member->status === 'active' ? 'inactive' : 'active';
        $this->member->save();
        $this->dispatch('toast-success', message: "Status updated to {$this->member->status}");
    }

    public function confirmDelete(): void
    {
        if (!$this->member) {
            return;
        }
        
        $this->dispatch('confirm-delete', memberId: $this->member->id);
    }

    public function delete(): void
    {
        if (!$this->member) {
            $this->dispatch('toast-error', message: 'Member not found');
            redirect()->route('team');
            return;
        }
        
        if (in_array($this->member->name, ['dave', 'sam', 'chen'])) {
            $this->dispatch('toast-error', message: 'Cannot delete protected team member');
            return;
        }

        $name = $this->member->name;
        $id = $this->member->id;
        $this->member->delete();
        $this->dispatch('toast-success', message: "Team member '{$name}' deleted successfully.");
        redirect()->route('team');
    }

    public function render()
    {
        return view('livewire.team.team-details');
    }
}
