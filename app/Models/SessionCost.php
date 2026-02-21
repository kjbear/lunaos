<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionCost extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model',
        'tokens_input',
        'tokens_output',
        'cost',
        'session_key',
        'created_at',
    ];

    protected $casts = [
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'cost' => 'decimal:6',
        'created_at' => 'datetime',
    ];

    public function scopeByModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    public function scopeBySession($query, string $sessionKey)
    {
        return $query->where('session_key', $sessionKey);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}