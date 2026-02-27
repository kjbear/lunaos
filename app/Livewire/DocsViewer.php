<?php

namespace App\Livewire;

use App\Models\DocCollection;
use App\Models\DocCategory;
use App\Models\DocFile;
use App\Services\DocIndexerService;
use Livewire\Component;
use Illuminate\Support\Str;

class DocsViewer extends Component
{
    public ?string $collectionSlug = null;
    public ?string $fileId = null;
    public string $search = '';
    public array $searchResults = [];
    public bool $showSearch = false;
    
    public array $collections = [];
    public ?DocCollection $currentCollection = null;
    public ?DocFile $currentFile = null;
    public string $content = '';
    public array $breadcrumbs = [];
    public array $docs = [];
    public array $categories = [];
    public array $tags = [];
    public ?string $selectedCategory = null;
    public ?string $selectedTag = null;
    public string $sortBy = 'updated_at';
    public ?string $selectedDoc = null;

    protected $queryString = [
        'collectionSlug' => ['except' => null],
        'fileId' => ['except' => null],
        'search' => ['except' => ''],
    ];

    protected $listeners = ['refreshDocs' => '$refresh'];

    public function mount(): void
    {
        $this->loadCollections();
        
        if ($this->collectionSlug) {
            $this->loadCollection($this->collectionSlug);
        }
        
        if ($this->fileId) {
            $this->loadFile($this->fileId);
        }
    }

    public function loadCollections(): void
    {
        $this->collections = DocCollection::orderBy('name')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'file_count' => $c->file_count,
            ];
        })->toArray();
    }

    public function selectCollection(string $slug): void
    {
        $this->collectionSlug = $slug;
        $this->fileId = null;
        $this->currentFile = null;
        $this->content = '';
        $this->loadCollection($slug);
    }

    public function loadCollection(string $slug): void
    {
        $this->currentCollection = DocCollection::where('slug', $slug)
            ->with(['rootCategories.files', 'files' => function ($q) {
                $q->whereNull('category_id')->orderBy('title');
            }])
            ->first();
        
        if ($this->currentCollection) {
            // Load docs for display
            $this->docs = $this->currentCollection->files()
                ->orderBy($this->sortBy, 'desc')
                ->limit(50)
                ->get()
                ->map(fn($f) => [
                    'id' => $f->id,
                    'title' => $f->title,
                    'content' => $f->content,
                    'excerpt' => Str::limit(strip_tags($f->getProcessedContent()), 150),
                    'category' => $f->category?->name ?? 'Uncategorized',
                    'tags' => $f->tags ?? [],
                    'updated_at' => $f->updated_at,
                ])
                ->toArray();
            
            // Load categories for sidebar
            $this->categories = $this->currentCollection->rootCategories
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'count' => $c->files()->count(),
                ])
                ->toArray();
            
            // Load popular tags
            $allTags = $this->currentCollection->files()
                ->get()
                ->flatMap(fn($f) => $f->tags ?? [])
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            $this->tags = array_slice($allTags, 0, 10);
        } else {
            $this->docs = [];
            $this->categories = [];
            $this->tags = [];
        }
            
        $this->buildBreadcrumbs();
    }

    public function selectFile(string $id): void
    {
        $this->fileId = $id;
        $this->loadFile($id);
    }

    public function loadFile(string $id): void
    {
        $this->currentFile = DocFile::with(['collection', 'category'])->find($id);
        
        if ($this->currentFile) {
            $this->content = $this->currentFile->getProcessedContent();
            $this->collectionSlug = $this->currentFile->collection->slug;
            
            if (!$this->currentCollection || $this->currentCollection->id !== $this->currentFile->collection_id) {
                $this->loadCollection($this->collectionSlug);
            }
        }
        
        $this->buildBreadcrumbs();
    }

    public function buildBreadcrumbs(): void
    {
        $this->breadcrumbs = [];
        
        if ($this->currentCollection) {
            $this->breadcrumbs[] = [
                'label' => $this->currentCollection->name,
                'url' => route('docs', ['collectionSlug' => $this->currentCollection->slug]),
            ];
        }
        
        if ($this->currentFile && $this->currentFile->category) {
            // Add category path
            $category = $this->currentFile->category;
            $path = [];
            
            while ($category) {
                array_unshift($path, $category);
                $category = $category->parent;
            }
            
            foreach ($path as $cat) {
                $this->breadcrumbs[] = [
                    'label' => $cat->name,
                    'url' => '#', // Could navigate to category view
                ];
            }
            
            $this->breadcrumbs[] = [
                'label' => $this->currentFile->title,
                'url' => null,
            ];
        }
    }

    public function updatedSearch(): void
    {
        if (strlen($this->search) >= 2) {
            $indexer = app(DocIndexerService::class);
            $this->searchResults = $indexer->search($this->search, $this->collectionSlug);
            $this->showSearch = true;
        } else {
            $this->searchResults = [];
            $this->showSearch = false;
        }
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->searchResults = [];
        $this->showSearch = false;
    }

    public function getIndexCommand(string $path): string
    {
        $name = basename($path);
        return "php artisan docs:index {$path} --name=\"{$name}\"";
    }

    public function selectCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->loadDocs();
    }

    public function filterByTag(string $tag): void
    {
        $this->selectedTag = $tag ?? null;
        $this->loadDocs();
    }

    protected function loadDocs(): void
    {
        if (!$this->currentCollection) {
            $this->docs = [];
            return;
        }

        $query = $this->currentCollection->files();
        
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }
        
        if ($this->selectedTag ?? null) {
            $query->whereJsonContains('tags', $this->selectedTag);
        }
        
        $this->docs = $query
            ->orderBy($this->sortBy, 'desc')
            ->limit(50)
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'title' => $f->title,
                'content' => $f->content ?? '',
                'excerpt' => Str::limit(strip_tags($f->getProcessedContent()), 150),
                'category' => $f->category?->name ?? 'Uncategorized',
                'tags' => $f->tags ?? [],
                'updated_at' => $f->updated_at,
            ])
            ->toArray();
    }

    public function selectDoc(string $docId): void
    {
        $this->selectedDoc = $docId;
        $this->loadFile($docId);
    }

    /**
     * Get the currently selected document data for display
     */
    public function getSelectedDocDataProperty(): ?array
    {
        if (!$this->selectedDoc) {
            return null;
        }
        
        // Find the doc in the loaded docs array
        return collect($this->docs)->firstWhere('id', $this->selectedDoc);
    }

    public function render()
    {
        return view('livewire.docs-viewer');
    }
}