#!/usr/bin/env php
<?php

/**
 * Sam & Chen Worker Agent Test Script
 * 
 * Tests the unified AgentWorker pattern with Sam (QA) and Chen (DevOps)
 * 
 * Usage: php tests/sam-chen-test.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Agents\SamAgentWorker;
use App\Agents\ChenAgentWorker;
use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;
use Illuminate\Support\Str;

echo "===========================================\n";
echo "🧪 Sam & Chen Worker Agent Test\n";
echo "===========================================\n\n";

// Ensure Sam and Chen exist in database
echo "📋 Verifying agent configurations...\n";

$sam = Agent::where('name', 'sam')->first();
$chen = Agent::where('name', 'chen')->first();

if (!$sam) {
    echo "❌ Sam not found in database. Run migration first.\n";
    exit(1);
}

if (!$chen) {
    echo "❌ Chen not found in database. Run migration first.\n";
    exit(1);
}

echo "✅ Sam found: {$sam->display_name} ({$sam->emoji}) - Model: {$sam->model}\n";
echo "✅ Chen found: {$chen->display_name} ({$chen->emoji}) - Model: {$chen->model}\n\n";

// Create test tasks for Sam and Chen
echo "📋 Creating test tasks...\n";

$testTasks = [];

// Task 1: For Sam (QA testing)
$task1 = Task::create([
    'title' => 'Test Login Flow After Auth Update',
    'description' => 'Dave just updated the authentication flow. Run PHPUnit tests for AuthController and Dusk tests for login page.',
    'status' => 'pending',
    'step' => 'qa',
    'assigned_to' => 'sam',
    'priority' => 'high',
    'task_type' => 'feature',
]);
$testTasks[] = $task1;
echo "✅ Created task #1 for Sam (QA)\n";

// Task 2: For Chen (Staging deployment)
$task2 = Task::create([
    'title' => 'Deploy Feature Branch to Staging',
    'description' => 'Sam approved the changes. Deploy feature/auth-improvements to staging environment and run health checks.',
    'status' => 'pending',
    'step' => 'staging',
    'assigned_to' => 'chen',
    'priority' => 'medium',
    'task_type' => 'deployment',
]);
$testTasks[] = $task2;
echo "✅ Created task #2 for Chen (Staging)\n";

// Task 3: For Chen (Production deployment)
$task3 = Task::create([
    'title' => 'Deploy Hotfix to Production',
    'description' => 'Critical bug fix for login redirect. Deploy to production with zero-downtime strategy.',
    'status' => 'pending',
    'step' => 'production',
    'assigned_to' => 'chen',
    'priority' => 'critical',
    'task_type' => 'hotfix',
]);
$testTasks[] = $task3;
echo "✅ Created task #3 for Chen (Production)\n\n";

echo "===========================================\n";
echo "🤖 Testing Agent Instantiation\n";
echo "===========================================\n\n";

// Test Sam instantiation
echo "🧪 Testing Sam (QA) agent...\n";
try {
    $samAgent = new SamAgentWorker();
    echo "✅ Sam instantiated successfully\n";
    echo "   Name: {$samAgent->name}\n";
    echo "   Type: {$samAgent->type->value}\n";
    echo "   Poll Interval: {$samAgent->pollInterval}s\n";
    echo "   Capabilities: " . implode(', ', $samAgent->capabilities) . "\n\n";
    
    // Test polling via Task model directly
    echo "   Testing work availability...\n";
    $polledTask = \App\Models\Task::where('assigned_to', 'sam')
        ->whereIn('status', ['pending', 'in_progress'])
        ->where('step', 'qa')
        ->first();
    if ($polledTask) {
        echo "   ✅ Work available: #{$polledTask->title}\n";
    } else {
        echo "   ⚠️  No work found\n";
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ Sam instantiation failed: {$e->getMessage()}\n\n";
}

// Test Chen instantiation
echo "🚀 Testing Chen (DevOps) agent...\n";
try {
    $chenAgent = new ChenAgentWorker();
    echo "✅ Chen instantiated successfully\n";
    echo "   Name: {$chenAgent->name}\n";
    echo "   Type: {$chenAgent->type->value}\n";
    echo "   Poll Interval: {$chenAgent->pollInterval}s\n";
    echo "   Capabilities: " . implode(', ', $chenAgent->capabilities) . "\n\n";
    
    // Test polling via Task model directly
    echo "   Testing work availability...\n";
    $polledTask = \App\Models\Task::where('assigned_to', 'chen')
        ->whereIn('status', ['pending', 'in_progress'])
        ->whereIn('step', ['staging', 'production'])
        ->first();
    if ($polledTask) {
        echo "   ✅ Work available: #{$polledTask->title} (step: {$polledTask->step})\n";
    } else {
        echo "   ⚠️  No work found\n";
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ Chen instantiation failed: {$e->getMessage()}\n\n";
}

echo "===========================================\n";
echo "📊 Unified Pattern Verification\n";
echo "===========================================\n\n";

// Verify all workers share the same base class
$agents = [
    'Dave' => new \App\Agents\DaveAgentWorker(),
    'Sam' => new SamAgentWorker(),
    'Chen' => new ChenAgentWorker(),
];

foreach ($agents as $name => $agent) {
    echo "🤖 {$name}:\n";
    echo "   - Base class: " . get_parent_class($agent) . "\n";
    echo "   - Type: {$agent->type->value}\n";
    echo "   - Is Worker: " . ($agent->isWorker() ? '✅' : '❌') . "\n";
    echo "   - Poll interval: {$agent->pollInterval}s\n";
    echo "   - Capabilities: " . count($agent->capabilities) . " skills\n";
    echo "\n";
}

echo "===========================================\n";
echo "🧹 Cleanup Test Data\n";
echo "===========================================\n\n";

echo "Removing test tasks...\n";
foreach ($testTasks as $task) {
    $task->delete();
}
echo "✅ Test tasks removed\n\n";

echo "Removing activity logs...\n";
AgentActivity::where('agent_name', 'sam')->delete();
AgentActivity::where('agent_name', 'chen')->delete();
echo "✅ Activity logs cleaned up\n\n";

echo "===========================================\n";
echo "✅ Sam & Chen Worker Test COMPLETE\n";
echo "===========================================\n\n";

echo "Next Steps:\n";
echo "1. Run migration: php artisan migrate\n";
echo "2. Start agents:\n";
echo "   - php artisan tinker (then: new SamAgentWorker())->run()\n";
echo "   - php artisan tinker (then: new ChenAgentWorker())->run()\n";
echo "3. Monitor Kanban board at http://lunaos.test/kanban\n\n";

echo "Architecture Notes:\n";
echo "- Sam (QA): Polls every 30s for 'qa' step tasks\n";
echo "- Chen (DevOps): Polls every 30s for 'staging'/'production' steps\n";
echo "- Both use Qwen3-Coder via Ollama Cloud\n";
echo "- Unified Worker pattern with AgentWorker base class\n";
echo "- Full transparency: All decisions logged to agent_activities\n\n";
