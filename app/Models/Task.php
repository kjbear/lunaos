<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'agent_id',
        'name',
        'description',
        'status',
        'priority',
        'tokens_used',
        'cost',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => 'string',
        'priority' => 'string',
        'tokens_used' => 'integer',
        'cost' => 'decimal:4',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInSeconds($this->completed_at);
        }
        return null;
    }

    public function getDurationFormattedAttribute(): string
    {
        $seconds = $this->duration;
        if ($seconds === null) {
            return '—';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }
        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        }
        return sprintf('%ds', $secs);
    }
}