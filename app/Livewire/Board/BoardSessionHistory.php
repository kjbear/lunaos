<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use Livewire\Component;
use Livewire\Attributes\Computed;

class BoardSessionHistory extends Component
{
    public int $limit = 20;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    
    protected $listeners = ['sessionCompleted' => '$refresh', 'refreshHistory' => '$refresh'];

    #[Computed]
    public function sessions()
    {
        return BoardSession::withTrashed()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->limit($this->limit)
            ->get();
    }

    public function sortByField(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            'decided' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'failed' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'cancelled' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
            'debating' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            'pending' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            default => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        };
    }

    public function render()
    {
        return view('livewire.board.board-session-history');
    }
}
