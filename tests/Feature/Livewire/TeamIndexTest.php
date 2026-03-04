<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Livewire\Team\TeamIndex;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class TeamIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function team_index_loads_without_errors(): void
    {
        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('Team');
    }

    /** @test */
    public function team_index_shows_empty_state_when_no_members(): void
    {
        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('No team members');
    }

    /** @test */
    public function team_index_displays_members(): void
    {
        TeamMember::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'role' => 'worker',
            'title' => 'Developer',
        ]);

        TeamMember::create([
            'name' => 'Jane Smith',
            'email' => 'jane@test.com',
            'role' => 'persona',
            'title' => 'Designer',
        ]);

        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->assertSee('Developer')
            ->assertSee('Designer');
    }

    /** @test */
    public function team_index_filters_by_workers_tab(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Worker 2', 'email' => 'w2@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas']);

        Livewire::test(TeamIndex::class)
            ->set('activeTab', 'workers')
            ->assertStatus(200)
            ->assertSee('Worker 1')
            ->assertSee('Worker 2')
            ->assertDontSee('Persona 1');
    }

    /** @test */
    public function team_index_filters_by_personas_tab(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas']);
        TeamMember::create(['name' => 'Persona 2', 'email' => 'p2@test.com', 'type' => 'personas']);

        Livewire::test(TeamIndex::class)
            ->set('activeTab', 'personas')
            ->assertStatus(200)
            ->assertSee('Persona 1')
            ->assertSee('Persona 2')
            ->assertDontSee('Worker 1');
    }

    /** @test */
    public function team_index_filters_by_board_members_tab(): void
    {
        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Board 1', 'email' => 'b1@test.com', 'type' => 'board-members']);
        TeamMember::create(['name' => 'Board 2', 'email' => 'b2@test.com', 'type' => 'board-members']);

        Livewire::test(TeamIndex::class)
            ->set('activeTab', 'board-members')
            ->assertStatus(200)
            ->assertSee('Board 1')
            ->assertSee('Board 2')
            ->assertDontSee('Worker 1');
    }

    /** @test */
    public function team_index_tab_state_persists(): void
    {
        TeamMember::create(['name' => 'Worker', 'email' => 'w@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'type' => 'personas']);

        Livewire::test(TeamIndex::class)
            ->set('activeTab', 'personas')
            ->assertSet('activeTab', 'personas')
            ->assertSee('Persona')
            ->assertDontSee('Worker')
            
            ->call('refresh')
            ->assertSet('activeTab', 'personas')
            ->assertSee('Persona')
            ->assertDontSee('Worker');
    }

    /** @test */
    public function team_index_filters_by_status(): void
    {
        TeamMember::create(['name' => 'Active', 'email' => 'active@test.com', 'status' => 'active']);
        TeamMember::create(['name' => 'Inactive', 'email' => 'inactive@test.com', 'status' => 'inactive']);

        Livewire::test(TeamIndex::class)
            ->set('statusFilter', 'active')
            ->assertStatus(200)
            ->assertSee('Active')
            ->assertDontSee('Inactive');
    }

    /** @test */
    public function team_index_searches_by_name(): void
    {
        TeamMember::create(['name' => 'Alice Johnson', 'email' => 'alice@test.com']);
        TeamMember::create(['name' => 'Bob Smith', 'email' => 'bob@test.com']);
        TeamMember::create(['name' => 'Charlie Brown', 'email' => 'charlie@test.com']);

        Livewire::test(TeamIndex::class)
            ->set('search', 'Alice')
            ->assertStatus(200)
            ->assertSee('Alice Johnson')
            ->assertDontSee('Bob Smith')
            ->assertDontSee('Charlie Brown');
    }

    /** @test */
    public function team_index_searches_by_email(): void
    {
        TeamMember::create(['name' => 'Alice', 'email' => 'alice@test.com']);
        TeamMember::create(['name' => 'Bob', 'email' => 'bob@test.com']);

        Livewire::test(TeamIndex::class)
            ->set('search', 'test.com')
            ->assertStatus(200)
            ->assertSee('Alice')
            ->assertSee('Bob');
    }

    /** @test */
    public function team_index_pagination_displays_when_needed(): void
    {
        TeamMember::factory()->count(20)->create();

        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('Pagination');
    }

    /** @test */
    public function team_index_shows_member_count(): void
    {
        TeamMember::factory()->count(5)->create();

        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('5');
    }

    /** @test */
    public function team_index_shows_create_button(): void
    {
        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('Add Member');
    }

    /** @test */
    public function team_index_sorts_members_by_name(): void
    {
        TeamMember::create(['name' => 'Zoe', 'email' => 'zoe@test.com']);
        TeamMember::create(['name' => 'Alice', 'email' => 'alice@test.com']);
        TeamMember::create(['name' => 'Bob', 'email' => 'bob@test.com']);

        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSeeInOrder(['Alice', 'Bob', 'Zoe']);
    }

    /** @test */
    public function team_index_shows_role_badges(): void
    {
        TeamMember::create(['name' => 'Worker', 'email' => 'w@test.com', 'role' => 'worker']);
        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'role' => 'persona']);
        TeamMember::create(['name' => 'Board', 'email' => 'b@test.com', 'role' => 'board_member']);

        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('Worker')
            ->assertSee('Persona')
            ->assertSee('Board');
    }

    /** @test */
    public function team_index_shows_status_badges(): void
    {
        TeamMember::create(['name' => 'Active', 'email' => 'active@test.com', 'status' => 'active']);
        TeamMember::create(['name' => 'Inactive', 'email' => 'inactive@test.com', 'status' => 'inactive']);

        Livewire::test(TeamIndex::class)
            ->assertStatus(200)
            ->assertSee('Active')
            ->assertSee('Inactive');
    }

    /** @test */
    public function team_index_loads_with_correct_tab_from_query_string(): void
    {
        TeamMember::create(['name' => 'Worker', 'email' => 'w@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'type' => 'personas']);

        Livewire::withQueryParams(['tab' => 'personas'])
            ->test(TeamIndex::class)
            ->assertSet('activeTab', 'personas')
            ->assertSee('Persona')
            ->assertDontSee('Worker');
    }

    /** @test */
    public function team_index_updates_url_on_tab_change(): void
    {
        Livewire::test(TeamIndex::class)
            ->set('activeTab', 'personas')
            ->assertSet('activeTab', 'personas');
    }

    /** @test */
    public function team_index_refreshes_data(): void
    {
        TeamMember::create(['name' => 'Initial', 'email' => 'initial@test.com']);

        $component = Livewire::test(TeamIndex::class)
            ->assertSee('Initial');

        TeamMember::create(['name' => 'Added', 'email' => 'added@test.com']);

        $component->call('refresh')
            ->assertSee('Added');
    }

    /** @test */
    public function team_index_handles_deletion(): void
    {
        $member = TeamMember::create(['name' => 'Delete Me', 'email' => 'delete@test.com']);

        Livewire::test(TeamIndex::class)
            ->assertSee('Delete Me')
            ->call('deleteMember', $member->id)
            ->assertDontSee('Delete Me');

        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }
}
