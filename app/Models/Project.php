<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The relations that should be cascade deleted when this model is soft-deleted.
     *
     * @var array
     */
    protected $cascadeDeletes = ['tasks', 'agents', 'issues'];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'repo_url',
        'repository_id',
        'health',
        'progress',
        'percent_complete',
        'owner',
        'status',
        'architecture_type',
        'technologies',
        'project_manager_id',
        'archived_at',
        'deleted_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'progress' => 'integer',
        'technologies' => 'array',
        'percent_complete' => 'decimal:2',
    ];



    public function artifacts(): HasMany
    {
        return $this->hasMany(ProjectArtifact::class, 'project_id', 'id');
    }

    public function boardDiscussions(): HasMany
    {
        return $this->hasMany(ProjectArtifact::class, 'project_id', 'id')
            ->where('type', 'board_discussion')
            ->orderBy('order');
    }

    /**
     * Get the assignments for this project.
     */
    public function assignments()
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    /**
     * Scope for active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for planning projects.
     */
    public function scopePlanning($query)
    {
        return $query->where('status', 'planning');
    }

    /**
     * Scope for completed projects.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for archived projects.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Get health badge color.
     */
    public function getHealthColorAttribute(): string
    {
        return match($this->health) {
            'healthy' => 'green',
            'at_risk' => 'yellow',
            'blocked' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get percent complete as accessor (auto-calculated from tasks, or manual value).
     * 
     * This accessor ALWAYS calculates from tasks to ensure accuracy.
     * The database column is used as a fallback/cache for performance.
     */
    public function getPercentCompleteAttribute(): float
    {
        // Always calculate from tasks for accuracy (when relationship is available)
        if ($this->relationLoaded('tasks') || $this->tasks()->exists()) {
            return $this->calculatePercentComplete();
        }
        
        // Fallback to stored value
        return (float) ($this->attributes['percent_complete'] ?? 0.00);
    }

    // ============== NEW RELATIONSHIPS (Priority 1 Sprint) ==============

    /**
     * Get the repository for this project.
     */
    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * Get the project manager (AI agent).
     */
    public function projectManager()
    {
        return $this->belongsTo(Agent::class, 'project_manager_id');
    }

    /**
     * Get all team member assignments.
     */
    public function teamMembers()
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    /**
     * Get agent assignments for this project (alias for teamMembers).
     */
    public function agents()
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    /**
     * Get all tasks for this project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all issues for this project.
     */
    public function issues()
    {
        return $this->hasMany(ProjectIssue::class);
    }

    /**
     * Scope for at-risk projects.
     */
    public function scopeAtRisk($query)
    {
        return $query->where('health', 'at_risk');
    }

    /**
     * Calculate percent complete from tasks.
     */
    public function calculatePercentComplete(): float
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        
        $completed = $this->tasks()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Auto-update percent_complete when saving.
     * Cascade soft delete related models.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
        
        static::saving(function ($project) {
            // Auto-calculate percent_complete from tasks if not manually set
            if ($project->tasks()->exists()) {
                $project->percent_complete = $project->calculatePercentComplete();
            }
        });
        
        static::deleting(function ($project) {
            // Cascade soft delete to related models
            $project->tasks()->delete();
            $project->agents()->delete();
            $project->issues()->delete();
        });
    }
}