<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use Livewire\Component;

class LiveDiscussion extends Component
{
    public string $sessionId;
    public array $sessionData = [];
    public array $responses = [];
    public bool $isComplete = false;
    public ?string $ceoRecommendation = null;

    protected $listeners = ['sessionCompleted' => 'refreshDiscussion'];

    public function mount(string $sessionId): void
    {
        $this->sessionId = $sessionId;
        $this->loadSession();
        $this->refreshResponses();
    }

    public function loadSession(): void
    {
        $session = BoardSession::find($this->sessionId);
        if ($session) {
            $this->sessionData = [
                'question' => $session->question,
                'context' => $session->context,
                'status' => $session->status,
            ];
            $this->isComplete = in_array($session->status, ['decided', 'failed', 'cancelled']);
            $this->ceoRecommendation = $session->final_decision;
        }
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
            ];
        })->toArray();

        // Refresh session status
        $this->loadSession();

        // Dispatch scroll event if new responses arrived
        if (count($this->responses) > 0) {
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function refreshDiscussion(): void
    {
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
        return view('livewire.board.live-discussion');
    }
}
