<?php

namespace App\Livewire\Team;

use App\Models\TeamMember;
use Livewire\Component;

class TeamEdit extends Component
{
    public TeamMember $member;
    
    public string $name = '';
    public string $email = '';
    public string $title = '';
    public string $role = 'worker';
    public string $status = 'active';
    public string $model = '';
    public string $provider = 'ollama';
    public string $system_prompt = '';
    public string $avatar = '';
    public string $emoji = '🤖';

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:100',
            'email' => $this->email ? ['nullable', 'email', 'max:255', 'unique:team_members,email,' . $this->member->id] : 'nullable',
            'title' => 'nullable|max:100',
            'role' => 'required|in:worker,persona,board_member',
            'status' => 'required|in:active,inactive,online,offline,error,busy,archived',
            'model' => 'nullable|max:100',
            'provider' => 'nullable|max:100',
            'system_prompt' => 'nullable|string',
            'avatar' => 'nullable|max:255',
            'emoji' => 'nullable|max:10',
        ];
    }

    public function mount(string $id): void
    {
        $this->member = TeamMember::findOrFail($id);
        
        // Pre-populate form
        $this->name = $this->member->name;
        $this->email = $this->member->email ?? '';
        $this->title = $this->member->title ?? '';
        $this->role = $this->member->role;
        $this->status = $this->member->status;
        $this->model = $this->member->model ?? '';
        $this->provider = $this->member->provider ?? 'ollama';
        $this->system_prompt = $this->member->system_prompt ?? '';
        $this->avatar = $this->member->avatar ?? '';
        $this->emoji = $this->member->emoji ?? '🤖';
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'nullable|email|max:255|unique:team_members,email,' . $this->member->id,
            'title' => 'nullable|max:100',
            'role' => 'required|in:worker,persona,board_member',
            'status' => 'required|in:active,inactive,online,offline,error,busy,archived',
        ]);

        // Auto-set type based on role
        $type = match($this->role) {
            'worker' => 'workers',
            'persona' => 'personas',
            'board_member' => 'board-members',
            default => 'workers',
        };

        $this->member->update([
            'name' => $this->name,
            'email' => $this->email ?: null,
            'title' => $this->title ?: null,
            'type' => $type,
            'role' => $this->role,
            'status' => $this->status,
            'model' => $this->model ?: null,
            'provider' => $this->provider,
            'system_prompt' => $this->system_prompt ?: null,
            'avatar' => $this->avatar ?: null,
            'emoji' => $this->emoji ?: '🤖',
        ]);

        $this->dispatch('toast-success', message: "Team member '{$this->name}' updated successfully.");
        redirect()->route('team.show', $this->member->id);
    }

    public function cancel(): void
    {
        redirect()->route('team.show', $this->member->id);
    }

    public function delete(): void
    {
        if (in_array($this->member->name, ['dave', 'sam', 'chen'])) {
            $this->dispatch('toast-error', message: 'Cannot delete protected team member');
            return;
        }

        $name = $this->member->name;
        $this->member->delete();
        $this->dispatch('toast-success', message: "Team member '{$name}' deleted successfully.");
        redirect()->route('team');
    }

    public function render()
    {
        return view('livewire.team.team-edit');
    }
}
