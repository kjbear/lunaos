<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function index_returns_successful_response_with_members(): void
    {
        TeamMember::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/team');

        $response->assertStatus(200);
        $response->assertViewIs('team.index');
        $response->assertViewHas('members');
    }

    /** @test */
    public function index_returns_empty_state_when_no_members(): void
    {
        $response = $this->actingAs($this->user)->get('/team');

        $response->assertStatus(200);
        $response->assertViewIs('team.index');
    }

    /** @test */
    public function show_returns_successful_response_with_member(): void
    {
        $member = TeamMember::create([
            'name' => 'Test Member',
            'email' => 'test@test.com',
            'role' => 'worker',
        ]);

        $response = $this->actingAs($this->user)->get("/team/{$member->id}");

        $response->assertStatus(200);
        $response->assertViewIs('team.show');
        $response->assertViewHas('member', $member);
    }

    /** @test */
    public function show_returns_404_for_non_existent_member(): void
    {
        $response = $this->actingAs($this->user)->get('/team/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function create_returns_successful_response(): void
    {
        $response = $this->actingAs($this->user)->get('/team/create');

        $response->assertStatus(200);
        $response->assertViewIs('team.create');
    }

    /** @test */
    public function store_creates_new_member_and_redirects(): void
    {
        $data = [
            'name' => 'New Member',
            'email' => 'new@test.com',
            'role' => 'worker',
            'title' => 'Developer',
        ];

        $response = $this->actingAs($this->user)->post('/team', $data);

        $response->assertRedirect('/team');
        $this->assertDatabaseHas('team_members', [
            'name' => 'New Member',
            'email' => 'new@test.com',
        ]);
    }

    /** @test */
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/team', []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /** @test */
    public function store_validates_unique_email(): void
    {
        TeamMember::create([
            'name' => 'Existing',
            'email' => 'existing@test.com',
        ]);

        $response = $this->actingAs($this->user)->post('/team', [
            'name' => 'Duplicate',
            'email' => 'existing@test.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function edit_returns_successful_response(): void
    {
        $member = TeamMember::create([
            'name' => 'Edit Me',
            'email' => 'edit@test.com',
        ]);

        $response = $this->actingAs($this->user)->get("/team/{$member->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('team.edit');
        $response->assertViewHas('member', $member);
    }

    /** @test */
    public function update_modifies_member_and_redirects(): void
    {
        $member = TeamMember::create([
            'name' => 'Original',
            'email' => 'original@test.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->put("/team/{$member->id}", [
            'name' => 'Updated',
            'status' => 'inactive',
        ]);

        $response->assertRedirect("/team/{$member->id}");
        $this->assertDatabaseHas('team_members', [
            'id' => $member->id,
            'name' => 'Updated',
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function delete_removes_member(): void
    {
        $member = TeamMember::create([
            'name' => 'Delete Me',
            'email' => 'delete@test.com',
        ]);

        $response = $this->actingAs($this->user)->delete("/team/{$member->id}");

        $response->assertRedirect('/team');
        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }

    /** @test */
    public function index_without_params_shows_all_members(): void
    {
        // Issue #21: '/team' should show all members, not filter to workers
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas']);
        TeamMember::create(['name' => 'Board 1', 'email' => 'b1@test.com', 'type' => 'board-members']);

        $response = $this->actingAs($this->user)->get('/team');

        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            // Should contain all 3 members (workers, personas, board)
            return $members->total() === 3;
        });
        $response->assertViewHas('activeTab', 'all');
    }

    /** @test */
    public function index_with_type_all_shows_all_members(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas']);

        $response = $this->actingAs($this->user)->get('/team?type=all');

        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->total() === 2;
        });
    }

    /** @test */
    public function workers_tab_filters_by_workers(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Worker 2', 'email' => 'w2@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas']);

        $response = $this->actingAs($this->user)->get('/team?tab=workers');

        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->every(fn($m) => $m->type === 'workers');
        });
    }

    /** @test */
    public function personas_tab_filters_by_personas(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas']);
        TeamMember::create(['name' => 'Persona 2', 'email' => 'p2@test.com', 'type' => 'personas']);

        $response = $this->actingAs($this->user)->get('/team?tab=personas');

        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->every(fn($m) => $m->type === 'personas');
        });
    }

    /** @test */
    public function board_members_tab_filters_by_board_members(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Board 1', 'email' => 'b1@test.com', 'type' => 'board-members']);
        TeamMember::create(['name' => 'Board 2', 'email' => 'b2@test.com', 'type' => 'board-members']);

        $response = $this->actingAs($this->user)->get('/team?tab=board-members');

        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->every(fn($m) => $m->type === 'board-members');
        });
    }

    /** @test */
    public function index_supports_pagination_with_per_page_param(): void
    {
        // Issue #20: Pagination support
        TeamMember::factory()->count(25)->create();

        // Default pagination (20 per page)
        $response = $this->actingAs($this->user)->get('/team');
        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->perPage() === 20 && $members->count() === 20;
        });

        // Custom per_page (10)
        $response = $this->actingAs($this->user)->get('/team?per_page=10');
        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->perPage() === 10;
        });

        // Custom per_page (50)
        $response = $this->actingAs($this->user)->get('/team?per_page=50');
        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            return $members->perPage() === 50;
        });
    }

    /** @test */
    public function pagination_preserves_query_params(): void
    {
        TeamMember::factory()->count(25)->create(['type' => 'workers']);
        TeamMember::factory()->count(15)->create(['type' => 'personas']);

        $response = $this->actingAs($this->user)->get('/team?type=workers&per_page=10');
        $response->assertStatus(200);
        $response->assertViewHas('members', function ($members) {
            // Verify pagination preserves type filter
            return $members->perPage() === 10 && $members->every(fn($m) => $m->type === 'workers');
        });
    }

    // API Endpoint Tests

    /** @test */
    public function api_index_returns_json_response(): void
    {
        TeamMember::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/team');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'role', 'type', 'status']
            ]
        ]);
    }

    /** @test */
    public function api_show_returns_single_member_json(): void
    {
        $member = TeamMember::create([
            'name' => 'API Test',
            'email' => 'api@test.com',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/team/{$member->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $member->id,
                'name' => 'API Test',
            ]
        ]);
    }

    /** @test */
    public function api_show_returns_404_for_non_existent(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/team/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function api_store_creates_member(): void
    {
        $data = [
            'name' => 'API Created',
            'email' => 'apicreated@test.com',
            'role' => 'worker',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/team', $data);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'API Created',
            ]
        ]);
        $this->assertDatabaseHas('team_members', ['name' => 'API Created']);
    }

    /** @test */
    public function api_update_modifies_member(): void
    {
        $member = TeamMember::create([
            'name' => 'Before',
            'email' => 'before@test.com',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/team/{$member->id}", [
            'name' => 'After',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'After',
            ]
        ]);
        $this->assertDatabaseHas('team_members', ['id' => $member->id, 'name' => 'After']);
    }

    /** @test */
    public function api_delete_removes_member(): void
    {
        $member = TeamMember::create([
            'name' => 'API Delete',
            'email' => 'apidelete@test.com',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/team/{$member->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }

    /** @test */
    public function api_members_endpoint_returns_subordinates(): void
    {
        $parent = TeamMember::create(['name' => 'Parent', 'email' => 'parent@test.com']);
        $child1 = TeamMember::create(['name' => 'Child 1', 'email' => 'c1@test.com', 'parent_id' => $parent->id]);
        $child2 = TeamMember::create(['name' => 'Child 2', 'email' => 'c2@test.com', 'parent_id' => $parent->id]);

        $response = $this->actingAs($this->user)->getJson("/api/team/{$parent->id}/members");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }
}
