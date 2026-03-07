<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectArtifact extends Model
{
    protected $fillable = [
        'project_id',
        'type',
        'title',
        'content',
        'source_type',
        'source_id',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected $appends = ['type_badge'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            'requirement' => '📋 Requirement',
            'board_discussion' => '🎙 Board Discussion',
            'doc' => '📄 Document',
            'note' => '📝 Note',
            'decision' => '✅ Decision',
            default => ucfirst($this->type),
        };
    }
}
