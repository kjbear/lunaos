<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'repo_url',
        'health',
        'progress',
        'owner',
        'status',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'progress' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the requirements for this project.
     */
    public function requirements()
    {
        return $this->hasMany(Requirement::class);
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
}