<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;

class AgentList extends Component
{
    public bool $showCreateModal = false;
    public ?Agent $editingAgent = null;
    
    public function editAgent(int $agentId): void
    {
        $this->editingAgent = Agent::find($agentId);
        $this->showCreateModal = true;
    }
    
    public function confirmDelete(int $agentId): void
    {
        if (confirm("Are you sure you want to delete this agent?")) {
            $this->deleteAgent($agentId);
        }
    }
    
    public function deleteAgent(int $agentId): void
    {
        $agent = Agent::find($agentId);
        if ($agent && !in_array($agent->name, ['dave', 'sam', 'chen'])) {
            $agent->delete();
            $this->dispatch('toast-success', message: 'Agent deleted successfully');
        }
    }
    
    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->editingAgent = null;
    }
    
    public function render()
    {
        $agents = Agent::with('tasks')->latest()->get();
        
        return view('livewire.agents.agent-list', [
            'agents' => $agents,
            'strategies' => StrategyRegistry::all(),
        ]);
    }
}
