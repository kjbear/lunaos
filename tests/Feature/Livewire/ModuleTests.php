<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Livewire\TaskManager;
use App\Livewire\OrgChart;
use App\Livewire\ActivityFeed;
use App\Livewire\Calendar;
use App\Livewire\GlobalSearch;
use App\Livewire\Standup;
use App\Livewire\WorkspaceViewer;
use App\Livewire\DocsViewer;
use App\Models\Agent;
use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class TaskManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_manager_loads_with_empty_state(): void
    {
        Livewire::test(TaskManager::class)
            ->assertStatus(200)
            ->assertSee('Tasks');
    }

    public function test_task_manager_displays_tasks(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        Task::create([
            'agent_id' => $agent->id,
            'title' => 'Test Task',
            'description' => 'Testing task display',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Livewire::test(TaskManager::class)
            ->assertStatus(200)
            ->assertSee('Test Task')
            ->assertSee('high');
    }

    public function test_task_manager_filters_by_status(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'model' => 'ollama-local/qwen3.5:cloud',
            'status' => 'active',
        ]);

        Task::create([
            'agent_id' => $agent->id,
            'title' => 'Pending Task',
            'status' => 'pending',
        ]);

        Task::create([
            'agent_id' => $agent->id,
            'title' => 'Completed Task',
            'status' => 'completed',
        ]);

        Livewire::test(TaskManager::class)
            ->set('filter', 'pending')
            ->assertStatus(200)
            ->assertSee('Pending Task')
            ->assertDontSee('Completed Task');
    }
}

class OrgChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_chart_loads(): void
    {
        Livewire::test(OrgChart::class)
            ->assertStatus(200)
            ->assertSee('Organization');
    }

    public function test_org_chart_displays_agents(): void
    {
        Agent::create([
            'name' => 'Luna',
            'role' => 'Chief of Staff',
            'model' => 'ollama-local/qwen3.5:397b-cloud',
            'status' => 'active',
        ]);

        Agent::create([
            'name' => 'Sam',
            'role' => 'QA Engineer',
            'model' => 'ollama-local/qwen3-coder-next:cloud',
            'status' => 'active',
        ]);

        Livewire::test(OrgChart::class)
            ->assertStatus(200)
            ->assertSee('Luna')
            ->assertSee('Sam')
            ->assertSee('QA Engineer');
    }
}

class ActivityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_feed_loads(): void
    {
        Livewire::test(ActivityFeed::class)
            ->assertStatus(200);
    }

    public function test_activity_feed_shows_activities(): void
    {
        $agent = Agent::create([
            'name' => 'test-agent',
            'role' => 'Developer',
            'status' => 'active',
        ]);

        ActivityLog::create([
            'agent_id' => $agent->id,
            'action' => 'task_started',
            'description' => 'Started working on feature',
        ]);

        Livewire::test(ActivityFeed::class)
            ->assertStatus(200)
            ->assertSee('task_started')
            ->assertSee('Started working on feature');
    }
}

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_loads(): void
    {
        Livewire::test(Calendar::class)
            ->assertStatus(200)
            ->assertSee('Calendar');
    }
}

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_loads(): void
    {
        Livewire::test(GlobalSearch::class)
            ->assertStatus(200)
            ->assertSee('Search');
    }

    public function test_global_search_finds_agents(): void
    {
        Agent::create([
            'name' => 'Sam',
            'role' => 'QA Engineer',
            'status' => 'active',
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Sam')
            ->assertStatus(200)
            ->assertSee('Sam')
            ->assertSee('QA Engineer');
    }
}

class StandupTest extends TestCase
{
    use RefreshDatabase;

    public function test_standup_loads(): void
    {
        Livewire::test(Standup::class)
            ->assertStatus(200);
    }
}

class WorkspaceViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_viewer_loads(): void
    {
        Livewire::test(WorkspaceViewer::class)
            ->assertStatus(200)
            ->assertSee('Workspace');
    }
}

class DocsViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_viewer_loads(): void
    {
        Livewire::test(DocsViewer::class)
            ->assertStatus(200)
            ->assertSee('Documentation');
    }
}
