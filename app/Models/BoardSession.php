<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class BoardSession extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question',
        'status',
        'decision_summary',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the participants for this session.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(BoardParticipant::class, 'session_id');
    }

    /**
     * Get the discussion entries for this session.
     */
    public function discussionEntries(): HasMany
    {
        return $this->hasMany(BoardDiscussionEntry::class, 'session_id');
    }

    /**
     * Get the final decision for this session.
     */
    public function decision(): HasOne
    {
        return $this->hasOne(BoardDecision::class, 'session_id');
    }

    /**
     * Scope for pending sessions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for debating sessions.
     */
    public function scopeDebating($query)
    {
        return $query->where('status', 'debating');
    }

    /**
     * Scope for decided sessions.
     */
    public function scopeDecided($query)
    {
        return $query->where('status', 'decided');
    }

    /**
     * Scope for closed sessions.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'debating']);
    }

    /**
     * Get the transcript of the session.
     */
    public function getTranscript(): array
    {
        return $this->discussionEntries()
            ->with('participant')
            ->orderBy('round')
            ->orderBy('created_at')
            ->get()
            ->map(function ($entry) {
                return [
                    'participant_id' => $entry->participant_id,
                    'persona_role' => $entry->participant?->persona_role,
                    'round' => $entry->round,
                    'message' => $entry->message,
                    'model_response' => $entry->model_response,
                    'created_at' => $entry->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }
}
