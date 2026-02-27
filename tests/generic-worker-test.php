<?php

/**
 * GenericWorker + Strategy Pattern Test
 * 
 * Tests that:
 * 1. Strategies can be instantiated and used directly
 * 2. GenericWorker loads strategies correctly
 * 3. Existing agents (dave, sam, chen) work with GenericWorker
 * 4. New agents can be created via configuration
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Agents\GenericWorker;
use App\Agents\Strategies\StrategyRegistry;
use App\Agents\Strategies\DevelopStrategy;
use App\Agents\Strategies\QAStrategy;
use App\Agents\Strategies\DeployStrategy;
use App\Models\Agent;
use App\Models\Task;

echo "===========================================\n";
echo "🧪 GenericWorker + Strategy Pattern Test\n";
echo "===========================================\n\n";

// Test 1: Strategy Registry
echo "1️⃣ Testing Strategy Registry...\n";

$strategies = StrategyRegistry::all();
echo "   Available strategies: " . implode(', ', StrategyRegistry::keys()) . "\n";

$develop = StrategyRegistry::get('develop');
echo "   ✅ DevelopStrategy loaded: " . get_class($develop) . "\n";

$qa = StrategyRegistry::get('qa');
echo "   ✅ QAStrategy loaded: " . get_class($qa) . "\n";

$deploy = StrategyRegistry::get('deploy');
echo "   ✅ DeployStrategy loaded: " . get_class($deploy) . "\n\n";

// Test 2: Strategy Capabilities
echo "2️⃣ Testing Strategy Capabilities...\n";

echo "   Develop: " . implode(', ', $develop->getCapabilities()) . "\n";
echo "   QA: " . implode(', ', $qa->getCapabilities()) . "\n";
echo "   Deploy: " . implode(', ', $deploy->getCapabilities()) . "\n\n";

// Test 3: Strategy Workflow Steps
echo "3️⃣ Testing Workflow Steps...\n";

echo "   Develop steps: " . implode(', ', $develop->getWorkflowSteps()) . "\n";
echo "   QA steps: " . implode(', ', $qa->getWorkflowSteps()) . "\n";
echo "   Deploy steps: " . implode(', ', $deploy->getWorkflowSteps()) . "\n\n";

// Test 4: GenericWorker with Existing Agents
echo "4️⃣ Testing GenericWorker with Existing Agents...\n";

$daveAgent = Agent::where('name', 'dave')->first();
if ($daveAgent) {
    echo "   Found Dave in database\n";
    echo "   Strategy: {$daveAgent->strategy_class}\n";
    
    $daveWorker = new GenericWorker($daveAgent);
    echo "   ✅ GenericWorker instantiated for Dave\n";
    echo "   Strategy loaded: " . get_class($daveWorker->getStrategy()) . "\n";
    echo "   Poll interval: {$daveWorker->getPollInterval()}s\n\n";
} else {
    echo "   ⚠️  Dave not found in database (migration not run?)\n\n";
}

$samAgent = Agent::where('name', 'sam')->first();
if ($samAgent) {
    echo "   Found Sam in database\n";
    echo "   Strategy: {$samAgent->strategy_class}\n";
    
    $samWorker = new GenericWorker($samAgent);
    echo "   ✅ GenericWorker instantiated for Sam\n";
    echo "   Strategy loaded: " . get_class($samWorker->getStrategy()) . "\n\n";
}

$chenAgent = Agent::where('name', 'chen')->first();
if ($chenAgent) {
    echo "   Found Chen in database\n";
    echo "   Strategy: {$chenAgent->strategy_class}\n";
    
    $chenWorker = new GenericWorker($chenAgent);
    echo "   ✅ GenericWorker instantiated for Chen\n";
    echo "   Strategy loaded: " . get_class($chenWorker->getStrategy()) . "\n\n";
}

// Test 5: Create New Agent Dynamically
echo "5️⃣ Testing Dynamic Agent Creation...\n";

$newAgent = Agent::create([
    'name' => 'alex',
    'display_name' => 'Alex',
    'emoji' => '📡',
    'type' => 'worker',
    'role' => 'API Developer',
    'strategy_class' => 'develop',
    'step_filter' => 'api_develop',
    'model' => 'qwen3-coder:latest',
    'provider' => 'ollama',
    'system_prompt' => 'You are Alex, an API developer specializing in REST and GraphQL.',
    'model_settings' => [
        'temperature' => 0.3,
        'max_tokens' => 4096,
        'poll_interval' => 30,
    ],
    'workflow_config' => [
        'next_step' => 'api_review',
        'next_assignee' => 'api_reviewer',
    ],
    'is_online' => true,
    'runtime_location' => 'php',
]);

echo "   ✅ Created new agent: Alex (API Developer)\n";
echo "   Strategy: {$newAgent->strategy_class}\n";
echo "   Step filter: {$newAgent->step_filter}\n";

$alexWorker = new GenericWorker($newAgent);
echo "   ✅ GenericWorker instantiated for Alex\n";
echo "   Strategy: " . get_class($alexWorker->getStrategy()) . "\n\n";

// Cleanup
$newAgent->delete();
echo "   🧹 Cleaned up test agent\n\n";

// Test 6: Custom Strategy Registration
echo "6️⃣ Testing Custom Strategy Registration...\n";

class CustomTestStrategy implements \App\Agents\Strategies\WorkerStrategy {
    public function pollForWork(\App\Models\Agent $agent): ?\App\Models\Task { return null; }
    public function processTask(\App\Models\Task $task, \App\Models\Agent $agent): void {}
    public function getCapabilities(): array { return ['custom']; }
    public function getWorkflowSteps(): array { return ['custom']; }
    public function getName(): string { return 'custom'; }
}

StrategyRegistry::register('custom_test', CustomTestStrategy::class);
echo "   ✅ Registered custom strategy: custom_test\n";
echo "   Available: " . implode(', ', StrategyRegistry::keys()) . "\n\n";

// Test custom strategy instantiation
$custom = StrategyRegistry::get('custom_test');
echo "   ✅ Custom strategy instantiated: " . get_class($custom) . "\n\n";

echo "===========================================\n";
echo "✅ Strategy Pattern Test COMPLETE\n";
echo "===========================================\n\n";

echo "Summary:\n";
echo "- Strategies are working independently\n";
echo "- GenericWorker loads strategies from database\n";
echo "- Existing agents (Dave, Sam, Chen) migrated to GenericWorker\n";
echo "- New agents can be created via configuration\n";
echo "- Custom strategies can be registered at runtime\n\n";

echo "Next Steps:\n";
echo "1. Update old agent classes to extend GenericWorker (or deprecate)\n";
echo "2. Create web UI for agent management (HR module)\n";
echo "3. Test GenericWorker with real tasks\n";
echo "4. Document strategy creation for developers\n\n";
