<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_can_be_created(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $activity = ActivityLog::create([
            'agent_id' => $agent->id,
            'action' => 'task_started',
            'description' => 'Started working on test task',
            'metadata' => ['task_id' => 1],
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agent_id' => $agent->id,
            'action' => 'task_started',
        ]);
    }

    public function test_activity_log_metadata_stored_as_json(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $activity = ActivityLog::create([
            'agent_id' => $agent->id,
            'action' => 'task_completed',
            'description' => 'Completed task',
            'metadata' => ['task_id' => 1, 'duration' => 300],
        ]);

        $this->assertEquals(1, $activity->metadata['task_id']);
        $this->assertEquals(300, $activity->metadata['duration']);
    }
}
