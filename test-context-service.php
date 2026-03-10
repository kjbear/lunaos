<?php
/**
 * Test script for ContextService Phase 1
 * 
 * Run from lunaos directory:
 * php test-context-service.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ContextService;

echo "=== ContextService Phase 1 Test ===\n\n";

$contextService = new ContextService();

// Test 1: Project Detection
echo "Test 1: Project Detection\n";
echo "-------------------------\n";

$testMessages = [
    "What is IHSSP?" => ['IHSSP'],
    "Tell me about the SPA project" => ['SPA'],
    "LunaOS dashboard status?" => ['LunaOS'],
    "How's the weather?" => [],
    "IHSSP and SPA comparison" => ['IHSSP', 'SPA'],
    "in-home services platform" => ['IHSSP'],
    "status page aggregator" => ['SPA'],
    "luna os dashboard" => ['LunaOS'],
];

foreach ($testMessages as $message => $expectedProjects) {
    $detected = $contextService->detectProjects($message);
    $status = ($detected === $expectedProjects) ? '✓' : '✗';
    echo "{$status} '{$message}'\n";
    echo "   Expected: " . json_encode($expectedProjects) . "\n";
    echo "   Detected: " . json_encode($detected) . "\n\n";
}

// Test 2: Summary Loading
echo "\nTest 2: Summary Loading\n";
echo "----------------------\n";

$projects = ['IHSSP', 'SPA', 'LunaOS'];
foreach ($projects as $project) {
    $summary = $contextService->loadProjectSummary($project);
    $status = $summary ? '✓' : '✗';
    $length = $summary ? strlen($summary) : 0;
    echo "{$status} {$project}: {$length} chars\n";
    if ($summary) {
        $preview = substr($summary, 0, 100) . '...';
        echo "   Preview: {$preview}\n\n";
    }
}

// Test 3: Context Building
echo "\nTest 3: Context Building\n";
echo "-----------------------\n";

$testCases = [
    "What is IHSSP?",
    "SPA status check",
    "Tell me about LunaOS",
    "How's the weather?", // Should return empty
];

foreach ($testCases as $message) {
    echo "Message: '{$message}'\n";
    $context = $contextService->buildContext($message);
    if ($context) {
        echo "✓ Context injected:\n";
        echo substr($context, 0, 200) . "...\n\n";
    } else {
        echo "✓ No context (no project mentioned)\n\n";
    }
}

// Test 4: Token Estimation
echo "\nTest 4: Context Size\n";
echo "--------------------\n";

foreach ($projects as $project) {
    $summary = $contextService->loadProjectSummary($project);
    if ($summary) {
        $tokens = (int) ceil(strlen($summary) / 4);
        echo "$project: ~{$tokens} tokens\n";
    }
}

echo "\n=== All Tests Complete ===\n";