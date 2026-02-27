<?php

namespace App\Agents\Strategies;

use App\Models\Task;
use App\Models\Agent;
use App\Agents\Strategies\Concerns\HasWorkerCapabilities;
use Illuminate\Support\Facades\Process;

/**
 * QA Strategy
 * 
 * Handles testing and quality assurance tasks.
 * Extracted from SamAgentWorker.
 */
class QAStrategy implements WorkerStrategy
{
    use HasWorkerCapabilities;
    
    /**
     * {@inheritDoc}
     */
    public function pollForWork(Agent $agent): ?Task
    {
        return Task::where('assigned_to', $agent->name)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('step', 'qa')
            ->orderBy('created_at', 'asc')
            ->first();
    }
    
    /**
     * {@inheritDoc}
     */
    public function processTask(Task $task, Agent $agent): void
    {
        try {
            \Illuminate\Support\Facades\Log::info("QA strategy processing task #{$task->id}", [
                'agent' => $agent->name,
            ]);
            
            // Step 1: Checkout branch
            $branchName = $this->checkoutBranch($task, $agent);
            
            // Step 2: Run PHPUnit tests
            $phpunitResult = $this->runPhpunitTests($task, $agent);
            
            // Step 3: Run Dusk tests if applicable
            $duskResult = null;
            if ($this->shouldRunDusk($task)) {
                $duskResult = $this->runDuskTests($task, $agent);
            }
            
            // Step 4: AI analysis
            $analysis = $this->analyzeTestResults($task, $agent, $phpunitResult, $duskResult);
            
            // Step 5: Decision
            if ($analysis['recommendation'] === 'pass') {
                $this->completeTask($task, $agent, 'security', 'security', [
                    'branch' => $branchName,
                    'test_results' => [
                        'phpunit_passed' => $phpunitResult['passed'],
                        'dusk_passed' => $duskResult['passed'] ?? null,
                        'coverage' => $phpunitResult['coverage'] ?? null,
                    ],
                    'ai_summary' => $analysis['summary'],
                ]);
            } else {
                $this->failTask($task, $agent, $analysis['reason'], 'develop', 'dave');
            }
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("QA strategy failed task #{$task->id}: {$e->getMessage()}");
            $this->failTask($task, $agent, $e->getMessage(), 'qa', 'sam');
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCapabilities(): array
    {
        return ['phpunit', 'dusk', 'testing', 'qa', 'validation', 'coverage'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getWorkflowSteps(): array
    {
        return ['qa'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'qa';
    }
    
    /**
     * Run PHPUnit tests.
     */
    protected function runPhpunitTests(Task $task, Agent $agent): array
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $result = Process::path($repoPath)
            ->timeout(300)
            ->run('./vendor/bin/phpunit --coverage-text --testdox');
        
        $output = $result->output();
        $exitCode = $result->exitCode();
        
        $passed = ($exitCode === 0);
        $totalTests = 0;
        $failures = [];
        $coverage = null;
        
        // Parse output
        if (preg_match('/OK \((\d+) tests?/', $output, $matches)) {
            $totalTests = (int) $matches[1];
        } elseif (preg_match('/(\d+) tests?/', $output, $matches)) {
            $totalTests = (int) $matches[1];
        }
        
        if (!$passed) {
            preg_match_all('/^\d+\) .+$/m', $output, $failMatches);
            $failures = $failMatches[0] ?? [];
        }
        
        if (preg_match('/Lines:\s+([\d.]+)%/', $output, $matches)) {
            $coverage = (float) $matches[1];
        }
        
        return [
            'passed' => $passed,
            'total_tests' => $totalTests,
            'failures' => $failures,
            'coverage' => $coverage,
            'exit_code' => $exitCode,
        ];
    }
    
    /**
     * Run Laravel Dusk browser tests.
     */
    protected function runDuskTests(Task $task, Agent $agent): array
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $result = Process::path($repoPath)
            ->timeout(600)
            ->env(['APP_ENV' => 'dusk', 'APP_URL' => 'http://localhost:8000'])
            ->run('php artisan dusk --stop-on-failure');
        
        $output = $result->output();
        $exitCode = $result->exitCode();
        
        $passed = ($exitCode === 0);
        $failures = [];
        
        if (!$passed) {
            preg_match_all('/^\d+\) .+$/m', $output, $failMatches);
            $failures = $failMatches[0] ?? [];
        }
        
        return [
            'passed' => $passed,
            'failures' => $failures,
            'exit_code' => $exitCode,
        ];
    }
    
    /**
     * Determine if Dusk tests should run.
     */
    protected function shouldRunDusk(Task $task): bool
    {
        $uiKeywords = ['livewire', 'blade', 'frontend', 'ui', 'component', 'view', 'form'];
        
        foreach ($uiKeywords as $keyword) {
            if (stripos($task->title, $keyword) !== false || stripos($task->description, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Analyze test results with AI.
     */
    protected function analyzeTestResults(Task $task, Agent $agent, array $phpunitResult, ?array $duskResult): array
    {
        $duskStatus = $duskResult ? ($duskResult['passed'] ? 'PASSED' : 'FAILED') : 'NOT RUN';
        $phpunitStatus = $phpunitResult['passed'] ? 'PASSED' : 'FAILED';
        $failuresStr = !empty($phpunitResult['failures']) ? implode("\n", $phpunitResult['failures']) : 'None';
        
        $prompt = <<<PROMPT
Analyze these test results for task #{$task->id}: {$task->title}

**TASK DESCRIPTION:**
{$task->description}

**PHPUNIT RESULTS:**
- Status: {$phpunitStatus}
- Total Tests: {$phpunitResult['total_tests']}
- Coverage: {$phpunitResult['coverage']}%

Failures:
{$failuresStr}

**DUSK BROWSER TESTS:**
- Status: {$duskStatus}

**YOUR TASK:**
Return JSON with:
- summary: Brief summary of test results
- recommendation: "pass" or "fail"
- reason: Detailed explanation

**CRITERIA:**
- PASS: All tests green, coverage > 70%
- FAIL: Any test failures, coverage < 50%
PROMPT;
        
        return $this->callAIJson($agent, $prompt, 1000);
    }
}
