<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Task;
use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $task = Task::create([
            'agent_id' => $agent->id,
            'title' => 'Test Task',
            'description' => 'Testing task creation',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'status' => 'pending',
        ]);
    }

    public function test_task_belongs_to_agent(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $task = Task::create([
            'agent_id' => $agent->id,
            'title' => 'Test Task',
            'description' => 'Testing task creation',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->assertEquals($agent->id, $task->agent_id);
        $this->assertInstanceOf(Agent::class, $task->agent);
        $this->assertEquals('test-agent', $task->agent->name);
    }

    public function test_task_status_transitions(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $task = Task::create([
            'agent_id' => $agent->id,
            'title' => 'Test Task',
            'status' => 'pending',
        ]);

        $task->update(['status' => 'in_progress']);
        $this->assertEquals('in_progress', $task->fresh()->status);

        $task->update(['status' => 'completed']);
        $this->assertEquals('completed', $task->fresh()->status);
    }
}
