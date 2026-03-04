<?php

namespace App\Livewire\Team;

use App\Models\TeamMember;
use Livewire\Component;

class TeamCreate extends Component
{
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
            'email' => $this->email ? 'nullable|email|max:255|unique:team_members,email' : 'nullable',
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

    public function mount(): void
    {
        // Default values
        $this->provider = 'ollama';
        $this->status = 'active';
        $this->emoji = '🤖';
    }

    public function save(): void
    {
        $this->validate();

        // Auto-set type based on role
        $type = match($this->role) {
            'worker' => 'workers',
            'persona' => 'personas',
            'board_member' => 'board-members',
            default => 'workers',
        };

        TeamMember::create([
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

        $this->dispatch('toast-success', message: "Team member '{$this->name}' created successfully.");
        redirect()->route('team');
    }

    public function cancel(): void
    {
        redirect()->route('team');
    }

    public function render()
    {
        return view('livewire.team.team-create');
    }
}
