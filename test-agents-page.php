<?php

// Quick test to check if AgentList component loads
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Agent;
use App\Livewire\Agents\AgentList;

echo "Testing AgentList Component\n";
echo "=========================\n\n";

// Test 1: Can we query agents?
echo "1. Agent count: " . Agent::count() . "\n";

// Test 2: Can we access with() relationship?
try {
    $agents = Agent::with('tasks')->latest()->get();
    echo "2. Agent query with tasks: SUCCESS (" . $agents->count() . " agents)\n";
} catch (\Exception $e) {
    echo "2. Agent query with tasks: FAILED - " . $e->getMessage() . "\n";
}

// Test 3: Can we instantiate the Livewire component?
try {
    $component = new AgentList();
    echo "3. Component instantiation: SUCCESS\n";
} catch (\Exception $e) {
    echo "3. Component instantiation: FAILED - " . $e->getMessage() . "\n";
}

// Test 4: Can we call render()?
try {
    $component = new AgentList();
    $result = $component->render();
    echo "4. Component render(): SUCCESS\n";
    echo "   View: " . $result->name() . "\n";
} catch (\Exception $e) {
    echo "4. Component render(): FAILED - " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 5: Check StrategyRegistry
try {
    $strategies = \App\Agents\Strategies\StrategyRegistry::all();
    echo "5. StrategyRegistry::all(): SUCCESS (" . count($strategies) . " strategies)\n";
} catch (\Exception $e) {
    echo "5. StrategyRegistry::all(): FAILED - " . $e->getMessage() . "\n";
}

echo "\n";
