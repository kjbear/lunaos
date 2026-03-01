<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Agent;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\Standup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_be_created(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('agents', [
            'name' => 'test-agent',
            'role' => 'Developer',
        ]);
    }

    public function test_agent_has_correct_relationships(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $this->assertTrue($agent->tasks()->exists());
        $this->assertTrue($agent->activities()->exists());
    }

    public function test_agent_strategy_pattern(): void
    {
        $agent = Agent::create([
            'name' => 'test-strategy',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
            'strategy_class' => 'develop',
        ]);

        $this->assertEquals('develop', $agent->strategy_class);
    }
}
