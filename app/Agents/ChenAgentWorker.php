<?php

namespace App\Agents;

use App\Models\Task;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;
use Illuminate\Support\Facades\Process;

/**
 * Chen - DevOps Deployment Agent Worker
 * 
 * Worker-tier agent responsible for:
 * - Deploying to staging environment
 * - Deploying to production environment
 * - Running health checks
 * - Managing Docker containers
 * - Monitoring deployment status
 * - Rollback on failures
 * 
 * Uses Qwen3-Coder via Ollama Cloud for deployment analysis
 * Polls every 30 seconds for deployment tasks
 */
class ChenAgentWorker extends AgentWorker
{
    public string $name = 'chen';
    
    public AgentType $type = AgentType::WORKER;
    
    public int $pollInterval = 30; // 30 seconds (fast polling for execution)
    
    public array $capabilities = ['deploy', 'staging', 'production', 'docker', 'kubernetes', 'healthcheck', 'rollback'];
    
    // Model loaded from DB via $agent->model // Ollama Cloud model
    
    /**
     * Poll for deployment tasks
     */
    protected function pollForWork(): ?Task
    {
        $task = Task::where('assigned_to', 'chen')
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereIn('current_step', ['staging', 'production'])
            ->orderBy('created_at', 'asc')
            ->first();
        
        return $task;
    }
    
    /**
     * Process a deployment task
     */
    protected function processTask(Task $task): void
    {
        try {
            echo "🔍 Chen analyzing deployment task #{$task->id}: {$task->title}\n";
            $this->logActivity($task, 'started', ['action' => 'deployment_analysis']);
            
            // Determine deployment target
            $target = $task->current_step; // 'staging' or 'production'
            
            // Step 1: Checkout the verified branch
            $branchName = $this->checkoutBranch($task);
            echo "🌿 Chen checked out branch: {$branchName}\n";
            
            // Step 2: Run pre-deployment checks
            echo "✅ Running pre-deployment checks...\n";
            $preChecks = $this->runPreDeploymentChecks($task, $target);
            
            if (!$preChecks['passed']) {
                throw new \Exception("Pre-deployment checks failed: " . implode(', ', $preChecks['failures']));
            }
            
            // Step 3: Execute deployment
            echo "🚀 Deploying to {$target}...\n";
            $deployResult = $this->executeDeployment($task, $target);
            
            // Step 4: Run health checks
            echo "🏥 Running post-deployment health checks...\n";
            $healthCheck = $this->runHealthChecks($task, $target);
            
            // Step 5: Analyze deployment with AI
            echo "🤖 Chen analyzing deployment results...\n";
            $analysis = $this->analyzeDeployment($task, $target, $deployResult, $healthCheck);
            
            // Step 6: Build artifacts
            $artifacts = [
                'target' => $target,
                'branch' => $branchName,
                'commit_hash' => $deployResult['commit_hash'] ?? null,
                'deployment_id' => $deployResult['deployment_id'] ?? null,
                'duration_ms' => $deployResult['duration_ms'] ?? 0,
                'health_check_passed' => $healthCheck['passed'],
                'health_check_details' => $healthCheck['details'] ?? [],
                'ai_analysis' => $analysis['summary'],
                'rollback_available' => $deployResult['rollback_available'] ?? true,
            ];
            
            // Step 7: Decision - advance or rollback?
            if ($analysis['recommendation'] === 'success') {
                echo "✅ Chen: Deployment to {$target} SUCCESSFUL\n";
                echo "   Summary: {$analysis['summary']}\n";
                
                if ($target === 'staging') {
                    // Advance to production deployment
                    $this->completeTask($task, 'production', 'chen', [
                        'deployment_results' => $artifacts,
                        'ai_summary' => $analysis['summary'],
                    ]);
                } else {
                    // Production complete - task done!
                    $task->update([
                        'status' => 'complete',
                        'current_step' => 'complete',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->logActivity($task, 'completed', $artifacts);
                    echo "✅ Chen: Production deployment COMPLETE - Task finished\n";
                }
                
            } else {
                echo "❌ Chen: Deployment FAILED - Initiating rollback\n";
                echo "   Reason: {$analysis['reason']}\n";
                
                // Attempt rollback
                $rollbackResult = $this->rollbackDeployment($task, $target);
                
                $this->failTask($task, $analysis['reason'], $target === 'staging' ? 'security' : 'staging', $target === 'staging' ? 'security' : 'chen');
            }
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Chen failed deployment task #{$task->id}: {$e->getMessage()}", [
                'task' => $task->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->failTask($task, $e->getMessage(), $task->current_step, 'chen');
        }
    }
    
    /**
     * Checkout the deployment branch
     */
    protected function checkoutBranch(Task $task): string
    {
        $gitService = $this->gitService($task);
        $branchName = "feature/{$task->id}-" . \Illuminate\Support\Str::slug($task->title);
        $gitService->checkoutBranch($branchName, true);
        return $branchName;
    }
    
    /**
     * Run pre-deployment validation checks
     */
    protected function runPreDeploymentChecks(Task $task, string $target): array
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $checks = [
            'git_status' => false,
            'dependencies' => false,
            'env_config' => false,
            'database_migrations' => false,
        ];
        $failures = [];
        
        // Check 1: Git status (clean working directory)
        $gitStatus = Process::path($repoPath)->run('git status --porcelain');
        $checks['git_status'] = trim($gitStatus->output()) === '';
        if (!$checks['git_status']) {
            $failures[] = 'Working directory not clean';
        }
        
        // Check 2: Composer dependencies installed
        $composerCheck = Process::path($repoPath)->run('test -d vendor && echo "ok" || echo "missing"');
        $checks['dependencies'] = trim($composerCheck->output()) === 'ok';
        if (!$checks['dependencies']) {
            $failures[] = 'Vendor directory missing - run composer install';
        }
        
        // Check 3: Environment configuration
        $envCheck = Process::path($repoPath)->run('test -f .env && echo "ok" || echo "missing"');
        $checks['env_config'] = trim($envCheck->output()) === 'ok';
        if (!$checks['env_config']) {
            $failures[] = '.env file missing';
        }
        
        // Check 4: Database migrations status
        $migrationCheck = Process::path($repoPath)
            ->timeout(60)
            ->run('php artisan migrate:status');
        $checks['database_migrations'] = ($migrationCheck->exitCode() === 0);
        if (!$checks['database_migrations']) {
            $failures[] = 'Database migration check failed';
        }
        
        return [
            'passed' => empty($failures),
            'checks' => $checks,
            'failures' => $failures,
        ];
    }
    
    /**
     * Execute deployment to target environment
     */
    protected function executeDeployment(Task $task, string $target): array
    {
        $startTime = microtime(true);
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $deploymentId = 'deploy-' . $task->id . '-' . $target . '-' . time();
        
        // Deployment strategy varies by target
        if ($target === 'staging') {
            // Staging deployment (full rebuild)
            echo "  📦 Running composer install --no-dev...\n";
            Process::path($repoPath)->timeout(300)->run('composer install --no-dev --optimize-autoloader');
            
            echo "  🎨 Running npm build...\n";
            Process::path($repoPath)->timeout(120)->run('npm run build');
            
            echo "  🗄️  Running database migrations...\n";
            Process::path($repoPath)->timeout(60)->run('php artisan migrate --force');
            
            echo "  🧹 Clearing caches...\n";
            Process::path($repoPath)->run('php artisan config:cache && php artisan route:cache && php artisan view:cache');
            
            echo "  🔄 Restarting PHP-FPM...\n";
            Process::run('sudo systemctl reload php-fpm'); // May need sudo
            
        } elseif ($target === 'production') {
            // Production deployment (zero-downtime strategy)
            echo "  🎯 Deploying to production (zero-downtime)...\n";
            
            // Pull latest code
            Process::path($repoPath)->timeout(120)->run('git pull origin main');
            
            // Install dependencies
            Process::path($repoPath)->timeout(300)->run('composer install --no-dev --optimize-autoloader');
            Process::path($repoPath)->timeout(120)->run('npm run build');
            
            // Run migrations
            Process::path($repoPath)->timeout(60)->run('php artisan migrate --force');
            
            // Clear and cache
            Process::path($repoPath)->run('php artisan optimize:clear && php artisan optimize');
            
            // Restart services
            Process::run('sudo systemctl reload php-fpm');
            Process::run('sudo systemctl reload nginx');
        }
        
        $duration = (microtime(true) - $startTime) * 1000;
        $commitHash = Process::path($repoPath)->run('git rev-parse HEAD')->output();
        
        return [
            'deployment_id' => $deploymentId,
            'commit_hash' => trim($commitHash),
            'duration_ms' => $duration,
            'rollback_available' => true,
        ];
    }
    
    /**
     * Run post-deployment health checks
     */
    protected function runHealthChecks(Task $task, string $target): array
    {
        $healthUrl = $target === 'staging' 
            ? 'https://staging.lunaos.test/health' 
            : 'https://lunaos.test/health';
        
        $details = [];
        $allPassed = true;
        
        // Health check 1: HTTP endpoint
        $httpHealthy = $this->checkHttpHealth($healthUrl);
        $details['http_endpoint'] = $httpHealthy;
        $allPassed = $allPassed && $httpHealthy;
        
        // Health check 2: Database connection
        $dbHealthy = $this->checkDatabaseHealth($task);
        $details['database'] = $dbHealthy;
        $allPassed = $allPassed && $dbHealthy;
        
        // Health check 3: Cache connection
        $cacheHealthy = $this->checkCacheHealth($task);
        $details['cache'] = $cacheHealthy;
        $allPassed = $allPassed && $cacheHealthy;
        
        return [
            'passed' => $allPassed,
            'details' => $details,
        ];
    }
    
    /**
     * Check HTTP endpoint health
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
     * Check database health
     */
    protected function checkDatabaseHealth(Task $task): bool
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $result = Process::path($repoPath)
            ->timeout(10)
            ->run('php artisan tinker --execute="DB::connection()->getPdo();"');
        
        return ($result->exitCode() === 0);
    }
    
    /**
     * Check cache health
     */
    protected function checkCacheHealth(Task $task): bool
    {
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        $result = Process::path($repoPath)
            ->timeout(10)
            ->run('php artisan tinker --execute="Cache::put(\'health_check\', true, 1); Cache::get(\'health_check\');"');
        
        return ($result->exitCode() === 0);
    }
    
    /**
     * Analyze deployment results with AI
     */
    protected function analyzeDeployment(Task $task, string $target, array $deployResult, array $healthCheck): array
    {
        $model = $this->getModel();
        $provider = $this->getProvider();
        
        $healthStatus = json_encode($healthCheck['details']);
        
        $prompt = <<<PROMPT
Analyze this deployment to {$target} for task #{$task->id}: {$task->title}

**DEPLOYMENT DETAILS:**
- Target: {$target}
- Branch: {$task->title}
- Duration: {$deployResult['duration_ms']}ms
- Commit: {$deployResult['commit_hash']}

**HEALTH CHECK RESULTS:**
{$healthStatus}

**YOUR TASK:**
1. Analyze the deployment success
2. Determine if the deployment was successful
3. Recommend next steps

Return JSON:
{
  "summary": "Brief summary of deployment results",
  "recommendation": "success|rollback",
  "reason": "Detailed explanation"
}

**CRITERIA:**
- SUCCESS: All health checks passed, deployment completed without errors
- ROLLBACK: Any health check failed, deployment errors, or service unavailable
PROMPT;
        
        $response = Ai::agent()
            ->withLab($provider)
            ->withModel($model)
            ->withMaxTokens(1000)
            ->run($prompt);
        
        // Parse JSON
        $content = (string) $response;
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
        
        $result = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback analysis
            return [
                'summary' => 'Deployment completed (manual analysis)',
                'recommendation' => $healthCheck['passed'] ? 'success' : 'rollback',
                'reason' => $healthCheck['passed'] ? 'All health checks passed' : 'Health check failures detected',
            ];
        }
        
        return $result;
    }
    
    /**
     * Rollback to previous deployment
     */
    protected function rollbackDeployment(Task $task, string $target): array
    {
        echo "  ↩️  Rolling back {$target} deployment...\n";
        
        $repo = $this->getRepository($task);
        $repoPath = $repo?->path ?? base_path();
        
        // Get previous commit
        $previousCommit = Process::path($repoPath)
            ->run('git rev-parse HEAD~1')
            ->output();
        
        echo "  Rolling back to: " . trim($previousCommit) . "\n";
        
        // Reset to previous commit
        Process::path($repoPath)->run('git reset --hard ' . trim($previousCommit));
        
        // Redeploy previous version
        Process::path($repoPath)->timeout(300)->run('composer install --no-dev --optimize-autoloader');
        Process::path($repoPath)->timeout(60)->run('php artisan migrate --force');
        Process::path($repoPath)->run('php artisan optimize');
        
        // Restart services
        Process::run('sudo systemctl reload php-fpm');
        Process::run('sudo systemctl reload nginx');
        
        $this->logActivity($task, 'rollback', [
            'target' => $target,
            'rolled_back_to' => trim($previousCommit),
        ]);
        
        return ['rollback_commit' => trim($previousCommit)];
    }
}
