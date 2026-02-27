<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Scheduled Item Model
 * Note: Migration pending - this is a stub to prevent errors
 */
class ScheduledItem extends Model
{
    protected $fillable = [
        'title',
        'type',
        'status',
        'start_time',
        'end_time',
        'color',
        'icon',
        'priority',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get priority as stars
     */
    public function getPriorityStarsAttribute(): string
    {
        return str_repeat('⭐', $this->priority ?? 1);
    }

    /**
     * Static method to prevent errors when table doesn't exist
     */
    public static function where($column, $operator = null, $value = null)
    {
        try {
            return parent::where($column, $operator, $value);
        } catch (\Illuminate\Database\QueryException $e) {
            // Return empty query builder if table doesn't exist
            return (new static)->newQuery()->whereRaw('0 = 1');
        }
    }
}
