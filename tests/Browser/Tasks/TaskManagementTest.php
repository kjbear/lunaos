<?php

namespace Tests\Browser\Tasks;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Agent;

class TaskManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test that the task list loads with data.
     */
    public function test_can_view_task_list(): void
    {
        // Create test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create some test tasks
        Task::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->assertSee('Task Management');
        });
    }

    /**
     * Test that task details page loads.
     */
    public function test_can_view_task_details(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Test Task',
            'description' => 'This is a test task description.',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->click('@task-row-'.$task->id)
                    ->waitForText('Task Details')
                    ->assertSee('Task Details')
                    ->assertSee($task->title);
        });
    }

    /**
     * Test that create task form submits successfully.
     */
    public function test_can_create_task(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->click('@create-task-btn')
                    ->waitForText('Create Task')
                    ->assertSee('Create Task')
                    ->type('@task-title', 'New Task from Dusk Test')
                    ->type('@task-description', 'This task was created via Dusk browser test.')
                    ->select('@task-priority', 'high')
                    ->click('@save-task-btn')
                    ->waitForText('New Task from Dusk Test')
                    ->assertSee('New Task from Dusk Test');
        });
    }

    /**
     * Test that view mode switcher changes view.
     */
    public function test_can_switch_view_modes(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->assertSee('Task Management')
                    // Test list view
                    ->click('@view-mode-list')
                    ->waitForText('List View')
                    ->assertSee('List View')
                    // Test board view
                    ->click('@view-mode-board')
                    ->waitForText('Board View')
                    ->assertSee('Board View')
                    // Test executive view
                    ->click('@view-mode-executive')
                    ->waitForText('Executive View')
                    ->assertSee('Executive View');
        });
    }

    /**
     * Test that edit task updates data.
     */
    public function test_can_edit_task(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original description',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->click('@task-row-'.$task->id)
                    ->waitForText('Task Details')
                    ->assertSee('Original Title')
                    ->click('@edit-task-btn')
                    ->waitForText('Edit Task')
                    ->assertSee('Edit Task')
                    ->clear('@task-title')
                    ->type('@task-title', 'Updated Title')
                    ->clear('@task-description')
                    ->type('@task-description', 'Updated description')
                    ->select('@task-status', 'in_progress')
                    ->click('@save-task-btn')
                    ->waitForText('Updated Title')
                    ->assertSee('Updated Title')
                    ->assertSee('Updated description');
        });
    }

    /**
     * Test that tasks load with proper status badges.
     */
    public function test_task_list_displays_status_badges(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create([
            'title' => 'Pending Task',
            'status' => 'pending',
        ]);

        Task::factory()->create([
            'title' => 'In Progress Task',
            'status' => 'in_progress',
        ]);

        Task::factory()->create([
            'title' => 'Complete Task',
            'status' => 'complete',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->assertSee('Pending Task')
                    ->assertSee('In Progress Task')
                    ->assertSee('Complete Task');
        });
    }

    /**
     * Test that filter functionality works.
     */
    public function test_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'complete']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->assertSee('Pending')
                    ->select('@status-filter', 'pending')
                    ->waitForText('1 task')
                    ->assertDontSee('In Progress')
                    ->assertDontSee('Complete');
        });
    }

    /**
     * Test that search functionality works.
     */
    public function test_can_search_tasks(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Search Test Task Alpha']);
        Task::factory()->create(['title' => 'Search Test Task Beta']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->type('@task-search', 'Alpha')
                    ->waitForText('Search Test Task Alpha')
                    ->assertSee('Search Test Task Alpha')
                    ->assertDontSee('Search Test Task Beta');
        });
    }

    /**
     * Test that sorting functionality works.
     */
    public function test_can_sort_tasks(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['priority' => 'low']);
        Task::factory()->create(['priority' => 'high']);
        Task::factory()->create(['priority' => 'medium']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->click('@sort-priority')
                    ->waitForText('Task Management');
        });
    }

    /**
     * Test that pagination works correctly.
     */
    public function test_task_list_has_pagination(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create more than page limit
        Task::factory()->count(25)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->assertSee('Task Management')
                    ->assertVisible('@pagination');
        });
    }
}
