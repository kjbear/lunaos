<?php

namespace App\Services;

use App\Models\DocCollection;
use App\Models\DocCategory;
use App\Models\DocFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DocIndexerService
{
    /**
     * Index a documentation collection from a directory.
     */
    public function indexCollection(string $name, string $directory, ?string $sourceUrl = null, ?string $description = null): DocCollection
    {
        // Create or update collection
        $slug = Str::slug($name);
        
        $collection = DocCollection::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'source_url' => $sourceUrl,
                'storage_path' => $directory,
            ]
        );

        // Index all files
        $this->indexFiles($collection, $directory);

        // Update file count
        $collection->updateFileCount();
        $collection->update(['last_synced_at' => now()]);

        return $collection;
    }

    /**
     * Index all markdown files in a directory.
     */
    protected function indexFiles(DocCollection $collection, string $directory): void
    {
        // Clear existing files for this collection
        DocFile::where('collection_id', $collection->id)->delete();
        DocCategory::where('collection_id', $collection->id)->delete();

        // Find all markdown files
        $files = File::glob($directory . '/**/*.md');
        $flatFiles = File::glob($directory . '/*.md');
        $allFiles = array_merge($files, $flatFiles);
        $allFiles = array_unique($allFiles);

        $categories = [];
        $sortOrder = 0;

        foreach ($allFiles as $filePath) {
            $this->indexFile($collection, $filePath, $categories, $sortOrder++, $directory);
        }
    }

    /**
     * Index a single file.
     */
    protected function indexFile(DocCollection $collection, string $filePath, array &$categories, int $sortOrder, string $baseDirectory): void
    {
        $content = file_get_contents($filePath);
        $relativePath = str_replace($baseDirectory . '/', '', $filePath);
        
        // Extract metadata from frontmatter
        $metadata = $this->parseFrontmatter($content);
        $title = $metadata['title'] ?? $this->extractTitle($content) ?? $this->titleFromFilename($relativePath);
        $sourceUrl = $metadata['source'] ?? null;

        // Determine category from path
        $category = null;
        $pathParts = explode('/', $relativePath);
        
        if (count($pathParts) > 1) {
            // Build category hierarchy
            $categoryPath = '';
            $parentCategoryId = null;
            
            for ($i = 0; $i < count($pathParts) - 1; $i++) {
                $categoryName = $this->titleFromSlug($pathParts[$i]);
                $categorySlug = Str::slug($categoryName);
                $categoryPath .= ($categoryPath ? '/' : '') . $pathParts[$i];
                
                $categoryKey = $collection->id . ':' . $categoryPath;
                
                if (!isset($categories[$categoryKey])) {
                    $categories[$categoryKey] = DocCategory::create([
                        'collection_id' => $collection->id,
                        'parent_id' => $parentCategoryId,
                        'name' => $categoryName,
                        'slug' => $categorySlug,
                        'path' => $categoryPath,
                        'sort_order' => $i,
                    ]);
                }
                
                $parentCategoryId = $categories[$categoryKey]->id;
                $category = $categories[$categoryKey];
            }
        }

        // Calculate word count (excluding frontmatter)
        $processedContent = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        $wordCount = str_word_count(strip_tags($processedContent));

        // Create file record
        DocFile::create([
            'collection_id' => $collection->id,
            'category_id' => $category?->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'file_path' => $filePath,
            'source_url' => $sourceUrl,
            'content_hash' => md5($content),
            'word_count' => $wordCount,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Parse YAML frontmatter from markdown content.
     */
    protected function parseFrontmatter(string $content): array
    {
        if (!preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            return [];
        }

        $yaml = $matches[1];
        $metadata = [];

        foreach (explode("\n", $yaml) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', $line, $match)) {
                $metadata[$match[1]] = trim($match[2]);
            }
        }

        return $metadata;
    }

    /**
     * Extract title from first H1 in content.
     */
    protected function extractTitle(string $content): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $match)) {
            return trim($match[1]);
        }
        return null;
    }

    /**
     * Generate title from filename.
     */
    protected function titleFromFilename(string $filename): string
    {
        $name = basename($filename, '.md');
        return $this->titleFromSlug($name);
    }

    /**
     * Convert slug to title.
     */
    protected function titleFromSlug(string $slug): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    /**
     * Search across all documentation.
     */
    public function search(string $query, ?string $collectionSlug = null): array
    {
        $results = [];
        
        $filesQuery = DocFile::query()
            ->with(['collection', 'category']);
            
        if ($collectionSlug) {
            $collection = DocCollection::where('slug', $collectionSlug)->first();
            if ($collection) {
                $filesQuery->where('collection_id', $collection->id);
            }
        }

        $files = $filesQuery->get();

        foreach ($files as $file) {
            $content = $file->getContent();
            $processedContent = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
            
            if (stripos($processedContent, $query) !== false || stripos($file->title, $query) !== false) {
                $results[] = [
                    'id' => $file->id,
                    'title' => $file->title,
                    'collection' => $file->collection->name,
                    'collection_slug' => $file->collection->slug,
                    'category' => $file->category?->name,
                    'category_path' => $file->category?->getFullPath(),
                    'excerpt' => $this->extractExcerpt($processedContent, $query),
                    'word_count' => $file->word_count,
                    'source_url' => $file->source_url,
                ];
            }
        }

        return $results;
    }

    /**
     * Extract excerpt around search term.
     */
    protected function extractExcerpt(string $content, string $query, int $contextLength = 100): string
    {
        $pos = stripos($content, $query);
        
        if ($pos === false) {
            return Str::limit($content, 200);
        }

        $start = max(0, $pos - $contextLength);
        $length = strlen($query) + ($contextLength * 2);
        
        $excerpt = substr($content, $start, $length);
        
        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }
        
        if ($start + $length < strlen($content)) {
            $excerpt = $excerpt . '...';
        }

        return trim($excerpt);
    }
}