<?php

namespace App\Agents;

use App\Models\Task;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;
use Illuminate\Support\Facades\Process;

/**
 * Sam - QA Testing Agent Worker
 * 
 * Worker-tier agent responsible for:
 * - Running PHPUnit unit tests
 * - Running Laravel Dusk browser tests
 * - Validating code quality
 * - Checking test coverage
 * - Reporting bugs and failures
 * 
 * Uses Qwen3-Coder via Ollama Cloud for test analysis
 * Polls every 30 seconds for QA tasks
 */
class SamAgentWorker extends AgentWorker
{
    public string $name = 'sam';
    
    public AgentType $type = AgentType::WORKER;
    
    public int $pollInterval = 30; // 30 seconds (fast polling for execution)
    
    public array $capabilities = ['phpunit', 'dusk', 'testing', 'qa', 'validation', 'coverage'];
    
    protected string $model = 'qwen3-coder:latest'; // Ollama Cloud model
    
    /**
     * Poll for QA tasks
     */
    protected function pollForWork(): ?Task
    {
        $task = Task::where('assigned_to', 'sam')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('current_step', 'qa')
            ->orderBy('created_at', 'asc')
            ->first();
        
        return $task;
    }
    
    /**
     * Process a QA task - run tests and validate
     */
    protected function processTask(Task $task): void
    {
        try {
            echo "🔍 Sam analyzing task #{$task->id}: {$task->title}\n";
            $this->logActivity($task, 'started', ['action' => 'test_analysis']);
            
            // Step 1: Check out the PR/branch created by Dave
            $branchName = $this->checkoutBranch($task);
            echo "🌿 Sam checked out branch: {$branchName}\n";
            
            // Step 2: Run PHPUnit tests
            echo "🧪 Running PHPUnit tests...\n";
            $phpunitResult = $this->runPhpunitTests($task);
            
            // Step 3: Run Dusk browser tests (if applicable)
            $duskResult = null;
            if ($this->shouldRunDusk($task)) {
                echo "🌐 Running Laravel Dusk browser tests...\n";
                $duskResult = $this->runDuskTests($task);
            }
            
            // Step 4: Analyze results with AI
            echo "🤖 Sam analyzing test results...\n";
            $analysis = $this->analyzeTestResults($task, $phpunitResult, $duskResult);
            
            // Step 5: Build artifacts
            $artifacts = [
                'phpunit_passed' => $phpunitResult['passed'],
                'phpunit_failures' => $phpunitResult['failures'] ?? [],
                'dusk_passed' => $duskResult['passed'] ?? null,
                'dusk_failures' => $duskResult['failures'] ?? [],
                'coverage' => $phpunitResult['coverage'] ?? null,
                'ai_analysis' => $analysis['summary'],
                'recommendation' => $analysis['recommendation'],
                'total_tests' => $phpunitResult['total_tests'] ?? 0,
            ];
            
            // Step 6: Decision - pass or fail?
            if ($analysis['recommendation'] === 'pass') {
                echo "✅ Sam: QA PASSED - All tests green\n";
                echo "   Summary: {$analysis['summary']}\n";
                
                // Advance to Security step
                $this->completeTask($task, 'security', 'security', [
                    'branch' => $branchName,
                    'test_results' => $artifacts,
                    'ai_summary' => $analysis['summary'],
                ]);
                
            } else {
                echo "❌ Sam: QA FAILED - Tests need fixes\n";
                echo "   Reason: {$analysis['reason']}\n";
                
                // Return to Dave for fixes
                $this->failTask($task, $analysis['reason'], 'develop', 'dave');
            }
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Sam failed task #{$task->id}: {$e->getMessage()}", [
                'task' => $task->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->failTask($task, $e->getMessage(), 'qa', 'sam');
        }
    }
    
    /**
     * Checkout the feature branch for testing
     */
    protected function checkoutBranch(Task $task): string
    {
        $gitService = $this->gitService($task);
        
        // Get branch name from task artifacts or generate it
        $branchName = "feature/{$task->id}-" . \Illuminate\Support\Str::slug($task->title);
        
        $gitService->checkoutBranch($branchName, true); // true = force checkout
        
        return $branchName;
    }
    
    /**
     * Run PHPUnit tests and capture results
     */
    protected function runPhpunitTests(Task $task): array
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        // Run PHPUnit with coverage and JSON output
        $result = Process::path($repoPath)
            ->timeout(300) // 5 minutes max
            ->run('./vendor/bin/phpunit --coverage-text --testdox --log-junit=storage/logs/phpunit-report.xml');
        
        $output = $result->output();
        $exitCode = $result->exitCode();
        
        // Parse results
        $passed = ($exitCode === 0);
        $totalTests = 0;
        $failures = [];
        $coverage = null;
        
        // Extract test count and failures from output
        if (preg_match('/OK \((\d+) tests?/', $output, $matches)) {
            $totalTests = (int) $matches[1];
        } elseif (preg_match('/(\d+) tests?/', $output, $matches)) {
            $totalTests = (int) $matches[1];
        }
        
        // Extract failures
        if (!$passed) {
            preg_match_all('/^\d+\) .+$/m', $output, $failMatches);
            $failures = $failMatches[0] ?? [];
        }
        
        // Extract coverage percentage
        if (preg_match('/Lines:\s+([\d.]+)%/', $output, $matches)) {
            $coverage = (float) $matches[1];
        }
        
        return [
            'passed' => $passed,
            'total_tests' => $totalTests,
            'failures' => $failures,
            'coverage' => $coverage,
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }
    
    /**
     * Run Laravel Dusk browser tests
     */
    protected function runDuskTests(Task $task): array
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        // Run Dusk (headless Chrome)
        $result = Process::path($repoPath)
            ->timeout(600) // 10 minutes max for browser tests
            ->env(['APP_ENV' => 'dusk', 'APP_URL' => 'http://localhost:8000'])
            ->run('php artisan dusk --stop-on-failure');
        
        $output = $result->output();
        $exitCode = $result->exitCode();
        
        $passed = ($exitCode === 0);
        $failures = [];
        
        if (!$passed) {
            // Extract Dusk failures
            preg_match_all('/^\d+\) .+$/m', $output, $failMatches);
            $failures = $failMatches[0] ?? [];
        }
        
        return [
            'passed' => $passed,
            'failures' => $failures,
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }
    
    /**
     * Determine if Dusk tests should run for this task
     */
    protected function shouldRunDusk(Task $task): bool
    {
        // Run Dusk if task involves UI/frontend changes
        $uiKeywords = ['livewire', 'blade', 'frontend', 'ui', 'component', 'view', 'form'];
        
        foreach ($uiKeywords as $keyword) {
            if (stripos($task->title, $keyword) !== false || stripos($task->description, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Analyze test results with AI
     */
    protected function analyzeTestResults(Task $task, array $phpunitResult, ?array $duskResult): array
    {
        $model = $this->getModel();
        $provider = $this->getProvider();
        $settings = $this->getModelSettings();
        
        $duskStatus = $duskResult ? ($duskResult['passed'] ? 'PASSED' : 'FAILED') : 'NOT RUN';
        $phpunitStatus = $phpunitResult['passed'] ? 'PASSED' : 'FAILED';
        $coverageStr = $phpunitResult['coverage'] ?? 'N/A';
        $failuresStr = !empty($phpunitResult['failures']) ? implode("\n", $phpunitResult['failures']) : 'None';
        $duskFailuresStr = ($duskResult && !empty($duskResult['failures'])) ? "Failures: " . implode("\n", $duskResult['failures']) : '';
        
        $prompt = <<<PROMPT
Analyze these test results for task #{$task->id}: {$task->title}

**TASK DESCRIPTION:**
{$task->description}

**PHPUNIT RESULTS:**
- Status: {$phpunitStatus}
- Total Tests: {$phpunitResult['total_tests']}
- Coverage: {$coverageStr}%
- Exit Code: {$phpunitResult['exit_code']}

Failures:
{$failuresStr}

**DUSK BROWSER TESTS:**
- Status: {$duskStatus}
{$duskFailuresStr}

**YOUR TASK:**
1. Analyze the test results
2. Determine if the code is ready to advance to the next step
3. If failures exist, explain what needs to be fixed

Return JSON:
{
  "summary": "Brief summary of test results",
  "recommendation": "pass|fail",
  "reason": "Detailed explanation of the decision"
}

**CRITERIA:**
- PASS: All tests green, coverage > 70%
- FAIL: Any test failures, coverage < 50%, or critical issues
PROMPT;
        
        echo "🤖 Sam calling AI for test analysis...\n";
        
        $response = Ai::agent()
            ->withLab($provider)
            ->withModel($model)
            ->withMaxTokens(1000)
            ->run($prompt);
        
        // Parse JSON response
        $content = (string) $response;
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
        
        $result = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback analysis
            return [
                'summary' => 'Manual analysis: ' . ($phpunitResult['passed'] ? 'PHPUnit passed' : 'PHPUnit failed'),
                'recommendation' => $phpunitResult['passed'] ? 'pass' : 'fail',
                'reason' => $phpunitResult['passed'] ? 'All tests passed' : ($phpunitResult['failures'][0] ?? 'Test failures detected'),
            ];
        }
        
        return $result;
    }
}
