<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Task;
use App\Models\Agent;
use App\Models\Repository;
use App\Models\ProjectIssue;
use App\Models\ProjectAssignment;
use App\Models\ProjectArtifact;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // RELATIONSHIP TESTS
    // ==========================================

    public function test_project_has_many_tasks(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $this->assertEquals(3, $project->tasks()->count());
        $this->assertInstanceOf(Task::class, $project->tasks->first());
    }

    public function test_project_has_many_agents_through_assignments(): void
    {
        $project = Project::factory()->create();
        $agent1 = Agent::create(['name' => 'agent1', 'role' => 'developer', 'status' => 'online']);
        $agent2 = Agent::create(['name' => 'agent2', 'role' => 'qa', 'status' => 'online']);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent1->id,
            'role' => 'developer',
        ]);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent2->id,
            'role' => 'qa',
        ]);

        $this->assertEquals(2, $project->agents()->count());
    }

    public function test_project_belongs_to_repository(): void
    {
        $repository = Repository::factory()->create();
        $project = Project::factory()->create(['repository_id' => $repository->id]);

        $this->assertTrue($project->repository->is($repository));
        $this->assertInstanceOf(Repository::class, $project->repository);
    }

    public function test_project_repository_returns_null_when_not_set(): void
    {
        $project = Project::factory()->create(['repository_id' => null]);

        $this->assertNull($project->repository);
    }

    public function test_project_has_many_issues(): void
    {
        $project = Project::factory()->create();
        ProjectIssue::factory()->count(2)->create(['project_id' => $project->id]);

        $this->assertEquals(2, $project->issues()->count());
        $this->assertInstanceOf(ProjectIssue::class, $project->issues->first());
    }

    public function test_project_has_many_requirements(): void
    {
        $project = Project::factory()->create();
        
        // Requirements relationship may not exist yet - skip if not implemented
        if (!method_exists($project, 'requirements')) {
            $this->markTestSkipped('Project does not have requirements relationship yet.');
        }
        
        Requirement::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'project_id' => $project->id,
            'title' => 'Test Requirement',
            'status' => 'draft',
        ]);

        $this->assertEquals(1, $project->requirements()->count());
        $this->assertInstanceOf(Requirement::class, $project->requirements->first());
    }

    public function test_project_has_many_artifacts(): void
    {
        $project = Project::factory()->create();
        ProjectArtifact::create([
            'project_id' => $project->id,
            'type' => 'requirement',
            'title' => 'Test Artifact',
            'content' => 'Artifact content',
        ]);

        $this->assertEquals(1, $project->artifacts()->count());
        $this->assertInstanceOf(ProjectArtifact::class, $project->artifacts->first());
    }

    public function test_project_has_assignments_relationship(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        $this->assertEquals(1, $project->assignments()->count());
        $this->assertInstanceOf(ProjectAssignment::class, $project->assignments->first());
    }

    // ==========================================
    // PERCENT COMPLETE TESTS
    // ==========================================

    public function test_percent_complete_returns_zero_with_no_tasks(): void
    {
        $project = Project::factory()->create();

        $this->assertEquals(0.00, $project->percent_complete);
    }

    public function test_percent_complete_calculates_correctly(): void
    {
        $project = Project::factory()->create();

        // Create 4 tasks: 2 completed, 2 pending
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $project->refresh();

        $this->assertEquals(50.00, $project->percent_complete);
    }

    public function test_percent_complete_returns_100_when_all_completed(): void
    {
        $project = Project::factory()->create();

        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);

        $project->refresh();

        $this->assertEquals(100.00, $project->percent_complete);
    }

    public function test_percent_complete_handles_mixed_statuses(): void
    {
        $project = Project::factory()->create();

        // Create tasks with various statuses
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'failed']);

        $project->refresh();

        // Only 'completed' status counts
        // 2 out of 5 = 40%
        $this->assertEquals(40.00, $project->percent_complete);
    }

    public function test_calculate_percent_complete_method(): void
    {
        $project = Project::factory()->create();

        Task::factory()->count(3)->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->count(1)->create(['project_id' => $project->id, 'status' => 'pending']);

        // 3 of 4 completed = 75%
        $this->assertEquals(75.00, $project->calculatePercentComplete());
    }

    // ==========================================
    // SCOPE TESTS
    // ==========================================

    public function test_scope_active_filters_by_status(): void
    {
        Project::factory()->create(['status' => 'active', 'name' => 'Active Project']);
        Project::factory()->create(['status' => 'planning', 'name' => 'Planning Project']);
        Project::factory()->create(['status' => 'completed', 'name' => 'Completed Project']);

        $activeProjects = Project::active()->get();

        $this->assertEquals(1, $activeProjects->count());
        $this->assertEquals('Active Project', $activeProjects->first()->name);
    }

    public function test_scope_planning_filters_by_status(): void
    {
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'planning', 'name' => 'Planning Project']);

        $planningProjects = Project::planning()->get();

        $this->assertEquals(1, $planningProjects->count());
        $this->assertEquals('Planning Project', $planningProjects->first()->name);
    }

    public function test_scope_completed_filters_by_status(): void
    {
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'completed', 'name' => 'Done Project']);

        $completedProjects = Project::completed()->get();

        $this->assertEquals(1, $completedProjects->count());
        $this->assertEquals('Done Project', $completedProjects->first()->name);
    }

    public function test_scope_archived_filters_by_archived_at(): void
    {
        Project::factory()->create(['archived_at' => null, 'name' => 'Not Archived']);
        Project::factory()->create(['archived_at' => now(), 'name' => 'Archived Project']);

        $archivedProjects = Project::archived()->get();

        $this->assertEquals(1, $archivedProjects->count());
        $this->assertEquals('Archived Project', $archivedProjects->first()->name);
    }

    public function test_scope_at_risk_filters_by_health(): void
    {
        Project::factory()->create(['health' => 'healthy', 'name' => 'Healthy Project']);
        Project::factory()->create(['health' => 'at_risk', 'name' => 'At Risk Project']);
        Project::factory()->create(['health' => 'blocked', 'name' => 'Blocked Project']);

        $atRiskProjects = Project::atRisk()->get();

        $this->assertEquals(1, $atRiskProjects->count());
        $this->assertEquals('At Risk Project', $atRiskProjects->first()->name);
    }

    // ==========================================
    // HEALTH COLOR ACCESSOR TEST
    // ==========================================

    public function test_health_color_attribute(): void
    {
        $healthy = Project::factory()->create(['health' => 'healthy']);
        $atRisk = Project::factory()->create(['health' => 'at_risk']);
        $blocked = Project::factory()->create(['health' => 'blocked']);
        $unknown = Project::factory()->create(['health' => 'unknown']);

        $this->assertEquals('green', $healthy->health_color);
        $this->assertEquals('yellow', $atRisk->health_color);
        $this->assertEquals('red', $blocked->health_color);
        $this->assertEquals('gray', $unknown->health_color);
    }

    // ==========================================
    // MODEL TRAIT TESTS
    // ==========================================

    public function test_project_uses_soft_deletes(): void
    {
        $project = Project::factory()->create();
        $project->delete();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertNotNull($project->deleted_at);
    }

    public function test_project_uses_uuid_as_primary_key(): void
    {
        $project = Project::factory()->create();

        $this->assertIsString($project->id);
        $this->assertEquals(36, strlen($project->id)); // UUID format
    }

    public function test_project_restores_from_soft_delete(): void
    {
        $project = Project::factory()->create();
        $projectId = $project->id;

        $project->delete();
        $this->assertSoftDeleted('projects', ['id' => $projectId]);

        $project->restore();
        $this->assertDatabaseHas('projects', ['id' => $projectId, 'deleted_at' => null]);
    }

    // ==========================================
    // CASTS & ATTRIBUTES TESTS
    // ==========================================

    public function test_technologies_cast_to_array(): void
    {
        $project = Project::factory()->create([
            'technologies' => ['Laravel', 'Vue', 'MySQL'],
        ]);

        $this->assertIsArray($project->technologies);
        $this->assertEquals(['Laravel', 'Vue', 'MySQL'], $project->technologies);
    }

    public function test_archived_at_cast_to_datetime(): void
    {
        $project = Project::factory()->create([
            'archived_at' => '2024-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->archived_at);
    }

    public function test_percent_complete_cast_to_decimal(): void
    {
        $project = Project::factory()->create(['percent_complete' => '75.50']);

        $this->assertIsFloat($project->percent_complete);
    }

    // ==========================================
    // EAGER LOADING TESTS
    // ==========================================

    public function test_relationships_can_be_eager_loaded(): void
    {
        $project = Project::factory()->create();
        $repository = Repository::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        Task::factory()->create(['project_id' => $project->id]);
        ProjectIssue::factory()->create(['project_id' => $project->id]);
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent->id]);

        $loaded = Project::with(['tasks', 'agents', 'issues', 'repository', 'artifacts'])
            ->find($project->id);

        $this->assertTrue($loaded->relationLoaded('tasks'));
        $this->assertTrue($loaded->relationLoaded('agents'));
        $this->assertTrue($loaded->relationLoaded('issues'));
        $this->assertTrue($loaded->relationLoaded('repository'));
        $this->assertTrue($loaded->relationLoaded('artifacts'));
    }
}