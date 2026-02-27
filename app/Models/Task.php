<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Task Model
 * 
 * Represents a task in the development pipeline Kanban board.
 * Tasks flow through the workflow: Assign → Develop → QA → Security → Staging → Production
 */
class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'repository_id',
        'status',
        'step',
        'priority',
        'task_type',
        'context_json',
        'branch_name',
        'pr_url',
        'artifacts_json',
        'failure_reason',
        'retry_count',
        'started_at',
        'completed_at',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'context_json' => 'array',
        'artifacts_json' => 'array',
        'retry_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($task) {
            if (!$task->step) {
                $task->step = 'develop';
            }
            if (!$task->status) {
                $task->status = 'pending';
            }
            if (!$task->priority) {
                $task->priority = 'medium';
            }
            if (!$task->task_type) {
                $task->task_type = 'feature';
            }
        });
    }
    
    /**
     * Scope for tasks assigned to a specific agent
     */
    public function scopeAssignedTo($query, string $agent)
    {
        return $query->where('assigned_to', $agent);
    }
    
    /**
     * Get the agent assigned to this task.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'assigned_to', 'name');
    }

    /**
     * Get the repository for this task.
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * Scope for tasks in a specific step
     */
    public function scopeInStep($query, string $step)
    {
        return $query->where('step', $step);
    }
    
    /**
     * Scope for tasks with a specific status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope for available tasks (pending or in_progress)
     */
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
    
    /**
     * Scope for completed tasks today
     */
    public function scopeCompletedToday($query)
    {
        return $query->where('status', 'complete')
            ->whereDate('completed_at', today());
    }
    
    /**
     * Get the agent activities for this task
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AgentActivity::class);
    }
    
    /**
     * Get task progress as percentage (based on workflow steps)
     */
    public function getProgressPercentageAttribute(): int
    {
        $steps = [
            'develop' => 20,
            'qa' => 40,
            'security' => 60,
            'staging' => 80,
            'production' => 100,
        ];
        
        return $steps[$this->step] ?? 0;
    }
    
    /**
     * Get human-readable priority badge class
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'high' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            'medium' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
            'low' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
            default => 'bg-slate-500/20 text-slate-400',
        };
    }
    
    /**
     * Get human-readable status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
            'in_progress' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'complete' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'failed' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'blocked' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            default => 'bg-slate-500/20 text-slate-400',
        };
    }
    
    /**
     * Get agent display name
     */
    public function getAgentDisplayNameAttribute(): string
    {
        return match($this->assigned_to) {
            'dave' => 'Dave (Dev)',
            'sam' => 'Sam (QA)',
            'chen' => 'Chen (DevOps)',
            'security' => 'Security Bot',
            default => ucfirst($this->assigned_to ?? 'Unassigned'),
        };
    }
    
    /**
     * Check if task is ready for agent polling
     */
    public function isReadyForAgent(string $agent): bool
    {
        return $this->assigned_to === $agent 
            && in_array($this->status, ['pending', 'in_progress']);
    }
    
    /**
     * Get the next step in the workflow
     */
    public function getNextStep(): ?string
    {
        $workflow = [
            'develop' => 'qa',
            'qa' => 'security',
            'security' => 'staging',
            'staging' => 'production',
            'production' => null,
        ];
        
        return $workflow[$this->step] ?? null;
    }
    
    /**
     * Get the agent for the next step
     */
    public function getNextAssignee(): ?string
    {
        $assignments = [
            'qa' => 'sam',
            'security' => 'security',
            'staging' => 'chen',
            'production' => 'chen',
        ];
        
        $nextStep = $this->getNextStep();
        return $assignments[$nextStep] ?? null;
    }
    
    /**
     * Format timeago for display
     */
    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
    
    /**
     * Get artifacts safely
     */
    public function getArtifactsAttribute(): array
    {
        return $this->artifacts_json ?? [];
    }
}
