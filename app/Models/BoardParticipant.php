<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BoardParticipant extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'session_id',
        'persona_role',
        'model_config',
    ];

    protected $casts = [
        'model_config' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the session this participant belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BoardSession::class, 'session_id');
    }

    /**
     * Get the discussion entries for this participant.
     */
    public function discussionEntries(): HasMany
    {
        return $this->hasMany(BoardDiscussionEntry::class, 'participant_id');
    }

    /**
     * Get the model configuration value.
     */
    public function getModelAttribute(): string
    {
        return $this->model_config['model'] ?? 'glm-5';
    }

    /**
     * Get the default system prompt for this role.
     */
    public function getSystemPrompt(): string
    {
        $role = strtoupper($this->persona_role);
        
        return match($role) {
            'COO' => "You are the COO (Chief Operations Officer). You focus on operational efficiency, resource allocation, process optimization, and execution feasibility. You think practically about how to implement decisions. You're data-driven and concerned with metrics and KPIs.",
            'CFO' => "You are the CFO (Chief Financial Officer). You focus on financial implications, ROI, budget constraints, risk management, and capital allocation. You're conservative and analytical, always considering the bottom line and financial sustainability.",
            'CTO' => "You are the CTO (Chief Technology Officer). You focus on technical feasibility, system architecture, technology choices, scalability, and innovation. You think about implementation details, technical debt, and engineering resources.",
            'CMO' => "You are the CMO (Chief Marketing Officer). You focus on market positioning, customer impact, brand perception, go-to-market strategy, and growth. You think about how decisions affect customer acquisition and brand value.",
            'CPO' => "You are the CPO (Chief Product Officer). You focus on product strategy, user experience, feature prioritization, product-market fit, and roadmap alignment. You advocate for the user and product vision.",
            default => "You are a C-level executive providing strategic input. Consider the business impact, risks, and opportunities. Be concise, specific, and actionable.",
        };
    }

    /**
     * Get the persona name based on role.
     */
    public function getPersonaName(): string
    {
        $role = strtoupper($this->persona_role);
        
        return match($role) {
            'COO' => 'Gwynne',
            'CFO' => 'Warren',
            'CTO' => 'Werner',
            'CMO' => 'Bozoma',
            'CPO' => 'Fidji',
            default => 'Executive',
        };
    }

    /**
     * Get the avatar emoji for this role.
     */
    public function getAvatarEmoji(): string
    {
        $role = strtoupper($this->persona_role);
        
        return match($role) {
            'COO' => '👔',
            'CFO' => '💰',
            'CTO' => '💻',
            'CMO' => '📢',
            'CPO' => '📦',
            default => '👤',
        };
    }
}
