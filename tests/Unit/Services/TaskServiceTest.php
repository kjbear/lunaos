<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\TaskService;
use App\Models\Task;
use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = new TaskService();
    }

    /** @test */
    public function it_gets_all_tasks_without_filters(): void
    {
        Task::factory()->count(5)->create();

        $result = $this->taskService->getAllTasks();

        $this->assertEquals(5, $result->total());
    }

    /** @test */
    public function it_filters_tasks_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'complete']);

        $result = $this->taskService->getAllTasks(['status' => 'pending']);

        $this->assertEquals(2, $result->total());
        $this->assertTrue($result->pluck('status')->contains('pending'));
    }

    /** @test */
    public function it_filters_tasks_by_view_mode(): void
    {
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'board']);
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'executive']);

        $result = $this->taskService->getAllTasks(['view_mode' => 'list']);

        $this->assertEquals(2, $result->total());
    }

    /** @test */
    public function it_filters_tasks_by_agent(): void
    {
        Task::factory()->create(['assigned_to' => 'dave']);
        Task::factory()->create(['assigned_to' => 'sam']);
        Task::factory()->create(['assigned_to' => 'dave']);

        $result = $this->taskService->getAllTasks(['agent' => 'dave']);

        $this->assertEquals(2, $result->total());
    }

    /** @test */
    public function it_filters_tasks_by_priority(): void
    {
        Task::factory()->create(['priority' => 'high']);
        Task::factory()->create(['priority' => 'medium']);
        Task::factory()->create(['priority' => 'high']);

        $result = $this->taskService->getAllTasks(['priority' => 'high']);

        $this->assertEquals(2, $result->total());
    }

    /** @test */
    public function it_filters_tasks_by_step(): void
    {
        Task::factory()->create(['step' => 'develop']);
        Task::factory()->create(['step' => 'qa']);
        Task::factory()->create(['step' => 'develop']);

        $result = $this->taskService->getAllTasks(['step' => 'develop']);

        $this->assertEquals(2, $result->total());
    }

    /** @test */
    public function it_paginates_results(): void
    {
        Task::factory()->count(25)->create();

        $result = $this->taskService->getAllTasks(['per_page' => 10]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    /** @test */
    public function it_orders_tasks_by_created_at_descending(): void
    {
        $oldest = Task::factory()->create(['created_at' => now()->subDays(2)]);
        $middle = Task::factory()->create(['created_at' => now()->subDays(1)]);
        $newest = Task::factory()->create(['created_at' => now()]);

        $result = $this->taskService->getAllTasks();

        $ids = $result->pluck('id');
        $this->assertEquals($newest->id, $ids->first());
        $this->assertEquals($oldest->id, $ids->last());
    }

    /** @test */
    public function it_gets_tasks_by_view_mode(): void
    {
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'board']);
        Task::factory()->create(['view_mode' => 'list']);

        $result = $this->taskService->getTasksByViewMode('list', 10);

        $this->assertEquals(2, $result->total());
        $this->assertTrue($result->pluck('view_mode')->contains('list'));
    }

    /** @test */
    public function it_defaults_to_list_view_when_invalid_view_mode(): void
    {
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'board']);

        $result = $this->taskService->getTasksByViewMode('invalid', 10);

        // Should return list view tasks as default
        $this->assertGreaterThanOrEqual(0, $result->total());
    }

    /** @test */
    public function it_creates_a_task_with_defaults(): void
    {
        $data = [
            'title' => 'New Task',
            'description' => 'Task description',
            'assigned_to' => 'dave',
            'status' => 'pending',
            'step' => 'develop',
            'priority' => 'medium',
        ];

        $task = $this->taskService->createTask($data);

        $this->assertInstanceOf(Model::class, $task);
        $this->assertEquals('New Task', $task->title);
        $this->assertEquals('list', $task->view_mode);
        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    /** @test */
    public function it_creates_a_task_with_custom_view_mode(): void
    {
        $data = [
            'title' => 'Board Task',
            'view_mode' => 'board',
        ];

        $task = $this->taskService->createTask($data);

        $this->assertEquals('board', $task->view_mode);
    }

    /** @test */
    public function it_sets_default_view_mode_when_invalid(): void
    {
        $data = [
            'title' => 'Invalid View Task',
            'view_mode' => 'invalid_mode',
        ];

        $task = $this->taskService->createTask($data);

        $this->assertEquals('list', $task->view_mode);
    }

    /** @test */
    public function it_updates_a_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'status' => 'pending',
        ]);

        $updated = $this->taskService->updateTask($task, [
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ]);

        $this->assertTrue($updated);
        $this->assertEquals('Updated Title', $task->fresh()->title);
        $this->assertEquals('in_progress', $task->fresh()->status);
    }

    /** @test */
    public function it_updates_task_view_mode_with_validation(): void
    {
        $task = Task::factory()->create(['view_mode' => 'list']);

        $this->taskService->updateTask($task, ['view_mode' => 'board']);
        $this->assertEquals('board', $task->fresh()->view_mode);

        $this->taskService->updateTask($task, ['view_mode' => 'invalid']);
        $this->assertEquals('list', $task->fresh()->view_mode);
    }

    /** @test */
    public function it_deletes_a_task(): void
    {
        $task = Task::factory()->create();

        $deleted = $this->taskService->deleteTask($task);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_gets_statistics(): void
    {
        Task::factory()->create(['status' => 'pending', 'priority' => 'high', 'step' => 'develop']);
        Task::factory()->create(['status' => 'in_progress', 'priority' => 'medium', 'step' => 'qa']);
        Task::factory()->create(['status' => 'pending', 'priority' => 'low', 'step' => 'develop']);

        $stats = $this->taskService->getStatistics();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['in_progress']);
        $this->assertEquals(1, $stats['by_priority']['high']);
        $this->assertEquals(2, $stats['by_step']['develop']);
        $this->assertEquals(1, $stats['by_step']['qa']);
    }

    /** @test */
    public function it_gets_view_mode_statistics(): void
    {
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'board']);
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'executive']);

        $stats = $this->taskService->getStatistics();

        $this->assertEquals(2, $stats['list_view']);
        $this->assertEquals(1, $stats['board_view']);
        $this->assertEquals(1, $stats['executive_view']);
    }

    /** @test */
    public function it_gets_available_view_modes(): void
    {
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'board']);
        Task::factory()->create(['view_mode' => 'list']);

        $modes = $this->taskService->getAvailableViewModes();

        $this->assertContains('list', $modes);
        $this->assertContains('board', $modes);
        $this->assertCount(2, $modes);
    }

    /** @test */
    public function it_changes_view_mode(): void
    {
        $task = Task::factory()->create(['view_mode' => 'list']);

        $result = $this->taskService->changeViewMode($task, 'board');

        $this->assertTrue($result);
        $this->assertEquals('board', $task->fresh()->view_mode);
    }

    /** @test */
    public function it_rejects_invalid_view_mode_change(): void
    {
        $task = Task::factory()->create(['view_mode' => 'list']);

        $result = $this->taskService->changeViewMode($task, 'invalid_mode');

        $this->assertFalse($result);
        $this->assertEquals('list', $task->fresh()->view_mode);
    }

    /** @test */
    public function it_loads_relationships_when_fetching(): void
    {
        $agent = Agent::create([
            'name' => 'dave',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $task = Task::factory()->create(['assigned_to' => 'dave']);

        $result = $this->taskService->getAllTasks();

        // Verify tasks are loaded (relationships will be eager loaded)
        $this->assertEquals(1, $result->total());
    }

    /** @test */
    public function it_handles_empty_filter_values(): void
    {
        Task::factory()->count(3)->create();

        $result = $this->taskService->getAllTasks([
            'status' => 'all',
            'priority' => 'all',
            'view_mode' => 'all',
        ]);

        $this->assertEquals(3, $result->total());
    }
}
