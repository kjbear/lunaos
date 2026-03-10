<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use Livewire\Livewire;
use Tests\TestCase;

class HelloWorldTest extends TestCase
{
    /**
     * Test that the component renders correctly.
     * 
     * @return void
     */
    public function test_component_renders(): void
    {
        $this->get('/')
            ->assertStatus(200);
    }

    /**
     * Test that the component displays the default greeting.
     * 
     * @return void
     */
    public function test_displays_default_greeting(): void
    {
        Livewire::test('hello-world')
            ->assertSee('Hello, World!');
    }

    /**
     * Test that updating the name works correctly.
     * 
     * @return void
     */
    public function test_can_update_name(): void
    {
        Livewire::test('hello-world')
            ->set('name', 'Luna')
            ->assertSee('Hello, Luna!');
    }

    /**
     * Test that special name triggers bonus message.
     * 
     * @return void
     */
    public function test_special_name_shows_bonus_message(): void
    {
        Livewire::test('hello-world')
            ->set('name', 'Dave')
            ->assertSee('Welcome back, Dave!');
    }
}
