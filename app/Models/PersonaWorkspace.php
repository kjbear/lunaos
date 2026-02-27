<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonaWorkspace extends Model
{
    protected $fillable = [
        'persona_id',
        'file_name',
        'content',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the persona this workspace belongs to.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Sync content from filesystem.
     */
    public function syncFromFilesystem(): bool
    {
        if (!$this->persona->workspace_path) {
            return false;
        }

        $filePath = rtrim($this->persona->workspace_path, '/') . '/' . $this->file_name;

        if (file_exists($filePath)) {
            $this->content = file_get_contents($filePath);
            $this->last_synced_at = now();
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Scope for AGENTS.md files.
     */
    public function scopeAgentsMd($query)
    {
        return $query->where('file_name', 'AGENTS.md');
    }

    /**
     * Scope for TOOLS.md files.
     */
    public function scopeToolsMd($query)
    {
        return $query->where('file_name', 'TOOLS.md');
    }
}