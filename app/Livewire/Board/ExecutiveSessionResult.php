<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use Livewire\Component;

class ExecutiveSessionResult extends Component
{
    public string $sessionId;
    public ?BoardSession $session = null;
    public bool $isComplete = false;

    protected $listeners = ['sessionCompleted' => 'refreshSession'];

    public function mount(string $sessionId): void
    {
        $this->sessionId = $sessionId;
        $this->loadSession();
    }

    public function loadSession(): void
    {
        $this->session = BoardSession::with(['responses'])->find($this->sessionId);
        
        if (!$this->session) {
            $this->dispatch('toast-error', message: 'Session not found');
            return;
        }

        // Check if session just completed
        if (!$this->isComplete && in_array($this->session->status, ['decided', 'failed', 'cancelled'])) {
            $this->isComplete = true;
        }
    }

    public function refreshSession(): void
    {
        $this->loadSession();
        $this->dispatch('refreshFeed');
    }

    public function createProject(): void
    {
        if (!$this->session || $this->session->status !== 'decided' || empty($this->session->final_decision)) {
            $this->dispatch('toast-warning', message: 'No decision available to create project from');
            return;
        }

        // Store decision data in session for project creation
        $title = substr($this->session->question, 0, 60) . (strlen($this->session->question) > 60 ? '...' : '');
        $description = "Board Decision:\n\n" . $this->session->final_decision;
        
        if ($this->session->risks_benefits) {
            $description .= "\n\nRisks & Considerations:\n" . $this->session->risks_benefits;
        }

        session()->flash('board_decision', [
            'session_id' => $this->session->id,
            'title' => $title,
            'description' => $description,
        ]);

        $this->dispatch('toast-success', message: 'Board decision ready for project creation');
        $this->redirect(route('dashboard'));
    }

    public function deleteSession(): void
    {
        if (!$this->session) {
            $this->dispatch('toast-error', message: 'Session not found');
            return;
        }

        $this->session->delete();
        
        $this->dispatch('toast-success', message: 'Board session deleted');
        $this->redirect(route('board'));
    }

    public function render()
    {
        return view('livewire.board.executive-session-result');
    }
}
