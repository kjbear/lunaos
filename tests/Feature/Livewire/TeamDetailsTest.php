<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Livewire\Team\TeamDetails;
use App\Models\TeamMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class TeamDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function team_details_loads_for_valid_member(): void
    {
        $member = TeamMember::create([
            'name' => 'Test Member',
            'email' => 'test@test.com',
            'role' => 'worker',
            'title' => 'Developer',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('Test Member')
            ->assertSee('Developer');
    }

    /** @test */
    public function team_details_shows_all_member_information(): void
    {
        $member = TeamMember::create([
            'name' => 'Complete Member',
            'email' => 'complete@test.com',
            'role' => 'worker',
            'title' => 'Senior Developer',
            'status' => 'active',
            'model' => 'ollama-local/qwen3.5:cloud',
            'avatar' => '🤖',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('Complete Member')
            ->assertSee('complete@test.com')
            ->assertSee('Senior Developer')
            ->assertSee('active')
            ->assertSee('🤖');
    }

    /** @test */
    public function team_details_shows_correct_badge_for_worker(): void
    {
        $worker = TeamMember::create([
            'name' => 'Worker',
            'email' => 'worker@test.com',
            'role' => 'worker',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $worker->id])
            ->assertStatus(200)
            ->assertSee('Worker');
    }

    /** @test */
    public function team_details_shows_correct_badge_for_persona(): void
    {
        $persona = TeamMember::create([
            'name' => 'Persona',
            'email' => 'persona@test.com',
            'role' => 'persona',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $persona->id])
            ->assertStatus(200)
            ->assertSee('Persona');
    }

    /** @test */
    public function team_details_shows_correct_badge_for_board_member(): void
    {
        $boardMember = TeamMember::create([
            'name' => 'Board Member',
            'email' => 'board@test.com',
            'role' => 'board_member',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $boardMember->id])
            ->assertStatus(200)
            ->assertSee('Board Member');
    }

    /** @test */
    public function team_details_shows_assigned_tasks(): void
    {
        $member = TeamMember::create([
            'name' => 'Task Assignee',
            'email' => 'assignee@test.com',
        ]);

        Task::create(['title' => 'Task 1', 'assigned_to' => $member->name]);
        Task::create(['title' => 'Task 2', 'assigned_to' => $member->name]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('Task 1')
            ->assertSee('Task 2');
    }

    /** @test */
    public function team_details_shows_no_tasks_when_none_assigned(): void
    {
        $member = TeamMember::create([
            'name' => 'No Tasks',
            'email' => 'notasks@test.com',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('No tasks assigned');
    }

    /** @test */
    public function team_details_shows_parent_relationship(): void
    {
        $parent = TeamMember::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
        ]);

        $child = TeamMember::create([
            'name' => 'Subordinate',
            'email' => 'sub@test.com',
            'parent_id' => $parent->id,
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $child->id])
            ->assertStatus(200)
            ->assertSee('Manager');
    }

    /** @test */
    public function team_details_shows_children_relationship(): void
    {
        $parent = TeamMember::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
        ]);

        TeamMember::create([
            'name' => 'Team Member 1',
            'email' => 'tm1@test.com',
            'parent_id' => $parent->id,
        ]);

        TeamMember::create([
            'name' => 'Team Member 2',
            'email' => 'tm2@test.com',
            'parent_id' => $parent->id,
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $parent->id])
            ->assertStatus(200)
            ->assertSee('Team Member 1')
            ->assertSee('Team Member 2');
    }

    /** @test */
    public function team_details_has_edit_button(): void
    {
        $member = TeamMember::create([
            'name' => 'Edit Test',
            'email' => 'edit@test.com',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('Edit');
    }

    /** @test */
    public function team_details_has_delete_button(): void
    {
        $member = TeamMember::create([
            'name' => 'Delete Test',
            'email' => 'delete@test.com',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('Delete');
    }

    /** @test */
    public function team_details_can_toggle_status(): void
    {
        $member = TeamMember::create([
            'name' => 'Status Toggle',
            'email' => 'toggle@test.com',
            'status' => 'active',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('active')
            ->call('toggleStatus')
            ->assertSee('inactive');

        $this->assertEquals('inactive', $member->fresh()->status);
    }

    /** @test */
    public function team_details_returns_404_for_invalid_id(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        Livewire::test(TeamDetails::class, ['memberId' => 999]);
    }

    /** @test */
    public function team_details_shows_member_since(): void
    {
        $member = TeamMember::create([
            'name' => 'Tenure Test',
            'email' => 'tenure@test.com',
            'created_at' => now()->subMonths(3),
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('3 months');
    }

    /** @test */
    public function team_details_shows_model_info(): void
    {
        $member = TeamMember::create([
            'name' => 'Model Test',
            'email' => 'model@test.com',
            'model' => 'ollama-local/qwen3.5:cloud',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('ollama-local/qwen3.5:cloud');
    }

    /** @test */
    public function team_details_handles_missing_relationships_gracefully(): void
    {
        $member = TeamMember::create([
            'name' => 'Solo Member',
            'email' => 'solo@test.com',
            'parent_id' => null,
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertDontSee('null');
    }

    /** @test */
    public function team_details_shows_metadata(): void
    {
        $member = TeamMember::create([
            'name' => 'Metadata Test',
            'email' => 'meta@test.com',
            'metadata_json' => ['source' => 'migration', 'notes' => 'Important member'],
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('migration');
    }

    /** @test */
    public function team_details_confirms_before_delete(): void
    {
        $member = TeamMember::create([
            'name' => 'Confirm Delete',
            'email' => 'confirm@test.com',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->call('confirmDelete')
            ->assertDispatched('confirm-delete');
    }

    /** @test */
    public function team_details_redirects_after_delete(): void
    {
        $member = TeamMember::create([
            'name' => 'Redirect Delete',
            'email' => 'redirect@test.com',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->call('delete')
            ->assertRedirect('/team');

        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }

    /** @test */
    public function team_details_shows_activity_history(): void
    {
        $member = TeamMember::create([
            'name' => 'Activity Test',
            'email' => 'activity@test.com',
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('Activity');
    }

    /** @test */
    public function team_details_shows_settings(): void
    {
        $member = TeamMember::create([
            'name' => 'Settings Test',
            'email' => 'settings@test.com',
            'settings' => ['theme' => 'dark', 'notifications' => true],
        ]);

        Livewire::test(TeamDetails::class, ['memberId' => $member->id])
            ->assertStatus(200)
            ->assertSee('dark');
    }
}
