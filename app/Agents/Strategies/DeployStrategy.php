<?php

namespace App\Agents\Strategies;

use App\Models\Task;
use App\Models\Agent;
use App\Agents\Strategies\Concerns\HasWorkerCapabilities;
use Illuminate\Support\Facades\Process;

/**
 * Deploy Strategy
 * 
 * Handles staging and production deployments.
 * Extracted from ChenAgentWorker.
 */
class DeployStrategy implements WorkerStrategy
{
    use HasWorkerCapabilities;
    
    /**
     * {@inheritDoc}
     */
    public function pollForWork(Agent $agent): ?Task
    {
        return Task::where('assigned_to', $agent->name)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereIn('step', ['staging', 'production'])
            ->orderBy('created_at', 'asc')
            ->first();
    }
    
    /**
     * {@inheritDoc}
     */
    public function processTask(Task $task, Agent $agent): void
    {
        try {
            \Illuminate\Support\Facades\Log::info("Deploy strategy processing task #{$task->id}", [
                'agent' => $agent->name,
                'step' => $task->step,
            ]);
            
            $target = $task->step; // 'staging' or 'production'
            
            // Step 1: Checkout branch
            $branchName = $this->checkoutBranch($task, $agent);
            
            // Step 2: Pre-deployment checks
            $preChecks = $this->runPreDeploymentChecks($task, $agent);
            if (!$preChecks['passed']) {
                throw new \Exception("Pre-deployment checks failed: " . implode(', ', $preChecks['failures']));
            }
            
            // Step 3: Execute deployment
            $deployResult = $this->executeDeployment($task, $agent, $target);
            
            // Step 4: Health checks
            $healthCheck = $this->runHealthChecks($task, $agent, $target);
            
            // Step 5: AI analysis
            $analysis = $this->analyzeDeployment($task, $agent, $target, $deployResult, $healthCheck);
            
            // Step 6: Decision
            if ($analysis['recommendation'] === 'success') {
                if ($target === 'staging') {
                    $this->completeTask($task, $agent, 'production', 'chen', [
                        'deployment_results' => $deployResult,
                        'health_check' => $healthCheck,
                        'ai_summary' => $analysis['summary'],
                    ]);
                } else {
                    // Production complete
                    $task->update([
                        'status' => 'complete',
                        'step' => 'complete',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $this->logActivity($task, $agent, 'completed', [
                        'deployment_results' => $deployResult,
                        'health_check' => $healthCheck,
                        'ai_summary' => $analysis['summary'],
                    ]);
                }
            } else {
                // Rollback
                $this->rollbackDeployment($task, $agent, $target);
                $this->failTask($task, $agent, $analysis['reason'], $target === 'staging' ? 'security' : 'staging', $target === 'staging' ? 'security' : 'chen');
            }
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Deploy strategy failed task #{$task->id}: {$e->getMessage()}");
            $this->failTask($task, $agent, $e->getMessage(), $task->step, 'chen');
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCapabilities(): array
    {
        return ['deploy', 'staging', 'production', 'docker', 'kubernetes', 'healthcheck', 'rollback'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getWorkflowSteps(): array
    {
        return ['staging', 'production'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'deploy';
    }
    
    /**
     * Run pre-deployment checks.
     */
    protected function runPreDeploymentChecks(Task $task, Agent $agent): array
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $checks = [
            'git_status' => false,
            'dependencies' => false,
            'env_config' => false,
            'migrations' => false,
        ];
        $failures = [];
        
        // Git status
        $gitStatus = Process::path($repoPath)->run('git status --porcelain');
        $checks['git_status'] = trim($gitStatus->output()) === '';
        if (!$checks['git_status']) {
            $failures[] = 'Working directory not clean';
        }
        
        // Dependencies
        $composerCheck = Process::path($repoPath)->run('test -d vendor && echo "ok" || echo "missing"');
        $checks['dependencies'] = trim($composerCheck->output()) === 'ok';
        if (!$checks['dependencies']) {
            $failures[] = 'Vendor directory missing';
        }
        
        // Env config
        $envCheck = Process::path($repoPath)->run('test -f .env && echo "ok" || echo "missing"');
        $checks['env_config'] = trim($envCheck->output()) === 'ok';
        if (!$checks['env_config']) {
            $failures[] = '.env file missing';
        }
        
        // Migrations
        $migrationCheck = Process::path($repoPath)->timeout(60)->run('php artisan migrate:status');
        $checks['migrations'] = ($migrationCheck->exitCode() === 0);
        if (!$checks['migrations']) {
            $failures[] = 'Database migration check failed';
        }
        
        return [
            'passed' => empty($failures),
            'checks' => $checks,
            'failures' => $failures,
        ];
    }
    
    /**
     * Execute deployment.
     */
    protected function executeDeployment(Task $task, Agent $agent, string $target): array
    {
        $startTime = microtime(true);
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $deploymentId = 'deploy-' . $task->id . '-' . $target . '-' . time();
        
        if ($target === 'staging') {
            // Full rebuild for staging
            Process::path($repoPath)->timeout(300)->run('composer install --no-dev --optimize-autoloader');
            Process::path($repoPath)->timeout(120)->run('npm run build');
            Process::path($repoPath)->timeout(60)->run('php artisan migrate --force');
            Process::path($repoPath)->run('php artisan config:cache && php artisan route:cache && php artisan view:cache');
            Process::run('sudo systemctl reload php-fpm');
            
        } elseif ($target === 'production') {
            // Zero-downtime for production
            Process::path($repoPath)->timeout(120)->run('git pull origin main');
            Process::path($repoPath)->timeout(300)->run('composer install --no-dev --optimize-autoloader');
            Process::path($repoPath)->timeout(120)->run('npm run build');
            Process::path($repoPath)->timeout(60)->run('php artisan migrate --force');
            Process::path($repoPath)->run('php artisan optimize:clear && php artisan optimize');
            Process::run('sudo systemctl reload php-fpm');
            Process::run('sudo systemctl reload nginx');
        }
        
        $duration = (microtime(true) - $startTime) * 1000;
        $commitHash = trim(Process::path($repoPath)->run('git rev-parse HEAD')->output());
        
        return [
            'deployment_id' => $deploymentId,
            'commit_hash' => $commitHash,
            'duration_ms' => $duration,
            'rollback_available' => true,
        ];
    }
    
    /**
     * Run health checks.
     */
    protected function runHealthChecks(Task $task, Agent $agent, string $target): array
    {
        $healthUrl = $target === 'staging' ? 'https://staging.lunaos.test/health' : 'https://lunaos.test/health';
        
        $details = [
            'http' => $this->checkHttpHealth($healthUrl),
            'database' => $this->checkDatabaseHealth($task, $agent),
            'cache' => $this->checkCacheHealth($task, $agent),
        ];
        
        return [
            'passed' => !in_array(false, $details, true),
            'details' => $details,
        ];
    }
    
    /**
     * Check HTTP endpoint.
     */
    protected function checkHttpHealth(string $url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200);
    }
    
    /**
     * Check database health.
     */
    protected function checkDatabaseHealth(Task $task, Agent $agent): bool
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $result = Process::path($repoPath)
            ->timeout(10)
            ->run('php artisan tinker --execute="DB::connection()->getPdo();"');
        
        return ($result->exitCode() === 0);
    }
    
    /**
     * Check cache health.
     */
    protected function checkCacheHealth(Task $task, Agent $agent): bool
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $result = Process::path($repoPath)
            ->timeout(10)
            ->run('php artisan tinker --execute="Cache::put(\'health_check\', true, 1); Cache::get(\'health_check\');"');
        
        return ($result->exitCode() === 0);
    }
    
    /**
     * Analyze deployment with AI.
     */
    protected function analyzeDeployment(Task $task, Agent $agent, string $target, array $deployResult, array $healthCheck): array
    {
        $healthDetails = json_encode($healthCheck['details']);
        
        $prompt = <<<PROMPT
Analyze deployment to {$target} for task #{$task->id}: {$task->title}

**DEPLOYMENT:**
- Duration: {$deployResult['duration_ms']}ms
- Commit: {$deployResult['commit_hash']}

**HEALTH CHECKS:**
{$healthDetails}

Return JSON:
- summary: Deployment summary
- recommendation: "success" or "rollback"
- reason: Explanation

**CRITERIA:**
- SUCCESS: All health checks passed
- ROLLBACK: Any health check failed
PROMPT;
        
        return $this->callAIJson($agent, $prompt, 1000);
    }
    
    /**
     * Rollback deployment.
     */
    protected function rollbackDeployment(Task $task, Agent $agent, string $target): void
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $previousCommit = trim(Process::path($repoPath)->run('git rev-parse HEAD~1')->output());
        
        Process::path($repoPath)->run('git reset --hard ' . $previousCommit);
        Process::path($repoPath)->timeout(300)->run('composer install --no-dev --optimize-autoloader');
        Process::path($repoPath)->timeout(60)->run('php artisan migrate --force');
        Process::path($repoPath)->run('php artisan optimize');
        Process::run('sudo systemctl reload php-fpm');
        Process::run('sudo systemctl reload nginx');
        
        $this->logActivity($task, $agent, 'rollback', [
            'target' => $target,
            'commit' => $previousCommit,
        ]);
    }
}
