<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use App\Models\BoardDecision;
use App\Services\BoardService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

/**
 * BoardMeetingManager - Main Livewire component for managing board meetings
 * 
 * Integrates with TaskExecutive view via <livewire:board-meeting-manager />
 */
class BoardMeetingManager extends Component
{
    public string $question = '';
    public ?string $currentSessionId = null;
    public int $currentRound = 0;
    public int $maxRounds = 3;
    public array $transcript = [];
    public ?array $decision = null;
    public bool $isDebating = false;
    public bool $isDecided = false;
    public bool $apiConfigured = false;

    protected array $personas = ['COO', 'CFO', 'CTO', 'CMO', 'CPO'];

    protected $listeners = ['board:refresh' => '$refresh'];

    public function mount(): void
    {
        $this->checkApiConfiguration();
    }

    /**
     * Start a board meeting with the given question.
     */
    public function askQuestion(): void
    {
        if (empty(trim($this->question))) {
            $this->dispatch('toast-warning', message: 'Please enter a question for the board.');
            return;
        }

        try {
            $boardService = app(BoardService::class);
            
            // Start the session
            $session = $boardService->startSession($this->question, $this->personas);
            
            $this->currentSessionId = $session->id;
            $this->currentRound = 0;
            $this->transcript = [];
            $this->decision = null;
            $this->isDebating = true;
            $this->isDecided = false;
            
            $this->dispatch('toast-success', message: 'Board session started! Ready for debate.');
            
        } catch (\Exception $e) {
            Log::error('BoardMeetingManager: Failed to start session', [
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('toast-error', message: 'Failed to start board session: ' . $e->getMessage());
        }
    }

    /**
     * Run the next debate round.
     */
    public function getNextDebateRound(): void
    {
        if (!$this->currentSessionId || !$this->isDebating) {
            $this->dispatch('toast-warning', message: 'No active session to debate.');
            return;
        }

        if ($this->currentRound >= $this->maxRounds) {
            $this->closeSession();
            return;
        }

        try {
            $this->currentRound++;
            
            $boardService = app(BoardService::class);
            $results = $boardService->runDebateRound($this->currentSessionId, $this->currentRound);
            
            // Update transcript
            $this->transcript = $boardService->getTranscript($this->currentSessionId);
            
            $this->dispatch('toast-info', message: "Round {$this->currentRound} complete. {$this->maxRounds - $this->currentRound} rounds remaining.");
            
            // Auto-check if we should consolidate
            if ($this->currentRound >= $this->maxRounds) {
                $this->consolidateDecision();
            }
            
        } catch (\Exception $e) {
            Log::error('BoardMeetingManager: Debate round failed', [
                'session_id' => $this->currentSessionId,
                'round' => $this->currentRound,
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('toast-error', message: 'Debate round failed: ' . $e->getMessage());
        }
    }

    /**
     * Consolidate the decision from the debate.
     */
    public function consolidateDecision(): void
    {
        if (!$this->currentSessionId) {
            $this->dispatch('toast-warning', message: 'No session to consolidate.');
            return;
        }

        try {
            $boardService = app(BoardService::class);
            
            $decision = $boardService->consolidateDecision($this->currentSessionId);
            
            if ($decision) {
                $this->decision = [
                    'id' => $decision->id,
                    'text' => $decision->decision_text,
                    'confidence' => $decision->confidence_score,
                    'confidence_level' => $decision->confidence_level,
                    'reasoning' => $decision->reasoning,
                    'created_at' => $decision->created_at->format('M j, Y g:i A'),
                ];
                
                $this->isDebating = false;
                $this->isDecided = true;
                
                $this->dispatch('toast-success', message: 'Board decision reached!');
            } else {
                $this->dispatch('toast-error', message: 'Failed to consolidate decision.');
            }
            
        } catch (\Exception $e) {
            Log::error('BoardMeetingManager: Consolidation failed', [
                'session_id' => $this->currentSessionId,
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('toast-error', message: 'Decision consolidation failed: ' . $e->getMessage());
        }
    }

    /**
     * Close the board session.
     */
    public function closeSession(): void
    {
        if (!$this->currentSessionId) {
            $this->dispatch('toast-warning', message: 'No session to close.');
            return;
        }

        try {
            $boardService = app(BoardService::class);
            
            // Only consolidate if not already decided
            if (!$this->isDecided && $this->currentRound > 0) {
                $this->consolidateDecision();
            } else {
                $boardService->closeSession($this->currentSessionId);
                $this->isDebating = false;
            }
            
            $this->dispatch('toast-info', message: 'Board session closed.');
            $this->dispatch('board:closed', sessionId: $this->currentSessionId);
            
        } catch (\Exception $e) {
            Log::error('BoardMeetingManager: Close session failed', [
                'session_id' => $this->currentSessionId,
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('toast-error', message: 'Failed to close session: ' . $e->getMessage());
        }
    }

    /**
     * Reset the component state.
     */
    public function resetManager(): void
    {
        $this->question = '';
        $this->currentSessionId = null;
        $this->currentRound = 0;
        $this->transcript = [];
        $this->decision = null;
        $this->isDebating = false;
        $this->isDecided = false;
        
        $this->dispatch('toast-info', message: 'Board manager reset.');
    }

    /**
     * Check if OpenRouter API is configured.
     */
    protected function checkApiConfiguration(): void
    {
        $this->apiConfigured = !empty(config('services.openrouter.key')) || !empty(env('OPENROUTER_API_KEY'));
    }

    /**
     * Get display data for the transcript.
     */
    public function getTranscriptDisplayProperty(): array
    {
        return collect($this->transcript)
            ->groupBy('round')
            ->map(function ($entries, $round) {
                return [
                    'round' => $round,
                    'entries' => $entries->map(function ($entry) {
                        return [
                            'role' => $entry['persona_role'],
                            'name' => $entry['name'],
                            'emoji' => $entry['emoji'],
                            'response' => $entry['response'],
                            'time' => $entry['created_at'],
                        ];
                    })
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get session info.
     */
    public function getSessionInfoProperty(): ?array
    {
        if (!$this->currentSessionId) {
            return null;
        }

        $session = BoardSession::find($this->currentSessionId);
        
        if (!$session) {
            return null;
        }

        return [
            'id' => $session->id,
            'question' => $session->question,
            'status' => $session->status,
            'rounds' => $this->currentRound,
            'started_at' => $session->started_at?->format('M j, Y g:i A'),
            'completed_at' => $session->completed_at?->format('M j, Y g:i A'),
        ];
    }

    public function render()
    {
        return view('livewire.board.board-meeting-manager');
    }
}
