<?php

namespace Tests\Browser\Tasks;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class TaskListLoadTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that task list page loads successfully.
     */
    public function test_task_list_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tasks')
                    ->waitForText('Task Management')
                    ->assertSee('Task Management');
        });
    }
}
