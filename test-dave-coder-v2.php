<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Ai\Agents\DaveCoder;
use Laravel\Ai\Enums\Lab;

echo "🧪 Testing Dave Coder Agent - Extract JSON manually\n";
echo "====================================================\n\n";

try {
    $dave = new DaveCoder;
    
    $taskTitle = "Create Hello World Livewire Component";
    $taskDescription = "Create a simple Livewire 3 component named HelloCounter that displays a counter with increment/decrement buttons. Include the view file. Use Tailwind v4 styling with purple/indigo gradient.";
    
    $prompt = <<<PROMPT
**Task:** {$taskTitle}

**Description:** 
{$taskDescription}

**Requirements:**
1. Create a Livewire 3 component at App/Livewire/HelloCounter.php
2. Create the corresponding view at resources/views/livewire/hello-counter.blade.php
3. Component should have a counter property with increment() and decrement() methods
4. View should display the counter with two buttons
5. Use Tailwind v4 styling with purple/indigo gradient
6. Follow Laravel 12 best practices

**Output Format:** Return ONLY valid JSON (no markdown code blocks). 
- summary: string
- files: array of {path: string, content: string, action: "created"|"modified"}
- tests_created: boolean (true or false, NOT an array)
- requires_migration: boolean (true or false)

IMPORTANT: tests_created and requires_migration MUST be true/false, not empty arrays!
PROMPT;

    echo "🤖 Spawning Dave Coder with gpt-oss:120b-cloud...\n";
    echo "Prompt length: " . strlen($prompt) . " chars\n\n";
    
    // Get raw response (not structured)
    $response = $dave->prompt($prompt, provider: Lab::Ollama, model: 'gpt-oss:120b-cloud', timeout: 120);
    
    echo "✅ Response received!\n\n";
    
    // Extract content as string
    $content = (string) $response;
    
    // Remove markdown code blocks if present
    $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
    $content = preg_replace('/```\s*$/', '', $content);
    $content = trim($content);
    
    // Parse JSON manually
    $result = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "⚠️ JSON parse error: " . json_last_error_msg() . "\n";
        echo "\n📄 Raw response:\n{$content}\n";
        exit(1);
    }
    
    echo "📊 Parsed JSON successfully!\n";
    echo "Summary: " . ($result['summary'] ?? 'N/A') . "\n\n";
    
    // Write the files
    if (!empty($result['files'])) {
        echo "📝 Writing " . count($result['files']) . " files...\n\n";
        
        foreach ($result['files'] as $file) {
            $path = $file['path'];
            $content = $file['content'];
            $action = $file['action'] ?? 'created';
            
            // Build full path
            $fullPath = base_path($path);
            
            // Create directory if needed
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Write file
            $bytes = file_put_contents($fullPath, $content);
            echo "  ✅ {$action}: {$path} ({$bytes} bytes)\n";
        }
        
        echo "\n🎉 All files written successfully!\n";
        echo "\n📋 Files created:\n";
        foreach ($result['files'] as $file) {
            echo "   - {$file['path']}\n";
        }
        
        // Check if files were actually created
        echo "\n🔍 Verifying files...\n";
        foreach ($result['files'] as $file) {
            $exists = file_exists(base_path($file['path'])) ? '✅ EXISTS' : '❌ MISSING';
            echo "   {$exists}: {$file['path']}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Test complete!\n";
