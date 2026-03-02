<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Task;
use App\Models\Agent;
use App\Models\AgentActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Test Task',
            'description' => 'A test task',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'status' => 'pending',
            'priority' => 'high',
        ]);
    }

    /** @test */
    public function it_has_default_values_on_creation(): void
    {
        $task = Task::factory()->create([
            'title' => 'Test Task',
        ]);

        $this->assertEquals('develop', $task->step);
        $this->assertEquals('pending', $task->status);
        $this->assertEquals('medium', $task->priority);
        $this->assertEquals('feature', $task->task_type);
        $this->assertEquals('list', $task->view_mode);
    }

    /** @test */
    public function it_belongs_to_an_agent(): void
    {
        $agent = Agent::create([
            'name' => 'dave',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        $task = Task::factory()->create([
            'assigned_to' => 'dave',
        ]);

        $this->assertEquals('dave', $task->assigned_to);
        $this->assertInstanceOf(Agent::class, $task->agent);
        $this->assertEquals('dave', $task->agent->name);
    }

    /** @test */
    public function it_has_activities(): void
    {
        $task = Task::factory()->create();

        AgentActivity::create([
            'task_id' => $task->id,
            'agent_name' => 'dave',
            'action' => 'started',
            'metadata_json' => json_encode(['note' => 'Beginning work']),
        ]);

        $this->assertCount(1, $task->activities);
        $this->assertEquals('started', $task->activities->first()->action);
    }

    /** @test */
    public function it_filters_by_assigned_agent(): void
    {
        Task::factory()->create(['assigned_to' => 'dave']);
        Task::factory()->create(['assigned_to' => 'sam']);
        Task::factory()->create(['assigned_to' => 'dave']);

        $daveTasks = Task::assignedTo('dave')->get();

        $this->assertCount(2, $daveTasks);
    }

    /** @test */
    public function it_filters_by_step(): void
    {
        Task::factory()->create(['step' => 'develop']);
        Task::factory()->create(['step' => 'qa']);
        Task::factory()->create(['step' => 'develop']);

        $developTasks = Task::inStep('develop')->get();

        $this->assertCount(2, $developTasks);
    }

    /** @test */
    public function it_filters_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'pending']);

        $pendingTasks = Task::withStatus('pending')->get();

        $this->assertCount(2, $pendingTasks);
    }

    /** @test */
    public function it_filters_available_tasks(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'complete']);
        Task::factory()->create(['status' => 'failed']);

        $available = Task::available()->get();

        $this->assertCount(2, $available);
        $this->assertEquals(['pending', 'in_progress'], $available->pluck('status')->sort()->values()->toArray());
    }

    /** @test */
    public function it_filters_tasks_completed_today(): void
    {
        Task::factory()->create([
            'status' => 'complete',
            'completed_at' => Carbon::today(),
        ]);

        Task::factory()->create([
            'status' => 'complete',
            'completed_at' => Carbon::yesterday(),
        ]);

        $todayTasks = Task::completedToday()->get();

        $this->assertCount(1, $todayTasks);
    }

    /** @test */
    public function it_filters_by_view_mode(): void
    {
        Task::factory()->create(['view_mode' => 'list']);
        Task::factory()->create(['view_mode' => 'board']);
        Task::factory()->create(['view_mode' => 'executive']);
        Task::factory()->create(['view_mode' => 'list']);

        $listTasks = Task::withViewMode('list')->get();

        $this->assertCount(2, $listTasks);
    }

    /** @test */
    public function it_calculates_progress_percentage(): void
    {
        $taskDevelop = Task::factory()->create(['step' => 'develop']);
        $taskQa = Task::factory()->create(['step' => 'qa']);
        $taskSecurity = Task::factory()->create(['step' => 'security']);
        $taskStaging = Task::factory()->create(['step' => 'staging']);
        $taskProduction = Task::factory()->create(['step' => 'production']);

        $this->assertEquals(20, $taskDevelop->progress_percentage);
        $this->assertEquals(40, $taskQa->progress_percentage);
        $this->assertEquals(60, $taskSecurity->progress_percentage);
        $this->assertEquals(80, $taskStaging->progress_percentage);
        $this->assertEquals(100, $taskProduction->progress_percentage);
    }

    /** @test */
    public function it_returns_priority_badge_class(): void
    {
        $critical = Task::factory()->create(['priority' => 'critical']);
        $high = Task::factory()->create(['priority' => 'high']);
        $medium = Task::factory()->create(['priority' => 'medium']);
        $low = Task::factory()->create(['priority' => 'low']);

        $this->assertStringContainsString('red', $critical->priority_badge_class);
        $this->assertStringContainsString('orange', $high->priority_badge_class);
        $this->assertStringContainsString('yellow', $medium->priority_badge_class);
        $this->assertStringContainsString('slate', $low->priority_badge_class);
    }

    /** @test */
    public function it_returns_status_badge_class(): void
    {
        $pending = Task::factory()->create(['status' => 'pending']);
        $inProgress = Task::factory()->create(['status' => 'in_progress']);
        $complete = Task::factory()->create(['status' => 'complete']);
        $failed = Task::factory()->create(['status' => 'failed']);
        $blocked = Task::factory()->create(['status' => 'blocked']);

        $this->assertStringContainsString('slate', $pending->status_badge_class);
        $this->assertStringContainsString('blue', $inProgress->status_badge_class);
        $this->assertStringContainsString('emerald', $complete->status_badge_class);
        $this->assertStringContainsString('red', $failed->status_badge_class);
        $this->assertStringContainsString('orange', $blocked->status_badge_class);
    }

    /** @test */
    public function it_returns_agent_display_name(): void
    {
        $dave = Task::factory()->create(['assigned_to' => 'dave']);
        $sam = Task::factory()->create(['assigned_to' => 'sam']);
        $chen = Task::factory()->create(['assigned_to' => 'chen']);
        $security = Task::factory()->create(['assigned_to' => 'security']);
        $unassigned = Task::factory()->create(['assigned_to' => null]);

        $this->assertEquals('Dave (Dev)', $dave->agent_display_name);
        $this->assertEquals('Sam (QA)', $sam->agent_display_name);
        $this->assertEquals('Chen (DevOps)', $chen->agent_display_name);
        $this->assertEquals('Security Bot', $security->agent_display_name);
        $this->assertEquals('Unassigned', $unassigned->agent_display_name);
    }

    /** @test */
    public function it_checks_if_ready_for_agent(): void
    {
        $pendingTask = Task::factory()->create([
            'assigned_to' => 'dave',
            'status' => 'pending',
        ]);

        $inProgressTask = Task::factory()->create([
            'assigned_to' => 'dave',
            'status' => 'in_progress',
        ]);

        $completeTask = Task::factory()->create([
            'assigned_to' => 'dave',
            'status' => 'complete',
        ]);

        $otherTask = Task::factory()->create([
            'assigned_to' => 'sam',
            'status' => 'pending',
        ]);

        $this->assertTrue($pendingTask->isReadyForAgent('dave'));
        $this->assertTrue($inProgressTask->isReadyForAgent('dave'));
        $this->assertFalse($completeTask->isReadyForAgent('dave'));
        $this->assertFalse($otherTask->isReadyForAgent('dave'));
    }

    /** @test */
    public function it_gets_next_step(): void
    {
        $develop = Task::factory()->create(['step' => 'develop']);
        $qa = Task::factory()->create(['step' => 'qa']);
        $security = Task::factory()->create(['step' => 'security']);
        $staging = Task::factory()->create(['step' => 'staging']);
        $production = Task::factory()->create(['step' => 'production']);

        $this->assertEquals('qa', $develop->getNextStep());
        $this->assertEquals('security', $qa->getNextStep());
        $this->assertEquals('staging', $security->getNextStep());
        $this->assertEquals('production', $staging->getNextStep());
        $this->assertNull($production->getNextStep());
    }

    /** @test */
    public function it_gets_next_assignee(): void
    {
        $develop = Task::factory()->create(['step' => 'develop']);
        $qa = Task::factory()->create(['step' => 'qa']);
        $security = Task::factory()->create(['step' => 'security']);
        $staging = Task::factory()->create(['step' => 'staging']);

        $this->assertEquals('sam', $develop->getNextAssignee());
        $this->assertEquals('security', $qa->getNextAssignee());
        $this->assertEquals('chen', $security->getNextAssignee());
        $this->assertEquals('chen', $staging->getNextAssignee());
    }

    /** @test */
    public function it_formats_created_at_human(): void
    {
        $task = Task::factory()->create(['created_at' => Carbon::now()->subHours(2)]);

        $this->assertStringContainsString('hours', $task->created_at_human);
    }

    /** @test */
    public function it_returns_view_mode_label(): void
    {
        $list = Task::factory()->create(['view_mode' => 'list']);
        $board = Task::factory()->create(['view_mode' => 'board']);
        $executive = Task::factory()->create(['view_mode' => 'executive']);

        $this->assertEquals('List View', $list->view_mode_label);
        $this->assertEquals('Board View', $board->view_mode_label);
        $this->assertEquals('Executive Summary', $executive->view_mode_label);
    }

    /** @test */
    public function it_returns_view_mode_icon(): void
    {
        $list = Task::factory()->create(['view_mode' => 'list']);
        $board = Task::factory()->create(['view_mode' => 'board']);
        $executive = Task::factory()->create(['view_mode' => 'executive']);

        $this->assertEquals('📋', $list->view_mode_icon);
        $this->assertEquals('/board', $board->view_mode_icon);
        $this->assertEquals('📊', $executive->view_mode_icon);
    }

    /** @test */
    public function it_gets_artifacts_safely(): void
    {
        $taskWithArtifacts = Task::factory()->create(['artifacts_json' => ['file1.txt', 'file2.txt']]);
        $taskWithoutArtifacts = Task::factory()->create(['artifacts_json' => null]);

        $this->assertEquals(['file1.txt', 'file2.txt'], $taskWithArtifacts->artifacts);
        $this->assertEquals([], $taskWithoutArtifacts->artifacts);
    }

    /** @test */
    public function it_casts_json_fields(): void
    {
        $task = Task::factory()->create([
            'context_json' => ['key' => 'value'],
            'artifacts_json' => ['file1', 'file2'],
        ]);

        $this->assertIsArray($task->context_json);
        $this->assertIsArray($task->artifacts_json);
    }

    /** @test */
    public function it_mass_assigns_fillable_fields(): void
    {
        $task = Task::create([
            'title' => 'Mass Assign Test',
            'description' => 'Testing mass assignment',
            'assigned_to' => 'dave',
            'status' => 'pending',
            'step' => 'develop',
            'priority' => 'high',
            'task_type' => 'feature',
            'view_mode' => 'board',
        ]);

        $this->assertEquals('Mass Assign Test', $task->title);
        $this->assertEquals('Testing mass assignment', $task->description);
        $this->assertEquals('dave', $task->assigned_to);
        $this->assertEquals('pending', $task->status);
        $this->assertEquals('develop', $task->step);
        $this->assertEquals('high', $task->priority);
        $this->assertEquals('feature', $task->task_type);
        $this->assertEquals('board', $task->view_mode);
    }
}
