<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'log_text',
        'level',
        'created_at',
    ];

    protected $casts = [
        'level' => 'string',
        'created_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}