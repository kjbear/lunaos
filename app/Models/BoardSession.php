<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BoardSession extends Model
{
    use SoftDeletes;
    
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question',
        'context',
        'status',
        'rounds_planned',
        'final_decision',
        'risks_benefits',
        'confidence_score',
        'dissenting_opinions',
        'key_themes',
        'decided_at',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'rounds_planned' => 'integer',
        'confidence_score' => 'float',
        'dissenting_opinions' => 'array',
        'key_themes' => 'array',
        'decided_at' => 'datetime',
    ];

    /**
     * Get confidence score percentage.
     */
    public function getConfidencePercentageAttribute(): int
    {
        return (int) round(($this->confidence_score ?? 0) * 100);
    }

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
     * Get the responses for this session.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(BoardResponse::class, 'session_id');
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
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'debating']);
    }

    /**
     * Get the formatted transcript.
     */
    public function getTranscript(): array
    {
        return $this->responses()
            ->orderBy('round')
            ->orderBy('response_order')
            ->get()
            ->map(function ($response) {
                return [
                    'member_name' => $response->member_name,
                    'member_role' => $response->member_role,
                    'round' => $response->round,
                    'response' => $response->response,
                    'model' => $response->model_used,
                    'created_at' => $response->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    

}
