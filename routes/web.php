<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\TaskManagerController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\WorkspaceController;
use App\Livewire\Counter;
use App\Livewire\TaskManager;
use App\Livewire\DocsViewer;
use App\Livewire\HR\PersonasIndex;
use App\Livewire\KanbanBoard;
use App\Livewire\HR\PersonaWorkspaceViewer;
use App\Livewire\Projects\ProjectsIndex;
use App\Livewire\Projects\ProjectRequirements;
use App\Livewire\Board\ExecutiveBoard;

// Web routes
Route::get('/', function () {
    return view('lunaos-home');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');

// Mission Control - Original vs Polished (for comparison)
Route::view('/mission-control', 'pages.mission-control-original')->name('mission-control.original');
Route::view('/mission-control-polished', 'pages.mission-control-polished')->name('mission-control.polished');

// Task routes
Route::get('/tasks', fn() => redirect()->route('mission-control.polished'))->name('tasks');
Route::get('/tasks/{task}', \App\Livewire\TaskDetail::class)->name('tasks.show');
Route::get('/org-chart', function () {
    return view('org-chart');
})->name('org-chart');
Route::get('/workspace', function () {
    return view('workspace');
})->name('workspace');
Route::get('/calendar', function () {
    return view('calendar');
})->name('calendar');
Route::view('/docs', 'docs')->name('docs');
Route::get('/standup', function () {
    return view('standup');
})->name('standup');
Route::get('/activity', function () {
    return view('activity');
})->name('activity');

// HR Module
Route::view('/hr', 'hr')->name('hr');
Route::get('/hr/{id}/workspace', function ($id) {
    return view('hr-workspace', ['id' => $id]);
})->name('hr.workspace');

// Projects Module
Route::view('/projects', 'projects')->name('projects');
Route::get('/projects/{id}', function ($id) {
    return view('projects-detail', ['id' => $id]);
})->name('projects.show');
Route::get('/projects/{id}/requirements', function ($id) {
    return view('project-requirements', ['id' => $id]);
})->name('projects.requirements');

// Executive Board Module
Route::view('/board', 'board')->name('board');

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
    
    // Activity Ingest API (for OpenClaw webhook)
    Route::post('/activity/ingest', [\App\Http\Controllers\Api\ActivityIngestController::class, 'ingest'])->name('activity.ingest');
    Route::post('/activity/poll', [\App\Http\Controllers\Api\ActivityIngestController::class, 'poll'])->name('activity.poll');
    Route::get('/activity/health', [\App\Http\Controllers\Api\ActivityIngestController::class, 'health'])->name('activity.health');
    
});

// Kanban Board (outside API group)
Route::view('/kanban', 'pages.kanban')->name('kanban.index');