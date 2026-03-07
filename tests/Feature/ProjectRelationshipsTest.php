<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Agent;
use App\Models\ProjectAssignment;
use App\Models\Repository;
use App\Models\ProjectIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_has_many_tasks(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $this->assertEquals(3, $project->tasks()->count());
    }

    public function test_project_has_many_agents(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        
        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        $this->assertEquals(1, $project->agents()->count());
        $this->assertTrue($project->agents()->first()->agent->is($agent));
    }

    public function test_project_belongs_to_repository(): void
    {
        $repository = Repository::factory()->create();
        $project = Project::factory()->create(['repository_id' => $repository->id]);

        $this->assertTrue($project->repository->is($repository));
    }

    public function test_project_has_many_issues(): void
    {
        $project = Project::factory()->create();
        ProjectIssue::factory()->count(2)->create(['project_id' => $project->id]);

        $this->assertEquals(2, $project->issues()->count());
    }

    public function test_task_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->assertTrue($task->project->is($project));
    }

    public function test_agent_has_many_projects(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        $project = Project::factory()->create();
        
        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
            'role' => 'developer',
        ]);

        $this->assertEquals(1, $agent->projects()->count());
        $this->assertTrue($agent->projects()->first()->is($assignment));
    }

    public function test_cascade_delete_on_project_removes_tasks(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $taskId = $project->tasks()->first()->id;
        
        $project->delete();

        // Verify tasks were cascade deleted
        $this->assertEquals(0, Task::where('project_id', $project->id)->count());
    }

    public function test_cascade_delete_on_project_removes_assignments(): void
    {
        $project = Project::factory()->create();
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        
        ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $agent->id,
        ]);

        $project->delete();

        // Verify assignments were cascade deleted
        $this->assertEquals(0, ProjectAssignment::where('project_id', $project->id)->count());
    }

    public function test_soft_delete_on_project(): void
    {
        $project = Project::factory()->create();

        $project->delete();

        // Should be soft deleted (in database but not queryable)
        $this->assertNotNull($project->deleted_at);
        $this->assertSoftDeleted($project);
    }

    public function test_percent_complete_accessor_auto_calculates(): void
    {
        $project = Project::factory()->create();
        
        // Create 4 tasks, 2 completed
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $project->refresh();
        
        // Should calculate 50% (2 out of 4 completed)
        $this->assertEquals(50.00, $project->percent_complete);
    }

    public function test_technologies_field_is_json_cast(): void
    {
        $project = Project::factory()->create([
            'technologies' => ['Laravel', 'Vue', 'MySQL'],
        ]);

        $this->assertIsArray($project->technologies);
        $this->assertEquals(['Laravel', 'Vue', 'MySQL'], $project->technologies);
    }

    public function test_relationships_are_queryable_via_eloquent(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'developer',
            'status' => 'online',
        ]);
        ProjectAssignment::create(['project_id' => $project->id, 'agent_id' => $agent->id]);
        
        // Test eager loading
        $loaded = Project::with(['tasks', 'agents', 'repository', 'issues'])->find($project->id);
        
        $this->assertInstanceOf(Project::class, $loaded);
        $this->assertEquals(1, $loaded->tasks->count());
        $this->assertEquals(1, $loaded->agents->count());
    }
}
