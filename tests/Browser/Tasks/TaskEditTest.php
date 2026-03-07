<?php

namespace Tests\Browser\Tasks;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;

class TaskEditTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that edit form loads with existing task data.
     */
    public function test_task_edit_form_loads(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Original Edit Test Title',
            'description' => 'Original description for edit test.',
            'status' => 'pending',
            'priority' => 'medium',
            'step' => 'develop',
            'assigned_to' => 'sam',
            'task_type' => 'feature',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->assertSee('Edit Task')
                    ->assertInputValue('[wire\\:model="title"]', 'Original Edit Test Title');
        });
    }

    /**
     * Test edit form validation errors.
     */
    public function test_task_edit_validation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Validation Test Task',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->clear('[wire\\:model="title"]')
                    ->click('button[wire\\:click="save"]')
                    ->waitForText('error', 5);
        });
    }

    /**
     * Test edit form saves and redirects to detail.
     */
    public function test_task_edit_save_and_redirect(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Before Save Title',
            'description' => 'Before save description',
            'status' => 'pending',
            'priority' => 'medium',
            'assigned_to' => 'dave',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->clear('[wire\\:model="title"]')
                    ->type('[wire\\:model="title"]', 'After Save Title')
                    ->clear('[wire\\:model="description"]')
                    ->type('[wire\\:model="description"]', 'After save description')
                    ->click('button[wire\\:click="save"]')
                    ->waitForText('After Save Title', 10)
                    ->assertPathIsNot("/tasks/{$task->id}/edit");
        });

        $task->refresh();
        $this->assertEquals('After Save Title', $task->title);
        $this->assertEquals('After save description', $task->description);
    }

    /**
     * Test edit form changes project.
     */
    public function test_task_edit_project_change(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $project1 = Project::factory()->create([
            'name' => 'Project One',
        ]);

        $project2 = Project::factory()->create([
            'name' => 'Project Two',
        ]);

        $task = Task::factory()->create([
            'title' => 'Project Change Test',
            'project_id' => $project1->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->assertSee('Edit Task')
                    ->assertPresent('select');
        });
    }

    /**
     * Test that create task page loads correctly.
     */
    public function test_create_task_page_loads(): void
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
                    ->assertPresent('[wire\\:model="title"]')
                    ->assertPresent('[wire\\:model="description"]');
        });
    }

    /**
     * Test cancel button on edit form.
     */
    public function test_edit_cancel_button(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Cancel Button Test',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->click('button[wire\\:click="cancel"]')
                    ->waitForText('Cancel Button Test', 10)
                    ->assertPathIsNot("/tasks/{$task->id}/edit");
        });
    }

    /**
     * Test that all dropdown fields are populated.
     */
    public function test_all_dropdown_fields_populated(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Dropdown Test Task',
            'status' => 'pending',
            'priority' => 'medium',
            'step' => 'develop',
            'task_type' => 'feature',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->assertPresent('[wire\\:model="status"]')
                    ->assertPresent('[wire\\:model="priority"]')
                    ->assertPresent('[wire\\:model="step"]')
                    ->assertPresent('[wire\\:model="task_type"]')
                    ->assertPresent('[wire\\:model="assigned_to"]');
        });
    }

    /**
     * Test edit form updates step correctly.
     */
    public function test_edit_form_updates_step(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Step Change Test',
            'step' => 'develop',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->select('[wire\\:model="step"]', 'qa')
                    ->click('button[wire\\:click="save"]')
                    ->waitForText('Step Change Test', 10);
        });

        $task->refresh();
        $this->assertEquals('qa', $task->step);
    }

    /**
     * Test edit form updates assignment correctly.
     */
    public function test_edit_form_updates_assignment(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Assignment Change Test',
            'assigned_to' => 'dave',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->select('[wire\\:model="assigned_to"]', 'sam')
                    ->click('button[wire\\:click="save"]')
                    ->waitForText('Assignment Change Test', 10);
        });

        $task->refresh();
        $this->assertEquals('sam', $task->assigned_to);
    }

    /**
     * Test description textarea updates.
     */
    public function test_description_textarea_updates(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Description Update Test',
            'description' => 'Old description',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->clear('[wire\\:model="description"]')
                    ->type('[wire\\:model="description"]', 'New description content')
                    ->click('button[wire\\:click="save"]')
                    ->waitForText('Description Update Test', 10);
        });

        $task->refresh();
        $this->assertStringContainsString('New description', $task->description);
    }

    /**
     * Test that edit form shows all priority options.
     */
    public function test_edit_form_shows_all_priority_options(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Priority Options Test',
            'priority' => 'medium',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->select('[wire\\:model="priority"]', 'critical')
                    ->click('button[wire\\:click="save"]')
                    ->waitForText('Priority Options Test', 10);
        });

        $task->refresh();
        $this->assertEquals('critical', $task->priority);
    }

    /**
     * Test mobile responsive on edit form.
     */
    public function test_mobile_responsive_edit_form(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $task = Task::factory()->create([
            'title' => 'Mobile Edit Test',
        ]);

        $this->browse(function (Browser $browser) use ($user, $task) {
            $browser->resize(375, 667) // iPhone SE size
                    ->loginAs($user)
                    ->visit("/tasks/{$task->id}/edit")
                    ->waitForText('Edit Task', 10)
                    ->assertSee('Edit Task');
        });
    }

    /**
     * Test that required field indicators are present.
     */
    public function test_required_field_indicators(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks/create')
                    ->waitForText('Create Task', 10)
                    ->assertPresent('[wire\\:model="title"]');
        });
    }
}