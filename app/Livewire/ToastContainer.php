<?php

namespace App\Livewire;

use Livewire\Component;

class ToastContainer extends Component
{
    public array $toasts = [];
    
    protected $listeners = [
        'toast' => 'addToast',
        'toast-success' => 'success',
        'toast-error' => 'error',
        'toast-info' => 'info',
        'toast-warning' => 'warning',
        'removeToast' => 'removeToast',
    ];
    
    public function addToast(string $message, string $type = 'info', int $duration = 4000): void
    {
        $id = uniqid('toast_');
        
        $this->toasts[$id] = [
            'id' => $id,
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
        ];
        
        // Auto-remove after duration
        $this->js("setTimeout(() => { Livewire.dispatch('removeToast', { id: '{$id}' }) }, {$duration})");
    }
    
    public function success(string $message, int $duration = 4000): void
    {
        $this->addToast($message, 'success', $duration);
    }
    
    public function error(string $message, int $duration = 6000): void
    {
        $this->addToast($message, 'error', $duration);
    }
    
    public function info(string $message, int $duration = 4000): void
    {
        $this->addToast($message, 'info', $duration);
    }
    
    public function warning(string $message, int $duration = 5000): void
    {
        $this->addToast($message, 'warning', $duration);
    }
    
    public function removeToast(string $id): void
    {
        unset($this->toasts[$id]);
    }
    
    public function render()
    {
        return view('livewire.toast-container');
    }
}
