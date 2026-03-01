<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Agents\Strategies\StrategyRegistry;
use Livewire\Component;

class AgentList extends Component
{
    public bool $showCreateModal = false;
    public ?Agent $editingAgent = null;
    public ?int $pendingDeleteId = null;
    public bool $showDeleteConfirm = false;
    
    public function editAgent(int $agentId): void
    {
        $this->editingAgent = Agent::find($agentId);
        $this->showCreateModal = true;
    }
    
    public function promptDelete(int $agentId): void
    {
        $this->pendingDeleteId = $agentId;
        $this->showDeleteConfirm = true;
    }
    
    public function confirmDelete(): void
    {
        if ($this->pendingDeleteId) {
            $this->deleteAgent($this->pendingDeleteId);
        }
        $this->showDeleteConfirm = false;
        $this->pendingDeleteId = null;
    }
    
    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->pendingDeleteId = null;
    }
    
    public function deleteAgent(int $agentId): void
    {
        $agent = Agent::find($agentId);
        if ($agent && !in_array($agent->name, ['dave', 'sam', 'chen'])) {
            $agent->delete();
            $this->dispatch('toast-success', message: 'Agent deleted successfully');
        } else {
            $this->dispatch('toast-error', message: 'Cannot delete protected agent');
        }
        $this->showDeleteConfirm = false;
        $this->pendingDeleteId = null;
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
