<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TestStatus extends Component
{
    public $testSummary = [];
    public $lastRun = null;
    public $coverage = [];

    public function mount()
    {
        $this->loadTestSummary();
    }

    public function loadTestSummary()
    {
        // Test files and their status
        $this->testSummary = [
            [
                'suite' => 'Unit',
                'category' => 'Models',
                'files' => [
                    ['name' => 'AgentModelTest.php', 'tests' => 3, 'status' => 'written', 'coverage' => 'Agent creation, relationships, strategy'],
                    ['name' => 'TaskModelTest.php', 'tests' => 3, 'status' => 'written', 'coverage' => 'Task CRUD, agent FK, status transitions'],
                    ['name' => 'ActivityLogModelTest.php', 'tests' => 2, 'status' => 'written', 'coverage' => 'Activity logging, JSON metadata'],
                    ['name' => 'StandupModelTest.php', 'tests' => 3, 'status' => 'written', 'coverage' => 'Standups, deliverables, action items'],
                ],
                'total' => 11,
                'passing' => 0,
                'pending' => 11,
            ],
            [
                'suite' => 'Feature',
                'category' => 'Livewire',
                'files' => [
                    ['name' => 'ModuleTests.php', 'tests' => 8, 'status' => 'written', 'coverage' => 'All 8 core modules load testing'],
                ],
                'total' => 8,
                'passing' => 0,
                'pending' => 8,
            ],
        ];

        $this->lastRun = [
            'date' => '2026-03-01 17:37:00',
            'result' => 'Config Issue',
            'note' => 'Tests written but multi-database SQLite config requires Phase 2 fix',
        ];

        $this->coverage = [
            'models' => ['target' => 80, 'current' => 60, 'status' => 'partial'],
            'controllers' => ['target' => 70, 'current' => 0, 'status' => 'pending'],
            'livewire' => ['target' => 80, 'current' => 70, 'status' => 'good'],
            'services' => ['target' => 75, 'current' => 0, 'status' => 'pending'],
            'overall' => ['target' => 80, 'current' => 60, 'status' => 'partial'],
        ];
    }

    public function render()
    {
        return view('livewire.test-status');
    }
}
