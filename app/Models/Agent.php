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
        'status',
        'parent_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Agent::class, 'parent_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function workspaceConfig(): HasOne
    {
        return $this->hasOne(WorkspaceConfig::class);
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }
}