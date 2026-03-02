<?php

namespace App\Livewire;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use App\Services\BoardOrchestrator;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

/**
 * BoardMeetingManager - Embedded Livewire Component
 * 
 * Real-time executive board meeting interface with personae debate simulation.
 * Designed to be embedded in the TaskExecutive view.
 */
class BoardMeetingManager extends Component
{
    public array $boardMembers = [];
    public string $question = '';
    public string $context = '';
    public ?string $currentSessionId = null;
    public array $transcript = [];
    public ?string $finalDecision = null;
    public ?string $risksBenefits = null;
    public ?float $confidenceScore = null;
    public bool $isDebating = false;
    public int $currentRound = 0;
    public int $maxRounds = 3;
    public array $stats = [];
    public bool $apiConfigured = false;
    public ?string $activeSpeakerId = null;

    protected $listeners = ['refresh-boards' => 'loadStats'];

    public function mount(): void
    {
        $this->loadBoardMembers();
        $this->loadStats();
        $this->checkApiConfiguration();
        $this->loadLatestSession();
    }

    public function loadBoardMembers(): void
    {
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
                    'status' => 'waiting', // waiting, thinking, speaking
                ];
            })->toArray();
        } else {
            $this->boardMembers = [
                ['id' => 'coo', 'name' => 'Gwynne', 'title' => 'COO', 'model' => 'haiku', 'avatar' => '👔', 'inspiration' => 'Gwynne Shotwell - operational excellence', 'status' => 'waiting'],
                ['id' => 'cfo', 'name' => 'Warren', 'title' => 'CFO', 'model' => 'glm-5', 'avatar' => '💰', 'inspiration' => 'Warren Buffet - value investing, ROI discipline', 'status' => 'waiting'],
                ['id' => 'cto', 'name' => 'Werner', 'title' => 'CTO', 'model' => 'dolphin', 'avatar' => '💻', 'inspiration' => 'Werner Vogels - scalability, architecture', 'status' => 'waiting'],
                ['id' => 'cmo', 'name' => 'Bozoma', 'title' => 'CMO', 'model' => 'haiku', 'avatar' => '📢', 'inspiration' => 'Bozoma Saint John - cultural marketing', 'status' => 'waiting'],
                ['id' => 'cpo', 'name' => 'Fidji', 'title' => 'CPO', 'model' => 'glm-5', 'avatar' => '📦', 'inspiration' => 'Fidji Simo - user-centric product', 'status' => 'waiting'],
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

    public function loadLatestSession(): void
    {
        $latest = BoardSession::latest()->first();
        
        if ($latest && $latest->status === 'debating') {
            $this->currentSessionId = $latest->id;
            $this->question = $latest->question;
            $this->context = $latest->context ?? '';
            $this->isDebating = true;
            $this->loadTranscript();
            $this->finalDecision = $latest->final_decision;
            $this->risksBenefits = $latest->risks_benefits;
            $this->confidenceScore = $latest->confidence ?? null;
        } elseif ($latest && $latest->status === 'decided') {
            $this->currentSessionId = $latest->id;
            $this->question = $latest->question;
            $this->context = $latest->context ?? '';
            $this->loadTranscript();
            $this->finalDecision = $latest->final_decision;
            $this->risksBenefits = $latest->risks_benefits;
            $this->confidenceScore = $latest->confidence ?? null;
        }
    }

    public function checkApiConfiguration(): void
    {
        try {
            $orchestrator = app(BoardOrchestrator::class);
            $this->apiConfigured = $orchestrator->isConfigured();
        } catch (\Throwable $e) {
            $this->apiConfigured = false;
        }
    }

    public function conveneBoard(): void
    {
        if (empty($this->question)) {
            $this->dispatch('toast', type: 'warning', message: 'Please enter a question for the board.');
            return;
        }

        $this->dispatch('toast', type: 'info', message: 'Convening the board...');

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
        $this->confidenceScore = null;
        $this->currentRound = 0;
        $this->activeSpeakerId = null;

        try {
            $orchestrator = app(BoardOrchestrator::class);
            $orchestrator->runSession($session->id, $this->question, $this->context ?: null);

            $session->refresh();
            $this->loadTranscript();
            $this->finalDecision = $session->final_decision;
            $this->risksBenefits = $session->risks_benefits;
            $this->confidenceScore = $session->confidence ?? null;

            $this->dispatch('toast', type: 'success', message: 'Board session complete!');

        } catch (\Exception $e) {
            Log::error('BoardMeetingManager: Failed to run session', ['error' => $e->getMessage()]);
            $this->createFallbackResponse($session);
            $this->dispatch('toast', type: 'error', message: 'Board session failed. Check API configuration.');
        }

        $this->isDebating = false;
        $this->activeSpeakerId = null;
        $this->loadStats();
    }

    protected function createFallbackResponse(BoardSession $session): void
    {
        $order = 0;
        foreach ($this->boardMembers as $member) {
            BoardResponse::create([
                'session_id' => $session->id,
                'member_id' => $member['id'],
                'member_name' => $member['name'],
                'member_role' => $member['role'] ?? $member['title'] ?? 'Executive',
                'response' => "[API unavailable - {$member['name']} would provide their {$member['title']} perspective here.]",
                'model_used' => $member['model'],
                'response_order' => $order++,
            ]);
        }

        $session->update([
            'status' => 'decided',
            'final_decision' => 'Unable to reach a decision - API configuration required.',
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
                'timestamp' => $r->created_at->format('H:i'),
            ];
        })->toArray();

        $this->currentRound = max(1, (int) ceil(count($this->transcript) / count($this->boardMembers)));
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

    public function getModelBadgeClass(string $model): string
    {
        return match($model) {
            'dolphin' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
            'haiku' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            'glm-5' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        };
    }

    public function getConfidenceColor(): string
    {
        if ($this->confidenceScore === null) return 'text-slate-400';
        if ($this->confidenceScore >= 80) return 'text-emerald-400';
        if ($this->confidenceScore >= 60) return 'text-amber-400';
        return 'text-red-400';
    }

    public function resetSession(): void
    {
        $this->question = '';
        $this->context = '';
        $this->currentSessionId = null;
        $this->transcript = [];
        $this->finalDecision = null;
        $this->risksBenefits = null;
        $this->confidenceScore = null;
        $this->isDebating = false;
        $this->currentRound = 0;
        $this->activeSpeakerId = null;

        $this->dispatch('toast', type: 'info', message: 'Session cleared. Ready for new question.');
    }

    public function render()
    {
        return view('livewire.board-meeting-manager', [
            'boardMembers' => $this->boardMembers,
            'transcript' => $this->transcript,
            'finalDecision' => $this->finalDecision,
            'risksBenefits' => $this->risksBenefits,
            'confidenceScore' => $this->confidenceScore,
            'isDebating' => $this->isDebating,
            'currentRound' => $this->currentRound,
            'maxRounds' => $this->maxRounds,
            'stats' => $this->stats,
            'apiConfigured' => $this->apiConfigured,
            'activeSpeakerId' => $this->activeSpeakerId,
        ]);
    }
}
