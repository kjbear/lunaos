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
 * @property string $ai_model
 * @property float $temperature
 * @property int $max_tokens
 * @property float $top_p
 * @property float $frequency_penalty
 * @property float $presence_penalty
 * @property string $response_style
 * @property string|null $persona_description
 * @property string|null $special_instructions
 * @property array|null $capabilities
 * @property int $max_concurrent_tasks
 * @property bool $auto_assign_enabled
 * @property string $priority_level
 * @property array|null $custom_metadata
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
        'category',
        'status',
        'model',
        'ai_model',
        'temperature',
        'max_tokens',
        'top_p',
        'frequency_penalty',
        'presence_penalty',
        'response_style',
        'persona_description',
        'special_instructions',
        'capabilities',
        'max_concurrent_tasks',
        'auto_assign_enabled',
        'priority_level',
        'custom_metadata',
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
        'capabilities' => 'array',
        'custom_metadata' => 'array',
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'top_p' => 'float',
        'frequency_penalty' => 'float',
        'presence_penalty' => 'float',
        'max_concurrent_tasks' => 'integer',
        'auto_assign_enabled' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
        'role' => 'worker',
        'type' => 'workers',
        'provider' => 'ollama',
        'emoji' => '🤖',
        'ai_model' => 'glm-5',
        'temperature' => 0.7,
        'max_tokens' => 4096,
        'top_p' => 1.0,
        'frequency_penalty' => 0.0,
        'presence_penalty' => 0.0,
        'response_style' => 'technical',
        'max_concurrent_tasks' => 3,
        'auto_assign_enabled' => true,
        'priority_level' => 'normal',
    ];

    /**
     * Validation rules for AI configuration fields.
     */
    public static function aiConfigRules(): array
    {
        return [
            'ai_model' => ['required', 'string', 'max:255'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['required', 'integer', 'min:1', 'max:128000'],
            'top_p' => ['required', 'numeric', 'min:0', 'max:1'],
            'frequency_penalty' => ['required', 'numeric', 'min:-2', 'max:2'],
            'presence_penalty' => ['required', 'numeric', 'min:-2', 'max:2'],
            'response_style' => ['required', 'string', 'in:technical,casual,formal,creative,concise'],
            'system_prompt' => ['nullable', 'string', 'max:16000'],
            'persona_description' => ['nullable', 'string', 'max:2000'],
            'special_instructions' => ['nullable', 'string', 'max:4000'],
            'capabilities' => ['nullable', 'array'],
            'max_concurrent_tasks' => ['required', 'integer', 'min:1', 'max:10'],
            'auto_assign_enabled' => ['required', 'boolean'],
            'priority_level' => ['required', 'string', 'in:low,normal,high,critical'],
            'custom_metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Validate AI configuration data.
     */
    public function validateAiConfig(array $data): array
    {
        return validator($data, self::aiConfigRules())->validate();
    }

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
     * Get metrics for this team member.
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(TeamMemberMetric::class, 'team_member_id');
    }

    /**
     * Get workspaces for this team member.
     */
    public function workspaces(): HasMany
    {
        return $this->hasMany(TeamMemberWorkspace::class, 'team_member_id');
    }

    /**
     * Get activities for this team member.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AgentActivity::class, 'team_member_id');
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
        return $query->whereIn('status', ['active', 'online', 'busy']);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
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
            'active', 'online' => 'badge-success',
            'inactive', 'offline' => 'badge-secondary',
            'error' => 'badge-danger',
            'busy' => 'badge-warning',
            'archived' => 'badge-gray',
            default => 'badge-secondary',
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
