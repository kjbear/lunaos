<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardResponse extends Model
{
    protected $fillable = [
        'session_id',
        'member_id',  // Can be null for persona-based responses
        'member_name',
        'member_role',
        'response',
        'model_used',
        'response_order',
        'round',
    ];

    protected $casts = [
        'response_order' => 'integer',
        'round' => 'integer',
        'member_id' => 'string',
    ];

    /**
     * Get the session this response belongs to.
     */
    public function session()
    {
        return $this->belongsTo(BoardSession::class, 'session_id');
    }

    /**
     * Get formatted response with metadata.
     */
    public function getFormattedResponseAttribute(): string
    {
        return "[{$this->member_role} {$this->member_name} - Round {$this->round}]\n{$this->response}";
    }

    /**
     * Scope to get responses from a specific round.
     */
    public function scopeRound($query, int $round)
    {
        return $query->where('round', $round);
    }

    /**
     * Scope to order by round and response order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('round')->orderBy('response_order');
    }
}
