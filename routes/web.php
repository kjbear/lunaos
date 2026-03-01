<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login or tasks
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tasks');
    }
    return redirect()->route('login');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
