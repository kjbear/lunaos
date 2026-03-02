<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'assigned_to' => $this->assigned_to,
            'repository_id' => $this->repository_id,
            'status' => $this->status,
            'step' => $this->step,
            'priority' => $this->priority,
            'task_type' => $this->task_type,
            'view_mode' => $this->view_mode ?? 'list',
            'context' => $this->context_json,
            'branch_name' => $this->branch_name,
            'pr_url' => $this->pr_url,
            'artifacts' => $this->artifacts_json ?? [],
            'failure_reason' => $this->failure_reason,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'progress_percentage' => $this->progress_percentage,
            'priority_badge_class' => $this->priority_badge_class,
            'status_badge_class' => $this->status_badge_class,
            'agent_display_name' => $this->agent_display_name,
            'created_at_human' => $this->created_at_human,
            'agent' => AgentResource::make($this->whenLoaded('agent')),
            'repository' => RepositoryResource::make($this->whenLoaded('repository')),
            'activities' => AgentActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
