<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonaMetric extends Model
{
    protected $fillable = [
        'persona_id',
        'projects_count',
        'tasks_completed',
        'tasks_failed',
        'tokens_used',
        'sessions_count',
        'decisions_count',
        'success_rate',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'success_rate' => 'decimal:2',
    ];

    /**
     * Get the persona these metrics belong to.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Increment task completion and recalculate success rate.
     */
    public function recordTaskCompleted(): void
    {
        $this->tasks_completed++;
        $this->recalculateSuccessRate();
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Increment task failure and recalculate success rate.
     */
    public function recordTaskFailed(): void
    {
        $this->tasks_failed++;
        $this->recalculateSuccessRate();
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Recalculate success rate based on completed/failed tasks.
     */
    private function recalculateSuccessRate(): void
    {
        $total = $this->tasks_completed + $this->tasks_failed;
        if ($total > 0) {
            $this->success_rate = ($this->tasks_completed / $total) * 100;
        }
    }

    /**
     * Add tokens used.
     */
    public function addTokens(int $tokens): void
    {
        $this->tokens_used += $tokens;
        $this->save();
    }

    /**
     * Increment session count.
     */
    public function incrementSessions(): void
    {
        $this->sessions_count++;
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Increment decisions count (for board members).
     */
    public function incrementDecisions(): void
    {
        $this->decisions_count++;
        $this->last_active_at = now();
        $this->save();
    }
}