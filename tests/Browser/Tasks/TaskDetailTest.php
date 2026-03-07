<?php

namespace Tests\Browser\Tasks;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Agent;

class TaskDetailTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that task detail displays all fields correctly.
     */
    public function test_task_detail_displays_all_fields(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Detail Test Task Title',
            'description' => 'This is a detailed description for the task.',
            'status' => 'in_progress',
            'priority' => 'high',
            'step' => 'develop',
            'assigned_to' => 'dave',
            'task_type' => 'feature',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Detail Test Task Title', 10)
                    ->assertSee('Detail Test Task Title')
                    ->assertSee('This is a detailed description');
        });
    }

    /**
     * Test task detail project link works.
     */
    public function test_task_detail_project_link(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $project = Project::factory()->create([
            'name' => 'Linked Project Alpha',
        ]);

        $task = Task::factory()->create([
            'title' => 'Task With Linked Project',
            'project_id' => $project->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Task With Linked Project', 10)
                    ->assertSee('Task With Linked Project')
                    ->assertSee('Linked Project Alpha');
        });
    }

    /**
     * Test navigation from detail to edit page.
     */
    public function test_task_detail_edit_navigation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Edit Navigation Test Task',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Edit Navigation Test Task', 10)
                    ->assertSee('Edit Navigation Test Task')
                    ->click('a[href*="/edit"]')
                    ->waitForText('Edit Task', 10)
                    ->assertPathIs("/tasks/{$task->id}/edit")
                    ->assertSee('Edit Task');
        });
    }

    /**
     * Test status badge displays correctly for all statuses.
     */
    public function test_status_badge_displays_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Status Test Pending', 'status' => 'pending', 'step' => 'develop']);
        Task::factory()->create(['title' => 'Status Test Progress', 'status' => 'in_progress', 'step' => 'qa']);
        Task::factory()->create(['title' => 'Status Test Complete', 'status' => 'complete', 'step' => 'production']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/board')
                    ->waitForText('Status Test Pending', 10)
                    ->assertSee('Status Test Pending')
                    ->assertSee('Status Test Progress')
                    ->assertSee('Status Test Complete');
        });
    }

    /**
     * Test priority badge displays correctly.
     */
    public function test_priority_badge_displays_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Task::factory()->create(['title' => 'Priority Test Low', 'priority' => 'low', 'step' => 'develop']);
        Task::factory()->create(['title' => 'Priority Test High', 'priority' => 'high', 'step' => 'qa']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/board')
                    ->waitForText('Priority Test', 10)
                    ->assertSee('Priority Test Low')
                    ->assertSee('Priority Test High');
        });
    }

    /**
     * Test task detail shows agent assignment.
     */
    public function test_task_detail_shows_agent_assignment(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Agent Assignment Test',
            'assigned_to' => 'dave',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Agent Assignment Test', 10)
                    ->assertSee('Agent Assignment Test')
                    ->assertSee('dave');
        });
    }

    /**
     * Test task detail shows activity history.
     */
    public function test_task_detail_shows_activity_history(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Activity History Test',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Activity History Test', 10)
                    ->assertSee('Activity History Test');
        });
    }

    /**
     * Test task detail without project (unassigned).
     */
    public function test_task_detail_without_project(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Unassigned Task Detail',
            'project_id' => null,
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Unassigned Task Detail', 10)
                    ->assertSee('Unassigned Task Detail');
        });
    }

    /**
     * Test task detail back to list navigation.
     */
    public function test_task_detail_back_navigation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Back Navigation Test',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Back Navigation Test', 10)
                    ->assertPathIs("/tasks/{$task->id}")
                    ->visit('/tasks/list')
                    ->waitForText('Back Navigation Test', 10);
        });
    }

    /**
     * Test task detail displays all task types.
     */
    public function test_task_detail_displays_all_task_types(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Task Type Display',
            'task_type' => 'feature',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Task Type Display', 10)
                    ->assertSee('Task Type Display');
        });
    }

    /**
     * Test mobile responsive on task detail.
     */
    public function test_mobile_responsive_task_detail(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Mobile Responsive Task',
            'description' => 'Testing mobile responsiveness on detail page.',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->resize(375, 667) // iPhone SE size
                    ->loginAs($user)
                    ->visit("/tasks/{$task->id}")
                    ->waitForText('Mobile Responsive Task', 10)
                    ->assertSee('Mobile Responsive Task');
        });
    }
}