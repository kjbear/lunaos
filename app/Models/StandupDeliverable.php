<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandupDeliverable extends Model
{
    protected $fillable = [
        'standup_id',
        'title',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function standup(): BelongsTo
    {
        return $this->belongsTo(Standup::class);
    }
}