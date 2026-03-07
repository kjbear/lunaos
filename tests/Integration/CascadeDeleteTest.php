<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Task;
use App\Models\Agent;
use App\Models\ProjectAssignment;
use App\Models\ProjectIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // PROJECT → TASKS CASCADE TESTS
    // ==========================================

    public function test_project_soft_delete_cascades_to_tasks(): void
    {
        $project = Project::factory()->create();
        $task1 = Task::factory()->create(['project_id' => $project->id]);
        $task2 = Task::factory()->create(['project_id' => $project->id]);

        // Verify tasks exist
        $this->assertDatabaseHas('tasks', ['id' => $task1->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('tasks', ['id' => $task2->id, 'deleted_at' => null]);

        // Delete project (soft delete)
        $project->delete();

        // Verify project is soft deleted
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        // Verify tasks are cascade soft deleted
        $this->assertSoftDeleted('tasks', ['id' => $task1->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task2->id]);

        // Verify tasks are not visible in normal queries
        $this->assertEquals(0, Task::where('project_id', $project->id)->count());
    }

    public function test_project_force_delete_cascades_to_tasks(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Verify task exists
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);

        // Force delete project
        $project->forceDelete();

        // Verify project is completely removed
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);

        // Verify task is cascade deleted
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_project_restore_restores_tasks(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Soft delete project
        $project->delete();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);

        // Restore project
        $project->restore();

        // Verify project is restored
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);

        // Verify task is also restored
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
    }

    // ==========================================
    // PROJECT → PROJECT_ASSIGNMENTS CASCADE TESTS
    // ==========================================

    public function test_project_soft_delete_cascades_to_assignments(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        // Verify assignment exists
        $this->assertDatabaseHas('project_assignments', ['id' => $assignment->id, 'deleted_at' => null]);

        // Delete project (soft delete)
        $project->delete();

        // Verify project is soft deleted
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        // Verify assignment is cascade soft deleted
        $this->assertSoftDeleted('project_assignments', ['id' => $assignment->id]);

        // Verify no visible assignments
        $this->assertEquals(0, ProjectAssignment::where('project_id', $project->id)->count());
    }

    public function test_project_force_delete_cascades_to_assignments(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        // Force delete project
        $project->forceDelete();

        // Verify assignment is cascade deleted
        $this->assertDatabaseMissing('project_assignments', ['id' => $assignment->id]);
    }

    public function test_project_restore_restores_assignments(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'onprojectline']);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        // Soft delete project
        $project->delete();
        $this->assertSoftDeleted('project_assignments', ['id' => $assignment->id]);

        // Restore project
        $project->restore();

        // Verify assignment is restored
        $this->assertDatabaseHas('project_assignments', ['id' => $assignment->id, 'deleted_at' => null]);
    }

    // ==========================================
    // PROJECT → PROJECT_ISSUES CASCADE TESTS
    // ==========================================

    public function test_project_soft_delete_cascades_to_issues(): void
    {
        $project = Project::factory()->create();
        $issue1 = ProjectIssue::factory()->create(['project_id' => $project->id]);
        $issue2 = ProjectIssue::factory()->create(['project_id' => $project->id]);

        // Verify issues exist
        $this->assertDatabaseHas('project_issues', ['id' => $issue1->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('project_issues', ['id' => $issue2->id, 'deleted_at' => null]);

        // Delete project (soft delete)
        $project->delete();

        // Verify project is soft deleted
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        // Verify issues are cascade soft deleted
        $this->assertSoftDeleted('project_issues', ['id' => $issue1->id]);
        $this->assertSoftDeleted('project_issues', ['id' => $issue2->id]);

        // Verify no visible issues
        $this->assertEquals(0, ProjectIssue::where('project_id', $project->id)->count());
    }

    public function test_project_force_delete_cascades_to_issues(): void
    {
        $project = Project::factory()->create();
        $issue = ProjectIssue::factory()->create(['project_id' => $project->id]);

        // Force delete project
        $project->forceDelete();

        // Verify issue is cascade deleted
        $this->assertDatabaseMissing('project_issues', ['id' => $issue->id]);
    }

    public function test_project_restore_restores_issues(): void
    {
        $project = Project::factory()->create();
        $issue = ProjectIssue::factory()->create(['project_id' => $project->id]);

        // Soft delete project
        $project->delete();
        $this->assertSoftDeleted('project_issues', ['id' => $issue->id]);

        // Restore project
        $project->restore();

        // Verify issue is restored
        $this->assertDatabaseHas('project_issues', ['id' => $issue->id, 'deleted_at' => null]);
    }

    // ==========================================
    // CASCADING DELETE WITH MULTIPLE RELATED MODELS
    // ==========================================

    public function test_project_delete_cascades_to_all_related_models(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        // Create related models
        $task = Task::factory()->create(['project_id' => $project->id]);
        $assignment = ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent->id]);
        $issue = ProjectIssue::factory()->create(['project_id' => $project->id]);

        // Verify all exist
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('project_assignments', ['id' => $assignment->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('project_issues', ['id' => $issue->id, 'deleted_at' => null]);

        // Delete project
        $project->delete();

        // Verify all are soft deleted
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
        $this->assertSoftDeleted('project_assignments', ['id' => $assignment->id]);
        $this->assertSoftDeleted('project_issues', ['id' => $issue->id]);

        // Restore project
        $project->restore();

        // Verify all are restored
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('project_assignments', ['id' => $assignment->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('project_issues', ['id' => $issue->id, 'deleted_at' => null]);
    }

    // ==========================================
    // INDEPENDENT MODEL DELETION TESTS
    // ==========================================

    public function test_deleting_task_does_not_delete_project(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Delete task
        $task->delete();

        // Project should still exist
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_deleting_agent_does_not_delete_project(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create(['name' => 'test-agent', 'role' => 'developer', 'status' => 'online']);

        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        // Delete agent
        $agent->delete();

        // Project should still exist
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    // ==========================================
    // CASCADE COUNT VERIFICATION
    // ==========================================

    public function test_cascade_delete_affects_correct_count(): void
    {
        $project = Project::factory()->create();
        $agent1 = Agent::create(['name' => 'agent1', 'role' => 'developer', 'status' => 'online']);
        $agent2 = Agent::create(['name' => 'agent2', 'role' => 'qa', 'status' => 'online']);

        // Create multiple tasks
        Task::factory()->count(5)->create(['project_id' => $project->id]);

        // Create multiple assignments
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent1->id]);
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent2->id]);

        // Create multiple issues
        ProjectIssue::factory()->count(3)->create(['project_id' => $project->id]);

        // Verify counts before delete
        $this->assertEquals(5, Task::withTrashed()->where('project_id', $project->id)->count());
        $this->assertEquals(2, ProjectAssignment::withTrashed()->where('project_id', $project->id)->count());
        $this->assertEquals(3, ProjectIssue::withTrashed()->where('project_id', $project->id)->count());

        // Delete project
        $project->delete();

        // Verify all related models are soft deleted
        $this->assertEquals(0, Task::where('project_id', $project->id)->count());
        $this->assertEquals(0, ProjectAssignment::where('project_id', $project->id)->count());
        $this->assertEquals(0, ProjectIssue::where('project_id', $project->id)->count());

        // But with trashed, they should still exist
        $this->assertEquals(5, Task::withTrashed()->where('project_id', $project->id)->count());
        $this->assertEquals(2, ProjectAssignment::withTrashed()->where('project_id', $project->id)->count());
        $this->assertEquals(3, ProjectIssue::withTrashed()->where('project_id', $project->id)->count());
    }
}