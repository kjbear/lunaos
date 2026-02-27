<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubagentActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $agentId;
    public string $agentName;
    public string $action;
    public string $task;
    public string $status;
    public int $tokensUsed;
    public int $runtimeMs;
    public string $timestamp;

    public function __construct(
        string $agentId,
        string $agentName,
        string $action,
        string $task = '',
        string $status = 'running',
        int $tokensUsed = 0,
        int $runtimeMs = 0
    ) {
        $this->agentId = $agentId;
        $this->agentName = $agentName;
        $this->action = $action;
        $this->task = $task;
        $this->status = $status;
        $this->tokensUsed = $tokensUsed;
        $this->runtimeMs = $runtimeMs;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [new Channel('mission-control')];
    }

    public function broadcastAs(): string
    {
        return 'subagent.activity';
    }
}