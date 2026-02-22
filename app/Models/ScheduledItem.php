<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduledItem extends Model
{
    use HasFactory;

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
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Types
    const TYPE_TASK = 'task';
    const TYPE_REMINDER = 'reminder';
    const TYPE_MEETING = 'meeting';
    const TYPE_DEADLINE = 'deadline';
    const TYPE_OTHER = 'other';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the color for this item type
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_REMINDER => 'reminder', // orange
            self::TYPE_MEETING => 'meeting',   // green
            self::TYPE_DEADLINE => 'deadline', // red
            self::TYPE_TASK => 'task',         // purple
            default => 'default',
        };
    }

    /**
     * Get the icon for this item type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_REMINDER => '⏰',
            self::TYPE_MEETING => '📅',
            self::TYPE_DEADLINE => '⚠️',
            self::TYPE_TASK => '📋',
            default => '📌',
        };
    }

    /**
     * Get priority stars
     */
    public function getPriorityStarsAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => '⭐⭐⭐',
            self::PRIORITY_HIGH => '⭐⭐',
            self::PRIORITY_NORMAL => '⭐',
            self::PRIORITY_LOW => '◇',
            default => '',
        };
    }

    /**
     * Scope for upcoming items
     */
    public function scopeUpcoming($query, $days = 28)
    {
        return $query->where('start_time', '>=', now())
                     ->where('start_time', '<=', now()->addDays($days))
                     ->orderBy('start_time');
    }

    /**
     * Scope for a specific week
     */
    public function scopeForWeek($query, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        return $query->where('start_time', '>=', $startOfWeek)
                     ->where('start_time', '<=', $endOfWeek)
                     ->orderBy('start_time');
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for calendar display
     */
    public function scopeForCalendar($query, $date = null)
    {
        return $query->forWeek($date)->pending();
    }
}