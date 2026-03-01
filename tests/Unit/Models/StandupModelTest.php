<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Standup;
use App\Models\Agent;
use App\Models\StandupDeliverable;
use App\Models\StandupActionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StandupModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_standup_can_be_created(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $standup = Standup::create([
            'agent_id' => $agent->id,
            'date' => now()->toDateString(),
            'yesterday' => 'Worked on tests',
            'today' => 'Writing more tests',
            'blockers' => 'None',
        ]);

        $this->assertDatabaseHas('standups', [
            'agent_id' => $agent->id,
            'yesterday' => 'Worked on tests',
        ]);
    }

    public function test_standup_has_deliverables(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $standup = Standup::create([
            'agent_id' => $agent->id,
            'date' => now()->toDateString(),
            'yesterday' => 'Worked on tests',
            'today' => 'Writing more tests',
            'blockers' => 'None',
        ]);

        $deliverable = StandupDeliverable::create([
            'standup_id' => $standup->id,
            'description' => 'Completed unit tests',
        ]);

        $this->assertEquals(1, $standup->deliverables()->count());
        $this->assertEquals('Completed unit tests', $standup->deliverables->first()->description);
    }

    public function test_standup_has_action_items(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $standup = Standup::create([
            'agent_id' => $agent->id,
            'date' => now()->toDateString(),
            'yesterday' => 'Worked on tests',
            'today' => 'Writing more tests',
            'blockers' => 'None',
        ]);

        $actionItem = StandupActionItem::create([
            'standup_id' => $standup->id,
            'description' => 'Review PR #42',
            'assigned_to' => 'test-agent',
        ]);

        $this->assertEquals(1, $standup->actionItems()->count());
        $this->assertEquals('Review PR #42', $standup->actionItems->first()->description);
    }
}
