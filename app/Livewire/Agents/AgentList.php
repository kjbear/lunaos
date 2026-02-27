<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AgentList extends Component
{
    public bool $showCreateModal = false;
    public ?Agent $editingAgent = null;
    
    protected $listeners = ['agent-created', 'agent-updated', 'agent-deleted'];
    
    public function render()
    {
        $agents = Agent::with('tasks')->latest()->get();
        
        return view('livewire.agents.agent-list', [
            'agents' => $agents,
            'strategies' => StrategyRegistry::all(),
        ]);
    }
    
    public function openCreateModal(): void
    {
        $this->editingAgent = null;
        $this->showCreateModal = true;
    }
    
    public function openEditModal(int $agentId): void
    {
        $this->editingAgent = Agent::find($agentId);
        $this->showCreateModal = true;
    }
    
    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->editingAgent = null;
    }
    
    public function agentCreated(): void
    {
        $this->closeModal();
        $this->dispatch('notify', message: 'Agent created successfully');
    }
    
    public function agentUpdated(): void
    {
        $this->closeModal();
        $this->dispatch('notify', message: 'Agent updated successfully');
    }
    
    public function deleteAgent(int $agentId): void
    {
        $agent = Agent::find($agentId);
        if ($agent) {
            $agent->delete();
            $this->dispatch('notify', message: 'Agent deleted successfully');
        }
    }
}
