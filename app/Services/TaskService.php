<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Task Service Layer
 * 
 * Handles all business logic for task management including:
 * - CRON operations
 * - Task workflow management
 * - View mode configuration
 * - Task statistics and reporting
 */
class TaskService
{
    /**
     * Valid view modes for tasks
     */
    public const VIEW_MODES = [
        'list',      // Standard list view with cards
        'board',     // Kanban board view
        'executive', // Executive summary view
    ];

    /**
     * Default view mode
     */
    public const DEFAULT_VIEW_MODE = 'list';

    /**
     * Get all tasks with optional filters
     */
    public function getAllTasks(array $filters = []): LengthAwarePaginator
    {
        $query = Task::with('agent', 'repository');

        // Apply filters
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['view_mode']) && $filters['view_mode'] !== 'all') {
            $query->where('view_mode', $filters['view_mode']);
        }

        if (isset($filters['agent']) && $filters['agent']) {
            $query->where('assigned_to', $filters['agent']);
        }

        if (isset($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['step']) && $filters['step']) {
            $query->where('step', $filters['step']);
        }

        // Default ordering
        $query->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get tasks for a specific view mode
     */
    public function getTasksByViewMode(string $viewMode, int $perPage = 20): LengthAwarePaginator
    {
        $validViewModes = self::VIEW_MODES;

        if (!in_array($viewMode, $validViewModes)) {
            $viewMode = self::DEFAULT_VIEW_MODE;
        }

        return Task::where('view_mode', $viewMode)
            ->with('agent', 'repository')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new task
     */
    public function createTask(array $data): Model
    {
        $data['view_mode'] = $data['view_mode'] ?? self::DEFAULT_VIEW_MODE;
        
        // Validate view mode
        if (!in_array($data['view_mode'], self::VIEW_MODES)) {
            $data['view_mode'] = self::DEFAULT_VIEW_MODE;
        }

        return Task::create($data);
    }

    /**
     * Update an existing task
     */
    public function updateTask(Task $task, array $data): bool
    {
        // Validate view mode if being updated
        if (isset($data['view_mode']) && !in_array($data['view_mode'], self::VIEW_MODES)) {
            $data['view_mode'] = self::DEFAULT_VIEW_MODE;
        }

        return $task->update($data);
    }

    /**
     * Delete a task
     */
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Get task statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed' => Task::where('status', 'completed')->count(),
            'failed' => Task::where('status', 'failed')->count(),
            'list_view' => Task::where('view_mode', 'list')->count(),
            'board_view' => Task::where('view_mode', 'board')->count(),
            'executive_view' => Task::where('view_mode', 'executive')->count(),
            'by_priority' => [
                'critical' => Task::where('priority', 'critical')->count(),
                'high' => Task::where('priority', 'high')->count(),
                'medium' => Task::where('priority', 'medium')->count(),
                'low' => Task::where('priority', 'low')->count(),
            ],
            'by_step' => [
                'develop' => Task::where('step', 'develop')->count(),
                'qa' => Task::where('step', 'qa')->count(),
                'security' => Task::where('step', 'security')->count(),
                'staging' => Task::where('step', 'staging')->count(),
                'production' => Task::where('step', 'production')->count(),
            ],
        ];
    }

    /**
     * Get unique view modes in use
     */
    public function getAvailableViewModes(): array
    {
        return Task::select('view_mode')
            ->distinct()
            ->pluck('view_mode')
            ->toArray();
    }

    /**
     * Change task view mode
     */
    public function changeViewMode(Task $task, string $viewMode): bool
    {
        if (!in_array($viewMode, self::VIEW_MODES)) {
            return false;
        }

        return $task->update(['view_mode' => $viewMode]);
    }
}
