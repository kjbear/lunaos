<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Navigation extends Component
{
    public bool $mobileOpen = false;
    
    public function mount()
    {
        // Initialize mobile state
        $this->mobileOpen = false;
    }
    
    public function render()
    {
        return view('livewire.layout.navigation');
    }
}
