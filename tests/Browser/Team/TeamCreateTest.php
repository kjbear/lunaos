<?php

namespace Tests\Browser\Team;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TeamMember;

class TeamCreateTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test navigate to create page.
     */
    public function test_navigate_to_create_page(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team')
                    ->waitForText('Add Member', 10)
                    ->clickLink('Add Member')
                    ->waitForText('Create Team Member', 10)
                    ->assertPathIs('/team/create')
                    ->assertSee('Create Team Member');
        });
    }

    /**
     * Test create a worker member.
     */
    public function test_create_worker_member(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->type('input[wire\\:model="name"]', 'New Worker')
                    ->type('input[wire\\:model="email"]', 'worker@test.com')
                    ->select('select[wire\\:model="role"]', 'worker')
                    ->type('input[wire\\:model="title"]', 'Software Engineer')
                    ->click('button[type="submit"]')
                    ->waitForText('New Worker', 10)
                    ->assertPathIsNot('/team/create')
                    ->assertSee('New Worker');
        });

        $this->assertDatabaseHas('team_members', [
            'name' => 'New Worker',
            'email' => 'worker@test.com',
            'role' => 'worker',
        ]);
    }

    /**
     * Test create a persona member.
     */
    public function test_create_persona_member(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->type('input[wire\\:model="name"]', 'Socrates')
                    ->type('input[wire\\:model="email"]', 'socrates@test.com')
                    ->select('select[wire\\:model="role"]', 'persona')
                    ->type('input[wire\\:model="title"]', 'Philosopher')
                    ->click('button[type="submit"]')
                    ->waitForText('Socrates', 10)
                    ->assertSee('Socrates')
                    ->assertSee('Philosopher');
        });

        $this->assertDatabaseHas('team_members', [
            'name' => 'Socrates',
            'email' => 'socrates@test.com',
            'role' => 'persona',
        ]);
    }

    /**
     * Test create a board member.
     */
    public function test_create_board_member(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->type('input[wire\\:model="name"]', 'Board Director')
                    ->type('input[wire\\:model="email"]', 'director@test.com')
                    ->select('select[wire\\:model="role"]', 'board_member')
                    ->type('input[wire\\:model="title"]', 'Board Member')
                    ->click('button[type="submit"]')
                    ->waitForText('Board Director', 10)
                    ->assertSee('Board Director');
        });

        $this->assertDatabaseHas('team_members', [
            'name' => 'Board Director',
            'email' => 'director@test.com',
            'role' => 'board_member',
        ]);
    }

    /**
     * Test validation errors display for invalid input.
     */
    public function test_validation_errors_display_for_invalid_input(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    // Don't fill required fields
                    ->click('button[type="submit"]')
                    ->waitForText('error', 5)
                    ->assertPresent('@error');
        });
    }

    /**
     * Test validation error for duplicate email.
     */
    public function test_validation_error_for_duplicate_email(): void
    {
        $user = User::factory()->create();

        TeamMember::create([
            'name' => 'Existing',
            'email' => 'existing@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->type('input[wire\\:model="name"]', 'Duplicate')
                    ->type('input[wire\\:model="email"]', 'existing@test.com')
                    ->click('button[type="submit"]')
                    ->waitForText('error', 5)
                    ->assertSee('error');
        });
    }

    /**
     * Test cancel button returns to index.
     */
    public function test_cancel_button_returns_to_index(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->clickLink('Cancel')
                    ->waitForLocationChange(5)
                    ->assertPathIs('/team')
                    ->assertSee('Team');
        });
    }

    /**
     * Test flash message appears on success.
     */
    public function test_flash_message_appears_on_success(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->type('input[wire\\:model="name"]', 'Success Test')
                    ->type('input[wire\\:model="email"]', 'success@test.com')
                    ->select('select[wire\\:model="role"]', 'worker')
                    ->click('button[type="submit"]')
                    ->waitForText('successfully', 10)
                    ->assertSee('successfully');
        });
    }

    /**
     * Test form has all required fields.
     */
    public function test_form_has_all_required_fields(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->assertPresent('input[wire\\:model="name"]')
                    ->assertPresent('input[wire\\:model="email"]')
                    ->assertPresent('select[wire\\:model="role"]')
                    ->assertPresent('input[wire\\:model="title"]');
        });
    }

    /**
     * Test form has optional fields.
     */
    public function test_form_has_optional_fields(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->assertPresent('input[wire\\:model="model"]')
                    ->assertPresent('textarea[wire\\:model="system_prompt"]');
        });
    }

    /**
     * Test create with all fields filled.
     */
    public function test_create_with_all_fields_filled(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->type('input[wire\\:model="name"]', 'Complete Member')
                    ->type('input[wire\\:model="email"]', 'complete@test.com')
                    ->select('select[wire\\:model="role"]', 'worker')
                    ->type('input[wire\\:model="title"]', 'Full Stack Developer')
                    ->type('input[wire\\:model="model"]', 'ollama-local/qwen3.5:cloud')
                    ->type('textarea[wire\\:model="system_prompt"]', 'You are a helpful assistant')
                    ->click('button[type="submit"]')
                    ->waitForText('Complete Member', 10)
                    ->assertSee('Complete Member')
                    ->assertSee('Full Stack Developer');
        });

        $this->assertDatabaseHas('team_members', [
            'name' => 'Complete Member',
            'email' => 'complete@test.com',
            'title' => 'Full Stack Developer',
        ]);
    }

    /**
     * Test responsive design on mobile viewport.
     */
    public function test_responsive_design_on_mobile_viewport(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 667) // iPhone SE size
                    ->loginAs($user)
                    ->visit('/team/create')
                    ->waitForText('Create Team Member', 10)
                    ->assertVisible('body');
        });
    }
}
