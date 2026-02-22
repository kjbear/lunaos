<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class WorkspaceService
{
    protected string $workspacePath;

    public function __construct()
    {
        // Hardcode path for now since config might be cached
        $this->workspacePath = '/Users/kobear/.openclaw/workspace';
    }

    /**
     * List all files in the workspace
     */
    public function listFiles(): array
    {
        return Cache::remember('workspace.files', 300, function () {
            $files = [];

            // Core files we want to show
            $coreFiles = [
                'SOUL.md',
                'IDENTITY.md', 
                'USER.md',
                'IMPORTANT.md',
                'HEARTBEAT.md',
                'TOOLS.md',
                'AGENTS.md',
                'MEMORY.md',
            ];

            foreach ($coreFiles as $file) {
                $path = $this->workspacePath . '/' . $file;
                if (File::exists($path)) {
                    $files[] = [
                        'name' => $file,
                        'path' => $file,
                        'size' => File::size($path),
                        'modified' => File::lastModified($path),
                        'type' => 'file',
                        'icon' => $this->getFileIcon($file),
                    ];
                }
            }

            // Add memory directory
            $memoryPath = $this->workspacePath . '/memory';
            if (File::isDirectory($memoryPath)) {
                $memoryFiles = File::files($memoryPath);
                foreach ($memoryFiles as $file) {
                    if ($file->getExtension() === 'md') {
                        $files[] = [
                            'name' => 'memory/' . $file->getFilename(),
                            'path' => 'memory/' . $file->getFilename(),
                            'size' => $file->getSize(),
                            'modified' => $file->getMTime(),
                            'type' => 'file',
                            'icon' => '📝',
                        ];
                    }
                }
            }

            // Sort by name
            usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));

            return $files;
        });
    }

    /**
     * Read a file's content
     */
    public function readFile(string $path): ?array
    {
        // Security: Prevent path traversal
        $path = $this->sanitizePath($path);
        
        if (!$path) {
            return null;
        }

        $fullPath = $this->workspacePath . '/' . $path;

        if (!File::exists($fullPath)) {
            return null;
        }

        $content = File::get($fullPath);

        return [
            'name' => basename($path),
            'path' => $path,
            'content' => $content,
            'size' => File::size($fullPath),
            'modified' => date('Y-m-d H:i:s', File::lastModified($fullPath)),
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
        ];
    }

    /**
     * Sanitize path to prevent traversal attacks
     */
    protected function sanitizePath(string $path): ?string
    {
        // Remove any directory traversal attempts
        $path = str_replace(['../', '..\\', '..', '~'], '', $path);
        
        // Remove leading slashes
        $path = ltrim($path, '/\\');

        // Only allow specific file patterns
        $allowedPatterns = [
            '^[A-Z_]+\.md$',  // Core files like SOUL.md
            '^memory/[0-9]{4}-[0-9]{2}-[0-9]{2}.*\.md$',  // Memory files
            '^projects/.*\.md$',  // Project docs
        ];

        $isAllowed = false;
        foreach ($allowedPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/', $path)) {
                $isAllowed = true;
                break;
            }
        }

        return $isAllowed ? $path : null;
    }

    /**
     * Get icon for file type
     */
    protected function getFileIcon(string $filename): string
    {
        return match ($filename) {
            'SOUL.md' => '🌙',
            'IDENTITY.md' => '🎭',
            'USER.md' => '👤',
            'IMPORTANT.md' => '⚡',
            'HEARTBEAT.md' => '💓',
            'TOOLS.md' => '🔧',
            'AGENTS.md' => '🤖',
            'MEMORY.md' => '🧠',
            default => '📄',
        };
    }
}