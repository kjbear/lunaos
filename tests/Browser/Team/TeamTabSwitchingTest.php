<?php

namespace Tests\Browser\Team;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TeamMember;

class TeamTabSwitchingTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that clicking Workers tab shows only workers.
     */
    public function test_workers_tab_shows_only_workers(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers', 'role' => 'worker']);
        TeamMember::create(['name' => 'Worker 2', 'email' => 'w2@test.com', 'type' => 'workers', 'role' => 'worker']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas', 'role' => 'persona']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Workers', 10)
                    ->clickLink('Workers')
                    ->waitForText('Worker 1', 5)
                    ->assertSee('Worker 1')
                    ->assertSee('Worker 2')
                    ->assertDontSee('Persona 1');
        });
    }

    /**
     * Test that clicking Personas tab shows only personas.
     */
    public function test_personas_tab_shows_only_personas(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers', 'role' => 'worker']);
        TeamMember::create(['name' => 'Persona 1', 'email' => 'p1@test.com', 'type' => 'personas', 'role' => 'persona']);
        TeamMember::create(['name' => 'Persona 2', 'email' => 'p2@test.com', 'type' => 'personas', 'role' => 'persona']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Personas', 10)
                    ->clickLink('Personas')
                    ->waitForText('Persona 1', 5)
                    ->assertSee('Persona 1')
                    ->assertSee('Persona 2')
                    ->assertDontSee('Worker 1');
        });
    }

    /**
     * Test that clicking Board Members tab shows only board members.
     */
    public function test_board_members_tab_shows_only_board_members(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Worker 1', 'email' => 'w1@test.com', 'type' => 'workers', 'role' => 'worker']);
        TeamMember::create(['name' => 'Board 1', 'email' => 'b1@test.com', 'type' => 'board-members', 'role' => 'board_member']);
        TeamMember::create(['name' => 'Board 2', 'email' => 'b2@test.com', 'type' => 'board-members', 'role' => 'board_member']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Board Members', 10)
                    ->clickLink('Board Members')
                    ->waitForText('Board 1', 5)
                    ->assertSee('Board 1')
                    ->assertSee('Board 2')
                    ->assertDontSee('Worker 1');
        });
    }

    /**
     * Test that tab state persists without page reload.
     */
    public function test_tab_state_persists_without_page_reload(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Worker', 'email' => 'w@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'type' => 'personas']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Workers', 10)
                    
                    // Click Personas tab
                    ->clickLink('Personas')
                    ->waitForText('Persona', 5)
                    ->assertSee('Persona')
                    ->assertDontSee('Worker')
                    
                    // Interact with page (search) without losing tab state
                    ->type('input[wire\\:model="search"]', 'Persona')
                    ->waitForText('Persona', 5)
                    ->assertSee('Persona')
                    ->assertDontSee('Worker');
        });
    }

    /**
     * Test that URL updates with tab parameter.
     */
    public function test_url_updates_with_tab_parameter(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'type' => 'personas']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Personas', 10)
                    ->clickLink('Personas')
                    ->waitForLocationChange(5)
                    ->assertPathIs('/team?tab=personas')
                    ->assertSee('Persona');
        });
    }

    /**
     * Test that active tab has correct styling.
     */
    public function test_active_tab_has_correct_styling(): void
    {
        $user = User::factory()->create();

        TeamMember::factory()->count(3)->create(['type' => 'workers']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Workers', 10)
                    
                    // Workers tab should be active by default
                    ->assertClassPresent('a:contains("Workers")', 'active')
                    
                    ->clickLink('Personas')
                    ->waitForText('No personas', 5)
                    
                    // Personas tab should now be active
                    ->assertClassPresent('a:contains("Personas")', 'active');
        });
    }

    /**
     * Test switching between all tabs sequentially.
     */
    public function test_switching_between_all_tabs_sequentially(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Worker', 'email' => 'w@test.com', 'type' => 'workers']);
        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'type' => 'personas']);
        TeamMember::create(['name' => 'Board', 'email' => 'b@test.com', 'type' => 'board-members']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Workers', 10)
                    
                    // Start on Workers (default)
                    ->assertSee('Worker')
                    ->assertDontSee('Persona')
                    ->assertDontSee('Board')
                    
                    // Switch to Personas
                    ->clickLink('Personas')
                    ->waitForText('Persona', 5)
                    ->assertSee('Persona')
                    ->assertDontSee('Worker')
                    ->assertDontSee('Board')
                    
                    // Switch to Board Members
                    ->clickLink('Board Members')
                    ->waitForText('Board', 5)
                    ->assertSee('Board')
                    ->assertDontSee('Worker')
                    ->assertDontSee('Persona')
                    
                    // Back to Workers
                    ->clickLink('Workers')
                    ->waitForText('Worker', 5)
                    ->assertSee('Worker')
                    ->assertDontSee('Persona')
                    ->assertDontSee('Board');
        });
    }

    /**
     * Test that tab works on page refresh.
     */
    public function test_tab_persists_on_page_refresh(): void
    {
        $user = User::factory()->create();

        TeamMember::create(['name' => 'Persona', 'email' => 'p@test.com', 'type' => 'personas']);
        TeamMember::create(['name' => 'Worker', 'email' => 'w@test.com', 'type' => 'workers']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Personas', 10)
                    ->clickLink('Personas')
                    ->waitForText('Persona', 5)
                    ->assertPathIs('/team?tab=personas')
                    
                    // Refresh page
                    ->refresh()
                    ->waitForText('Persona', 10)
                    ->assertPathIs('/team?tab=personas')
                    ->assertSee('Persona')
                    ->assertDontSee('Worker');
        });
    }

    /**
     * Test that switching tabs is smooth (no flicker).
     */
    public function test_tab_switching_is_smooth(): void
    {
        $user = User::factory()->create();

        TeamMember::factory()->count(5)->create(['type' => 'workers']);
        TeamMember::factory()->count(5)->create(['type' => 'personas']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Workers', 10)
                    
                    // Rapid tab switching
                    ->clickLink('Personas')
                    ->waitForText('Personas', 5)
                    ->clickLink('Workers')
                    ->waitForText('Workers', 5)
                    ->clickLink('Personas')
                    ->waitForText('Personas', 5)
                    
                    // Final state should be stable
                    ->assertSee('Personas')
                    ->assertDontSee('Worker');
        });
    }
}
