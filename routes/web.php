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
use App\Models\Project;
use App\Models\ProjectAssignment;

// Route model binding for UUID-based models
Route::bind('project', function ($value) {
    return Project::where('id', $value)->firstOrFail();
});
Route::bind('assignment', function ($value) {
    return ProjectAssignment::where('id', $value)->firstOrFail();
});
use App\Livewire\Board\ExecutiveBoard;
use App\Livewire\Board\ExecutiveBoardWait;
use App\Http\Controllers\BoardSessionController;
use App\Http\Controllers\ProjectController;
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
// Note: Static routes MUST come before parameterized routes (/{task})
Route::view('/tasks', 'pages.tasks')->name('tasks');
Route::get('/tasks/board', TaskBoardUnified::class)->name('tasks.board');
Route::get('/tasks/executive', TaskExecutive::class)->name('tasks.executive');
Route::get('/tasks/list', TaskList::class)->name('tasks.list');
Route::get('/tasks/create', TaskEdit::class)->name('tasks.create');
Route::get('/tasks/{task}/edit', TaskEdit::class)->name('tasks.edit');
Route::get('/tasks/{task}', [\App\Livewire\TaskDetail::class, 'show'])->name('tasks.show');

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
Route::redirect('/hr', '/team?type=personas')->name('hr.redirect');
Route::get('/hr/{id}/workspace', function ($id) {
    return redirect()->route('team.show', $id);
})->name('hr.workspace');

Route::redirect('/agents', '/team?type=workers')->name('agents.redirect');

// Projects Module
Route::get('/projects', function() {
    return view('pages.projects');
})->name('projects');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/projects/{id}/requirements', function ($id) {
    return view('project-requirements', ['id' => $id]);
})->name('projects.requirements');

// Executive Board Module
Route::view('/board', 'board')->name('board');
Route::get('/tasks/executive/board', ExecutiveBoard::class)->name('tasks.executive.board');
Route::get('/tasks/executive/wait/{sessionId}', ExecutiveBoardWait::class)->name('tasks.executive.wait');
Route::get('/tasks/executive/board/{sessionId}', [BoardSessionController::class, 'show'])->name('tasks.executive.result');
Route::post('/tasks/executive/board/{sessionId}/create-project', [BoardSessionController::class, 'createProject'])->name('tasks.executive.create-project');
Route::delete('/tasks/executive/board/{sessionId}', [BoardSessionController::class, 'delete'])->name('tasks.executive.delete');

// Projects Module - Full CRUD + Actions
Route::get('/projects', function() {
    return view('pages.projects');
})->name('projects');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

// Project Actions
Route::post('/projects/{project}/repository', [ProjectController::class, 'createRepository'])->name('projects.repository.create');
Route::post('/projects/{project}/assignments', [ProjectController::class, 'assignAgent'])->name('projects.assignments.store');
Route::delete('/projects/{project}/assignments/{assignment}', [ProjectController::class, 'removeAgent'])->name('projects.assignments.destroy');
Route::post('/projects/{project}/artifacts/{type}', [ProjectController::class, 'storeArtifact'])->name('projects.artifacts.store');
Route::get('/projects/{id}/requirements', function ($id) {
    return view('project-requirements', ['id' => $id]);
})->name('projects.requirements');

// Test Status Dashboard
// Route::get('/tests', TestStatus::class)->name('tests');
Route::view('/tests', 'tests')->name('tests');

// Kanban Board (outside API group)
Route::view('/kanban', 'pages.kanban')->name('kanban.index');

// Auth routes (Breeze)
require __DIR__.'/auth.php';
