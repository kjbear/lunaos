<?php

namespace App\Livewire;

use Livewire\Component;

class OrgChart extends Component
{
    public array $team = [];
    public ?array $selectedAgent = null;

    public function mount(): void
    {
        $this->loadTeam();
    }

    public function loadTeam(): void
    {
        $this->team = [
            // Depth 0: Luna (Main Assistant)
            [
                'id' => 'main',
                'name' => 'Luna',
                'role' => 'Main Assistant',
                'model' => 'GLM-5',
                'avatar' => '🌙',
                'depth' => 0,
                'children' => [
                    // Depth 1: Project Manager
                    [
                        'id' => 'pm',
                        'name' => 'Jordan',
                        'role' => 'Project Manager',
                        'model' => 'Dolphin 3.0',
                        'avatar' => '📋',
                        'depth' => 1,
                        'children' => [
                            // Depth 2: Specialists
                            [
                                'id' => 'dave',
                                'name' => 'Dave',
                                'role' => 'PHP Coder',
                                'model' => 'Dolphin 3.0',
                                'avatar' => '💻',
                                'depth' => 2,
                                'children' => [],
                            ],
                            [
                                'id' => 'maya',
                                'name' => 'Maya',
                                'role' => 'Frontend',
                                'model' => 'Dolphin 3.0',
                                'avatar' => '🎨',
                                'depth' => 2,
                                'children' => [],
                            ],
                            [
                                'id' => 'chen',
                                'name' => 'Chen',
                                'role' => 'DevOps',
                                'model' => 'Dolphin 3.0',
                                'avatar' => '🔧',
                                'depth' => 2,
                                'children' => [],
                            ],
                            [
                                'id' => 'sam',
                                'name' => 'Sam',
                                'role' => 'Test Engineer',
                                'model' => 'Dolphin 3.0',
                                'avatar' => '✅',
                                'depth' => 2,
                                'children' => [],
                            ],
                            [
                                'id' => 'alex',
                                'name' => 'Alex',
                                'role' => 'API Architect',
                                'model' => 'Dolphin 3.0',
                                'avatar' => '🔌',
                                'depth' => 2,
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function selectAgent(string $agentId): void
    {
        $this->selectedAgent = $this->findAgent($agentId, $this->team);
    }

    protected function findAgent(string $id, array $agents): ?array
    {
        foreach ($agents as $agent) {
            if ($agent['id'] === $id) {
                return $agent;
            }
            if (!empty($agent['children'])) {
                $found = $this->findAgent($id, $agent['children']);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }

    public function render()
    {
        return view('livewire.org-chart');
    }
}
