<?php

namespace App\Livewire\Board;

use App\Models\BoardResponse;
use Livewire\Component;

class LiveDiscussionFeed extends Component
{
    public string $sessionId;
    public bool $isComplete = false;
    public array $responses = [];
    public string $statusText = 'Waiting for responses...';

    protected $listeners = ['sessionCompleted' => 'markComplete', 'refreshFeed' => '$refresh'];

    public function mount(string $sessionId, bool $isComplete = false): void
    {
        $this->sessionId = $sessionId;
        $this->isComplete = $isComplete;
        $this->refreshResponses();
    }

    public function refreshResponses(): void
    {
        $responses = BoardResponse::where('session_id', $this->sessionId)
            ->orderBy('response_order')
            ->get();

        $this->responses = $responses->map(function ($r) {
            return [
                'id' => $r->id,
                'member_name' => $r->member_name,
                'member_role' => $r->member_role,
                'response' => $r->response,
                'model' => $r->model_used,
                'avatar' => $this->getAvatarForRole($r->member_role),
                'round' => $r->round ?? 1,
                'created_at' => $r->created_at?->diffForHumans() ?? '',
                'timestamp' => $r->created_at?->format('M j, Y g:i A') ?? '',
            ];
        })->toArray();

        // Update status text
        if ($this->isComplete) {
            $this->statusText = 'Debate complete';
        } elseif (count($this->responses) === 0) {
            $this->statusText = 'Waiting for responses...';
        } else {
            $this->statusText = 'Live discussion in progress...';
        }

        // Dispatch scroll event for new responses
        if (count($this->responses) > 0) {
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function markComplete(): void
    {
        $this->isComplete = true;
        $this->refreshResponses();
    }

    private function getAvatarForRole(string $role): string
    {
        return match(strtoupper($role)) {
            'CEO' => '🎯',
            'COO' => '👔',
            'CTO' => '💻',
            'CFO' => '💰',
            'CMO' => '📢',
            'CPO' => '📦',
            default => '👤',
        };
    }

    public function render()
    {
        return view('livewire.board.live-discussion-feed');
    }
}
