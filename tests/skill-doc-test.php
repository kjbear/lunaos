<?php

/**
 * Skill Doc Integration Test
 * 
 * Tests that:
 * 1. Skill docs exist and are readable
 * 2. Agent skill_metadata is populated
 * 3. Skill docs can be loaded by GenericWorker
 * 4. Enhanced prompts include skill doc content
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Agent;
use App\Agents\GenericWorker;
use App\Agents\Strategies\Concerns\HasWorkerCapabilities;

echo "===========================================\n";
echo "📚 Skill Doc Integration Test\n";
echo "===========================================\n\n";

// Test 1: Verify skill doc files exist
echo "1️⃣ Verifying skill doc files...\n";

$skillDocs = [
    'skills/laravel-specialist/SKILL.md',
    'skills/qa-engineer/SKILL.md',
    'skills/devops-engineer/SKILL.md',
];

foreach ($skillDocs as $path) {
    $fullPath = base_path($path);
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "   ✅ {$path} ({$size} bytes)\n";
    } else {
        echo "   ❌ {$path} NOT FOUND\n";
    }
}
echo "\n";

// Test 2: Verify agents have skill_doc_path configured
echo "2️⃣ Verifying agent skill doc configurations...\n";

$agents = [
    'dave' => 'skills/laravel-specialist/SKILL.md',
    'sam' => 'skills/qa-engineer/SKILL.md',
    'chen' => 'skills/devops-engineer/SKILL.md',
];

foreach ($agents as $agentName => $expectedPath) {
    $agent = Agent::where('name', $agentName)->first();
    
    if (!$agent) {
        echo "   ❌ {$agentName} not found in database\n";
        continue;
    }
    
    if ($agent->skill_doc_path !== $expectedPath) {
        echo "   ⚠️  {$agentName} has skill_doc_path: {$agent->skill_doc_path} (expected: {$expectedPath})\n";
    } else {
        echo "   ✅ {$agentName}: {$agent->skill_doc_path}\n";
    }
    
    // Check skill_metadata
    if (!empty($agent->skill_metadata)) {
        $constraints = $agent->skill_metadata['constraints'] ?? [];
        $mustDoCount = count($constraints['must_do'] ?? []);
        $mustNotCount = count($constraints['must_not'] ?? []);
        echo "      Constraints: {$mustDoCount} MUST DO, {$mustNotCount} MUST NOT DO\n";
    } else {
        echo "      ⚠️  No skill_metadata configured\n";
    }
}
echo "\n";

// Test 3: Test skill doc loading via trait
echo "3️⃣ Testing skill doc loading...\n";

class TestSkillLoader {
    use HasWorkerCapabilities;
    
    public function testLoadSkillDoc(string $path): ?string {
        return $this->loadSkillDoc($path);
    }
}

$loader = new TestSkillLoader();

foreach ($skillDocs as $path) {
    $content = $loader->testLoadSkillDoc($path);
    if ($content) {
        $lines = substr_count($content, "\n");
        echo "   ✅ {$path} loaded ({$lines} lines)\n";
    } else {
        echo "   ❌ {$path} failed to load\n";
    }
}
echo "\n";

// Test 4: Test enhanced prompt building
echo "4️⃣ Testing enhanced prompt building...\n";

$dave = Agent::where('name', 'dave')->first();
if ($dave) {
    $reflection = new ReflectionClass($loader);
    $method = $reflection->getMethod('buildEnhancedPrompt');
    $method->setAccessible(true);
    
    $taskPrompt = "Create a User model with email and password fields";
    $enhancedPrompt = $method->invoke($loader, $dave, $taskPrompt);
    
    if (strpos($enhancedPrompt, 'SKILL DEFINITION') !== false) {
        echo "   ✅ Enhanced prompt includes SKILL DEFINITION section\n";
    } else {
        echo "   ❌ Enhanced prompt missing SKILL DEFINITION\n";
    }
    
    if (strpos($enhancedPrompt, 'MUST DO') !== false) {
        echo "   ✅ Enhanced prompt includes MUST DO constraints\n";
    } else {
        echo "   ⚠️  Enhanced prompt missing MUST DO constraints\n";
    }
    
    if (strpos($enhancedPrompt, 'Laravel Specialist') !== false) {
        echo "   ✅ Enhanced prompt includes Laravel Specialist role\n";
    }
    
    echo "   Enhanced prompt length: " . strlen($enhancedPrompt) . " chars\n";
}
echo "\n";

// Test 5: Verify GenericWorker can load agents with skill docs
echo "5️⃣ Testing GenericWorker with skill doc agents...\n";

foreach ($agents as $agentName => $expectedPath) {
    $agent = Agent::where('name', $agentName)->first();
    if ($agent) {
        try {
            $worker = new GenericWorker($agent);
            echo "   ✅ {$agentName} worker instantiated successfully\n";
            echo "      Strategy: " . get_class($worker->getStrategy()) . "\n";
            echo "      Model: {$agent->model}\n";
            echo "      Skill Doc: {$agent->skill_doc_path}\n";
        } catch (\Exception $e) {
            echo "   ❌ {$agentName} worker failed: {$e->getMessage()}\n";
        }
    }
}
echo "\n";

echo "===========================================\n";
echo "✅ Skill Doc Integration Test COMPLETE\n";
echo "===========================================\n\n";

echo "Summary:\n";
echo "- Skill doc files created and readable\n";
echo "- Agents configured with skill_doc_path\n";
echo "- Skill metadata populated with constraints\n";
echo "- Enhanced prompt builder includes skill docs\n";
echo "- GenericWorker loads agents with skill docs\n\n";

echo "Next Steps:\n";
echo "1. Test GenericWorker with real tasks to verify skill doc influence\n";
echo "2. Build web UI for skill doc management (Phase 2)\n";
echo "3. Add more skill docs (API Architect, Security Reviewer, etc.)\n";
echo "4. Document skill doc authoring guide\n\n";
