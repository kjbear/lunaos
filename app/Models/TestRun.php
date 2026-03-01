<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestRun extends Model
{
    protected $fillable = [
        'run_at',
        'status',
        'total_tests',
        'passed',
        'failed',
        'skipped',
        'coverage',
        'duration_ms',
        'output',
        'results',
    ];

    protected $casts = [
        'run_at' => 'datetime',
        'results' => 'array',
        'coverage' => 'float',
    ];

    public function getPassRateAttribute()
    {
        if ($this->total_tests === 0) return 0;
        return round(($this->passed / $this->total_tests) * 100, 1);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'passed' => 'text-green-400',
            'failed' => 'text-red-400',
            'error' => 'text-yellow-400',
            default => 'text-gray-400',
        };
    }
}
