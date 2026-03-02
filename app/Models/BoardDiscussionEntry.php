<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BoardDiscussionEntry extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'session_id',
        'participant_id',
        'round',
        'message',
        'model_response',
    ];

    protected $casts = [
        'round' => 'integer',
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
     * Get the session this entry belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BoardSession::class, 'session_id');
    }

    /**
     * Get the participant who made this entry.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(BoardParticipant::class, 'participant_id');
    }

    /**
     * Get the persona role from the participant.
     */
    public function getPersonaRoleAttribute(): ?string
    {
        return $this->participant?->persona_role;
    }

    /**
     * Get the participant's name.
     */
    public function getParticipantNameAttribute(): ?string
    {
        return $this->participant?->getPersonaName();
    }

    /**
     * Get the participant's avatar emoji.
     */
    public function getAvatarEmojiAttribute(): ?string
    {
        return $this->participant?->getAvatarEmoji();
    }
}
