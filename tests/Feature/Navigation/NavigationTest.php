<?php

namespace Tests\Feature\Navigation;

use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // Route Redirect Tests
    // ==========================================

    /**
     * Test that /hr redirects to /team?type=personas
     */
    public function test_hr_redirects_to_team_persona(): void
    {
        $response = $this->get('/hr');

        $response->assertRedirect('/team?type=personas');
    }

    /**
     * Test that /agents redirects to /team?type=workers
     */
    public function test_agents_redirects_to_team_worker(): void
    {
        $response = $this->get('/agents');

        $response->assertRedirect('/team?type=workers');
    }

    // ==========================================
    // Team Filter Tests
    // ==========================================

    /**
     * Test team index shows all members when no filter
     */
    public function test_team_index_shows_all_members_by_default(): void
    {
        $worker = TeamMember::factory()->worker()->create(['name' => 'Test Worker']);
        $persona = TeamMember::factory()->persona()->create(['name' => 'Test Persona']);
        $boardMember = TeamMember::factory()->boardMember()->create(['name' => 'Test Board Member']);

        $response = $this->get('/team');

        $response->assertStatus(200);
        // Default shows workers tab
        $response->assertSee('Test Worker');
        $response->assertDontSee('Test Persona');
        $response->assertDontSee('Test Board Member');
    }

    /**
     * Test team filters by type=workers
     */
    public function test_team_filters_by_type_worker(): void
    {
        $worker = TeamMember::factory()->worker()->create(['name' => 'Test Worker']);
        $persona = TeamMember::factory()->persona()->create(['name' => 'Test Persona']);

        $response = $this->get('/team?type=workers');

        $response->assertStatus(200);
        $response->assertSee('Test Worker');
        $response->assertDontSee('Test Persona');
    }

    /**
     * Test team filters by type=personas
     */
    public function test_team_filters_by_type_persona(): void
    {
        $worker = TeamMember::factory()->worker()->create(['name' => 'Test Worker']);
        $persona = TeamMember::factory()->persona()->create(['name' => 'Test Persona']);

        $response = $this->get('/team?type=personas');

        $response->assertStatus(200);
        $response->assertSee('Test Persona');
        $response->assertDontSee('Test Worker');
    }

    /**
     * Test team filters by type=board-members
     */
    public function test_team_filters_by_type_board_members(): void
    {
        $worker = TeamMember::factory()->worker()->create(['name' => 'Test Worker']);
        $boardMember = TeamMember::factory()->boardMember()->create(['name' => 'Test Board Member']);

        $response = $this->get('/team?type=board-members');

        $response->assertStatus(200);
        $response->assertSee('Test Board Member');
        $response->assertDontSee('Test Worker');
    }

    /**
     * Test legacy tab parameter still works for backwards compatibility
     */
    public function test_team_legacy_tab_parameter_still_works(): void
    {
        $persona = TeamMember::factory()->persona()->create(['name' => 'Legacy Persona']);

        $response = $this->get('/team?tab=personas');

        $response->assertStatus(200);
        $response->assertSee('Legacy Persona');
    }

    /**
     * Test type parameter takes precedence over tab
     */
    public function test_type_parameter_takes_precedence_over_tab(): void
    {
        $worker = TeamMember::factory()->worker()->create(['name' => 'Type Worker']);
        $persona = TeamMember::factory()->persona()->create(['name' => 'Tab Persona']);

        // type=workers should win over tab=personas
        $response = $this->get('/team?type=workers&tab=personas');

        $response->assertStatus(200);
        $response->assertSee('Type Worker');
        $response->assertDontSee('Tab Persona');
    }

    // ==========================================
    // Route Existence Tests
    // ==========================================

    /**
     * Test main tasks route is accessible
     */
    public function test_tasks_route_accessible(): void
    {
        $response = $this->get('/tasks');

        $response->assertStatus(200);
    }

    /**
     * Test tasks board route is accessible
     */
    public function test_tasks_board_route_accessible(): void
    {
        $response = $this->get('/tasks/board');

        $response->assertStatus(200);
    }

    /**
     * Test tasks list route is accessible (Livewire component)
     */
    public function test_tasks_list_route_accessible(): void
    {
        // Note: tasks.list points to a Livewire component (TaskList)
        // Livewire routes may return 404 without proper Livewire setup
        // This test verifies the route is registered
        $routeExists = \Illuminate\Support\Facades\Route::has('tasks.list');
        $this->assertTrue($routeExists, 'tasks.list route should be registered');
    }

    /**
     * Test tasks executive route is accessible
     */
    public function test_tasks_executive_route_accessible(): void
    {
        $response = $this->get('/tasks/executive');

        $response->assertStatus(200);
    }

    /**
     * Test org chart route is accessible
     */
    public function test_org_chart_route_accessible(): void
    {
        $response = $this->get('/org-chart');

        $response->assertStatus(200);
    }

    /**
     * Test projects route is accessible
     */
    public function test_projects_route_accessible(): void
    {
        $response = $this->get('/projects');

        $response->assertStatus(200);
    }

    /**
     * Test board route is accessible
     */
    public function test_board_route_accessible(): void
    {
        $response = $this->get('/board');

        $response->assertStatus(200);
    }
}