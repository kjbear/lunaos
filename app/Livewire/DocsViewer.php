<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Doc;

class DocsViewer extends Component
{
    public ?string $selectedSlug = null;
    public ?Doc $selectedDoc = null;
    public string $searchQuery = '';
    public array $sections = [];

    public function mount(): void
    {
        $this->loadSections();
        
        // Select first doc by default
        if (!$this->selectedSlug) {
            $firstDoc = Doc::orderBy('order')->first();
            if ($firstDoc) {
                $this->selectDoc($firstDoc->slug);
            }
        }
    }

    public function loadSections(): void
    {
        $this->sections = Doc::select('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->map(function ($section) {
                return [
                    'name' => ucfirst($section),
                    'slug' => $section,
                    'docs' => Doc::where('section', $section)
                        ->orderBy('order')
                        ->get(['slug', 'title'])
                        ->toArray(),
                ];
            })
            ->toArray();
    }

    public function selectDoc(string $slug): void
    {
        $doc = Doc::where('slug', $slug)->first();
        if ($doc) {
            $this->selectedSlug = $slug;
            $this->selectedDoc = $doc;
        }
    }

    public function search(): void
    {
        if (strlen($this->searchQuery) < 2) {
            $this->loadSections();
            return;
        }

        // Simple search in title and content
        $results = Doc::where('title', 'like', "%{$this->searchQuery}%")
            ->orWhere('content', 'like', "%{$this->searchQuery}%")
            ->orderBy('section')
            ->orderBy('order')
            ->get(['slug', 'title', 'section']);

        $this->sections = [];
        $grouped = $results->groupBy('section');
        foreach ($grouped as $section => $docs) {
            $this->sections[] = [
                'name' => ucfirst($section),
                'slug' => $section,
                'docs' => $docs->toArray(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.docs-viewer');
    }
}