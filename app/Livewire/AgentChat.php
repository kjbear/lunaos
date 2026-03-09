<?php

namespace App\Livewire;

use App\Models\TeamMember;
use App\Models\ChatSession;
use App\Services\ChatService;
use Livewire\Component;
use Livewire\Attributes\On;

class AgentChat extends Component
{
    /**
     * Selected team member for chat
     */
    public ?string $selectedMemberId = null;

    /**
     * Selected team member details
     */
    public ?TeamMember $selectedMember = null;

    /**
     * Current chat session
     */
    public ?ChatSession $session = null;

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
     * Currently streaming response (for token-by-token display)
     */
    public string $streamingResponse = '';

    /**
     * Available team members for chat
     */
    public array $teamMembers = [];

    /**
     * Recent sessions
     */
    public array $recentSessions = [];

    /**
     * ChatService instance
     */
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
        $this->selectedMember = TeamMember::find($memberId);

        // Create or get existing session
        $existingSession = ChatSession::where('team_member_id', $memberId)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($existingSession) {
            $this->loadSession($existingSession->id);
        } else {
            $this->session = null;
            $this->messages = [];
        }
    }

    public function loadSession(string $sessionId): void
    {
        $this->session = ChatSession::with(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->find($sessionId);

        if ($this->session) {
            $this->selectedMemberId = $this->session->team_member_id;
            $this->selectedMember = $this->session->teamMember;
            $this->messages = $this->session->messages->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'timestamp' => $m->created_at->diffForHumans(),
                'metadata' => $m->metadata ?? [],
            ])->toArray();
        }
    }

    public function newChat(): void
    {
        if (!$this->selectedMemberId) {
            return;
        }

        $this->session = app(ChatService::class)->createSession($this->selectedMemberId);
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
        }

        $userMessage = $this->newMessage;
        $this->newMessage = '';
        
        // Add user message to UI immediately
        $tempUserId = 'temp-user-' . time();
        $this->messages[] = [
            'id' => $tempUserId,
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => 'Just now',
            'metadata' => [],
        ];

        // Start streaming
        $this->isTyping = true;
        $this->streamingResponse = '';
        
        // Store for stats
        $messageStats = null;
        $fullContent = '';

        // Stream the response token by token
        try {
            foreach (app(ChatService::class)->streamMessage($this->session, $userMessage) as $chunk) {
                if (isset($chunk['token'])) {
                    $fullContent .= $chunk['token'];
                    // Stream each token to the frontend
                    $this->stream('stream-response', $chunk['token']);
                }
                
                if (isset($chunk['done']) && $chunk['done'] === true) {
                    $messageStats = $chunk['stats'] ?? [];
                }
            }
            
            // Add final assistant message to messages array
            $this->messages[] = [
                'id' => 'final-' . time(),
                'role' => 'assistant',
                'content' => $fullContent,
                'timestamp' => 'Just now',
                'metadata' => $messageStats ?? [],
            ];
            
            // Update the temporary user message ID if we got a real one
            if (isset($chunk['user_message'])) {
                $userMsgIndex = count($this->messages) - 2;
                if (isset($this->messages[$userMsgIndex])) {
                    $this->messages[$userMsgIndex]['id'] = $chunk['user_message']->id;
                }
            }
        } catch (\Exception $e) {
            // Add error message
            $this->messages[] = [
                'id' => 'error-' . time(),
                'role' => 'assistant',
                'content' => 'Sorry, I encountered an error: ' . $e->getMessage(),
                'timestamp' => 'Just now',
                'metadata' => [],
            ];
        } finally {
            $this->isTyping = false;
            $this->streamingResponse = '';
        }

        // Refresh recent sessions
        $this->loadRecentSessions();
    }

    #[On('receive-message')]
    public function receiveMessage(string $sessionId, string $content): void
    {
        if ($this->session && $this->session->id === $sessionId) {
            $this->messages[] = [
                'id' => 'temp-assistant-' . time(),
                'role' => 'assistant',
                'content' => $content,
                'timestamp' => 'Just now',
                'metadata' => [],
            ];
        }
        
        $this->isTyping = false;
    }

    public function loadRecentSessions(): void
    {
        $this->recentSessions = ChatSession::with('teamMember')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title ?? 'New conversation',
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