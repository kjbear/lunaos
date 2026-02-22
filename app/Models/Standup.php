<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Standup extends Model
{
    protected $fillable = [
        'date',
        'team',
        'facilitator',
        'transcript',
        'status',
        'recording_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETED = 'completed';

    public function deliverables(): HasMany
    {
        return $this->hasMany(StandupDeliverable::class)->orderBy('order');
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(StandupActionItem::class)->orderBy('created_at');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days))->orderBy('date', 'desc');
    }
}