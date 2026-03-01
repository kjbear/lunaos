<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TestRun;

class TestStatus extends Component
{
    public $recentRuns = [];
    public $stats = [];

    protected $listeners = ['refreshRuns' => 'loadRecentRuns'];

    public function mount()
    {
        $this->loadRecentRuns();
        $this->calculateStats();
    }

    public function loadRecentRuns()
    {
        $this->recentRuns = TestRun::orderByDesc('run_at')
            ->limit(10)
            ->get()
            ->map(function($run) {
                return [
                    'id' => $run->id,
                    'date' => $run->run_at->format('M j, Y H:i'),
                    'status' => $run->status,
                    'total' => $run->total_tests,
                    'passed' => $run->passed,
                    'failed' => $run->failed,
                    'skipped' => $run->skipped,
                    'coverage' => $run->coverage,
                    'duration' => $run->duration_ms,
                    'pass_rate' => $run->pass_rate,
                ];
            });
    }

    public function calculateStats()
    {
        $allRuns = TestRun::all();
        
        $this->stats = [
            'total_runs' => $allRuns->count(),
            'last_run' => $allRuns->first()?->run_at?->format('M j, Y H:i') ?? 'Never',
            'avg_pass_rate' => $allRuns->avg('pass_rate') ?? 0,
            'best_coverage' => $allRuns->max('coverage') ?? 0,
            'tests_written' => 19, // Static for now
        ];
    }

    public function runTests()
    {
        // Execute PHPUnit and store results
        $output = shell_exec('cd ' . base_path() . ' && php artisan test --json 2>&1');
        
        $results = json_decode($output, true);
        
        $testRun = TestRun::create([
            'run_at' => now(),
            'status' => $results['status'] ?? 'error',
            'total_tests' => $results['tests'] ?? 0,
            'passed' => $results['passed'] ?? 0,
            'failed' => $results['failed'] ?? 0,
            'skipped' => $results['skipped'] ?? 0,
            'coverage' => null, // Would need --coverage flag
            'duration_ms' => $results['time'] ?? 0,
            'output' => $output,
            'results' => $results,
        ]);

        $this->dispatch('refreshRuns');
        $this->loadRecentRuns();
        $this->calculateStats();
    }

    public function render()
    {
        return view('livewire.test-status');
    }
}
