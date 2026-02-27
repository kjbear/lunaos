<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Write File Tool
 * 
 * Allows AI agents to write files to the filesystem.
 * Used by Dave Coder to create/modify PHP files, views, etc.
 */
class WriteFile implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Write content to a file. Creates the file if it doesn\'t exist, overwrites if it does. Automatically creates parent directories.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $path = $request->input('path');
        $content = $request->input('content');
        $action = $request->input('action', 'create'); // 'create' or 'modify'
        
        // Ensure path is safe (prevent directory traversal)
        $normalizedPath = str_replace('..', '', $path);
        
        // Build full path
        $fullPath = base_path($normalizedPath);
        
        // Create directory if it doesn't exist
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Write the file
        $bytesWritten = file_put_contents($fullPath, $content);
        
        if ($bytesWritten === false) {
            return json_encode([
                'success' => false,
                'error' => "Failed to write file: {$normalizedPath}",
            ]);
        }
        
        return json_encode([
            'success' => true,
            'action' => $action,
            'path' => $normalizedPath,
            'bytes_written' => $bytesWritten,
            'message' => "File {$action}d successfully: {$normalizedPath}",
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
            
            'content' => $schema->string()
                ->required()
                ->description('Complete file content to write'),
            
            'action' => $schema->string()
                ->enum(['create', 'modify'])
                ->default('create')
                ->description('Whether creating a new file or modifying existing'),
        ];
    }
}
