<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\TeamService;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeamService $teamService;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure clean state - truncate team_members table
        \DB::table('team_members')->truncate();
        $this->teamService = new TeamService();
    }

    /** @test */
    public function create_team_member(): void
    {
        $data = [
            'name' => 'new-member',
            'title' => 'Software Engineer',
            'type' => 'workers',
            'type' => 'workers',
            'model' => 'ollama-local/qwen3.5:cloud',
            'provider' => 'ollama',
            'status' => 'active',
            'system_prompt' => 'You are a helpful assistant',
        ];

        $member = $this->teamService->createTeamMember($data);

        $this->assertInstanceOf(Model::class, $member);
        $this->assertEquals('new-member', $member->name);
        $this->assertEquals('Software Engineer', $member->title);
        $this->assertDatabaseHas('team_members', [
            'name' => 'new-member',
            'type' => 'workers',
        ]);
    }

    /** @test */
    public function create_team_member_with_validation_errors(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Missing required 'name' field
        $this->teamService->createTeamMember([]);
    }

    /** @test */
    public function create_team_member_prevents_duplicate_name(): void
    {
        TeamMember::create([
            'name' => 'dave',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $this->teamService->createTeamMember([
            'name' => 'dave',
            'type' => 'personas',
            'role' => 'persona',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function get_all_team_members(): void
    {
        TeamMember::factory()->count(5)->create();

        $result = $this->teamService->getAllTeamMembers();

        $this->assertEquals(5, $result->total());
    }

    /** @test */
    public function get_team_member_by_id(): void
    {
        $member = TeamMember::create([
            'name' => 'find-by-id',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $found = $this->teamService->getTeamMemberById($member->id);

        $this->assertInstanceOf(Model::class, $found);
        $this->assertEquals($member->id, $found->id);
        $this->assertEquals('find-by-id', $found->name);
    }

    /** @test */
    public function get_team_member_by_id_returns_null_when_not_found(): void
    {
        $found = $this->teamService->getTeamMemberById('non-existent-uuid');

        $this->assertNull($found);
    }

    /** @test */
    public function get_team_member_by_name(): void
    {
        TeamMember::create([
            'name' => 'dave',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $found = $this->teamService->getTeamMemberByName('dave');

        $this->assertInstanceOf(Model::class, $found);
        $this->assertEquals('dave', $found->name);
    }

    /** @test */
    public function get_team_member_by_name_returns_null_when_not_found(): void
    {
        $found = $this->teamService->getTeamMemberByName('non-existent');

        $this->assertNull($found);
    }

    /** @test */
    public function update_team_member(): void
    {
        $member = TeamMember::create([
            'name' => 'update-test',
            'title' => 'Original Title',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $updated = $this->teamService->updateTeamMember($member, [
            'title' => 'Updated Title',
            'status' => 'online',
        ]);

        $this->assertTrue($updated);
        $this->assertEquals('Updated Title', $member->fresh()->title);
        $this->assertEquals('online', $member->fresh()->status);
    }

    /** @test */
    public function update_team_member_prevents_duplicate_name(): void
    {
        TeamMember::create([
            'name' => 'existing',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $member = TeamMember::create([
            'name' => 'to-update',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $this->teamService->updateTeamMember($member, [
            'name' => 'existing',
        ]);
    }

    /** @test */
    public function delete_team_member(): void
    {
        $member = TeamMember::create([
            'name' => 'delete-test',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $deleted = $this->teamService->deleteTeamMember($member);

        $this->assertTrue($deleted);
        $this->assertEquals('archived', $member->fresh()->status);
        $this->assertNotNull($member->fresh()->deactivated_at);
    }

    /** @test */
    public function restore_team_member(): void
    {
        $member = TeamMember::create([
            'name' => 'restore-test',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'archived',
            'deactivated_at' => now(),
        ]);

        $restored = $this->teamService->restoreTeamMember($member);

        $this->assertTrue($restored);
        $this->assertEquals('active', $member->fresh()->status);
        $this->assertNull($member->fresh()->deactivated_at);
    }

    /** @test */
    public function filter_by_category(): void
    {
        TeamMember::create(['name' => 'worker1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'worker2', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'board1', 'type' => 'personas', 'role' => 'board_member', 'status' => 'active']);
        TeamMember::create(['name' => 'subagent1', 'type' => 'personas', 'role' => 'persona', 'status' => 'active']);

        $workers = $this->teamService->getAllTeamMembers(['type' => 'workers']);
        $boardMembers = $this->teamService->getAllTeamMembers(['role' => 'board_member']);
        $subagents = $this->teamService->getAllTeamMembers(['role' => 'persona']);

        $this->assertEquals(2, $workers->total());
        $this->assertEquals(1, $boardMembers->total());
        $this->assertEquals(1, $subagents->total());
    }

    /** @test */
    public function filter_by_status(): void
    {
        TeamMember::create(['name' => 'active1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'online1', 'type' => 'workers', 'type' => 'workers', 'status' => 'online']);
        TeamMember::create(['name' => 'offline1', 'type' => 'workers', 'type' => 'workers', 'status' => 'offline']);
        TeamMember::create(['name' => 'active2', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);

        $active = $this->teamService->getAllTeamMembers(['status' => 'active']);
        $online = $this->teamService->getAllTeamMembers(['status' => 'online']);
        $offline = $this->teamService->getAllTeamMembers(['status' => 'offline']);

        $this->assertEquals(2, $active->total());
        $this->assertEquals(1, $online->total());
        $this->assertEquals(1, $offline->total());
    }

    /** @test */
    public function search_team_members(): void
    {
        TeamMember::create(['name' => 'dave', 'title' => 'Developer', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'sarah', 'title' => 'Designer', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'david', 'title' => 'DevOps', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'maya', 'title' => 'Frontend Dev', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);

        $searchResult = $this->teamService->searchTeamMembers('dav');

        $this->assertEquals(2, $searchResult->total());
        $this->assertTrue($searchResult->pluck('name')->contains('dave'));
        $this->assertTrue($searchResult->pluck('name')->contains('david'));
    }

    /** @test */
    public function pagination(): void
    {
        TeamMember::factory()->count(25)->create();

        $result = $this->teamService->getAllTeamMembers(['per_page' => 10, 'page' => 1]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    /** @test */
    public function bulk_update_status(): void
    {
        TeamMember::create(['name' => 'member1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'member2', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'member3', 'type' => 'workers', 'type' => 'workers', 'status' => 'online']);

        $ids = TeamMember::whereIn('name', ['member1', 'member2', 'member3'])->pluck('id')->toArray();

        $updated = $this->teamService->bulkUpdateStatus($ids, 'offline');

        $this->assertTrue($updated);
        $this->assertEquals(3, TeamMember::where('status', 'offline')->count());
    }

    /** @test */
    public function bulk_delete(): void
    {
        TeamMember::create(['name' => 'to-delete1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'to-delete2', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'to-keep', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);

        $ids = TeamMember::whereIn('name', ['to-delete1', 'to-delete2'])->pluck('id')->toArray();

        $deleted = $this->teamService->bulkDelete($ids);

        $this->assertTrue($deleted);
        $this->assertEquals(1, TeamMember::where('status', 'active')->count());
        $this->assertEquals(2, TeamMember::where('status', 'archived')->count());
    }

    /** @test */
    public function get_team_statistics(): void
    {
        TeamMember::create(['name' => 'worker1', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'worker2', 'type' => 'workers', 'status' => 'online']);
        TeamMember::create(['name' => 'board1', 'type' => 'board-members', 'role' => 'board_member', 'status' => 'active']);
        TeamMember::create(['name' => 'subagent1', 'type' => 'personas', 'role' => 'persona', 'status' => 'inactive']);
        TeamMember::create(['name' => 'custom1', 'type' => 'personas', 'role' => 'persona', 'status' => 'error']);

        $stats = $this->teamService->getTeamStatistics();

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(2, $stats['by_role']['worker']);
        $this->assertEquals(1, $stats['by_role']['board_member']);
        $this->assertEquals(2, $stats['by_role']['persona']);
        $this->assertEquals(2, $stats['by_status']['active']);
        $this->assertEquals(1, $stats['by_status']['online']);
        $this->assertEquals(1, $stats['by_status']['inactive']);
        $this->assertEquals(1, $stats['by_status']['error']);
        $this->assertEquals(2, $stats['by_type']['workers']);
        $this->assertEquals(2, $stats['by_type']['personas']);
        $this->assertEquals(1, $stats['by_type']['board-members']);
    }

    /** @test */
    public function assign_parent_to_team_member(): void
    {
        $parent = TeamMember::create([
            'name' => 'parent',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $child = TeamMember::create([
            'name' => 'child',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $updated = $this->teamService->assignParent($child, $parent->id);

        $this->assertTrue($updated);
        $this->assertEquals($parent->id, $child->fresh()->parent_id);
    }

    /** @test */
    public function prevent_circular_hierarchy(): void
    {
        $parent = TeamMember::create([
            'name' => 'parent',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
        ]);

        $child = TeamMember::create([
            'name' => 'child',
            'type' => 'workers',
            'type' => 'workers',
            'status' => 'active',
            'parent_id' => $parent->id,
        ]);

        // Try to make parent a child of child (circular reference)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create circular hierarchy');

        $this->teamService->assignParent($parent, $child->id);
    }

    /** @test */
    public function get_team_members_by_type(): void
    {
        TeamMember::create(['name' => 'agent1', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'agent2', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'persona1', 'type' => 'personas', 'role' => 'board_member', 'status' => 'active']);
        TeamMember::create(['name' => 'hybrid1', 'type' => 'board-members', 'role' => 'board_member', 'status' => 'active']);

        $agents = $this->teamService->getTeamMembersByType('workers');
        $personas = $this->teamService->getTeamMembersByType('personas');
        $hybrids = $this->teamService->getTeamMembersByType('board-members');

        $this->assertEquals(2, $agents->total());
        $this->assertEquals(1, $personas->total());
        $this->assertEquals(1, $hybrids->total());
    }

    /** @test */
    public function get_workers_only(): void
    {
        TeamMember::create(['name' => 'worker1', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'worker2', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'board1', 'type' => 'personas', 'role' => 'board_member', 'status' => 'active']);
        TeamMember::create(['name' => 'subagent1', 'type' => 'personas', 'role' => 'persona', 'status' => 'active']);

        $workers = $this->teamService->getWorkers();

        $this->assertEquals(2, $workers->total());
        $this->assertTrue($workers->pluck('role')->contains('worker'));
    }

    /** @test */
    public function get_board_members_only(): void
    {
        TeamMember::create(['name' => 'worker1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'board1', 'type' => 'personas', 'role' => 'board_member', 'status' => 'active']);
        TeamMember::create(['name' => 'board2', 'type' => 'personas', 'role' => 'board_member', 'status' => 'active']);

        $boardMembers = $this->teamService->getBoardMembers();

        $this->assertEquals(2, $boardMembers->total());
        $this->assertTrue($boardMembers->pluck('role')->contains('board_member'));
    }

    /** @test */
    public function get_online_members(): void
    {
        TeamMember::create(['name' => 'online1', 'type' => 'workers', 'type' => 'workers', 'status' => 'online']);
        TeamMember::create(['name' => 'offline1', 'type' => 'workers', 'type' => 'workers', 'status' => 'offline']);
        TeamMember::create(['name' => 'online2', 'type' => 'workers', 'type' => 'workers', 'status' => 'online']);
        TeamMember::create(['name' => 'active1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);

        $online = $this->teamService->getOnlineMembers();

        $this->assertEquals(2, $online->total());
        $this->assertTrue($online->pluck('status')->contains('online'));
    }

    /** @test */
    public function team_service_handles_empty_results(): void
    {
        $result = $this->teamService->getAllTeamMembers();

        $this->assertEquals(0, $result->total());
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function team_service_orders_by_created_at_descending(): void
    {
        // Use DB facade to insert with explicit timestamps (bypasses Eloquent's automatic timestamp setting)
        $oldestId = (string) Str::uuid();
        $middleId = (string) Str::uuid();
        $newestId = (string) Str::uuid();
        
        DB::table('team_members')->insert([
            'id' => $oldestId,
            'name' => 'oldest',
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => now(),
        ]);
        
        DB::table('team_members')->insert([
            'id' => $middleId,
            'name' => 'middle',
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
            'created_at' => '2024-01-02 00:00:00',
            'updated_at' => now(),
        ]);
        
        DB::table('team_members')->insert([
            'id' => $newestId,
            'name' => 'newest',
            'type' => 'workers',
            'role' => 'worker',
            'status' => 'active',
            'created_at' => '2024-01-03 00:00:00',
            'updated_at' => now(),
        ]);

        $result = $this->teamService->getAllTeamMembers();

        $this->assertEquals(3, $result->total());
        
        $ids = $result->pluck('id');
        $this->assertEquals($newestId, $ids->first());
        $this->assertEquals($oldestId, $ids->last());
    }

    /** @test */
    public function team_service_filters_by_multiple_criteria(): void
    {
        TeamMember::create(['name' => 'w1', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'w2', 'type' => 'workers', 'type' => 'workers', 'status' => 'online']);
        TeamMember::create(['name' => 'w3', 'type' => 'workers', 'type' => 'workers', 'status' => 'active']);
        TeamMember::create(['name' => 'b1', 'type' => 'personas', 'role' => 'board_member', 'status' => 'active']);

        $result = $this->teamService->getAllTeamMembers([
            'type' => 'workers',
            'status' => 'active',
        ]);

        $this->assertEquals(2, $result->total());
    }
}
