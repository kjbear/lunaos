<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $connection = 'sqlite-activity';
    protected $table = 'activity_logs';
    
    protected $fillable = [
        'agent_id',
        'agent_name',
        'action',
        'task',
        'status',
        'tokens_used',
        'runtime_ms',
        'cost',
        'metadata',
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
    
    public $timestamps = false;
    
    /**
     * Auto-prune old records (30-day retention)
     */
    protected static function booted()
    {
        static::created(function () {
            static::where('created_at', '<', now()->subDays(30))->delete();
        });
    }
    
    /**
     * Get recent activity
     */
    public static function getRecent(int $limit = 50): array
    {
        return static::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    /**
     * Get activity by agent
     */
    public static function getByAgent(string $agentId, int $limit = 20): array
    {
        return static::where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}