<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * List Directory Tool
 * 
 * Allows AI agents to list directory contents.
 * Used by Dave Coder to explore project structure.
 */
class ListDirectory implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List the contents of a directory. Returns files and subdirectories with their types.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        // Use array access instead of input() method
        $path = $request['path'] ?? '.';
        $recursive = $request['recursive'] ?? false;
        
        // Ensure path is safe (prevent directory traversal)
        $normalizedPath = str_replace('..', '', $path);
        
        // Build full path
        $fullPath = base_path($normalizedPath);
        
        // Check if directory exists
        if (!is_dir($fullPath)) {
            return json_encode([
                'success' => false,
                'error' => "Directory not found: {$normalizedPath}",
            ]);
        }
        
        // Scan directory
        $items = [];
        $iterator = $recursive 
            ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS))
            : new \DirectoryIterator($fullPath);
        
        foreach ($iterator as $item) {
            // Skip dot files/dirs (already skipped by SKIP_DOTS in recursive mode)
            if (!$recursive && $item->isDot()) {
                continue;
            }
            
            $relativePath = $recursive 
                ? str_replace($fullPath . '/', '', $item->getPathname())
                : $item->getFilename();
            
            // Skip hidden files and vendor/node_modules in non-recursive mode
            if (!$recursive && str_starts_with($item->getFilename(), '.')) {
                continue;
            }
            
            $items[] = [
                'name' => $item->getFilename(),
                'path' => $normalizedPath !== '.' ? "{$normalizedPath}/{$relativePath}" : $relativePath,
                'type' => $item->isDir() ? 'directory' : 'file',
                'size' => $item->isFile() ? $item->getSize() : null,
                'extension' => $item->isFile() ? pathinfo($item->getFilename(), PATHINFO_EXTENSION) : null,
            ];
        }
        
        // Sort directories first, then files
        usort($items, function($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcmp($a['name'], $b['name']);
            }
            return $a['type'] === 'directory' ? -1 : 1;
        });
        
        return json_encode([
            'success' => true,
            'path' => $normalizedPath,
            'items' => $items,
            'total' => count($items),
            'recursive' => $recursive,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()
                ->default('.')
                ->description('Directory path relative to project root (default: project root)'),
            
            'recursive' => $schema->boolean()
                ->default(false)
                ->description('Whether to list recursively (default: false)'),
        ];
    }
}
