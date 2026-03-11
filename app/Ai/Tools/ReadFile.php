<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Read File Tool
 * 
 * Allows AI agents to read files from the filesystem.
 * Used by Dave Coder to inspect existing code before modifications.
 */
class ReadFile implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Read the contents of a file. Returns the full file content or a specific range of lines.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        // Use array access instead of input() method
        $path = $request['path'] ?? null;
        $offset = $request['offset'] ?? 0;
        $limit = $request['limit'] ?? 100;
        
        if (!$path) {
            return json_encode([
                'success' => false,
                'error' => "Missing required parameter: path",
            ]);
        }
        
        // Ensure path is safe (prevent directory traversal)
        $normalizedPath = str_replace('..', '', $path);
        
        // Build full path
        $fullPath = base_path($normalizedPath);
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            return json_encode([
                'success' => false,
                'error' => "File not found: {$normalizedPath}",
            ]);
        }
        
        // Check if it's a text file (prevent reading binary files)
        $mimeType = mime_content_type($fullPath);
        if (strpos($mimeType, 'text/') !== 0 && !in_array(pathinfo($fullPath, PATHINFO_EXTENSION), ['php', 'js', 'ts', 'css', 'html', 'blade.php', 'json', 'yaml', 'yml', 'md', 'txt'])) {
            return json_encode([
                'success' => false,
                'error' => "Cannot read binary file: {$normalizedPath}",
            ]);
        }
        
        // Read file content
        $content = file_get_contents($fullPath);
        
        if ($content === false) {
            return json_encode([
                'success' => false,
                'error' => "Failed to read file: {$normalizedPath}",
            ]);
        }
        
        // Apply offset and limit if specified
        $lines = explode("\n", $content);
        if ($offset > 0 || $limit < count($lines)) {
            $lines = array_slice($lines, $offset, $limit);
        }
        $truncatedContent = implode("\n", $lines);
        
        return json_encode([
            'success' => true,
            'path' => $normalizedPath,
            'lines' => count($lines),
            'content' => $truncatedContent,
            'truncated' => count(explode("\n", $content)) > count($lines),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()
                ->required()
                ->description('File path relative to project root (e.g., app/Livewire/ExampleComponent.php)'),
            
            'offset' => $schema->integer()
                ->default(0)
                ->description('Line number to start reading from (0-indexed, optional)'),
            
            'limit' => $schema->integer()
                ->default(100)
                ->description('Maximum number of lines to read (optional)'),
        ];
    }
}
