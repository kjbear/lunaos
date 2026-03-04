<?php

namespace Tests\Browser\Team;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TeamMember;

class TeamIndexLoadTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that team index page loads successfully.
     */
    public function test_team_index_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Team')
                    ->assertSee('Team')
                    ->assertVisible('body');
        });
    }

    /**
     * Test that team index loads with data.
     */
    public function test_team_index_loads_with_data(): void
    {
        $user = User::factory()->create();

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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('John Doe', 10)
                    ->assertSee('John Doe')
                    ->assertSee('Jane Smith')
                    ->assertSee('Developer')
                    ->assertSee('Designer');
        });
    }

    /**
     * Test that team index shows empty state when no members.
     */
    public function test_team_index_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('No team members', 10)
                    ->assertSee('No team members');
        });
    }

    /**
     * Test that tab navigation is visible.
     */
    public function test_tab_navigation_is_visible(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Workers', 10)
                    ->assertSee('Workers')
                    ->assertSee('Personas')
                    ->assertSee('Board Members');
        });
    }

    /**
     * Test that create button is visible.
     */
    public function test_create_button_is_visible(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Add Member', 10)
                    ->assertSee('Add Member');
        });
    }

    /**
     * Test that no JavaScript errors in console.
     */
    public function test_no_javascript_errors(): void
    {
        $user = User::factory()->create();

        TeamMember::factory()->count(5)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Team', 10)
                    ->assertScriptErrorsCount(0);
        });
    }

    /**
     * Test that page title is correct.
     */
    public function test_page_title_is_correct(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Team', 10)
                    ->assertTitleContains('Team');
        });
    }

    /**
     * Test that member count badge displays.
     */
    public function test_member_count_badge_displays(): void
    {
        $user = User::factory()->create();

        TeamMember::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('3', 10)
                    ->assertSee('3');
        });
    }
}
