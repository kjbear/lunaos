<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use Tests\TestCase;

class ProjectPercentCompleteTest extends TestCase
{
    public function test_project_with_no_tasks_returns_zero_percent(): void
    {
        $project = Project::factory()->create();
        
        $this->assertEquals(0, $project->percent_complete);
    }

    public function test_project_calculates_percent_complete_from_tasks(): void
    {
        $project = Project::factory()->create();
        
        // Create 4 tasks
        Task::factory()->count(4)->create([
            'project_id' => $project->id,
            'status' => 'pending',
        ]);
        
        // Complete 1 task
        Task::where('project_id', $project->id)
            ->limit(1)
            ->update(['status' => 'completed']);
        
        $project->refresh();
        
        $this->assertEquals(25.00, $project->percent_complete);
    }

    public function test_project_with_all_completed_tasks_returns_100_percent(): void
    {
        $project = Project::factory()->create();
        
        // Create 3 completed tasks
        Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'status' => 'completed',
        ]);
        
        $project->refresh();
        
        $this->assertEquals(100.00, $project->percent_complete);
    }
}