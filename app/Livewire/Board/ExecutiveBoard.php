<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use App\Services\BoardOrchestrator;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ExecutiveBoard extends Component
{
    public array $boardMembers = [];
    public string $question = '';
    public string $context = '';
    public ?string $currentSessionId = null;
    public array $transcript = [];
    public ?string $finalDecision = null;
    public ?string $risksBenefits = null;
    public bool $isDebating = false;
    public array $stats = [];
    public bool $apiConfigured = false;

    protected $listeners = ['refreshStats' => 'loadStats'];

    public function mount(): void
    {
        $this->loadBoardMembers();
        $this->loadStats();
        $this->checkApiConfiguration();
    }

    public function loadBoardMembers(): void
    {
        // Load board member personas from database
        $members = Persona::where('role', 'board_member')
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        if ($members->count() > 0) {
            $this->boardMembers = $members->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'title' => $m->title ?? 'Executive',
                    'model' => $m->model,
                    'avatar' => $m->avatar ?? '👔',
                    'inspiration' => $m->inspiration,
                ];
            })->toArray();
        } else {
            // Default board members if none in DB
            $this->boardMembers = [
                ['id' => 'ceo', 'name' => 'Steven', 'title' => 'CEO', 'model' => 'glm-5', 'avatar' => '🎯', 'inspiration' => 'Steve Jobs - visionary, product-obsessed'],
                ['id' => 'coo', 'name' => 'Gwynne', 'title' => 'COO', 'model' => 'haiku', 'avatar' => '👔', 'inspiration' => 'Gwynne Shotwell - operational excellence'],
                ['id' => 'cto', 'name' => 'Werner', 'title' => 'CTO', 'model' => 'dolphin', 'avatar' => '💻', 'inspiration' => 'Werner Vogels - scalability, architecture'],
                ['id' => 'cfo', 'name' => 'Warren', 'title' => 'CFO', 'model' => 'glm-5', 'avatar' => '💰', 'inspiration' => 'Warren Buffet - value investing, ROI discipline'],
                ['id' => 'cmo', 'name' => 'Bozoma', 'title' => 'CMO', 'model' => 'haiku', 'avatar' => '📢', 'inspiration' => 'Bozoma Saint John - cultural marketing'],
                ['id' => 'cpo', 'name' => 'Fidji', 'title' => 'CPO', 'model' => 'glm-5', 'avatar' => '📦', 'inspiration' => 'Fidji Simo - user-centric product'],
            ];
        }
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_sessions' => BoardSession::count(),
            'decisions' => BoardSession::where('status', 'decided')->count(),
            'pending' => BoardSession::whereIn('status', ['pending', 'debating'])->count(),
        ];
    }

    public function checkApiConfiguration(): void
    {
        $orchestrator = app(BoardOrchestrator::class);
        $this->apiConfigured = $orchestrator->isConfigured();
    }

    public function conveneBoard(): void
    {
        if (empty($this->question)) {
            $this->dispatch('toast-warning', message: 'Please enter a question for the board.');
            return;
        }

        $this->dispatch('toast-info', message: 'Convening the board...');

        // Create session
        $session = BoardSession::create([
            'question' => $this->question,
            'context' => $this->context,
            'status' => 'debating',
        ]);

        $this->currentSessionId = $session->id;
        $this->isDebating = true;
        $this->transcript = [];
        $this->finalDecision = null;
        $this->risksBenefits = null;

        // Run the board session
        try {
            $orchestrator = app(BoardOrchestrator::class);
            $orchestrator->runSession(
                $session->id,
                $this->question,
                $this->context ?: null
            );

            // Reload the session to get updated data
            $session->refresh();
            
            // Load transcript
            $this->loadTranscript();
            
            // Load decision
            $this->finalDecision = $session->final_decision;
            $this->risksBenefits = $session->risks_benefits;
            
            // Success notification
            $this->dispatch('toast-success', message: 'Board session complete! Decision rendered.');
            
        } catch (\Exception $e) {
            Log::error('ExecutiveBoard: Failed to run session', [
                'error' => $e->getMessage(),
            ]);
            
            // Create fallback response
            $this->createFallbackResponse($session);
            
            $this->dispatch('toast-error', message: 'Board session failed. Check API configuration.');
        }

        $this->isDebating = false;
        $this->loadStats();
    }

    protected function createFallbackResponse(BoardSession $session): void
    {
        // If API fails, create placeholder responses
        $order = 0;
        foreach ($this->boardMembers as $member) {
            BoardResponse::create([
                'session_id' => $session->id,
                'member_id' => $member['id'],
                'member_name' => $member['name'],
                'member_role' => $member['title'],
                'response' => "[API unavailable - {$member['name']} would provide their {$member['title']} perspective here.]",
                'model_used' => $member['model'],
                'response_order' => $order++,
            ]);
        }

        $session->update([
            'status' => 'decided',
            'final_decision' => 'Unable to reach a decision - API configuration required. Add OPENROUTER_API_KEY to your .env file.',
            'decided_at' => now(),
        ]);

        $this->loadTranscript();
        $this->finalDecision = $session->final_decision;
    }

    public function loadTranscript(): void
    {
        if (!$this->currentSessionId) return;

        $responses = BoardResponse::where('session_id', $this->currentSessionId)
            ->orderBy('response_order')
            ->get();

        $this->transcript = $responses->map(function ($r) {
            return [
                'id' => $r->id,
                'member_name' => $r->member_name,
                'member_role' => $r->member_role,
                'response' => $r->response,
                'model' => $r->model_used,
                'avatar' => $this->getAvatarForRole($r->member_role),
            ];
        })->toArray();
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
            default => '👔',
        };
    }

    public function resetSession(): void
    {
        $this->question = '';
        $this->context = '';
        $this->currentSessionId = null;
        $this->transcript = [];
        $this->finalDecision = null;
        $this->risksBenefits = null;
        $this->isDebating = false;
        
        $this->dispatch('toast-info', message: 'Session cleared. Ready for a new question.');
    }

    public function render()
    {
        return view('livewire.board.executive-board');
    }
}
