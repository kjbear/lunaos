<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduledItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'timezone',
        'recurrence_rule',
        'priority',
        'status',
        'assignee_id',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
        'priority' => 'integer',
    ];

    // Source types
    const SOURCE_CRON = 'cron';
    const SOURCE_REMINDER = 'reminder';
    const SOURCE_CALENDAR = 'calendar';
    const SOURCE_EMAIL = 'email';
    const SOURCE_TASK = 'task';

    // Priority levels
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the color for this item type
     */
    public function getColorAttribute(): string
    {
        return match($this->source_type) {
            self::SOURCE_CRON => 'cron',      // red
            self::SOURCE_REMINDER => 'reminder', // orange
            self::SOURCE_CALENDAR => 'calendar', // green
            self::SOURCE_EMAIL => 'email',     // blue
            self::SOURCE_TASK => 'task',      // purple
            default => 'default',
        };
    }

    /**
     * Get the icon for this item type
     */
    public function getIconAttribute(): string
    {
        return match($this->source_type) {
            self::SOURCE_CRON => '⚙️',
            self::SOURCE_REMINDER => '⏰',
            self::SOURCE_CALENDAR => '📅',
            self::SOURCE_EMAIL => '📧',
            self::SOURCE_TASK => '📋',
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
        return $query->where('starts_at', '>=', now())
                     ->where('starts_at', '<=', now()->addDays($days))
                     ->orderBy('starts_at');
    }

    /**
     * Scope for a specific week
     */
    public function scopeForWeek($query, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        return $query->where('starts_at', '>=', $startOfWeek)
                     ->where('starts_at', '<=', $endOfWeek)
                     ->orderBy('starts_at');
    }

    /**
     * Scope by source type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('source_type', $type);
    }

    /**
     * Scope by status
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for calendar display (only pending)
     */
    public function scopeForCalendar($query, $date = null)
    {
        return $query->forWeek($date)->pending();
    }
}