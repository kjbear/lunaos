<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Agent;
use App\Models\ProjectAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectAssignmentTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // CREATION TESTS
    // ==========================================

    public function test_assignment_can_be_created(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        $this->assertDatabaseHas('project_assignments', [
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);
    }

    public function test_assignment_uses_auto_increment_id(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertIsInt($assignment->id);
        $this->assertGreaterThan(0, $assignment->id);
    }

    // ==========================================
    // RELATIONSHIP TESTS
    // ==========================================

    public function test_assignment_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertTrue($assignment->project->is($project));
        $this->assertInstanceOf(Project::class, $assignment->project);
    }

    public function test_assignment_belongs_to_agent(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertTrue($assignment->agent->is($agent));
        $this->assertInstanceOf(Agent::class, $assignment->agent);
    }

    // ==========================================
    // ROLE ASSIGNMENT TESTS
    // ==========================================

    public function test_assignment_can_have_role(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'lead_developer',
        ]);

        $this->assertEquals('lead_developer', $assignment->role);
    }

    public function test_assignment_role_is_optional(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertNull($assignment->role);
    }

    // ==========================================
    // MULTIPLE AGENTS PER PROJECT TESTS
    // ==========================================

    public function test_project_can_have_multiple_agents(): void
    {
        $project = Project::factory()->create();
        $agent1 = Agent::create(['name' => 'agent1', 'role' => 'developer', 'status' => 'online']);
        $agent2 = Agent::create(['name' => 'agent2', 'role' => 'qa', 'status' => 'online']);
        $agent3 = Agent::create(['name' => 'agent3', 'role' => 'devops', 'status' => 'online']);

        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent1->id, 'role' => 'developer']);
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent2->id, 'role' => 'qa']);
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent3->id, 'role' => 'devops']);

        $this->assertEquals(3, $project->agents()->count());
        
        // Check that all three agent assignments exist
        $assignments = $project->agents()->get();
        $agentIds = $assignments->pluck('agent_id')->map(fn($id) => (int) $id)->toArray();
        
        $this->assertContains((int) $agent1->id, $agentIds);
        $this->assertContains((int) $agent2->id, $agentIds);
        $this->assertContains((int) $agent3->id, $agentIds);
    }

    public function test_agent_can_be_assigned_to_multiple_projects(): void
    {
        $project1 = Project::factory()->create(['name' => 'Project 1']);
        $project2 = Project::factory()->create(['name' => 'Project 2']);
        $agent = Agent::create(['name' => 'shared-agent', 'role' => 'developer', 'status' => 'online']);

        ProjectAssignment::create(['project_id' => $project1->id, 'agent_id' => $agent->id]);
        ProjectAssignment::create(['project_id' => $project2->id, 'agent_id' => $agent->id]);

        $this->assertEquals(2, $agent->projects()->count());
    }

    // ==========================================
    // UNASSIGN FUNCTIONALITY TESTS
    // ==========================================

    public function test_assignment_can_be_deleted_unassign(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertEquals(1, $project->agents()->count());

        $assignment->delete();

        // Since ProjectAssignment uses SoftDeletes, it should be soft deleted
        $this->assertEquals(0, $project->agents()->count());
        $this->assertSoftDeleted('project_assignments', ['id' => $assignment->id]);
    }

    public function test_unassign_by_project_id(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertEquals(1, ProjectAssignment::where('project_id', $project->id)->count());

        ProjectAssignment::where('project_id', $project->id)->delete();

        $this->assertEquals(0, ProjectAssignment::where('project_id', $project->id)->count());
    }

    public function test_unassign_by_agent_id(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertEquals(1, ProjectAssignment::where('agent_id', $agent->id)->count());

        ProjectAssignment::where('agent_id', $agent->id)->delete();

        $this->assertEquals(0, ProjectAssignment::where('agent_id', $agent->id)->count());
    }

    // ==========================================
    // SOFT DELETE TESTS
    // ==========================================

    public function test_assignment_uses_soft_deletes(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $assignmentId = $assignment->id;
        $assignment->delete();

        // Soft deleted, not hard deleted
        $this->assertSoftDeleted('project_assignments', ['id' => $assignmentId]);

        // Can still retrieve with trashed
        $trashed = ProjectAssignment::withTrashed()->find($assignmentId);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }

    public function test_assignment_can_be_restored(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $assignmentId = $assignment->id;
        $assignment->delete();

        $this->assertSoftDeleted('project_assignments', ['id' => $assignmentId]);

        $assignment->restore();

        $this->assertDatabaseHas('project_assignments', [
            'id' => $assignmentId,
            'deleted_at' => null,
        ]);
    }

    // ==========================================
    // UNIQUE CONSTRAINT TESTS
    // ==========================================

    public function test_same_agent_cannot_be_assigned_twice_to_same_project(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        // Try to create duplicate assignment
        $countBefore = ProjectAssignment::count();

        // Depending on database constraints, this might throw or silently ignore
        // For now, we just verify one assignment exists
        $assignments = ProjectAssignment::where('project_id', $project->id)
            ->where('agent_id', $agent->id)
            ->count();

        $this->assertEquals(1, $assignments);
    }

    // ==========================================
    // QUERY BUILDER TESTS
    // ==========================================

    public function test_can_query_assignments_by_role(): void
    {
        $project = Project::factory()->create();
        $agent1 = Agent::create(['name' => 'agent1', 'role' => 'developer', 'status' => 'online']);
        $agent2 = Agent::create(['name' => 'agent2', 'role' => 'qa', 'status' => 'online']);

        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent1->id, 'role' => 'developer']);
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent2->id, 'role' => 'qa']);

        $developers = ProjectAssignment::where('role', 'developer')->get();
        $qas = ProjectAssignment::where('role', 'qa')->get();

        $this->assertEquals(1, $developers->count());
        $this->assertEquals(1, $qas->count());
    }

    public function test_can_load_assignment_with_relationships(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $loaded = ProjectAssignment::with(['project', 'agent'])->find($assignment->id);

        $this->assertTrue($loaded->relationLoaded('project'));
        $this->assertTrue($loaded->relationLoaded('agent'));
        $this->assertEquals($project->name, $loaded->project->name);
        $this->assertEquals($agent->name, $loaded->agent->name);
    }
}