<?php

namespace App\Console\Commands;

use App\Services\DocIndexerService;
use Illuminate\Console\Command;

class DocsIndex extends Command
{
    protected $signature = 'docs:index {path : Path to documentation directory}
                            {--name= : Name for the collection}
                            {--url= : Source URL}
                            {--description= : Description}';

    protected $description = 'Index documentation files into the database';

    public function handle(DocIndexerService $indexer): int
    {
        $path = $this->argument('path');
        
        // Resolve path
        if (!str_starts_with($path, '/')) {
            $path = base_path($path);
        }
        
        if (!is_dir($path)) {
            $this->error("Directory not found: {$path}");
            return 1;
        }
        
        $name = $this->option('name') ?? basename($path);
        $url = $this->option('url');
        $description = $this->option('description');
        
        $this->info("Indexing documentation: {$name}");
        $this->info("Path: {$path}");
        
        $collection = $indexer->indexCollection($name, $path, $url, $description);
        
        $this->info("✓ Collection: {$collection->name}");
        $this->info("✓ Files indexed: {$collection->file_count}");
        
        return 0;
    }
}