<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentUpdate extends Model
{
    protected $fillable = [
        'standup_id',
        'agent_name',
        'agent_role',
        'agent_color',
        'done_yesterday',
        'doing_today',
        'blockers',
        'order',
    ];

    public function standup(): BelongsTo
    {
        return $this->belongsTo(Standup::class);
    }

    /**
     * Check if agent has blockers
     */
    public function hasBlockers(): bool
    {
        return !empty($this->blockers) && $this->blockers !== 'None';
    }

    /**
     * Get formatted update text
     */
    public function getFormattedUpdate(): string
    {
        $lines = [];
        
        if ($this->done_yesterday) {
            $lines[] = "**Done:** {$this->done_yesterday}";
        }
        
        if ($this->doing_today) {
            $lines[] = "**Next:** {$this->doing_today}";
        }
        
        if ($this->hasBlockers()) {
            $lines[] = "**Blockers:** {$this->blockers}";
        }
        
        return implode("\n", $lines);
    }
}