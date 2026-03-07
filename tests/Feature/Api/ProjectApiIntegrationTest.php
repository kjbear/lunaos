<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Agent;
use App\Models\ProjectAssignment;
use App\Models\Repository;
use App\Models\Task;
use App\Models\ProjectIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectApiIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ==========================================
    // AUTHENTICATION TESTS
    // ==========================================

    /** @test */
    public function unauthenticated_requests_are_accepted_for_project_index(): void
    {
        // Note: This API is currently unprotected for internal use
        // This test documents the current behavior
        $response = $this->getJson('/api/projects');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_requests_are_accepted_for_project_show(): void
    {
        $project = Project::factory()->create();

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
    }

    // ==========================================
    // CRUD - INDEX (LIST PROJECTS)
    // ==========================================

    /** @test */
    public function index_returns_empty_array_when_no_projects(): void
    {
        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJson(['data' => []]);
    }

    /** @test */
    public function index_returns_paginated_projects(): void
    {
        Project::factory()->count(25)->create();

        $response = $this->getJson('/api/projects?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25);
    }

    /** @test */
    public function index_supports_pagination(): void
    {
        Project::factory()->count(30)->create();

        // First page
        $response = $this->getJson('/api/projects?per_page=15&page=1');
        $response->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.current_page', 1);

        // Second page
        $response = $this->getJson('/api/projects?per_page=15&page=2');
        $response->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    /** @test */
    public function index_includes_relationships(): void
    {
        $repository = Repository::factory()->create();
        $project = Project::factory()->create(['repository_id' => $repository->id]);
        $agent = Agent::create([
            'name' => 'pm-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'project_manager',
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'status',
                        'health',
                        'repository',
                        'project_manager',
                        'agents',
                    ],
                ],
            ]);
    }

    // ==========================================
    // FILTERING TESTS
    // ==========================================

    /** @test */
    public function index_filters_by_status(): void
    {
        Project::factory()->count(3)->create(['status' => 'active']);
        Project::factory()->count(2)->create(['status' => 'planning']);
        Project::factory()->count(1)->create(['status' => 'completed']);

        $response = $this->getJson('/api/projects?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function index_filters_by_multiple_statuses(): void
    {
        Project::factory()->count(3)->create(['status' => 'active']);
        Project::factory()->count(2)->create(['status' => 'planning']);
        Project::factory()->count(1)->create(['status' => 'completed']);

        $response = $this->getJson('/api/projects?status=active,planning');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function index_filters_by_health(): void
    {
        Project::factory()->count(4)->create(['health' => 'healthy']);
        Project::factory()->count(2)->create(['health' => 'at_risk']);
        Project::factory()->count(1)->create(['health' => 'blocked']);

        $response = $this->getJson('/api/projects?health=at_risk');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function index_filters_by_architecture_type(): void
    {
        Project::factory()->count(3)->create(['architecture_type' => 'microservices']);
        Project::factory()->count(2)->create(['architecture_type' => 'monolith']);

        $response = $this->getJson('/api/projects?architecture_type=microservices');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function index_filters_by_repository_id(): void
    {
        $repo1 = Repository::factory()->create();
        $repo2 = Repository::factory()->create();
        
        Project::factory()->count(3)->create(['repository_id' => $repo1->id]);
        Project::factory()->count(2)->create(['repository_id' => $repo2->id]);

        $response = $this->getJson("/api/projects?repository_id={$repo1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function index_searches_by_name(): void
    {
        Project::factory()->create(['name' => 'LunaOS Main Project']);
        Project::factory()->create(['name' => 'API Gateway Service']);
        Project::factory()->create(['name' => 'LunaOS Mobile App']);

        $response = $this->getJson('/api/projects?search=LunaOS');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function index_searches_by_description(): void
    {
        Project::factory()->create([
            'name' => 'Project A',
            'description' => 'A LunaOS core module for authentication',
        ]);
        Project::factory()->create([
            'name' => 'Project B',
            'description' => 'Payment processing service',
        ]);

        $response = $this->getJson('/api/projects?search=authentication');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function index_filters_by_created_after(): void
    {
        $old = Project::factory()->create(['created_at' => now()->subDays(10)]);
        $new = Project::factory()->create(['created_at' => now()->subDays(1)]);

        $response = $this->getJson('/api/projects?created_after=' . now()->subDays(5)->toDateString());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $new->id);
    }

    /** @test */
    public function index_filters_by_created_before(): void
    {
        $old = Project::factory()->create(['created_at' => now()->subDays(10)]);
        $new = Project::factory()->create(['created_at' => now()->subDays(1)]);

        $response = $this->getJson('/api/projects?created_before=' . now()->subDays(5)->toDateString());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $old->id);
    }

    // ==========================================
    // SORTING TESTS
    // ==========================================

    /** @test */
    public function index_sorts_by_name_asc(): void
    {
        Project::factory()->create(['name' => 'Zebra Project']);
        Project::factory()->create(['name' => 'Alpha Project']);
        Project::factory()->create(['name' => 'Middle Project']);

        $response = $this->getJson('/api/projects?sort=name&direction=asc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Alpha Project')
            ->assertJsonPath('data.2.name', 'Zebra Project');
    }

    /** @test */
    public function index_sorts_by_created_at_desc(): void
    {
        $oldest = Project::factory()->create(['created_at' => now()->subDays(3)]);
        $middle = Project::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Project::factory()->create(['created_at' => now()->subDays(1)]);

        $response = $this->getJson('/api/projects?sort=created_at&direction=desc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.2.id', $oldest->id);
    }

    /** @test */
    public function index_sorts_by_progress(): void
    {
        Project::factory()->create(['progress' => 90]);
        Project::factory()->create(['progress' => 10]);
        Project::factory()->create(['progress' => 50]);

        $response = $this->getJson('/api/projects?sort=progress&direction=desc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.progress', 90)
            ->assertJsonPath('data.2.progress', 10);
    }

    // ==========================================
    // CRUD - STORE (CREATE PROJECT)
    // ==========================================

    /** @test */
    public function store_creates_project_with_required_fields(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'New Project',
            'description' => 'Project description',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'New Project',
                    'description' => 'Project description',
                    'status' => 'planning',
                    'health' => 'healthy',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
        ]);
    }

    /** @test */
    public function store_sets_default_status_and_health(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Default Project',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'planning')
            ->assertJsonPath('data.health', 'healthy');
    }

    /** @test */
    public function store_accepts_all_fields(): void
    {
        $repository = Repository::factory()->create();
        $agent = Agent::create([
            'name' => 'pm-test',
            'role' => 'developer',
            'status' => 'online',
        ]);

        $response = $this->postJson('/api/projects', [
            'name' => 'Full Project',
            'description' => 'Complete project description',
            'repo_url' => 'https://github.com/test/repo',
            'repository_id' => $repository->id,
            'status' => 'active',
            'health' => 'healthy',
            'architecture_type' => 'microservices',
            'technologies' => ['Laravel', 'Vue', 'PostgreSQL'],
            'project_manager_id' => $agent->id,
            'progress' => 25,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Full Project',
                    'status' => 'active',
                    'architecture_type' => 'microservices',
                    'technologies' => ['Laravel', 'Vue', 'PostgreSQL'],
                ],
            ]);
    }

    /** @test */
    public function store_validates_required_name(): void
    {
        $response = $this->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function store_validates_name_max_length(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function store_validates_status_enum(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function store_validates_health_enum(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'health' => 'invalid_health',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['health']);
    }

    /** @test */
    public function store_validates_repo_url_format(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'repo_url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['repo_url']);
    }

    /** @test */
    public function store_validates_repository_exists(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'repository_id' => 'non-existent-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['repository_id']);
    }

    /** @test */
    public function store_validates_project_manager_exists(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'project_manager_id' => 'non-existent-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_manager_id']);
    }

    /** @test */
    public function store_validates_technologies_array(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'technologies' => 'not-an-array',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['technologies']);
    }

    /** @test */
    public function store_validates_progress_range(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test',
            'progress' => 150,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['progress']);
    }

    // ==========================================
    // CRUD - SHOW (GET SINGLE PROJECT)
    // ==========================================

    /** @test */
    public function show_returns_project_with_relationships(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);
        $agent = Agent::create([
            'name' => 'show-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'status',
                    'health',
                    'tasks',
                    'agents',
                ],
            ])
            ->assertJsonPath('data.id', $project->id);
    }

    /** @test */
    public function show_returns_404_for_non_existent_project(): void
    {
        $response = $this->getJson('/api/projects/non-existent-uuid');

        $response->assertStatus(404);
    }

    // ==========================================
    // CRUD - UPDATE
    // ==========================================

    /** @test */
    public function update_modifies_project_fields(): void
    {
        $project = Project::factory()->create([
            'name' => 'Original Name',
            'status' => 'planning',
        ]);

        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'Updated Name',
            'status' => 'active',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'status' => 'active',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function update_validates_name_required_when_present(): void
    {
        $project = Project::factory()->create();

        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function update_validates_status_enum(): void
    {
        $project = Project::factory()->create();

        $response = $this->putJson("/api/projects/{$project->id}", [
            'status' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function update_returns_404_for_non_existent_project(): void
    {
        $response = $this->putJson('/api/projects/non-existent-uuid', [
            'name' => 'Updated',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function update_can_change_technologies(): void
    {
        $project = Project::factory()->create([
            'technologies' => ['Laravel', 'MySQL'],
        ]);

        $response = $this->putJson("/api/projects/{$project->id}", [
            'technologies' => ['Vue', 'Node', 'MongoDB'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.technologies', ['Vue', 'Node', 'MongoDB']);
    }

    // ==========================================
    // CRUD - DELETE (SOFT DELETE)
    // ==========================================

    /** @test */
    public function destroy_soft_deletes_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Project archived successfully',
            ]);

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    /** @test */
    public function destroy_cascades_to_tasks(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->deleteJson("/api/projects/{$project->id}");

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function destroy_cascades_to_agent_assignments(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'cascade-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => (string) $agent->id,
        ]);

        $this->deleteJson("/api/projects/{$project->id}");

        // Assignment is soft-deleted (ProjectAssignment uses SoftDeletes)
        $this->assertSoftDeleted('project_assignments', [
            'id' => $assignment->id,
        ]);
    }

    /** @test */
    public function destroy_returns_404_for_non_existent_project(): void
    {
        $response = $this->deleteJson('/api/projects/non-existent-uuid');

        $response->assertStatus(404);
    }

    /** @test */
    public function index_excludes_soft_deleted_by_default(): void
    {
        $active = Project::factory()->create(['name' => 'Active Project']);
        $deleted = Project::factory()->create(['name' => 'Deleted Project']);
        $deleted->delete();

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active Project');
    }

    /** @test */
    public function index_includes_soft_deleted_with_trashed_param(): void
    {
        $active = Project::factory()->create(['name' => 'Active Project']);
        $deleted = Project::factory()->create(['name' => 'Deleted Project']);
        $deleted->delete();

        $response = $this->getJson('/api/projects?with_trashed=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // ==========================================
    // RESTORE TESTS
    // ==========================================

    /** @test */
    public function restore_restores_soft_deleted_project(): void
    {
        $project = Project::factory()->create();
        $project->delete();

        $response = $this->postJson("/api/projects/{$project->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Project restored successfully',
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);

        $project->refresh();
        $this->assertNull($project->deleted_at);
    }

    /** @test */
    public function restore_returns_error_for_non_deleted_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->postJson("/api/projects/{$project->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Project is not deleted',
            ]);
    }

    // ==========================================
    // FORCE DELETE TESTS
    // ==========================================

    /** @test */
    public function force_delete_permanently_removes_project(): void
    {
        $project = Project::factory()->create();
        $project->delete();

        $response = $this->deleteJson("/api/projects/{$project->id}/force");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /** @test */
    public function force_delete_works_on_soft_deleted_project(): void
    {
        $project = Project::factory()->create();
        $project->delete();

        $response = $this->deleteJson("/api/projects/{$project->id}/force");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    // ==========================================
    // STATISTICS TESTS
    // ==========================================

    /** @test */
    public function stats_returns_project_statistics(): void
    {
        Project::factory()->count(3)->create(['status' => 'active', 'health' => 'healthy']);
        Project::factory()->count(2)->create(['status' => 'planning', 'health' => 'at_risk']);
        Project::factory()->count(1)->create(['status' => 'completed', 'health' => 'blocked']);

        $response = $this->getJson('/api/projects/stats');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total' => 6,
                    'by_status' => [
                        'active' => 3,
                        'planning' => 2,
                        'completed' => 1,
                    ],
                    'by_health' => [
                        'healthy' => 3,
                        'at_risk' => 2,
                        'blocked' => 1,
                    ],
                ],
            ]);
    }

    /** @test */
    public function stats_includes_trashed_count(): void
    {
        Project::factory()->count(3)->create();
        Project::factory()->create()->delete();

        $response = $this->getJson('/api/projects/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.trashed', 1);
    }

    // ==========================================
    // AGENT ASSIGNMENT TESTS
    // ==========================================

    /** @test */
    public function assign_agent_to_project(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'assign-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);

        $response = $this->postJson("/api/projects/{$project->id}/agents", [
            'agent_id' => (string) $agent->id,
            'role' => 'developer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Agent assigned successfully',
                'data' => [
                    'agent_id' => (string) $agent->id,
                    'role' => 'developer',
                ],
            ]);

        $this->assertDatabaseHas('project_assignments', [
            'project_id' => $project->id,
            'agent_id' => (string) $agent->id,
            'role' => 'developer',
        ]);
    }

    /** @test */
    public function assign_agent_validates_role(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'role-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);

        $response = $this->postJson("/api/projects/{$project->id}/agents", [
            'agent_id' => (string) $agent->id,
            'role' => 'invalid_role',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function assign_agent_validates_agent_exists(): void
    {
        $project = Project::factory()->create();

        $response = $this->postJson("/api/projects/{$project->id}/agents", [
            'agent_id' => 'non-existent-uuid',
            'role' => 'developer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['agent_id']);
    }

    /** @test */
    public function assign_agent_prevents_duplicate_assignment(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'dup-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);

        // First assignment
        $this->postJson("/api/projects/{$project->id}/agents", [
            'agent_id' => (string) $agent->id,
            'role' => 'developer',
        ]);

        // Duplicate assignment
        $response = $this->postJson("/api/projects/{$project->id}/agents", [
            'agent_id' => (string) $agent->id,
            'role' => 'architect',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Agent already assigned to this project',
            ]);
    }

    /** @test */
    public function remove_agent_from_project(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'remove-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        
        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => (string) $agent->id,
            'role' => 'developer',
        ]);

        $response = $this->deleteJson("/api/projects/{$project->id}/agents/{$agent->id}");

        $response->assertStatus(204);

        // Assignment is soft-deleted by the ProjectAssignment model
        $this->assertSoftDeleted('project_assignments', [
            'project_id' => $project->id,
            'agent_id' => (string) $agent->id,
        ]);
    }

    /** @test */
    public function remove_agent_returns_404_if_not_assigned(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'not-assigned',
            'role' => 'developer',
            'status' => 'online',
        ]);

        $response = $this->deleteJson("/api/projects/{$project->id}/agents/{$agent->id}");

        $response->assertStatus(404);
    }

    // ==========================================
    // FILTERS ENDPOINT TESTS
    // ==========================================

    /** @test */
    public function filters_returns_available_filter_options(): void
    {
        $response = $this->getJson('/api/projects/filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'statuses',
                    'health_states',
                    'architecture_types',
                ],
            ])
            ->assertJson([
                'data' => [
                    'statuses' => ['planning', 'active', 'completed', 'archived'],
                    'health_states' => ['healthy', 'at_risk', 'blocked'],
                ],
            ]);
    }

    // ==========================================
    // EDGE CASES
    // ==========================================

    /** @test */
    public function show_includes_limited_tasks_and_issues(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(20)->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        
        // Should only load limited tasks (10)
        $tasks = $response->json('data.tasks');
        $this->assertLessThanOrEqual(10, count($tasks));
    }

    /** @test */
    public function update_partial_update_works(): void
    {
        $project = Project::factory()->create([
            'name' => 'Original',
            'description' => 'Original description',
        ]);

        // Only update description
        $response = $this->putJson("/api/projects/{$project->id}", [
            'description' => 'New description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Original', // Unchanged
                    'description' => 'New description',
                ],
            ]);
    }

    /** @test */
    public function store_generates_uuid(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'UUID Test',
        ]);

        $response->assertStatus(201);
        
        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id
        );
    }

    /** @test */
    public function technologies_stored_as_json_array(): void
    {
        $project = Project::factory()->create([
            'technologies' => ['Laravel', 'Vue', 'Redis'],
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.technologies', ['Laravel', 'Vue', 'Redis']);
    }
}