<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

/**
 * Class HelloCounter
 *
 * A simple Livewire component that displays a numeric counter with
 * increment and decrement actions. The component is intended as a
 * demonstration of Livewire 3 basics and Tailwind CSS styling.
 */
class HelloCounter extends Component
{
    /**
     * The current counter value.
     */
    public int $counter = 0;

    /**
     * Increment the counter by one.
     */
    public function increment(): void
    {
        $this->counter++;
    }

    /**
     * Decrement the counter by one, ensuring it never goes below zero.
     */
    public function decrement(): void
    {
        if ($this->counter > 0) {
            $this->counter--;
        }
    }

    /**
     * Render the component view.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.hello-counter');
    }
}
