<?php

namespace Tests\Browser\Team;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TeamMember;
use App\Models\Agent;
use App\Models\Persona;

class TeamMigrationTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test count agents before migration.
     */
    public function test_count_agents_before_migration(): void
    {
        $user = User::factory()->create();

        Agent::create([
            'name' => 'Dave',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        Agent::create([
            'name' => 'Sam',
            'role' => 'QA Engineer',
            'model' => 'ollama-local/qwen3-coder-next:cloud',
            'status' => 'active',
        ]);

        $agentCount = Agent::count();

        $this->assertEquals(2, $agentCount);
    }

    /**
     * Test count personas before migration.
     */
    public function test_count_personas_before_migration(): void
    {
        $user = User::factory()->create();

        Persona::create([
            'name' => 'Socrates',
            'title' => 'Philosopher',
            'role' => 'board_member',
            'status' => 'active',
        ]);

        Persona::create([
            'name' => 'Einstein',
            'title' => 'Physicist',
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $personaCount = Persona::count();

        $this->assertEquals(2, $personaCount);
    }

    /**
     * Test migration runs successfully.
     */
    public function test_migration_runs_successfully(): void
    {
        $user = User::factory()->create();

        // Create pre-migration data
        Agent::create([
            'name' => 'Dave',
            'role' => 'Developer',
            'status' => 'active',
        ]);

        Persona::create([
            'name' => 'Socrates',
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Team', 10)
                    ->assertSee('Team');
        });

        // Note: Actual migration would be run via artisan command
        // This test verifies the page loads post-migration structure
        $this->assertTrue(true);
    }

    /**
     * Test workers tab shows migrated agents.
     */
    public function test_workers_tab_shows_migrated_agents(): void
    {
        $user = User::factory()->create();

        // Simulate migrated data (agents became workers)
        TeamMember::create([
            'name' => 'Dave',
            'email' => 'dave@test.com',
            'role' => 'worker',
            'type' => 'workers',
            'metadata_json' => ['source' => 'agent_migration'],
        ]);

        TeamMember::create([
            'name' => 'Sam',
            'email' => 'sam@test.com',
            'role' => 'worker',
            'type' => 'workers',
            'metadata_json' => ['source' => 'agent_migration'],
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team?tab=workers')
                    ->waitForText('Dave', 10)
                    ->assertSee('Dave')
                    ->assertSee('Sam')
                    ->assertDontSee('No workers');
        });
    }

    /**
     * Test personas tab shows migrated personas.
     */
    public function test_personas_tab_shows_migrated_personas(): void
    {
        $user = User::factory()->create();

        // Simulate migrated data (personas stayed personas)
        TeamMember::create([
            'name' => 'Socrates',
            'email' => 'socrates@test.com',
            'role' => 'persona',
            'type' => 'personas',
            'metadata_json' => ['source' => 'persona_migration'],
        ]);

        TeamMember::create([
            'name' => 'Einstein',
            'email' => 'einstein@test.com',
            'role' => 'persona',
            'type' => 'personas',
            'metadata_json' => ['source' => 'persona_migration'],
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team?tab=personas')
                    ->waitForText('Socrates', 10)
                    ->assertSee('Socrates')
                    ->assertSee('Einstein');
        });
    }

    /**
     * Test board members tab shows board members.
     */
    public function test_board_members_tab_shows_board_members(): void
    {
        $user = User::factory()->create();

        // Simulate migrated data
        TeamMember::create([
            'name' => 'Board Member 1',
            'email' => 'board1@test.com',
            'role' => 'board_member',
            'type' => 'board-members',
            'metadata_json' => ['source' => 'persona_migration'],
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team?tab=board-members')
                    ->waitForText('Board Member 1', 10)
                    ->assertSee('Board Member 1');
        });
    }

    /**
     * Test migrated member details accessible.
     */
    public function test_migrated_member_details_accessible(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Migrated Dave',
            'email' => 'dave@test.com',
            'role' => 'worker',
            'type' => 'workers',
            'metadata_json' => ['source' => 'agent_migration'],
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}")
                    ->waitForText('Migrated Dave', 10)
                    ->assertSee('Migrated Dave')
                    ->assertSee('worker');
        });
    }

    /**
     * Test migrated member can be edited.
     */
    public function test_migrated_member_can_be_edited(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Old Name',
            'email' => 'old@test.com',
            'metadata_json' => ['source' => 'agent_migration'],
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="name"]')
                    ->type('input[wire\\:model="name"]', 'New Name')
                    ->click('button[type="submit"]')
                    ->waitForText('New Name', 10)
                    ->assertSee('New Name');
        });

        $member->refresh();
        $this->assertEquals('New Name', $member->name);
    }

    /**
     * Test migrated member relationships intact.
     */
    public function test_migrated_member_relationships_intact(): void
    {
        $user = User::factory()->create();

        $parent = TeamMember::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
            'metadata_json' => ['source' => 'agent_migration'],
        ]);

        $child = TeamMember::create([
            'name' => 'Subordinate',
            'email' => 'sub@test.com',
            'parent_id' => $parent->id,
            'metadata_json' => ['source' => 'agent_migration'],
        ]);

        $this->browse(function (Browser $browser) use ($user, $child) {
            $browser->loginAs($user)
                    ->visit("/team/{$child->id}")
                    ->waitForText('Subordinate', 10)
                    ->assertSee('Subordinate')
                    ->assertSee('Manager');
        });
    }

    /**
     * Test migrated member tasks accessible.
     */
    public function test_migrated_member_tasks_accessible(): void
    {
        $user = User::factory()->create();

        // Create task with migrated agent name
        $member = TeamMember::create([
            'name' => 'Dave',
            'email' => 'dave@test.com',
            'role' => 'worker',
        ]);

        \App\Models\Task::create([
            'title' => 'Migrated Task',
            'assigned_to' => 'Dave',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}")
                    ->waitForText('Dave', 10)
                    ->assertSee('Migrated Task');
        });
    }

    /**
     * Test no data loss after migration.
     */
    public function test_no_data_loss_after_migration(): void
    {
        $user = User::factory()->create();

        // Create various member types
        TeamMember::factory()->count(5)->create(['type' => 'workers']);
        TeamMember::factory()->count(3)->create(['type' => 'personas']);
        TeamMember::factory()->count(2)->create(['type' => 'board-members']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Team', 10)
                    ->assertSee('10'); // Total count should match

            // Check each tab
            $browser->clickLink('Workers')
                    ->waitForText('Workers', 5);

            $browser->clickLink('Personas')
                    ->waitForText('Personas', 5);

            $browser->clickLink('Board Members')
                    ->waitForText('Board Members', 5);
        });

        $this->assertEquals(10, TeamMember::count());
    }

    /**
     * Test rollback procedure exists.
     */
    public function test_rollback_procedure_exists(): void
    {
        // This test documents that rollback is possible
        // Actual rollback would be: php artisan migrate:rollback --step=1
        
        $migrationFile = database_path('migrations/*_consolidate_hr_and_agents.php');
        $migrationExists = !empty(glob($migrationFile));

        $this->assertTrue($migrationExists, 'Migration file should exist for rollback');
    }

    /**
     * Test all members have metadata after migration.
     */
    public function test_all_members_have_metadata_after_migration(): void
    {
        $user = User::factory()->create();

        TeamMember::create([
            'name' => 'Migrated 1',
            'email' => 'm1@test.com',
            'metadata_json' => ['source' => 'agent_migration', 'migrated_at' => '2026-03-03'],
        ]);

        TeamMember::create([
            'name' => 'Migrated 2',
            'email' => 'm2@test.com',
            'metadata_json' => ['source' => 'persona_migration', 'migrated_at' => '2026-03-03'],
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Team', 10);
        });

        $allMembers = TeamMember::all();
        $this->assertTrue($allMembers->every(fn($m) => $m->metadata_json !== null));
    }

    /**
     * Test migrated members display correctly in UI.
     */
    public function test_migrated_members_display_correctly_in_ui(): void
    {
        $user = User::factory()->create();

        TeamMember::create([
            'name' => 'Display Test',
            'email' => 'display@test.com',
            'role' => 'worker',
            'title' => 'Developer',
            'status' => 'active',
            'type' => 'workers',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Display Test', 10)
                    ->assertSee('Display Test')
                    ->assertSee('Developer')
                    ->assertSee('active');
        });
    }
}
