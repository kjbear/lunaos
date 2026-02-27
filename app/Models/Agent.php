<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'role',
        'model',
        'provider',
        'system_prompt',
        'model_settings',
        'avatar',
        'status',
        'parent_id',
        'emoji',
    ];

    protected $casts = [
        'status' => 'string',
        'model_settings' => 'array',
    ];

    /**
     * Get merged model settings with defaults.
     */
    public function getSettingsWithDefaults(): array
    {
        return array_merge([
            'temperature' => 0.7,
            'max_tokens' => 4096,
        ], $this->model_settings ?? []);
    }

    protected $attributes = [
        'emoji' => '🤖',
        'avatar' => '🤖',
        'provider' => 'ollama',
    ];

    /**
     * Get the parent agent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'parent_id');
    }

    /**
     * Get child agents.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Agent::class, 'parent_id');
    }

    /**
     * Get tasks assigned to this agent.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to', 'name');
    }

    /**
     * Get workspace configuration.
     */
    public function workspaceConfig(): HasOne
    {
        return $this->hasOne(WorkspaceConfig::class);
    }

    /**
     * Get full model identifier (provider/model).
     */
    public function getFullModelAttribute(): string
    {
        return "{$this->provider}/{$this->model}";
    }

    /**
     * Get merged model settings with defaults.
     */
    public function getModelSettingsAttribute($value = null): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : ($value ?? []);
        return array_merge([
            'temperature' => 0.7,
            'max_tokens' => 4096,
        ], $decoded ?? []);
    }

    /**
     * Scope for online agents.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope for offline agents.
     */
    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    /**
     * Scope for worker agents.
     */
    public function scopeWorkers($query)
    {
        return $query->where('role', 'worker');
    }
}
