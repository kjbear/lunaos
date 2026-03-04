<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\TaskManagerController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\WorkspaceController;
use App\Livewire\Counter;
use App\Livewire\TaskManager;
use App\Livewire\DocsViewer;
use App\Livewire\TaskList;
use App\Livewire\TaskBoardUnified;
use App\Livewire\TaskExecutive;
use App\Livewire\TaskEdit;
use App\Livewire\HR\PersonasIndex;
use App\Livewire\KanbanBoard;
use App\Livewire\HR\PersonaWorkspaceViewer;
use App\Livewire\Projects\ProjectsIndex;
use App\Livewire\Projects\ProjectRequirements;
use App\Livewire\Agents\AgentList;
use App\Livewire\Board\ExecutiveBoard;
use App\Livewire\TestStatus;

// Web routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tasks');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');

// Mission Control - Original vs Polished (for comparison)
Route::view('/mission-control', 'pages.mission-control-original')->name('mission-control.original');
Route::view('/mission-control-polished', 'pages.mission-control-polished')->name('mission-control.polished');

// Task routes
Route::view('/tasks', 'pages.tasks')->name('tasks');
Route::get('/tasks/board', TaskBoardUnified::class)->name('tasks.board');
Route::get('/tasks/executive', TaskExecutive::class)->name('tasks.executive');
Route::get('/tasks/create', TaskEdit::class)->name('tasks.create');
Route::get('/tasks/{task}/edit', TaskEdit::class)->name('tasks.edit');
Route::get('/tasks/{task}', [\App\Livewire\TaskDetail::class, 'show'])->name('tasks.show');
// Legacy routes for backwards compatibility (use view routes above instead)
Route::get('/tasks/list', TaskList::class)->name('tasks.list');

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

// Team Module (Unified - replaces HR and Agents)
use App\Http\Controllers\TeamController;

Route::get('/team', [TeamController::class, 'index'])->name('team');
Route::get('/team/create', [TeamController::class, 'create'])->name('team.create');
Route::post('/team', [TeamController::class, 'store'])->name('team.store');
Route::get('/team/{id}', [TeamController::class, 'show'])->name('team.show');
Route::get('/team/{id}/edit', [TeamController::class, 'edit'])->name('team.edit');
Route::put('/team/{id}', [TeamController::class, 'update'])->name('team.update');
Route::delete('/team/{id}', [TeamController::class, 'destroy'])->name('team.destroy');

// Legacy Routes - Redirect old HR and Agents routes to /team
Route::get('/hr', function () {
    return redirect()->route('team', ['tab' => 'personas']);
})->name('hr');
Route::get('/hr/{id}/workspace', function ($id) {
    return redirect()->route('team.show', $id);
})->name('hr.workspace');

Route::get('/agents', function () {
    return redirect()->route('team', ['tab' => 'workers']);
})->name('agents.index');
Route::get('/agents/create', function () {
    return redirect()->route('team');
})->name('agents.create');
Route::get('/agents/{id}/edit', function ($id) {
    return redirect()->route('team.show', $id);
})->name('agents.edit');

// Projects Module
Route::view('/projects', 'projects')->name('projects');
Route::get('/projects/{id}', function ($id) {
    return view('projects-detail', ['id' => $id]);
})->name('projects.show');
Route::get('/projects/{id}/requirements', function ($id) {
    return view('project-requirements', ['id' => $id]);
})->name('projects.requirements');

// Executive Board Module
use App\Livewire\Board\ExecutiveBoardWait;

Route::view('/board', 'board')->name('board');
Route::get('/tasks/executive/board', ExecutiveBoard::class)->name('tasks.executive.board');
Route::get('/tasks/executive/wait/{sessionId}', ExecutiveBoardWait::class)->name('tasks.executive.wait');
Route::get('/tasks/executive/board/{sessionId}', function ($sessionId) {
    $session = \App\Models\BoardSession::findOrFail($sessionId);
    return view('pages.executive-board-result', ['session' => $session]);
})->name('tasks.executive.result');

// Test Status Dashboard
// Route::get('/tests', TestStatus::class)->name('tests');
Route::view('/tests', 'tests')->name('tests');

// Kanban Board (outside API group)
Route::view('/kanban', 'pages.kanban')->name('kanban.index');

// Auth routes (Breeze)
require __DIR__.'/auth.php';
