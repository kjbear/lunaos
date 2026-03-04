<?php

namespace Tests\Browser\Team;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TeamMember;

class TeamEditTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test navigate to edit page from detail.
     */
    public function test_navigate_to_edit_page_from_detail(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Edit Test',
            'email' => 'edit@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}")
                    ->waitForText('Edit Test', 10)
                    ->clickLink('Edit')
                    ->waitForText('Edit Team Member', 10)
                    ->assertPathIs("/team/{$member->id}/edit")
                    ->assertSee('Edit Team Member');
        });
    }

    /**
     * Test edit member name and update persists.
     */
    public function test_update_member_name_persists(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Original Name',
            'email' => 'original@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="name"]')
                    ->type('input[wire\\:model="name"]', 'Updated Name')
                    ->click('button[type="submit"]')
                    ->waitForText('Updated Name', 10)
                    ->assertPathIsNot("/team/{$member->id}/edit");
        });

        $member->refresh();
        $this->assertEquals('Updated Name', $member->name);
    }

    /**
     * Test edit member email and update persists.
     */
    public function test_update_member_email_persists(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Email Test',
            'email' => 'old@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="email"]')
                    ->type('input[wire\\:model="email"]', 'new@test.com')
                    ->click('button[type="submit"]')
                    ->waitForText('Email Test', 10);
        });

        $member->refresh();
        $this->assertEquals('new@test.com', $member->email);
    }

    /**
     * Test change member role and update persists.
     */
    public function test_change_member_role_persists(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Role Change',
            'email' => 'role@test.com',
            'role' => 'worker',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->select('select[wire\\:model="role"]', 'persona')
                    ->click('button[type="submit"]')
                    ->waitForText('Role Change', 10);
        });

        $member->refresh();
        $this->assertEquals('persona', $member->role);
    }

    /**
     * Test change member status and update persists.
     */
    public function test_change_member_status_persists(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Status Change',
            'email' => 'status@test.com',
            'status' => 'active',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->select('select[wire\\:model="status"]', 'inactive')
                    ->click('button[type="submit"]')
                    ->waitForText('Status Change', 10);
        });

        $member->refresh();
        $this->assertEquals('inactive', $member->status);
    }

    /**
     * Test validation errors display on invalid input.
     */
    public function test_validation_errors_display_on_invalid_input(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Validation Test',
            'email' => 'validation@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="email"]')
                    ->click('button[type="submit"]')
                    ->waitForText('error', 5)
                    ->assertPresent('@error');
        });
    }

    /**
     * Test validation error for duplicate email on edit.
     */
    public function test_validation_error_for_duplicate_email_on_edit(): void
    {
        $user = User::factory()->create();

        TeamMember::create([
            'name' => 'Existing',
            'email' => 'existing@test.com',
        ]);

        $member = TeamMember::create([
            'name' => 'Editing',
            'email' => 'editing@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="email"]')
                    ->type('input[wire\\:model="email"]', 'existing@test.com')
                    ->click('button[type="submit"]')
                    ->waitForText('error', 5)
                    ->assertSee('error');
        });
    }

    /**
     * Test cancel button returns to detail without changes.
     */
    public function test_cancel_returns_to_detail_without_changes(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Cancel Test',
            'email' => 'cancel@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="name"]')
                    ->type('input[wire\\:model="name"]', 'Changed Name')
                    ->clickLink('Cancel')
                    ->waitForLocationChange(5)
                    ->assertPathIs("/team/{$member->id}")
                    ->assertSee('Cancel Test');
        });

        $member->refresh();
        $this->assertEquals('Cancel Test', $member->name);
    }

    /**
     * Test form pre-populates with current data.
     */
    public function test_form_prepopulates_with_current_data(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Prepopulate Test',
            'email' => 'prepopulate@test.com',
            'title' => 'Senior Developer',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->assertInputValue('input[wire\\:model="name"]', 'Prepopulate Test')
                    ->assertInputValue('input[wire\\:model="email"]', 'prepopulate@test.com')
                    ->assertInputValue('input[wire\\:model="title"]', 'Senior Developer');
        });
    }

    /**
     * Test flash message appears on successful update.
     */
    public function test_flash_message_appears_on_update(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Flash Test',
            'email' => 'flash@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="name"]')
                    ->type('input[wire\\:model="name"]', 'Updated Flash')
                    ->click('button[type="submit"]')
                    ->waitForText('successfully', 10)
                    ->assertSee('successfully');
        });
    }

    /**
     * Test update multiple fields at once.
     */
    public function test_update_multiple_fields_at_once(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Multi Update',
            'email' => 'multi@test.com',
            'role' => 'worker',
            'title' => 'Junior Developer',
            'status' => 'active',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->clear('input[wire\\:model="name"]')
                    ->type('input[wire\\:model="name"]', 'Senior Developer')
                    ->clear('input[wire\\:model="title"]')
                    ->type('input[wire\\:model="title"]', 'Tech Lead')
                    ->select('select[wire\\:model="role"]', 'board_member')
                    ->select('select[wire\\:model="status"]', 'inactive')
                    ->click('button[type="submit"]')
                    ->waitForText('Senior Developer', 10);
        });

        $member->refresh();
        $this->assertEquals('Senior Developer', $member->name);
        $this->assertEquals('Tech Lead', $member->title);
        $this->assertEquals('board_member', $member->role);
        $this->assertEquals('inactive', $member->status);
    }

    /**
     * Test responsive design on mobile viewport.
     */
    public function test_responsive_design_on_mobile_viewport(): void
    {
        $user = User::factory()->create();

        $member = TeamMember::create([
            'name' => 'Mobile Edit',
            'email' => 'mobile@test.com',
        ]);

        $this->browse(function (Browser $browser) use ($user, $member) {
            $browser->resize(375, 667) // iPhone SE size
                    ->loginAs($user)
                    ->visit("/team/{$member->id}/edit")
                    ->waitForText('Edit Team Member', 10)
                    ->assertVisible('body');
        });
    }

    /**
     * Test edit non-existent member returns 404.
     */
    public function test_edit_non_existent_member_returns_404(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/team/999/edit')
                    ->waitForText('404', 10)
                    ->assertSee('404');
        });
    }
}
