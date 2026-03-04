<?php

namespace Tests\Browser\Navigation;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * Responsive Sidebar Tests
 * 
 * Tests for Phase 2B.5: Responsive behavior across different viewport sizes
 * Verifies desktop, tablet, and mobile modes
 */
class ResponsiveSidebarTest extends DuskTestCase
{
    /**
     * Test desktop view (≥1024px) - sidebar uses push mode
     */
    public function test_desktop_view_push_mode(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(1920, 1080) // Large desktop
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Verify sidebar is visible and uses push mode
                    ->assertSee('LunaOS')
                    // Verify sidebar has fixed position class
                    ->assertPresent('aside.fixed')
                    // Verify main content has margin (push mode)
                    ->script('return document.querySelector("main").classList.contains("ml-64")', function ($hasMargin) {
                        $this->assertTrue($hasMargin);
                    })
                    // Verify no backdrop visible (push mode, not overlay)
                    ->assertMissing('div.fixed.inset-0.bg-black');
        });
    }

    /**
     * Test tablet view (768px-1023px) - sidebar uses push mode
     */
    public function test_tablet_view_push_mode(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(1024, 768) // Tablet breakpoint
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Verify sidebar visible with push mode
                    ->assertSee('LunaOS')
                    ->script('return document.querySelector("main").classList.contains("ml-64")', function ($hasMargin) {
                        $this->assertTrue($hasMargin);
                    })
                    ->assertMissing('div.fixed.inset-0.bg-black');
        });
    }

    /**
     * Test mobile view (<768px) - sidebar uses overlay mode
     */
    public function test_mobile_view_overlay_mode(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(767, 1024) // Just below mobile breakpoint
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('button[aria-label="Open navigation menu"]', 5)
                    // Verify sidebar is hidden initially (desktop aside is hidden on mobile)
                    ->assertMissing('LunaOS') // Desktop sidebar hidden
                    // Verify main content has no margin (overlay mode)
                    ->script('return document.querySelector("main").classList.contains("ml-0")', function ($noMargin) {
                        $this->assertTrue($noMargin);
                    })
                    // Open overlay
                    ->click('[aria-label="Open navigation menu"]')
                    ->pause(400)
                    // Verify overlay behavior
                    ->assertSee('LunaOS') // Mobile overlay visible
                    ->assertPresent('div.fixed.inset-0.bg-black') // Backdrop visible
                    // Main content should still have no margin (overlay, not push)
                    ->script('return document.querySelector("main").classList.contains("ml-0")', function ($noMargin) {
                        $this->assertTrue($noMargin);
                    });
        });
    }

    /**
     * Test transition smoothness - no layout jank
     */
    public function test_transition_smooth_no_jank(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(1920, 1080)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Record initial position
                    ->script('return document.querySelector("aside[aria-label=\'Main navigation\']").offsetLeft', function ($initialLeft) {
                        $this->assertEquals(0, $initialLeft);
                    })
                    // Toggle multiple times rapidly
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(100)
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(100)
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(100)
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // Verify final position matches expected (should be at left edge)
                    ->script('return document.querySelector("aside[aria-label=\'Main navigation\']").offsetLeft', function ($finalLeft) {
                        $this->assertEquals(0, $finalLeft);
                    });
        });
    }

    /**
     * Test that header collapses correctly to icon-only mode
     */
    public function test_header_collapses_icon_only(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(1920, 1080)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Verify header shows full logo + text in expanded state
                    ->assertSee('LunaOS')
                    ->assertPresent('div.w-10.h-10.rounded-full') // Logo icon
                    // Collapse
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // Verify text hidden, icon visible
                    ->assertMissing('LunaOS') // Text hidden
                    ->assertPresent('div.w-10.h-10.rounded-full'); // Icon still visible
        });
    }

    /**
     * Test that footer collapses correctly to avatar-only mode
     */
    public function test_footer_collapses_avatar_only(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(1920, 1080)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    // Collapse
                    ->click('[aria-label="Toggle navigation menu"]')
                    ->pause(400)
                    // In collapsed state, footer text elements should be hidden
                    // We verify by checking w-16 class is present
                    ->assertHasClass('aside[aria-label="Main navigation"]', 'w-16')
                    // Verify sidebar collapsed to icon-only mode
                    ->assertMissing('LunaOS');
        });
    }

    /**
     * Test responsive breakpoint transitions
     */
    public function test_responsive_breakpoint_transitions(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            // Start at desktop
            $browser->resize(1024, 768)
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitFor('aside[aria-label="Main navigation"]', 5)
                    ->script('return document.querySelector("main").classList.contains("ml-64")', function ($hasMargin) {
                        $this->assertTrue($hasMargin);
                    })
                    // Resize to just below tablet breakpoint
                    ->resize(767, 1024)
                    ->pause(400)
                    // Verify switch to overlay mode (no margin initially)
                    ->script('return document.querySelector("main").classList.contains("ml-0")', function ($noMargin) {
                        $this->assertTrue($noMargin);
                    })
                    // Resize back to desktop
                    ->resize(1024, 768)
                    ->pause(400)
                    // Verify return to push mode with margin
                    ->script('return document.querySelector("main").classList.contains("ml-64")', function ($hasMargin) {
                        $this->assertTrue($hasMargin);
                    });
        });
    }
}
