<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that deleting a project cascade deletes its tasks.
     */
    public function test_project_deletion_cascade_deletes_tasks(): void
    {
        // Create a project
        $project = Project::create([
            'name' => 'Test Project',
            'description' => 'A test project',
            'status' => 'active',
            'health' => 'healthy',
        ]);

        // Create tasks associated with the project
        $task1 = Task::create([
            'title' => 'Task 1',
            'description' => 'First test task',
            'project_id' => $project->id,
            'status' => 'pending',
            'step' => 'develop',
        ]);

        $task2 = Task::create([
            'title' => 'Task 2',
            'description' => 'Second test task',
            'project_id' => $project->id,
            'status' => 'in_progress',
            'step' => 'qa',
        ]);

        // Verify tasks exist before deletion
        $this->assertDatabaseHas('tasks', ['id' => $task1->id]);
        $this->assertDatabaseHas('tasks', ['id' => $task2->id]);

        // Delete the project (soft delete)
        $project->delete();

        // Verify the project is soft-deleted
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        // Verify tasks are cascade deleted (soft deleted because tasks uses SoftDeletes)
        $this->assertSoftDeleted('tasks', ['id' => $task1->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task2->id]);
    }

    /**
     * Test that cascade delete works with forced delete (hard delete).
     */
    public function test_project_forced_delete_cascade_deletes_tasks(): void
    {
        // Create a project
        $project = Project::create([
            'name' => 'Test Project',
            'description' => 'A test project',
            'status' => 'active',
            'health' => 'healthy',
        ]);

        // Create a task associated with the project
        $task = Task::create([
            'title' => 'Task to be cascade deleted',
            'description' => 'This task should be deleted when project is force deleted',
            'project_id' => $project->id,
            'status' => 'pending',
            'step' => 'develop',
        ]);

        // Verify task exists before deletion
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);

        // Force delete the project (hard delete)
        $project->forceDelete();

        // Verify the project is completely deleted
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);

        // Verify task is cascade deleted at database level
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
