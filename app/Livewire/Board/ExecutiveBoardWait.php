<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use Livewire\Component;

class ExecutiveBoardWait extends Component
{
    public string $sessionId;
    public ?BoardSession $session = null;
    public int $elapsed = 0;
    public bool $polling = true;

    public function mount(string $sessionId): void
    {
        $this->sessionId = $sessionId;
        $this->loadSession();
    }

    public function loadSession(): void
    {
        $this->session = BoardSession::find($this->sessionId);
        
        if (!$this->session) {
            $this->dispatch('toast-error', message: 'Session not found');
            return;
        }

        // Check if session is complete
        if ($this->session->status === 'decided') {
            $this->polling = false;
            $this->redirect(route('tasks.executive.result', $this->sessionId), navigate: true);
            return;
        }

        // Check if session failed
        if (in_array($this->session->status, ['failed', 'cancelled'])) {
            $this->polling = false;
            $this->dispatch('toast-error', message: 'Board session ' . $this->session->status);
            return;
        }
    }

    public function incrementElapsed(): void
    {
        if ($this->polling) {
            $this->elapsed += 2;
            $this->loadSession();
        }
    }

    public function cancel(): void
    {
        $this->polling = false;
        
        if ($this->session && $this->session->status === 'pending') {
            $this->session->update(['status' => 'cancelled']);
        }
        
        $this->dispatch('toast-info', message: 'Session cancelled');
        $this->redirect(route('tasks.executive.board'), navigate: true);
    }

    public function render()
    {
        return view('livewire.board.executive-board-wait')
            ->layout('components.layouts.app');
    }
}
