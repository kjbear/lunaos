<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Agent;
use App\Models\ModelHealth;

class OrgChart extends Component
{
    public $agents;
    public $tree;
    public $selectedAgent = null;
    public $modelHealth = [];
    public $stats = [];

    protected $listeners = ['refreshOrgChart' => '$refresh'];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        // Build tree
        $this->tree = $this->buildTree();
        $this->agents = Agent::with('children')->get();

        // Model health
        $this->modelHealth = ModelHealth::where('checked_at', '>=', now()->subMinutes(30))
            ->get()
            ->keyBy('model');

        // Stats
        $this->stats = [
            'total' => Agent::count(),
            'online' => Agent::online()->count(),
            'offline' => Agent::offline()->count(),
        ];
    }

    protected function buildTree(): array
    {
        $agents = Agent::with('children')->whereNull('parent_id')->get();
        return $agents->map(fn ($agent) => $this->formatAgent($agent))->toArray();
    }

    protected function formatAgent(Agent $agent): array
    {
        return [
            'id' => $agent->id,
            'name' => $agent->name,
            'role' => $agent->role,
            'model' => $agent->model,
            'status' => $agent->status,
            'children' => $agent->children->map(fn ($child) => $this->formatAgent($child))->toArray(),
        ];
    }

    public function selectAgent(int $agentId): void
    {
        $this->selectedAgent = Agent::with(['parent', 'children', 'tasks' => fn ($q) => $q->latest()->limit(5)])
            ->find($agentId);
    }

    public function clearSelection(): void
    {
        $this->selectedAgent = null;
    }

    public function render()
    {
        return view('livewire.org-chart');
    }
}