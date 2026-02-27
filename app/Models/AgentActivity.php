<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agent Activity Model
 * 
 * Tracks all actions performed by agent workers on tasks.
 * Used for audit trails, activity feeds, and debugging.
 */
class AgentActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'task_id',
        'agent_name',
        'action',
        'metadata_json',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata_json' => 'array',
    ];
    
    /**
     * Get the task this activity belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
    
    /**
     * Get human-readable action description
     */
    public function getActionDescriptionAttribute(): string
    {
        return match($this->action) {
            'started' => '🚀 Started working on task',
            'completed' => '✅ Completed task',
            'failed' => '❌ Failed task',
            'advanced' => '➡️ Advanced to next step',
            'reassigned' => '🔄 Reassigned to different agent',
            'error' => '⚠️ Encountered error',
            default => ucfirst($this->action),
        };
    }
    
    /**
     * Get metadata safely
     */
    public function getMetadataAttribute(): array
    {
        return $this->metadata_json ?? [];
    }
}
