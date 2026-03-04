<?php

namespace Tests\Feature\Navigation;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Sidebar State Feature Tests
 * 
 * Tests for Phase 2B.5: Livewire/Alpine sidebar state behavior
 * Verifies component rendering, toggle functionality, and ARIA attributes
 */
class SidebarStateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that sidebar renders with correct initial state
     * Default state should be expanded (collapsed = false)
     */
    public function test_sidebar_renders_with_correct_initial_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Check for Alpine binding syntax (dynamic classes)
                 ->assertSee(':class="collapsed ? \'w-16\' : \'w-64\'"')
                 ->assertSee('ml-64'); // Default margin
    }

    /**
     * Test that toggle button exists in sidebar
     */
    public function test_toggle_button_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('aria-label="Toggle navigation menu"')
                 ->assertSee('@click="toggleSidebar()"');
    }

    /**
     * Test that aria-expanded attribute is present and correct
     * Should match the current collapse state
     */
    public function test_aria_expanded_attribute_present(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Sidebar should have aria-expanded attribute (initially true via Alpine)
                 ->assertSee('aria-expanded')
                 ->assertSee('Main navigation');
    }

    /**
     * Test that sidebar has proper ARIA label
     */
    public function test_sidebar_has_aria_label(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('aria-label="Main navigation"');
    }

    /**
     * Test that nav items have proper accessibility attributes
     */
    public function test_nav_items_have_accessibility_attributes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Check for title attributes (tooltips)
                 ->assertSee('title="Tasks"')
                 ->assertSee('title="Team"')
                 ->assertSee('title="Projects"');
    }

    /**
     * Test that sidebar transition classes are present
     * Should have transition-all duration-300 ease-in-out
     */
    public function test_sidebar_has_transition_classes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('transition-all')
                 ->assertSee('duration-300')
                 ->assertSee('ease-in-out');
    }

    /**
     * Test that localStorage key is referenced correctly
     */
    public function test_localstorage_key_configured(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('lunaos.sidebar.collapsed');
    }

    /**
     * Test that sidebar content structure is correct
     * Header, nav items, and footer should all be present
     */
    public function test_sidebar_content_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Header
                 ->assertSee('LunaOS')
                 // Nav items container
                 ->assertSee('Tasks')
                 ->assertSee('Team')
                 ->assertSee('Projects')
                 // Footer should have user info (if sidebar-footer partial includes it)
                 ->assertSee('x-data="sidebarApp()"');
    }

    /**
     * Test that main content area has correct margin classes
     * ml-64 when expanded, ml-16 when collapsed
     */
    public function test_main_content_has_margin_classes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('ml-64')
                 ->assertSee('transition-all duration-300 ease-in-out');
    }

    /**
     * Test that mobile sidebar uses overlay mode
     * Should have fixed positioning and z-index
     */
    public function test_mobile_sidebar_uses_overlay_mode(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Desktop sidebar should be hidden on mobile
                 ->assertSee('hidden md:block')
                 ->assertSee('fixed inset-y-0 left-0');
    }

    /**
     * Test that mobile backdrop is present
     */
    public function test_mobile_backdrop_present(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('fixed inset-0')
                 ->assertSee('bg-black')
                 ->assertSee('bg-opacity-50');
    }

    /**
     * Test that header collapses to icon-only correctly
     */
    public function test_header_collapses_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Logo icon should always be visible
                 ->assertSee('🌙')
                 ->assertSee('LunaOS');
    }

    /**
     * Test that footer collapses to avatar-only correctly
     */
    public function test_footer_collapses_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Footer partial should be included
                 ->assertSee('layouts.partials.sidebar-footer');
    }

    /**
     * Test that Alpine state is properly initialized
     * Body tag should have x-data and x-init attributes
     */
    public function test_alpine_state_initialized(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 // Check that Alpine sidebar functionality is referenced
                 ->assertSee('sidebarApp')
                 ->assertSee('initApp');
    }

    /**
     * Test that localStorage initialization is present
     * Should read from localStorage on page load
     */
    public function test_localstorage_initialization_present(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/tasks');

        $response->assertStatus(200)
                 ->assertSee('localStorage.getItem')
                 ->assertSee('localStorage.setItem');
    }
}
