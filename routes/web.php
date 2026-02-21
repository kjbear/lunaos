<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\TaskManagerController;
use App\Livewire\Counter;

// Web routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');

// API routes (unprotected for HTMX)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/status', StatusController::class)->name('status');

    // Task Manager API
    Route::get('/tasks', [TaskManagerController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/stats', [TaskManagerController::class, 'stats'])->name('tasks.stats');
    Route::get('/tasks/filters', [TaskManagerController::class, 'filters'])->name('tasks.filters');
    Route::get('/tasks/{task}', [TaskManagerController::class, 'show'])->name('tasks.show');
});