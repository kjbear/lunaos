<?php

namespace App\Livewire\Board;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\TeamMember;
use App\Services\BoardOrchestrator;
use App\Jobs\ProcessBoardDebate;
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
    public bool $isLoading = false;
    public string $loadingStep = '';
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
        // Load configuration-based personas
        $this->boardMembers = [];
        
        // Try to load from database first
        $members = TeamMember::where('type', 'board-members')
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
            // Fall back to config-defined personas
            $configPersonas = config('executive-board.personas', []);
            
            if (!empty($configPersonas)) {
                foreach ($configPersonas as $config) {
                    if ($config['enabled'] ?? true) {
                        $personaClass = $config['class'];
                        if (class_exists($personaClass)) {
                            $persona = new $personaClass();
                            $this->boardMembers[] = $persona->toArray();
                        }
                    }
                }
            }
            
            // If still empty, use hardcoded defaults
            if (empty($this->boardMembers)) {
                $this->boardMembers = [
                    ['name' => 'Gwynne', 'title' => 'COO', 'model' => 'glm-5', 'avatar' => '👔', 'inspiration' => 'Gwynne Shotwell - operational excellence'],
                    ['name' => 'Warren', 'title' => 'CFO', 'model' => 'glm-5', 'avatar' => '💰', 'inspiration' => 'Warren Buffet - value investing'],
                    ['name' => 'Werner', 'title' => 'CTO', 'model' => 'glm-5', 'avatar' => '💻', 'inspiration' => 'Werner Vogels - scalability'],
                    ['name' => 'Bozoma', 'title' => 'CMO', 'model' => 'glm-5', 'avatar' => '📢', 'inspiration' => 'Bozoma Saint John - cultural marketing'],
                    ['name' => 'Fidji', 'title' => 'CPO', 'model' => 'glm-5', 'avatar' => '📦', 'inspiration' => 'Fidji Simo - user-centric product'],
                ];
            }
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
        $this->apiConfigured = !empty(config('services.openrouter.key') ?: env('OPENROUTER_API_KEY'));
    }

    public function conveneBoard(): void
    {
        if (empty($this->question)) {
            $this->dispatch('toast-warning', message: 'Please enter a question for the board.');
            return;
        }

        // Create session first
        $session = BoardSession::create([
            'question' => $this->question,
            'context' => $this->context,
            'status' => 'pending', // Start as pending, job will set to debating
        ]);

        $sessionId = $session->id;
        
        // Dispatch to queue for async processing
        ProcessBoardDebate::dispatch($sessionId);
        
        // Clear form
        $this->question = '';
        $this->context = '';
        
        // Redirect to wait page immediately
        $this->redirect(route('tasks.executive.wait', $sessionId), navigate: true);
    }

    protected function createFallbackResponse(BoardSession $session, string $error): void
    {
        // If API fails, create placeholder responses
        $order = 0;
        foreach ($this->boardMembers as $member) {
            BoardResponse::create([
                'session_id' => $session->id,
                'member_id' => $member['id'] ?? null,
                'member_name' => $member['name'] ?? 'Executive',
                'member_role' => $member['title'] ?? 'Board Member',
                'response' => "[Error: {$error} - Response unavailable]",
                'model_used' => $member['model'] ?? 'glm-5',
                'response_order' => $order++,
            ]);
        }

        $session->update([
            'status' => 'decided',
            'final_decision' => "Unable to reach a decision - Error: {$error}. Please check API configuration and retry.",
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
                'round' => $r->round ?? 1,
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
