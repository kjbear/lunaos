<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

/**
 * Class HelloWorld
 * 
 * A simple Livewire component that demonstrates basic functionality.
 * Displays a greeting message with user interaction.
 * 
 * @package App\Livewire
 */
class HelloWorld extends Component
{
    /**
     * The name to display in the greeting.
     * 
     * @var string
     */
    public string $name = 'World';

    /**
     * Update the name property when user types in the input.
     * 
     * @param  string  $name  The new name value
     * @return void
     */
    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Render the component view.
     * 
     * @return \Illuminate\View\View
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.hello-world');
    }
}
