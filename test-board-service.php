<?php

/**
 * Test script for BoardService
 * 
 * Run with: php test-board-service.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Services\BoardService;
use App\Models\BoardSession;
use Illuminate\Support\Facades\Artisan;

echo "=== Board Service Test ===\n\n";

// Create the test app
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "✓ Laravel bootstrapped\n\n";

// Run migrations
echo "Running migrations...\n";
Artisan::call('migrate', ['--force' => true]);
echo "✓ Migrations complete\n\n";

// Test 1: Start a session
echo "Test 1: Starting a board session...\n";
try {
    $boardService = app(BoardService::class);
    
    $session = $boardService->startSession(
        "Should we prioritize LunaOS development or the Status Page Aggregator first?",
        ['COO', 'CFO', 'CTO']
    );
    
    echo "✓ Session created: {$session->id}\n";
    echo "  Question: {$session->question}\n";
    echo "  Status: {$session->status}\n";
    echo "  Participants: {$session->participants()->count()}\n\n";
    
    // Test 2: Run a debate round
    echo "Test 2: Running debate round 1...\n";
    try {
        $results = $boardService->runDebateRound($session->id, 1);
        echo "✓ Round 1 complete: " . count($results) . " responses\n\n";
        
        foreach ($results as $result) {
            echo "  {$result['emoji']} {$result['name']} ({$result['persona_role']}):\n";
            echo "    " . substr($result['response'], 0, 100) . "...\n\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Round failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test 3: Get transcript
    echo "Test 3: Getting transcript...\n";
    try {
        $transcript = $boardService->getTranscript($session->id);
        echo "✓ Transcript retrieved: " . count($transcript) . " entries\n\n";
        
        foreach ($transcript as $entry) {
            echo "  - {$entry['name']} ({$entry['persona_role']}): Round {$entry['round']}\n";
        }
        echo "\n";
        
    } catch (\Exception $e) {
        echo "✗ Transcript failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test 4: Consolidate decision
    echo "Test 4: Consolidating decision...\n";
    try {
        $decision = $boardService->consolidateDecision($session->id);
        
        if ($decision) {
            echo "✓ Decision consolidated:\n";
            echo "  ID: {$decision->id}\n";
            echo "  Decision: " . substr($decision->decision_text, 0, 100) . "...\n";
            echo "  Confidence: " . ($decision->confidence_score ?? 'N/A') . "\n";
            if ($decision->confidence_score) {
                echo "  Level: {$decision->confidence_level}\n";
            }
            echo "  Reasoning: " . ($decision->reasoning ? substr($decision->reasoning, 0, 100) . "..." : "N/A") . "\n";
        } else {
            echo "✗ Decision consolidation returned null (API issue?)\n";
        }
        echo "\n";
        
    } catch (\Exception $e) {
        echo "✗ Decision consolidation failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test 5: Close session
    echo "Test 5: Closing session...\n";
    try {
        $closedSession = $boardService->closeSession($session->id);
        echo "✓ Session closed: {$closedSession->status}\n\n";
        
    } catch (\Exception $e) {
        echo "✗ Close failed: " . $e->getMessage() . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "  At: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

echo "=== Tests Complete ===\n";
echo "\nNote: API-dependent tests will fail if OPENROUTER_API_KEY is not configured.\n";
echo "Add OPENROUTER_API_KEY=your_key to your .env file to enable full functionality.\n";
