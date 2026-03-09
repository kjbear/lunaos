<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast when a user sends a message.
 * Instantly shows the user's message to all connected clients.
 */
class UserMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The chat session ID
     */
    public string $sessionId;

    /**
     * The user message content
     */
    public string $message;

    /**
     * The message ID
     */
    public string $messageId;

    /**
     * Timestamp of the message
     */
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(string $sessionId, string $message, string $messageId)
    {
        $this->sessionId = $sessionId;
        $this->message = $message;
        $this->messageId = $messageId;
        $this->timestamp = now()->toIso8601String();
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
        return 'user.message';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'content' => $this->message,
            'role' => 'user',
            'timestamp' => $this->timestamp,
        ];
    }
}