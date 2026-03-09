<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast for each AI token received during streaming.
 * Enables real-time token-by-token display of AI responses.
 */
class AiTokenReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The chat session ID
     */
    public string $sessionId;

    /**
     * The token content
     */
    public string $token;

    /**
     * Sequence number for ordering
     */
    public int $sequence;

    /**
     * Create a new event instance.
     */
    public function __construct(string $sessionId, string $token, int $sequence = 0)
    {
        $this->sessionId = $sessionId;
        $this->token = $token;
        $this->sequence = $sequence;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PresenceChannel('chat.' . $this->sessionId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ai.token';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'token' => $this->token,
            'sequence' => $this->sequence,
        ];
    }
}