<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * TeamMember Model
 * 
 * Unified model for all team members (personas, board members, and workers).
 * Consolidates the former separate Personas and Agents tables.
 * 
 * @property string $id
 * @property string $name
 * @property string|null $email
 * @property string|null $title
 * @property string $type (personas, board-members, workers)
 * @property string $role (board_member, persona, worker)
 * @property string $status (active, inactive, online, offline, error, busy, archived)
 * @property string|null $model
 * @property string $provider
 * @property string|null $avatar
 * @property string $emoji
 * @property string|null $system_prompt
 * @property array|null $settings
 * @property array|null $metadata_json
 * @property string|null $workspace_path
 * @property string|null $parent_id
 * @property Carbon|null $deactivated_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TeamMember extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\TeamMemberFactory::new();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'title',
        'type',
        'role',
        'status',
        'model',
        'provider',
        'avatar',
        'emoji',
        'system_prompt',
        'settings',
        'metadata_json',
        'workspace_path',
        'parent_id',
        'deactivated_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'metadata_json' => 'array',
        'deactivated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
        'role' => 'worker',
        'type' => 'workers',
        'provider' => 'ollama',
        'emoji' => '🤖',
    ];

    /**
     * Boot the model and generate UUIDs.
     */
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
     * Get the parent team member.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'parent_id');
    }

    /**
     * Get child team members.
     */
    public function children(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'parent_id');
    }

    /**
     * Get tasks assigned to this member.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to', 'name');
    }

    /**
     * Scope for worker team members.
     */
    public function scopeWorkers(Builder $query): Builder
    {
        return $query->where('role', 'worker');
    }

    /**
     * Scope for persona team members.
     */
    public function scopePersonas(Builder $query): Builder
    {
        return $query->where('role', 'persona');
    }

    /**
     * Scope for board member team members.
     */
    public function scopeBoardMembers(Builder $query): Builder
    {
        return $query->where('role', 'board_member');
    }

    /**
     * Scope for active team members.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get display name (includes title if available).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->title ? "{$this->name} - {$this->title}" : $this->name;
    }

    /**
     * Get badge class based on role.
     */
    public function getBadgeClassAttribute(): string
    {
        return match($this->role) {
            'worker' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'persona' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'board_member' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            default => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        };
    }

    /**
     * Check if member is a worker.
     */
    public function getIsWorkerAttribute(): bool
    {
        return $this->role === 'worker';
    }

    /**
     * Check if member is a persona.
     */
    public function getIsPersonaAttribute(): bool
    {
        return $this->role === 'persona';
    }

    /**
     * Check if member is a board member.
     */
    public function getIsBoardMemberAttribute(): bool
    {
        return $this->role === 'board_member';
    }

    /**
     * Get member type label for display.
     */
    public function getMemberTypeLabelAttribute(): string
    {
        return match($this->type) {
            'workers' => 'Worker',
            'personas' => 'Persona',
            'board-members' => 'Board Member',
            default => 'Unknown',
        };
    }

    /**
     * Get tab category for UI filtering.
     */
    public function getTabCategory(): string
    {
        return match($this->role) {
            'worker' => 'workers',
            'persona' => 'personas',
            'board_member' => 'board-members',
            default => 'workers',
        };
    }

    /**
     * Check if member is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active', 'online' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'inactive', 'offline' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
            'error' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'busy' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            'archived' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
            default => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        };
    }

    /**
     * Check if member was migrated from old tables.
     */
    public function isMigrated(): bool
    {
        return isset($this->metadata_json['migrated_from']);
    }

    /**
     * Get migration source.
     */
    public function getMigrationSourceAttribute(): ?string
    {
        return $this->metadata_json['migrated_from'] ?? null;
    }
}
