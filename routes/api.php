<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\TeamController;

// API routes for task management (unprotected for internal use)
// Note: Laravel's API routes automatically get /api prefix from bootstrap/app.php
Route::name('api.')->group(function () {
    
    // Health check endpoint
    Route::get('/status', StatusController::class)->name('status');

    // ==========================================
    // PROJECT MANAGEMENT API
    // ==========================================
    
    // List all projects with filtering, pagination, and sorting
    // GET /api/projects
    // Query params: status, health, architecture_type, repository_id, project_manager_id,
    //               search, created_after, created_before, sort, direction, per_page, page, with_trashed
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    
    // Get project statistics
    // GET /api/projects/stats
    Route::get('/projects/stats', [ProjectController::class, 'stats'])->name('projects.stats');
    
    // Get filter options
    // GET /api/projects/filters
    Route::get('/projects/filters', [ProjectController::class, 'filters'])->name('projects.filters');
    
    // Create a new project
    // POST /api/projects
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    
    // Show a specific project
    // GET /api/projects/{project}
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    
    // Update a project
    // PUT /api/projects/{project}
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    
    // Soft delete a project (archive)
    // DELETE /api/projects/{project}
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
    // Restore a soft-deleted project
    // POST /api/projects/{id}/restore
    Route::post('/projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    
    // Permanently delete a project
    // DELETE /api/projects/{id}/force
    Route::delete('/projects/{id}/force', [ProjectController::class, 'forceDelete'])->name('projects.force-delete');
    
    // Assign an agent to a project
    // POST /api/projects/{project}/agents
    Route::post('/projects/{project}/agents', [ProjectController::class, 'assignAgent'])->name('projects.agents.assign');
    
    // Remove an agent from a project
    // DELETE /api/projects/{project}/agents/{agent}
    Route::delete('/projects/{project}/agents/{agent}', [ProjectController::class, 'removeAgent'])->name('projects.agents.remove');

    // ==========================================
    // TASK MANAGEMENT API (Unified Endpoints)
    // ==========================================
    
    // List all tasks with filtering, pagination, and sorting
    // GET /api/tasks
    // Query params: status, assigned_to, priority, task_type, step, view_mode, 
    //               repository_id, created_after, created_before, search, 
    //               sort, direction, per_page, page
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    
    // Get tasks for a specific view mode
    // GET /api/tasks/view-modes/{viewMode}
    // URL params: viewMode (list|board|executive)
    // Query params: same as index + filtering
    Route::get('/tasks/view-modes/{viewMode}', [TaskController::class, 'viewMode'])
        ->name('tasks.view-modes')
        ->where('viewMode', 'list|board|executive');
    
    // Get filter options
    // GET /api/tasks/filters
    Route::get('/tasks/filters', [TaskController::class, 'filters'])->name('tasks.filters');
    
    // Get task statistics
    // GET /api/tasks/stats
    Route::get('/tasks/stats', [TaskController::class, 'stats'])->name('tasks.stats');
    
    // Create a new task
    // POST /api/tasks
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    
    // Show a specific task
    // GET /api/tasks/{task}
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    
    // Update a task
    // PUT /api/tasks/{task}
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    
    // Delete a task
    // DELETE /api/tasks/{task}
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    
    // Bulk update tasks
    // PUT /api/tasks/bulk
    Route::put('/tasks/bulk', [TaskController::class, 'bulkUpdate'])->name('tasks.bulk-update');
    
    // Bulk delete tasks
    // DELETE /api/tasks/bulk
    Route::delete('/tasks/bulk', [TaskController::class, 'bulkDestroy'])->name('tasks.bulk-destroy');

    // ==========================================
    // ORG CHART API
    // ==========================================
    Route::get('/org-chart', [OrgChartController::class, 'index'])->name('org-chart.index');
    Route::get('/org-chart/stats', [OrgChartController::class, 'stats'])->name('org-chart.stats');
    Route::get('/org-chart/health', [OrgChartController::class, 'health'])->name('org-chart.health');
    Route::get('/org-chart/{agent}', [OrgChartController::class, 'show'])->name('org-chart.show');

    // ==========================================
    // WORKSPACE API
    // ==========================================
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspace/{path}', [WorkspaceController::class, 'show'])
        ->name('workspace.show')
        ->where('path', '.*');
    
    // ==========================================
    // ACTIVITY INGEST API (for OpenClaw webhook)
    // ==========================================
    Route::post('/activity/ingest', [\App\Http\Controllers\Api\ActivityIngestController::class, 'ingest'])
        ->name('activity.ingest');
    Route::post('/activity/poll', [\App\Http\Controllers\Api\ActivityIngestController::class, 'poll'])
        ->name('activity.poll');
    Route::get('/activity/health', [\App\Http\Controllers\Api\ActivityIngestController::class, 'health'])
        ->name('activity.health');

    // ==========================================
    // BOARD API (Executive Board Feature)
    // ==========================================
    Route::prefix('board')->name('board.')->group(function () {
        // Board sessions
        Route::post('/sessions', [BoardController::class, 'createSession'])->name('sessions.create');
        Route::get('/sessions', [BoardController::class, 'listSessions'])->name('sessions.index');
        Route::get('/sessions/{sessionId}', [BoardController::class, 'getSession'])->name('sessions.show');
        Route::get('/sessions/{sessionId}/status', [BoardController::class, 'getSessionStatus'])->name('sessions.status');
        Route::get('/sessions/{sessionId}/responses', [BoardController::class, 'getResponses'])->name('sessions.responses');
        Route::post('/sessions/{sessionId}/start', [BoardController::class, 'startProcessing'])->name('sessions.start');
        Route::post('/sessions/{sessionId}/round', [BoardController::class, 'runRound'])->name('sessions.round');
        Route::post('/sessions/{sessionId}/consolidate', [BoardController::class, 'consolidateDecision'])->name('sessions.consolidate');
        Route::get('/sessions/{sessionId}/transcript', [BoardController::class, 'getTranscript'])->name('sessions.transcript');
        Route::delete('/sessions/{sessionId}', [BoardController::class, 'closeSession'])->name('sessions.close');
    });

    // ==========================================
    // TEAM API
    // ==========================================
    Route::prefix('team')->name('team.')->group(function () {
        // API routes (defined first to match before generic routes)
        Route::get('/', [TeamController::class, 'apiIndex'])->name('api.index');
        Route::get('/{team}', [TeamController::class, 'apiShow'])->name('api.show');
        Route::post('/', [TeamController::class, 'apiStore'])->name('api.store');
        Route::put('/{team}', [TeamController::class, 'apiUpdate'])->name('api.update');
        Route::delete('/{team}', [TeamController::class, 'apiDestroy'])->name('api.destroy');
        Route::get('/{team}/members', [TeamController::class, 'members'])->name('api.members');
    });
});
