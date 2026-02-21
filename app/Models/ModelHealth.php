<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelHealth extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model',
        'status',
        'cpu_percent',
        'memory_percent',
        'vram_percent',
        'tokens_per_sec',
        'queue_depth',
        'checked_at',
    ];

    protected $casts = [
        'cpu_percent' => 'decimal:2',
        'memory_percent' => 'decimal:2',
        'vram_percent' => 'decimal:2',
        'tokens_per_sec' => 'decimal:2',
        'queue_depth' => 'integer',
        'checked_at' => 'datetime',
    ];

    public function scopeHealthy($query)
    {
        return $query->where('status', 'healthy');
    }

    public function scopeDegraded($query)
    {
        return $query->where('status', 'degraded');
    }

    public function scopeDown($query)
    {
        return $query->where('status', 'down');
    }

    public function scopeRecent($query, int $minutes = 5)
    {
        return $query->where('checked_at', '>=', now()->subMinutes($minutes));
    }
}