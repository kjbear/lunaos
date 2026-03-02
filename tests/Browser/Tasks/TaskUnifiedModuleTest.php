<?php

namespace Tests\Browser\Tasks;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Agent;

class TaskUnifiedModuleTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that task list loads with data.
     */
    public function test_task_list_loads_with_data(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create test tasks
        Task::factory()->count(5)->create([
            'title' => 'Browser Test Task',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->assertSee('Task')
                    ->assertSee('Browser Test Task')
                    ->assertSee('pending')
                    ->assertSee('medium');
        });
    }

    /**
     * Test that task details page displays correctly.
     */
    public function test_task_details_page_displays_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Details Test Task',
            'description' => 'This is a comprehensive test task description.',
            'status' => 'in_progress',
            'priority' => 'high',
            'step' => 'develop',
            'assigned_to' => 'dave',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Details Test Task', 10)
                    ->assertSee('Details Test Task')
                    ->assertSee('This is a comprehensive test task description')
                    ->assertSee('in_progress')
                    ->assertSee('high')
                    ->assertSee('develop')
                    ->assertSee('dave');
        });
    }

    /**
     * Test that create task form submits successfully.
     */
    public function test_create_task_form_submits_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/create')
                    ->waitForText('Create Task', 10)
                    ->assertSee('Create Task')
                    ->type('input[wire\\:model="title"]', 'New Task from Browser Test')
                    ->type('textarea[wire\\:model="description"]', 'This task was created via Dusk browser test.')
                    ->select('[wire\\:model="assigned_to"]', 'dave')
                    ->select('[wire\\:model="step"]', 'develop')
                    ->select('[wire\\:model="status"]', 'pending')
                    ->select('[wire\\:model="priority"]', 'high')
                    ->select('[wire\\:model="task_type"]', 'feature')
                    ->click('button[type="submit"]')
                    ->waitForText('New Task from Browser Test', 10)
                    ->assertPathIsNot('/tasks/create')
                    ->assertSee('New Task from Browser Test');
        });

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task from Browser Test',
            'assigned_to' => 'dave',
            'priority' => 'high',
        ]);
    }

    /**
     * Test that view mode switcher changes view without page reload.
     */
    public function test_view_mode_switcher_changes_view_without_page_reload(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    
                    // Already in list view by default
                    ->assertPathIs('/tasks')
                    
                    // Switch to board view
                    ->clickLink('Board')
                    ->waitForLocationChange(10)
                    ->assertPathIs('/tasks/board')
                    ->assertSee('Task Board')
                    
                    // Switch to executive view
                    ->clickLink('Executive')
                    ->waitForLocationChange(10)
                    ->assertPathIs('/tasks/executive')
                    ->assertSee('Executive')
                    
                    // Back to list view
                    ->clickLink('List')
                    ->waitForLocationChange(10)
                    ->assertPathIs('/tasks')
                    ->assertSee('Task');
        });
    }

    /**
     * Test that edit task updates and saves data.
     */
    public function test_edit_task_updates_and_saves_data(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original description',
            'status' => 'pending',
            'priority' => 'medium',
            'assigned_to' => 'sam',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->assertSee('Edit Task')
                    
                    // Update fields
                    ->clear('input[wire\\:model="title"]')
                    ->type('input[wire\\:model="title"]', 'Updated Title from Browser')
                    ->clear('textarea[wire\\:model="description"]')
                    ->type('textarea[wire\\:model="description"]', 'Updated description via browser test')
                    ->select('[wire\\:model="status"]', 'in_progress')
                    ->select('[wire\\:model="priority"]', 'critical')
                    ->select('[wire\\:model="assigned_to"]', 'chen')
                    
                    // Save
                    ->click('button[type="submit"]')
                    ->waitForText('Updated Title from Browser', 10)
                    ->assertPathIsNot("/tasks/{$task->id}/edit");
        });

        // Verify changes persisted
        $task->refresh();
        $this->assertEquals('Updated Title from Browser', $task->title);
        $this->assertEquals('Updated description via browser test', $task->description);
        $this->assertEquals('in_progress', $task->status);
        $this->assertEquals('critical', $task->priority);
        $this->assertEquals('chen', $task->assigned_to);
    }

    /**
     * Test that task list displays status badges correctly.
     */
    public function test_task_list_displays_status_badges_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Pending Task', 'status' => 'pending']);
        Task::factory()->create(['title' => 'In Progress Task', 'status' => 'in_progress']);
        Task::factory()->create(['title' => 'Complete Task', 'status' => 'complete']);
        Task::factory()->create(['title' => 'Failed Task', 'status' => 'failed']);
        Task::factory()->create(['title' => 'Blocked Task', 'status' => 'blocked']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->assertSee('Pending Task')
                    ->assertSee('In Progress Task')
                    ->assertSee('Complete Task')
                    ->assertSee('Failed Task')
                    ->assertSee('Blocked Task');
        });
    }

    /**
     * Test that task list displays priority badges correctly.
     */
    public function test_task_list_displays_priority_badges_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Critical Priority', 'priority' => 'critical']);
        Task::factory()->create(['title' => 'High Priority', 'priority' => 'high']);
        Task::factory()->create(['title' => 'Medium Priority', 'priority' => 'medium']);
        Task::factory()->create(['title' => 'Low Priority', 'priority' => 'low']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->assertSee('Critical Priority')
                    ->assertSee('High Priority')
                    ->assertSee('Medium Priority')
                    ->assertSee('Low Priority');
        });
    }

    /**
     * Test that search functionality works.
     */
    public function test_search_functionality_works(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Search Test Alpha', 'description' => 'Alpha description']);
        Task::factory()->create(['title' => 'Search Test Beta', 'description' => 'Beta description']);
        Task::factory()->create(['title' => 'Unrelated Task', 'description' => 'Other']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->type('input[wire\\:model="search"]', 'Alpha')
                    ->waitForText('Search Test Alpha', 5)
                    ->assertSee('Search Test Alpha')
                    ->assertDontSee('Search Test Beta')
                    ->assertDontSee('Unrelated Task');
        });
    }

    /**
     * Test that status filter works.
     */
    public function test_status_filter_works(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Pending Task 1', 'status' => 'pending']);
        Task::factory()->create(['title' => 'Pending Task 2', 'status' => 'pending']);
        Task::factory()->create(['title' => 'Completed Task', 'status' => 'complete']);
        Task::factory()->create(['title' => 'In Progress Task', 'status' => 'in_progress']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->select('[wire\\:model="statusFilter"]', 'pending')
                    ->waitForText('Pending Task', 5)
                    ->assertSee('Pending Task')
                    ->assertDontSee('Completed Task')
                    ->assertDontSee('In Progress Task');
        });
    }

    /**
     * Test that priority filter works.
     */
    public function test_priority_filter_works(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'High Priority 1', 'priority' => 'high']);
        Task::factory()->create(['title' => 'High Priority 2', 'priority' => 'high']);
        Task::factory()->create(['title' => 'Medium Priority', 'priority' => 'medium']);
        Task::factory()->create(['title' => 'Low Priority', 'priority' => 'low']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->select('[wire\\:model="priorityFilter"]', 'high')
                    ->waitForText('High Priority', 5)
                    ->assertSee('High Priority')
                    ->assertDontSee('Medium Priority')
                    ->assertDontSee('Low Priority');
        });
    }

    /**
     * Test that Kanban board view loads.
     */
    public function test_kanban_board_view_loads(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['step' => 'develop', 'status' => 'pending']);
        Task::factory()->create(['step' => 'qa', 'status' => 'in_progress']);
        Task::factory()->create(['step' => 'security', 'status' => 'pending']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/board')
                    ->waitForText('Task Board', 10)
                    ->assertSee('Task Board')
                    ->assertSee('Develop')
                    ->assertSee('QA')
                    ->assertSee('Security')
                    ->assertSee('Staging')
                    ->assertSee('Production');
        });
    }

    /**
     * Test that Executive view loads with metrics.
     */
    public function test_executive_view_loads_with_metrics(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->count(5)->create(['status' => 'pending']);
        Task::factory()->count(3)->create(['status' => 'in_progress']);
        Task::factory()->count(2)->create(['status' => 'complete']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/executive')
                    ->waitForText('Executive', 10)
                    ->assertSee('Executive')
                    ->assertSee('Metrics')
                    ->assertSee('Overview');
        });
    }

    /**
     * Test that task navigation works from list to detail.
     */
    public function test_task_navigation_from_list_to_detail(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Navigation Test Task',
            'description' => 'Testing navigation',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->clickLink('Navigation Test Task')
                    ->waitForText('Navigation Test Task', 10)
                    ->assertPathIs("/tasks/{$task->id}")
                    ->assertSee('Navigation Test Task');
        });
    }

    /**
     * Test that task can be deleted.
     */
    public function test_task_can_be_deleted(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Delete Test Task',
        ]);

        $taskId = $task->id;

        $this->browse(function (Browser $browser) use ($user, $taskId) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$taskId}")
                    ->waitForText('Delete Test Task', 10)
                    ->click('button:contains("Delete")')
                    ->acceptDialog()
                    ->waitForLocationChange(10)
                    ->assertPathIsNot("/tasks/{$taskId}");
        });

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    /**
     * Test that flash messages appear on success.
     */
    public function test_flash_messages_appear_on_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/create')
                    ->waitForText('Create Task', 10)
                    ->type('input[wire\\:model="title"]', 'Success Message Test')
                    ->type('textarea[wire\\:model="description"]', 'Testing success message')
                    ->click('button[type="submit"]')
                    ->waitForText('successfully', 10)
                    ->assertSee('successfully');
        });
    }

    /**
     * Test that validation errors display on invalid input.
     */
    public function test_validation_errors_display_on_invalid_input(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/create')
                    ->waitForText('Create Task', 10)
                    ->type('input[wire\\:model="title"]', str_repeat('a', 300))
                    ->click('button[type="submit"]')
                    ->waitForText('error', 5)
                    ->assertPresent('@error');
        });
    }

    /**
     * Test responsive design on mobile viewport.
     */
    public function test_responsive_design_on_mobile_viewport(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 667) // iPhone SE size
                    ->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->assertSee('Task')
                    ->assertVisible('body');
        });
    }

    /**
     * Test that pagination works.
     */
    public function test_pagination_works(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create more than one page worth of tasks
        Task::factory()->count(25)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task', 10)
                    ->assertSee('Task')
                    ->assertVisible('nav[aria-label="Pagination"]');
        });
    }
}
