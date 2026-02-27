<?php

/**
 * Sprint Model
 * 
 * Represents a development sprint containing multiple tasks.
 * Sprints are optional - tasks can exist without being in a sprint.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Sprint status enum
     */
    const STATUS_PLANNING = 'planning';
    const STATUS_ACTIVE = 'active';
    const STATUS_REVIEW = 'review';
    const STATUS_COMPLETED = 'completed';
    
    /**
     * Get tasks in this sprint
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
    
    /**
     * Check if sprint is currently active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && now()->between($this->start_date, $this->end_date);
    }
    
    /**
     * Get progress percentage
     */
    public function getProgressAttribute(): float
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        
        $completed = $this->tasks()
            ->where('status', Task::STATUS_COMPLETE)
            ->count();
        
        return round(($completed / $total) * 100, 2);
    }
}
