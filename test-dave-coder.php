<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Ai\Agents\DaveCoder;
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;

echo "🧪 Testing Dave Coder Agent\n";
echo "============================\n\n";

try {
    // Create Dave Coder agent
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

**Tools Available:**
- WriteFile: Create or modify files
- ReadFile: Read existing files
- ListDirectory: Explore directory structure

**Output:** Return JSON with summary, files array, tests_created, requires_migration
PROMPT;

    echo "🤖 Spawning Dave Coder with Qwen3-Coder...\n";
    echo "Prompt length: " . strlen($prompt) . " chars\n\n";
    
    // Test with Ollama Cloud - try gpt-oss:120b-cloud (their flagship cloud model)
    $response = $dave->prompt($prompt, provider: Lab::Ollama, model: 'gpt-oss:120b-cloud', timeout: 120);
    echo "Using Ollama Cloud (model: gpt-oss:120b-cloud)\n\n";
    
    echo "✅ Response received!\n\n";
    
    // Try to get structured output
    try {
        // Access structured output from response object
        $result = $response->result ?? $response->content ?? (string) $response;
        echo "📊 Structured Output:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (\Exception $e) {
        echo "⚠️ Structured output failed: " . $e->getMessage() . "\n";
        echo "\n📄 Raw Response:\n";
        try {
            echo (string) $response . "\n";
        } catch (\Exception $e2) {
            echo "Cannot convert to string: " . $e2->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Test complete!\n";
