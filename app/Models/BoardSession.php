<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BoardSession extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question',
        'context',
        'status',
        'final_decision',
        'risks_benefits',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
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
     * Get the responses for this session.
     */
    public function responses()
    {
        return $this->hasMany(BoardResponse::class, 'session_id')->orderBy('response_order');
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
}