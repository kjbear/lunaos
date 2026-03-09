<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast when AI response is complete.
 * Includes final stats and metadata about the response.
 */
class AiResponseComplete implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The chat session ID
     */
    public string $sessionId;

    /**
     * Full response content
     */
    public string $content;

    /**
     * Message ID of the assistant message
     */
    public string $messageId;

    /**
     * Model used for generation
     */
    public string $model;

    /**
     * Response latency in milliseconds
     */
    public int $latencyMs;

    /**
     * Number of prompt tokens
     */
    public int $promptTokens;

    /**
     * Number of completion tokens
     */
    public int $completionTokens;

    /**
     * Timestamp of completion
     */
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $sessionId,
        string $content,
        string $messageId,
        string $model,
        int $latencyMs,
        int $promptTokens,
        int $completionTokens
    ) {
        $this->sessionId = $sessionId;
        $this->content = $content;
        $this->messageId = $messageId;
        $this->model = $model;
        $this->latencyMs = $latencyMs;
        $this->promptTokens = $promptTokens;
        $this->completionTokens = $completionTokens;
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
        return 'ai.complete';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'content' => $this->content,
            'role' => 'assistant',
            'metadata' => [
                'model' => $this->model,
                'latency_ms' => $this->latencyMs,
                'prompt_tokens' => $this->promptTokens,
                'completion_tokens' => $this->completionTokens,
            ],
            'timestamp' => $this->timestamp,
        ];
    }
}