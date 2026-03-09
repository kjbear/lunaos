<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OrgChartDataService;

class OrgChart extends Component
{
    public array $graphData = [];
    public ?array $selectedAgent = null;
    public bool $showModal = false;
    public int $teamCount = 0;

    protected $listeners = [
        'selectAgent' => 'selectAgent',
        'closeModal' => 'closeModal',
    ];

    public function mount(): void
    {
        $this->loadGraphData();
    }

    public function loadGraphData(): void
    {
        $service = new OrgChartDataService();
        $this->graphData = $service->getGraphData();
        $this->teamCount = count($this->graphData['nodes']);
    }

    public function selectAgent(string $agentId): void
    {
        $service = new OrgChartDataService();
        $nodes = $service->getNodes();
        
        $this->selectedAgent = $nodes->firstWhere('id', (string) $agentId) 
            ?? $nodes->firstWhere('id', (int) $agentId);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedAgent = null;
    }

    public function render()
    {
        return view('livewire.org-chart');
    }
}
