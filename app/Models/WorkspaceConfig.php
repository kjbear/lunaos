<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceConfig extends Model
{
    protected $fillable = [
        'agent_id',
        'soul_md',
        'identity_md',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}