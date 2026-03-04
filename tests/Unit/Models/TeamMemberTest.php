<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\TeamMember;
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
            'type' => 'workers',
            'role' => 'worker',
            'model' => 'ollama-local/qwen3.5:cloud',
            'provider' => 'ollama',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('team_members', [
            'name' => 'test-member',
            'title' => 'Test Engineer',
            'type' => 'workers',
        ]);
    }

    /** @test */
    public function team_member_requires_unique_name(): void
    {
        TeamMember::create([
            'name' => 'dave',
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        TeamMember::create([
            'name' => 'dave',
            'type' => 'personas',
            'role' => 'persona',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function team_member_type_enum(): void
    {
        // Valid types match migration: personas, board-members, workers
        $types = ['personas', 'board-members', 'workers'];
        
        foreach ($types as $type) {
            $role = $type === 'board-members' ? 'board_member' : ($type === 'personas' ? 'persona' : 'worker');
            $member = TeamMember::create([
                'name' => "test-{$type}",
                'type' => $type,
                'role' => $role,
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
            'type' => 'agent',
            'role' => 'worker',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function team_member_status_enum(): void
    {
        // Valid statuses from migration
        $statuses = ['active', 'inactive', 'online', 'offline', 'error', 'busy', 'archived'];
        
        foreach ($statuses as $status) {
            $member = TeamMember::create([
                'name' => "test-{$status}",
                'type' => 'workers',
                'role' => 'worker',
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
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
        ]);

        $this->assertTrue(Str::isUuid($member->id));
    }

    /** @test */
    public function team_member_parent_child_relationship(): void
    {
        $parent = TeamMember::create([
            'name' => 'parent-member',
            'type' => 'board-members',
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $child = TeamMember::create([
            'name' => 'child-member',
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertTrue($parent->fresh()->children->isNotEmpty());
    }

    /** @test */
    public function team_member_tasks_relationship(): void
    {
        $member = TeamMember::create([
            'name' => 'task-test',
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
        ]);

        $task = TeamMember::create(['name' => 'temp', 'type' => 'workers', 'role' => 'worker']) // Create member first
            ->tasks()
            ->create(['title' => 'Test Task', 'status' => 'pending']);

        $this->assertEquals('Test Task', $task->title);
    }

    /** @test */
    public function computed_attribute_status_badge_class(): void
    {
        $active = TeamMember::create(['name' => 'active-member', 'type' => 'workers', 'role' => 'worker', 'status' => 'active']);
        $offline = TeamMember::create(['name' => 'offline-member', 'type' => 'workers', 'role' => 'worker', 'status' => 'offline']);
        $error = TeamMember::create(['name' => 'error-member', 'type' => 'workers', 'role' => 'worker', 'status' => 'error']);

        $this->assertEquals('badge-success', $active->status_badge_class);
        $this->assertEquals('badge-secondary', $offline->status_badge_class);
        $this->assertEquals('badge-danger', $error->status_badge_class);
    }

    /** @test */
    public function scope_active_team_members(): void
    {
        TeamMember::create(['name' => 'active-1', 'type' => 'workers', 'role' => 'worker', 'status' => 'active']);
        TeamMember::create(['name' => 'active-2', 'type' => 'workers', 'role' => 'worker', 'status' => 'online']);
        TeamMember::create(['name' => 'inactive-1', 'type' => 'workers', 'role' => 'worker', 'status' => 'inactive']);

        $activeMembers = TeamMember::active()->get();

        $this->assertEquals(2, $activeMembers->count());
    }

    /** @test */
    public function scope_by_category(): void
    {
        TeamMember::create(['name' => 'worker-1', 'type' => 'workers', 'role' => 'worker', 'category' => 'worker']);
        TeamMember::create(['name' => 'board-1', 'type' => 'board-members', 'role' => 'board_member', 'category' => 'board_member']);
        TeamMember::create(['name' => 'worker-2', 'type' => 'workers', 'role' => 'worker']); // Default category

        $workers = TeamMember::byCategory('worker')->get();
        $boardMembers = TeamMember::byCategory('board_member')->get();

        $this->assertEquals(2, $workers->count()); // worker-1 + worker-2 (default)
        $this->assertEquals(1, $boardMembers->count());
        $this->assertEquals('board-1', $boardMembers->first()->name);
    }
}
