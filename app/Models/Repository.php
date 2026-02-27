<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Repository extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'path',
        'git_url',
        'default_branch',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
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
     * Get all tasks for this repository.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the branch prefix from settings.
     */
    public function getBranchPrefixAttribute(): string
    {
        return $this->settings['branch_prefix'] ?? 'feature';
    }

    /**
     * Get the PR template from settings.
     */
    public function getPrTemplateAttribute(): ?string
    {
        return $this->settings['pr_template'] ?? null;
    }

    /**
     * Scope for active repositories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
