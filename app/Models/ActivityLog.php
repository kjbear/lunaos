<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'agent',
        'action_type',
        'action_name',
        'context',
        'impact',
        'status',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function scopeByAgent($query, string $agent)
    {
        return $query->where('agent', $agent);
    }

    public function scopeByActionType($query, string $type)
    {
        return $query->where('action_type', $type);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Search using FTS5
     */
    public static function search(string $query): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()
            ->whereRaw("id IN (SELECT rowid FROM activity_logs_fts WHERE activity_logs_fts MATCH ?)", [$query]);
    }
}