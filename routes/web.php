<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\TaskManagerController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\WorkspaceController;
use App\Livewire\Counter;
use App\Livewire\TaskManager;

// Web routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');
Route::get('/tasks', TaskManager::class)->name('tasks');
Route::get('/org-chart', function () {
    return view('org-chart');
})->name('org-chart');
Route::get('/workspace', function () {
    return view('workspace');
})->name('workspace');
Route::get('/calendar', function () {
    return view('calendar');
})->name('calendar');

// API routes (unprotected for HTMX)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/status', StatusController::class)->name('status');

    // Task Manager API
    Route::get('/tasks', [TaskManagerController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/stats', [TaskManagerController::class, 'stats'])->name('tasks.stats');
    Route::get('/tasks/filters', [TaskManagerController::class, 'filters'])->name('tasks.filters');
    Route::get('/tasks/{task}', [TaskManagerController::class, 'show'])->name('tasks.show');

    // Org Chart API
    Route::get('/org-chart', [OrgChartController::class, 'index'])->name('org-chart.index');
    Route::get('/org-chart/stats', [OrgChartController::class, 'stats'])->name('org-chart.stats');
    Route::get('/org-chart/health', [OrgChartController::class, 'health'])->name('org-chart.health');
    Route::get('/org-chart/{agent}', [OrgChartController::class, 'show'])->name('org-chart.show');

    // Workspace API
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspace/{path}', [WorkspaceController::class, 'show'])->name('workspace.show')->where('path', '.*');
});