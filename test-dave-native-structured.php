<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Ai\Agents\DaveCoder;
use Laravel\Ai\Enums\Lab;

echo "🧪 Testing Dave Coder - Native Structured Output\n";
echo "==================================================\n\n";

try {
    $dave = new DaveCoder;
    
    $taskTitle = "Create a simple PHP helper function";
    $taskDescription = "Create a PHP helper class at app/Helpers/StringHelpers.php with 3 static methods: slugify(), truncate(), and wordCount(). Include docblocks and strict types.";
    
    $prompt = <<<PROMPT
**Task:** {$taskTitle}

**Description:** 
{$taskDescription}

**Requirements:**
- Create file: app/Helpers/StringHelpers.php
- Methods: slugify(\\\$string), truncate(\\\$string, \\\$length), wordCount(\\\$string)
- Use strict types, comprehensive docblocks
- Follow PSR-12 standards

**Output:** Return JSON with summary, files array, tests_created (boolean), requires_migration (boolean)
PROMPT;

    echo "🤖 Spawning Dave Coder with gpt-oss:120b-cloud...\n";
    
    // Use NATIVE structured output
    $response = $dave->prompt($prompt, provider: Lab::Ollama, model: 'gpt-oss:120b-cloud', timeout: 120);
    
    echo "✅ Response received!\n\n";
    
    // Try native structured() method
    try {
        $result = $response->structured();
        echo "📊 Native Structured Output SUCCESS!\n\n";
        echo "Summary: " . ($result['summary'] ?? 'N/A') . "\n";
        echo "Files: " . count($result['files'] ?? []) . "\n";
        echo "Tests Created: " . json_encode($result['tests_created'] ?? null) . "\n";
        echo "Requires Migration: " . json_encode($result['requires_migration'] ?? null) . "\n\n";
        
        // Write the files
        if (!empty($result['files'])) {
            echo "📝 Writing files...\n";
            foreach ($result['files'] as $file) {
                $path = $file['path'];
                $fullPath = base_path($path);
                $dir = dirname($fullPath);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $bytes = file_put_contents($fullPath, $file['content']);
                echo "  ✅ {$file['action']}: {$path} ({$bytes} bytes)\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "⚠️ Native structured() failed: " . $e->getMessage() . "\n";
        echo "\n📄 Falling back to manual JSON extraction...\n\n";
        
        // Fallback to manual extraction
        $content = (string) $response;
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $result = json_decode(trim($content), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "❌ JSON parse failed: " . json_last_error_msg() . "\n";
            exit(1);
        }
        
        echo "✅ Manual extraction worked!\n";
        echo "Summary: " . ($result['summary'] ?? 'N/A') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Test complete!\n";
