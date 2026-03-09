<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * ChatMessage Model
 * 
 * Individual messages in a chat session.
 * 
 * @property string $id
 * @property string $chat_session_id
 * @property string $role (user, assistant, system)
 * @property string $content
 * @property int|null $tokens
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ChatMessage extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'tokens',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tokens' => 'integer',
    ];

    /**
     * Valid roles for a message.
     */
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';

    /**
     * Generate UUID on creation.
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the chat session for this message.
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    /**
     * Check if this is a user message.
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    /**
     * Check if this is a system message.
     */
    public function isSystem(): bool
    {
        return $this->role === self::ROLE_SYSTEM;
    }

    /**
     * Estimate token count for content.
     * Simple approximation: ~4 chars per token for English.
     */
    public function estimateTokens(): int
    {
        if ($this->tokens !== null) {
            return $this->tokens;
        }

        // Approximate: 4 characters per token for English
        // This is a rough estimate; real tokenization would require 
        // tokenizer library (tiktoken, etc.)
        return (int) ceil(strlen($this->content) / 4);
    }

    /**
     * Format for API context (OpenAI-style).
     */
    public function toApiFormat(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}