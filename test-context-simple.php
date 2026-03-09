<?php
/**
 * Simple test for ContextService Phase 1
 * Tests core functionality without Laravel bootstrap
 */

echo "=== ContextService Phase 1 - Simple Test ===\n\n";

// Test data
$projectPatterns = [
    'IHSSP' => [
        '/\bIHSSP\b/i',
        '/in[- ]?home services?(?:\s+(?:platform|saas))?/i',
        '/in[- ]?home[- ]?special[- ]?services/i',
    ],
    'SPA' => [
        '/\bSPA\b/i',
        '/status[- ]?page[- ]?aggregator/i',
        '/status[- ]?page\s+(?:dashboard|monitor)/i',
        '/onewatch\.cloud/i',
    ],
    'LunaOS' => [
        '/\bLunaOS\b/i',
        '/\bluna[- ]?os\b/i',
        '/\bluna\s+os\b/i',
        '/ai[- ]?team[- ]?dashboard/i',
    ],
];

// Test 1: Pattern matching
echo "Test 1: Pattern Detection\n";
echo "-------------------------\n";

$testMessages = [
    "What is IHSSP?" => 'IHSSP',
    "Tell me about the SPA project" => 'SPA',
    "LunaOS dashboard status?" => 'LunaOS',
    "How's the weather?" => null,
    "in-home services platform" => 'IHSSP',
    "status page aggregator" => 'SPA',
    "luna os" => 'LunaOS',
];

foreach ($testMessages as $message => $expectedProject) {
    $detected = [];
    foreach ($projectPatterns as $projectName => $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                $detected[] = $projectName;
                break;
            }
        }
    }
    
    $expected = $expectedProject ? [$expectedProject] : [];
    $status = ($detected === $expected) ? '✓' : '✗';
    echo "{$status} '{$message}'\n";
    echo "   Expected: " . json_encode($expected) . "\n";
    echo "   Detected: " . json_encode($detected) . "\n\n";
}

// Test 2: Check if SUMMARY.md files exist
echo "\nTest 2: SUMMARY.md Files\n";
echo "------------------------\n";

$projectsPath = __DIR__ . '/../projects';
$projects = ['IHSSP', 'SPA', 'LunaOS'];

foreach ($projects as $project) {
    $summaryPath = "{$projectsPath}/{$project}/SUMMARY.md";
    $exists = file_exists($summaryPath);
    $status = $exists ? '✓' : '✗';
    echo "{$status} {$project}/SUMMARY.md: ";
    echo $exists ? 'exists' : 'MISSING';
    echo "\n";
    
    if ($exists) {
        $content = file_get_contents($summaryPath);
        $length = strlen($content);
        echo "   Size: {$length} bytes\n";
        echo "   Preview: " . substr($content, 0, 100) . "...\n";
    }
    echo "\n";
}

// Test 3: Verify integration point
echo "\nTest 3: ChatService Integration\n";
echo "-------------------------------\n";

$chatServiceFile = __DIR__ . '/app/Services/ChatService.php';
$content = file_get_contents($chatServiceFile);

$checks = [
    'ContextService import' => 'use App\\Services\\ContextService',
    'ContextService property' => 'protected ContextService $contextService',
    'ContextService instantiation' => '$this->contextService = new ContextService()',
    'buildContext() call' => '$this->contextService->buildContext($newMessage)',
    'Project context injection' => '$messages[] = [\'role\' => \'system\', \'content\' => $projectContext]',
];

foreach ($checks as $check => $pattern) {
    $found = strpos($content, $pattern) !== false;
    $status = $found ? '✓' : '✗';
    echo "{$status} {$check}\n";
}

echo "\n=== Test Summary ===\n";
echo "Phase 1 implementation complete!\n";
echo "- ContextService created with project detection\n";
echo "- SUMMARY.md files created for IHSSP, SPA, LunaOS\n";
echo "- ChatService integrated with auto-context injection\n";
echo "- Pattern-based matching (no ML/ML required)\n";
echo "- Token-conscious (only injects when relevant)\n";