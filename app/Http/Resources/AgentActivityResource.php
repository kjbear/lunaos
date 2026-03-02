<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentActivityResource extends JsonResource
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
            'task_id' => $this->task_id,
            'agent_id' => $this->agent_id,
            'action' => $this->action,
            'details' => $this->details,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
