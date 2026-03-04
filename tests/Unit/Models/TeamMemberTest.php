<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\TeamMember;
use App\Models\TeamMemberMetric;
use App\Models\TeamMemberWorkspace;
use App\Models\AgentActivity;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class TeamMemberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function team_member_can_be_created(): void
    {
        $member = TeamMember::create([
            'name' => 'test-member',
            'title' => 'Test Engineer',
            'type' => 'agent',
            'category' => 'worker',
            'model' => 'ollama-local/qwen3.5:cloud',
            'provider' => 'ollama',
            'status' => 'active',
            'system_prompt' => 'You are a test team member',
        ]);

        $this->assertDatabaseHas('team_members', [
            'name' => 'test-member',
            'title' => 'Test Engineer',
            'type' => 'agent',
        ]);
    }

    /** @test */
    public function team_member_requires_unique_name(): void
    {
        TeamMember::create([
            'name' => 'dave',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        TeamMember::create([
            'name' => 'dave',
            'type' => 'persona',
            'category' => 'subagent',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function team_member_type_enum(): void
    {
        // Valid types
        $types = ['persona', 'agent', 'hybrid'];
        
        foreach ($types as $type) {
            $member = TeamMember::create([
                'name' => "test-{$type}",
                'type' => $type,
                'category' => 'worker',
                'status' => 'active',
            ]);
            
            $this->assertEquals($type, $member->type);
        }
    }

    /** @test */
    public function team_member_type_enum_rejects_invalid_values(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        TeamMember::create([
            'name' => 'test-invalid',
            'type' => 'invalid_type',
            'category' => 'worker',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function team_member_category_enum(): void
    {
        // Valid categories
        $categories = ['board_member', 'subagent', 'worker', 'custom'];
        
        foreach ($categories as $category) {
            $member = TeamMember::create([
                'name' => "test-{$category}",
                'type' => 'agent',
                'category' => $category,
                'status' => 'active',
            ]);
            
            $this->assertEquals($category, $member->category);
        }
    }

    /** @test */
    public function team_member_category_enum_rejects_invalid_values(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        TeamMember::create([
            'name' => 'test-invalid',
            'type' => 'agent',
            'category' => 'invalid_category',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function team_member_status_enum(): void
    {
        // Valid statuses
        $statuses = ['active', 'inactive', 'online', 'offline', 'error', 'busy', 'archived'];
        
        foreach ($statuses as $status) {
            $member = TeamMember::create([
                'name' => "test-{$status}",
                'type' => 'agent',
                'category' => 'worker',
                'status' => $status,
            ]);
            
            $this->assertEquals($status, $member->status);
        }
    }

    /** @test */
    public function team_member_uses_uuid_primary_key(): void
    {
        $member = TeamMember::create([
            'name' => 'uuid-test',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $this->assertTrue(Str::isUuid($member->id));
    }

    /** @test */
    public function team_member_parent_child_relationship(): void
    {
        $parent = TeamMember::create([
            'name' => 'parent',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $child = TeamMember::create([
            'name' => 'child',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
            'parent_id' => $parent->id,
        ]);

        $this->assertTrue($child->parent()->exists());
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($parent->children()->exists());
        $this->assertEquals(1, $parent->children()->count());
    }

    /** @test */
    public function team_member_metrics_relationship(): void
    {
        $member = TeamMember::create([
            'name' => 'metrics-test',
            'type' => 'persona',
            'category' => 'board_member',
            'status' => 'active',
        ]);

        $metric = TeamMemberMetric::create([
            'team_member_id' => $member->id,
            'sessions_count' => 10,
            'tokens_used' => 50000,
        ]);

        $this->assertTrue($member->metrics()->exists());
        $this->assertEquals(10, $member->metrics->sessions_count);
    }

    /** @test */
    public function team_member_workspaces_relationship(): void
    {
        $member = TeamMember::create([
            'name' => 'workspace-test',
            'type' => 'persona',
            'category' => 'subagent',
            'status' => 'active',
            'workspace_path' => '/workspace/test',
        ]);

        TeamMemberWorkspace::create([
            'team_member_id' => $member->id,
            'file_path' => '/workspace/test/file1.md',
            'file_type' => 'markdown',
        ]);

        TeamMemberWorkspace::create([
            'team_member_id' => $member->id,
            'file_path' => '/workspace/test/file2.md',
            'file_type' => 'markdown',
        ]);

        $this->assertTrue($member->workspaces()->exists());
        $this->assertEquals(2, $member->workspaces()->count());
    }

    /** @test */
    public function team_member_activities_relationship(): void
    {
        $member = TeamMember::create([
            'name' => 'activity-test',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        AgentActivity::create([
            'team_member_id' => $member->id,
            'activity_type' => 'task_completed',
            'description' => 'Completed task #123',
        ]);

        AgentActivity::create([
            'team_member_id' => $member->id,
            'activity_type' => 'session_started',
            'description' => 'Started new session',
        ]);

        $this->assertTrue($member->activities()->exists());
        $this->assertEquals(2, $member->activities()->count());
    }

    /** @test */
    public function team_member_tasks_relationship(): void
    {
        $member = TeamMember::create([
            'name' => 'task-test',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        Task::create([
            'title' => 'Test Task 1',
            'assigned_to' => $member->name,
            'status' => 'pending',
            'step' => 'develop',
            'priority' => 'medium',
        ]);

        Task::create([
            'title' => 'Test Task 2',
            'assigned_to' => $member->name,
            'status' => 'in_progress',
            'step' => 'qa',
            'priority' => 'high',
        ]);

        $this->assertTrue($member->tasks()->exists());
        $this->assertEquals(2, $member->tasks()->count());
    }

    /** @test */
    public function computed_attribute_status_badge_class(): void
    {
        $active = TeamMember::create([
            'name' => 'active-member',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $online = TeamMember::create([
            'name' => 'online-member',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'online',
        ]);

        $offline = TeamMember::create([
            'name' => 'offline-member',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'offline',
        ]);

        $error = TeamMember::create([
            'name' => 'error-member',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'error',
        ]);

        $archived = TeamMember::create([
            'name' => 'archived-member',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'archived',
        ]);

        // Verify badge classes exist (actual values depend on implementation)
        $this->assertIsString($active->status_badge_class);
        $this->assertIsString($online->status_badge_class);
        $this->assertIsString($offline->status_badge_class);
        $this->assertIsString($error->status_badge_class);
        $this->assertIsString($archived->status_badge_class);
    }

    /** @test */
    public function scope_active_team_members(): void
    {
        $active = TeamMember::create([
            'name' => 'active',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $inactive = TeamMember::create([
            'name' => 'inactive',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'inactive',
        ]);

        $online = TeamMember::create([
            'name' => 'online',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'online',
        ]);

        $archived = TeamMember::create([
            'name' => 'archived',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'archived',
        ]);

        $activeMembers = TeamMember::active()->get();

        $this->assertEquals(2, $activeMembers->count());
        $this->assertTrue($activeMembers->contains('id', $active->id));
        $this->assertTrue($activeMembers->contains('id', $online->id));
        $this->assertFalse($activeMembers->contains('id', $inactive->id));
        $this->assertFalse($activeMembers->contains('id', $archived->id));
    }

    /** @test */
    public function scope_by_category(): void
    {
        $worker = TeamMember::create([
            'name' => 'worker',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $boardMember = TeamMember::create([
            'name' => 'board',
            'type' => 'persona',
            'category' => 'board_member',
            'status' => 'active',
        ]);

        $subagent = TeamMember::create([
            'name' => 'subagent',
            'type' => 'persona',
            'category' => 'subagent',
            'status' => 'active',
        ]);

        $workers = TeamMember::category('worker')->get();
        $boardMembers = TeamMember::category('board_member')->get();
        $subagents = TeamMember::category('subagent')->get();

        $this->assertEquals(1, $workers->count());
        $this->assertTrue($workers->contains('id', $worker->id));

        $this->assertEquals(1, $boardMembers->count());
        $this->assertTrue($boardMembers->contains('id', $boardMember->id));

        $this->assertEquals(1, $subagents->count());
        $this->assertTrue($subagents->contains('id', $subagent->id));
    }

    /** @test */
    public function scope_by_type(): void
    {
        $agent = TeamMember::create([
            'name' => 'agent',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $persona = TeamMember::create([
            'name' => 'persona',
            'type' => 'persona',
            'category' => 'board_member',
            'status' => 'active',
        ]);

        $hybrid = TeamMember::create([
            'name' => 'hybrid',
            'type' => 'hybrid',
            'category' => 'custom',
            'status' => 'active',
        ]);

        $agents = TeamMember::type('agent')->get();
        $personas = TeamMember::type('persona')->get();
        $hybrids = TeamMember::type('hybrid')->get();

        $this->assertEquals(1, $agents->count());
        $this->assertTrue($agents->contains('id', $agent->id));

        $this->assertEquals(1, $personas->count());
        $this->assertTrue($personas->contains('id', $persona->id));

        $this->assertEquals(1, $hybrids->count());
        $this->assertTrue($hybrids->contains('id', $hybrid->id));
    }

    /** @test */
    public function team_member_serialization(): void
    {
        $member = TeamMember::create([
            'name' => 'serialize-test',
            'title' => 'Test Engineer',
            'type' => 'agent',
            'category' => 'worker',
            'model' => 'ollama-local/qwen3.5:cloud',
            'provider' => 'ollama',
            'status' => 'active',
            'emoji' => '🤖',
            'avatar' => '🤖',
        ]);

        $json = $member->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertArrayHasKey('id', $decoded);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertArrayHasKey('title', $decoded);
        $this->assertArrayHasKey('type', $decoded);
        $this->assertArrayHasKey('category', $decoded);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertEquals('serialize-test', $decoded['name']);
        $this->assertEquals('Test Engineer', $decoded['title']);
    }

    /** @test */
    public function team_member_has_soft_delete_via_deactivated_at(): void
    {
        $member = TeamMember::create([
            'name' => 'soft-delete-test',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        $this->assertNull($member->deactivated_at);

        $member->update(['deactivated_at' => now()]);

        $this->assertNotNull($member->deactivated_at);
        $this->assertEquals('archived', $member->status);
    }

    /** @test */
    public function team_member_computed_attribute_is_online(): void
    {
        $onlineMember = TeamMember::create([
            'name' => 'online',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'online',
        ]);

        $offlineMember = TeamMember::create([
            'name' => 'offline',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'offline',
        ]);

        // This test assumes an isOnline computed attribute
        // Adjust based on actual implementation
        $this->assertTrue($onlineMember->is_online);
        $this->assertFalse($offlineMember->is_online);
    }

    /** @test */
    public function team_member_gets_default_values(): void
    {
        $member = TeamMember::create([
            'name' => 'defaults-test',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
        ]);

        // Verify defaults are set
        $this->assertEquals('ollama', $member->provider);
        $this->assertEquals('php', $member->runtime_location);
        $this->assertEquals('🤖', $member->emoji);
    }

    /** @test */
    public function team_member_fillable_attributes(): void
    {
        $member = new TeamMember();

        $fillable = [
            'name', 'title', 'type', 'category', 'model', 'provider',
            'avatar', 'emoji', 'status', 'system_prompt', 'settings',
            'workspace_path', 'runtime_location', 'strategy_class',
            'capabilities', 'parent_id', 'deactivated_at', 'last_location_check'
        ];

        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $member->getFillable());
        }
    }

    /** @test */
    public function team_member_casts_json_fields(): void
    {
        $member = TeamMember::create([
            'name' => 'cast-test',
            'type' => 'agent',
            'category' => 'worker',
            'status' => 'active',
            'settings' => ['key' => 'value'],
            'capabilities' => ['coding', 'testing'],
        ]);

        $this->assertIsArray($member->settings);
        $this->assertIsArray($member->capabilities);
        $this->assertEquals('value', $member->settings['key']);
        $this->assertContains('coding', $member->capabilities);
    }
}
