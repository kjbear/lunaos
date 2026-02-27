<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Persona extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'title',
        'role',
        'model',
        'avatar',
        'status',
        'inspiration',
        'system_prompt',
        'workspace_path',
        'deactivated_at',
    ];

    protected $casts = [
        'deactivated_at' => 'datetime',
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
     * Get the metrics for this persona.
     */
    public function metrics(): HasOne
    {
        return $this->hasOne(PersonaMetric::class);
    }

    /**
     * Get the workspace files for this persona.
     */
    public function workspaces(): HasMany
    {
        return $this->hasMany(PersonaWorkspace::class);
    }

    /**
     * Scope for active personas.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for subagents.
     */
    public function scopeSubagents($query)
    {
        return $query->where('role', 'subagent');
    }

    /**
     * Scope for board members.
     */
    public function scopeBoardMembers($query)
    {
        return $query->where('role', 'board_member');
    }

    /**
     * Scope for custom personas.
     */
    public function scopeCustom($query)
    {
        return $query->where('role', 'custom');
    }

    /**
     * Get the AGENTS.md file content.
     */
    public function getAgentsMdAttribute(): ?string
    {
        return $this->workspaces()->where('file_name', 'AGENTS.md')->first()?->content;
    }

    /**
     * Get the TOOLS.md file content.
     */
    public function getToolsMdAttribute(): ?string
    {
        return $this->workspaces()->where('file_name', 'TOOLS.md')->first()?->content;
    }

    /**
     * Check if persona is a board member.
     */
    public function isBoardMember(): bool
    {
        return $this->role === 'board_member';
    }

    /**
     * Check if persona is a subagent.
     */
    public function isSubagent(): bool
    {
        return $this->role === 'subagent';
    }
}