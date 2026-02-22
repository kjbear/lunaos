<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandupActionItem extends Model
{
    protected $fillable = [
        'standup_id',
        'title',
        'assigned_to',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function standup(): BelongsTo
    {
        return $this->belongsTo(Standup::class);
    }

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }
}