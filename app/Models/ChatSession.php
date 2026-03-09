<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * ChatSession Model
 * 
 * Tracks conversations with AI team members.
 * 
 * @property string $id
 * @property string $team_member_id
 * @property string|null $title
 * @property array|null $context
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ChatSession extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'team_member_id',
        'title',
        'context',
        'metadata',
    ];

    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'context' => '[]',
        'metadata' => '{}',
    ];

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
     * Get the team member for this session.
     */
    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    /**
     * Get all messages in this session.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get recent messages for context window.
     */
    public function recentMessages(int $limit = 20): HasMany
    {
        return $this->messages()->latest()->take($limit);
    }

    /**
     * Get total token count for context window.
     */
    public function getContextTokenCount(): int
    {
        return $this->messages()
            ->whereNotNull('tokens')
            ->sum('tokens');
    }

    /**
     * Update context sliding window.
     * Keeps messages within token limit.
     */
    public function updateContext(int $maxTokens = 8000, int $maxMessages = 20): void
    {
        $messages = $this->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        $contextMessages = [];
        $totalTokens = 0;

        // Always include system message if present
        $systemMessage = $messages->firstWhere('role', 'system');
        if ($systemMessage) {
            $contextMessages[] = [
                'role' => 'system',
                'content' => $systemMessage->content,
            ];
            $totalTokens += $systemMessage->tokens ?? 0;
        }

        // Add recent messages (from newest to oldest, then reverse)
        $recentMessages = $messages->where('role', '!=', 'system')
            ->reverse()
            ->take($maxMessages);

        $windowMessages = [];
        foreach ($recentMessages->reverse() as $message) {
            $msgTokens = $message->tokens ?? 0;
            if ($totalTokens + $msgTokens <= $maxTokens) {
                $windowMessages[] = [
                    'role' => $message->role,
                    'content' => $message->content,
                    'tokens' => $msgTokens,
                ];
                $totalTokens += $msgTokens;
            } else {
                break;
            }
        }

        // Always include system first, then conversation
        if ($systemMessage) {
            $this->context = array_merge($contextMessages, $windowMessages);
        } else {
            $this->context = $windowMessages;
        }

        $this->metadata = array_merge($this->metadata ?? [], [
            'context_tokens' => $totalTokens,
            'context_message_count' => count($this->context),
            'last_updated' => now()->toIso8601String(),
        ]);

        $this->save();
    }

    /**
     * Generate title from first user message.
     */
    public function generateTitle(): ?string
    {
        $firstMessage = $this->messages()->where('role', 'user')->first();
        
        if (!$firstMessage) {
            return null;
        }

        // Truncate to first 50 chars for title
        $title = substr($firstMessage->content, 0, 50);
        if (strlen($firstMessage->content) > 50) {
            $title .= '...';
        }

        $this->title = $title;
        $this->save();

        return $this->title;
    }
}