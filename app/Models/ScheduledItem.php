<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledItem extends Model
{
    protected $fillable = [
        'title',
        'type',
        'start_time',
        'end_time',
        'agent',
        'priority',
        'status',
        'notes',
    ];

    protected $casts = [
        'type' => 'string',
        'priority' => 'string',
        'status' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
            ->where('status', 'pending')
            ->orderBy('start_time');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()]);
    }
}