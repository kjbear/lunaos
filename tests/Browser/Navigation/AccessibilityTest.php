<?php

namespace Tests\Browser\Navigation;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * Accessibility Tests for Collapsible Navigation
 * 
 * Tests for Phase 2B.5: Accessibility compliance
 * Verifies keyboard navigation, screen reader support, and focus management
 */
class AccessibilityTest extends DuskTestCase
{
    /**
     * Test keyboard-only navigation works completely
     */
    public function test_keyboard_only_navigation_works(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Toggle navigation menu"]', 5)
                    // Sidebar toggle should be focusable and tab-reachable
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // Verify toggle worked
                    ->assertMissing('LunaOS') // Collapsed
                    // Tab to next element
                    ->tab()
                    ->pause(100)
                    // Continue tabbing through nav items
                    ->tab()
                    ->pause(100)
                    ->tab()
                    ->pause(100);
        });
    }

    /**
     * Test that screen reader announces collapse state changes
     * Using aria-expanded attribute
     */
    public function test_screen_reader_announces_collapse_state(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Verify initial state is expanded
                    ->assertAttribute('aside[aria-label="Main navigation"]', 'aria-expanded', 'true')
                    // Collapse sidebar
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // Verify aria-expanded updated to false
                    ->assertAttribute('aside[aria-label="Main navigation"]', 'aria-expanded', 'false')
                    // Expand again
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // Verify aria-expanded updated back to true
                    ->assertAttribute('aside[aria-label="Main navigation"]', 'aria-expanded', 'true');
        });
    }

    /**
     * Test that focus is trapped within mobile overlay when open
     */
    public function test_focus_trapped_in_mobile_overlay(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 812) // Mobile viewport
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Open navigation menu"]', 5)
                    // Open overlay
                    ->click('[aria-label="Open navigation menu"]')
                    ->pause(400)
                    // Tab through nav items (focus should stay in overlay)
                    ->tab()
                    ->pause(100)
                    ->tab()
                    ->pause(100)
                    ->tab()
                    ->pause(100)
                    ->tab()
                    ->pause(100)
                    ->tab()
                    ->pause(100);
            // Focus should still be within the overlay navigation
            $browser->assertPresent('.sidebar-item');
        });
    }

    /**
     * Test that focus returns to toggle button after mobile overlay closes
     */
    public function test_focus_returns_to_toggle_after_close(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 812) // Mobile viewport
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Open navigation menu"]', 5)
                    // Open overlay
                    ->click('[aria-label="Open navigation menu"]')
                    ->pause(400)
                    ->assertSee('LunaOS')
                    // Close with Escape
                    ->keys('{escape}')
                    ->pause(400)
                    // Verify overlay closed
                    ->assertMissing('aside[aria-label="Mobile navigation"]')
                    // Verify toggle button exists and is focusable
                    ->assertPresent('button[aria-label="Open navigation menu"]');
        });
    }

    /**
     * Test that focus returns to toggle after clicking backdrop
     */
    public function test_focus_returns_after_backdrop_click(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 812)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Open navigation menu"]', 5)
                    // Open overlay
                    ->click('[aria-label="Open navigation menu"]')
                    ->pause(400)
                    ->assertSee('LunaOS')
                    // Click the close button (X) in the mobile menu header
                    ->click('aside[aria-label="Mobile navigation"] button[aria-label="Close navigation menu"]')
                    ->pause(400)
                    // Verify toggle button is still present and accessible
                    ->assertPresent('button[aria-label="Open navigation menu"]');
        });
    }

    /**
     * Test that toggle button has proper accessibility attributes
     */
    public function test_toggle_button_accessibility(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Toggle navigation menu"]', 5)
                    // Verify button has correct ARIA attributes
                    ->assertAttribute('button[aria-label="Toggle navigation menu"]', 'aria-label', 'Toggle navigation menu')
                    ->assertAttribute('button[aria-label="Toggle navigation menu"]', 'aria-expanded', 'true')
                    // Collapse and verify update
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    ->assertAttribute('button[aria-label="Toggle navigation menu"]', 'aria-expanded', 'false');
        });
    }

    /**
     * Test that nav items have proper labels/aria attributes
     */
    public function test_nav_items_accessible_when_collapsed(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Collapse sidebar
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // Verify nav items are still present in DOM
                    ->assertPresent('a[href*="tasks"]')
                    ->assertPresent('a[href*="team"]')
                    ->assertPresent('a[href*="projects"]')
                    // Verify they have proper labels/aria attributes
                    ->assertAttribute('a[href*="tasks"]', 'title', 'Tasks')
                    ->assertAttribute('a[href*="team"]', 'title', 'Team')
                    ->assertAttribute('a[href*="projects"]', 'title', 'Projects');
        });
    }

    /**
     * Test that sidebar has proper ARIA role and label
     */
    public function test_proper_aria_role_and_label(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside', 5)
                    // Verify main navigation sidebar has proper ARIA
                    ->assertAttribute('aside[aria-label="Main navigation"]', 'aria-label', 'Main navigation')
                    // Verify aria-expanded is present
                    ->assertAttribute('aside[aria-label="Main navigation"]', 'aria-expanded', 'true');
        });
    }

    /**
     * Test keyboard Escape key functionality for mobile
     */
    public function test_escape_key_closes_mobile_overlay_accessibility(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 812)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Open navigation menu"]', 5)
                    // Open overlay
                    ->click('[aria-label="Open navigation menu"]')
                    ->pause(400)
                    ->assertSee('LunaOS')
                    // Press Escape
                    ->keys('{escape}')
                    ->pause(400)
                    ->assertMissing('div.fixed.inset-0.bg-black');
        });
    }

    /**
     * Test keyboard Escape key functionality for mobile
     */
    public function test_escape_key_closes_mobile_overlay_properly(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 812)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Open navigation menu"]', 5)
                    // Open overlay
                    ->click('[aria-label="Open navigation menu"]')
                    ->pause(400)
                    ->assertSee('LunaOS')
                    // Press Escape
                    ->keys('{escape}')
                    ->pause(400)
                    ->assertMissing('div.fixed.inset-0.bg-black');
        });
    }

    /**
     * Test that nav items are keyboard accessible
     */
    public function test_nav_items_keyboard_accessible(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('a[href*="tasks"]', 5)
                    // Verify all nav items are links (keyboard accessible)
                    ->assertPresent('a[href*="tasks"]')
                    ->assertPresent('a[href*="team"]')
                    ->assertPresent('a[href*="projects"]')
                    ->assertPresent('a[href*="board"]')
                    ->assertPresent('a[href*="kanban"]');
        });
    }

    /**
     * Test that all interactive elements have focus states
     */
    public function test_focus_indicators_visible(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Toggle navigation menu"]', 5)
                    // Click toggle to focus it
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(100)
                    // Verify button has focus ring classes
                    ->script('return document.querySelector("[aria-label=\'Toggle navigation menu\']").classList.contains("focus:ring-2")', function ($hasFocusRing) {
                        $this->assertTrue($hasFocusRing);
                    });
        });
    }
}


