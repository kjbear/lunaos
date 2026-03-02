<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BoardDecision extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'session_id',
        'decision_text',
        'confidence_score',
        'reasoning',
    ];

    protected $casts = [
        'confidence_score' => 'float',
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
     * Get the session this decision belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BoardSession::class, 'session_id');
    }

    /**
     * Get confidence level as a human-readable string.
     */
    public function getConfidenceLevelAttribute(): string
    {
        if ($this->confidence_score === null) {
            return 'Unknown';
        }

        return match (true) {
            $this->confidence_score >= 0.9 => 'Very High',
            $this->confidence_score >= 0.7 => 'High',
            $this->confidence_score >= 0.5 => 'Medium',
            $this->confidence_score >= 0.3 => 'Low',
            default => 'Very Low',
        };
    }

    /**
     * Check if decision is confident (>= 0.7).
     */
    public function isConfident(): bool
    {
        return $this->confidence_score !== null && $this->confidence_score >= 0.7;
    }
}
