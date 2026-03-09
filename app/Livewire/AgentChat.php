<?php

namespace App\Livewire;

use App\Models\TeamMember;
use App\Models\ChatSession;
use App\Services\ChatService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;

class AgentChat extends Component
{
    /**
     * Selected team member for chat
     */
    public ?string $selectedMemberId = null;

    /**
     * Selected team member details (for display)
     */
    public array $selectedMemberData = [];

    /**
     * Current chat session
     */
    public ?ChatSession $session = null;

    /**
     * Session ID (for Livewire binding)
     */
    public ?string $sessionId = null;

    /**
     * Messages in current session
     */
    public array $messages = [];

    /**
     * New message input
     */
    public string $newMessage = '';

    /**
     * Is AI currently responding?
     */
    public bool $isTyping = false;

    /**
     * Available team members for chat
     */
    public array $teamMembers = [];

    /**
     * Recent sessions
     */
    public array $recentSessions = [];

    /**
     * WebSocket connection status
     */
    public string $wsStatus = 'connecting'; // connecting, connected, disconnected

    protected ChatService $chatService;

    public function mount(?string $memberId = null): void
    {
        $this->chatService = app(ChatService::class);
        
        // Load team members
        $this->teamMembers = TeamMember::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'title', 'emoji', 'role'])
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'title' => $m->title,
                'emoji' => $m->emoji ?? '🤖',
                'role' => $m->role,
            ])
            ->toArray();

        // Load recent sessions
        $this->loadRecentSessions();

        // Pre-select member if provided
        if ($memberId) {
            $this->selectMember($memberId);
        }
    }

    public function selectMember(string $memberId): void
    {
        $this->selectedMemberId = $memberId;
        $member = TeamMember::find($memberId);
        
        if ($member) {
            $this->selectedMemberData = [
                'id' => $member->id,
                'name' => $member->name,
                'title' => $member->title,
                'emoji' => $member->emoji ?? '🤖',
                'role' => $member->role,
            ];
        }

        // Create or get existing session
        $existingSession = ChatSession::where('team_member_id', $memberId)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($existingSession) {
            $this->loadSession($existingSession->id);
        } else {
            $this->session = null;
            $this->sessionId = null;
            $this->messages = [];
        }
    }

    /**
     * Livewire lifecycle hook - called when selectedMemberId changes
     */
    public function updatedSelectedMemberId($value): void
    {
        if ($value) {
            $this->selectMember($value);
        }
    }

    public function loadSession(string $sessionId): void
    {
        $this->session = ChatSession::with(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->find($sessionId);

        if ($this->session) {
            $this->sessionId = $this->session->id;
            $this->selectedMemberId = $this->session->team_member_id;
            $member = $this->session->teamMember;
            
            if ($member) {
                $this->selectedMemberData = [
                    'id' => $member->id,
                    'name' => $member->name,
                    'title' => $member->title,
                    'emoji' => $member->emoji ?? '🤖',
                    'role' => $member->role,
                ];
            }
            
            $this->messages = $this->session->messages->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'timestamp' => $m->created_at->diffForHumans(),
                'metadata' => $m->metadata ?? [],
            ])->toArray();
        }
        
        $this->loadRecentSessions();
    }

    public function createSession(string $memberId): void
    {
        $this->selectMember($memberId);
        $this->newChat();
    }

    public function newChat(): void
    {
        if (!$this->selectedMemberId) {
            return;
        }

        $this->session = app(ChatService::class)->createSession($this->selectedMemberId);
        $this->sessionId = $this->session->id;
        $this->messages = [];
        $this->loadRecentSessions();
    }

    public function sendMessage(): void
    {
        if (empty($this->newMessage) || !$this->selectedMemberId) {
            return;
        }

        // Create session if not exists
        if (!$this->session) {
            $this->session = app(ChatService::class)->createSession($this->selectedMemberId);
            $this->sessionId = $this->session->id;
        }

        $userMessage = $this->newMessage;
        $this->newMessage = '';
        
        // Add user message immediately
        $userMsgId = 'user-' . time();
        $this->messages[] = [
            'id' => $userMsgId,
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => 'Just now',
            'metadata' => [],
        ];

        // Start streaming
        $this->isTyping = true;
        
        // Store for stats
        $messageStats = null;
        $fullContent = '';

        // Stream the response
        try {
            foreach (app(ChatService::class)->streamMessage($this->session, $userMessage) as $chunk) {
                if (isset($chunk['token'])) {
                    $fullContent .= $chunk['token'];
                    // For now, just collect - we'll show it all at once
                }
                
                if (isset($chunk['done']) && $chunk['done'] === true) {
                    $messageStats = $chunk['stats'] ?? [];
                }
            }
            
            // Add assistant message
            $this->messages[] = [
                'id' => 'assistant-' . time(),
                'role' => 'assistant',
                'content' => $fullContent,
                'timestamp' => 'Just now',
                'metadata' => $messageStats ?? [],
            ];
        } catch (\Exception $e) {
            $this->messages[] = [
                'id' => 'error-' . time(),
                'role' => 'assistant',
                'content' => 'Sorry, I encountered an error: ' . $e->getMessage(),
                'timestamp' => 'Just now',
                'metadata' => [],
            ];
        } finally {
            $this->isTyping = false;
        }

        $this->loadRecentSessions();
    }

    /**
     * Update WebSocket status from frontend
     */
    #[On('ws-status')]
    public function updateWsStatus(string $status): void
    {
        $this->wsStatus = $status;
    }

    private function loadRecentSessions(): void
    {
        $this->recentSessions = ChatSession::with('teamMember')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title ?? 'New Chat',
                'member' => $s->teamMember?->name ?? 'Unknown',
                'emoji' => $s->teamMember?->emoji ?? '🤖',
                'updated' => $s->updated_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.agent-chat');
    }
}