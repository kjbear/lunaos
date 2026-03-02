<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks with filtering, sorting, and view modes.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Task::with(['agent', 'repository', 'activities']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Get pagination parameters
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        // Return paginated results
        return TaskResource::collection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Get tasks for a specific view mode (list, board, executive).
     * 
     * @param string $viewMode
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function viewMode(string $viewMode, Request $request): AnonymousResourceCollection
    {
        $allowedViewModes = ['list', 'board', 'executive'];
        
        if (!in_array($viewMode, $allowedViewModes)) {
            return abort(400, response()->json([
                'error' => 'Invalid view mode',
                'allowed' => $allowedViewModes,
            ]));
        }

        $query = Task::with(['agent', 'repository', 'activities'])
            ->where('view_mode', $viewMode);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Get pagination parameters
        $perPage = $request->get('per_page', 20);

        return TaskResource::collection($query->paginate($perPage));
    }

    /**
     * Display the specified task.
     * 
     * @param Task $task
     * @return TaskResource
     */
    public function show(Task $task): TaskResource
    {
        $task->load(['agent', 'repository', 'activities']);
        
        return TaskResource::make($task);
    }

    /**
     * Store a newly created task.
     * 
     * @param Request $request
     * @return TaskResource
     */
    public function store(Request $request): TaskResource
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:agents,name',
            'repository_id' => 'nullable|exists:repositories,id',
            'status' => 'nullable|in:pending,in_progress,complete,failed,blocked',
            'step' => 'nullable|in:develop,qa,security,staging,production',
            'priority' => 'nullable|in:low,medium,high,critical',
            'task_type' => 'nullable|in:feature,bug,chore,hotfix,refactor',
            'view_mode' => 'nullable|in:list,board,executive',
            'context_json' => 'nullable|array',
            'branch_name' => 'nullable|string|max:255',
            'pr_url' => 'nullable|url|max:2048',
        ]);

        $task = Task::create($validated);
        $task->load(['agent', 'repository']);

        return TaskResource::make($task);
    }

    /**
     * Update the specified task.
     * 
     * @param Request $request
     * @param Task $task
     * @return TaskResource
     */
    public function update(Request $request, Task $task): TaskResource
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:agents,name',
            'repository_id' => 'nullable|exists:repositories,id',
            'status' => 'nullable|in:pending,in_progress,complete,failed,blocked',
            'step' => 'nullable|in:develop,qa,security,staging,production',
            'priority' => 'nullable|in:low,medium,high,critical',
            'task_type' => 'nullable|in:feature,bug,chore,hotfix,refactor',
            'view_mode' => 'nullable|in:list,board,executive',
            'context_json' => 'nullable|array',
            'branch_name' => 'nullable|string|max:255',
            'pr_url' => 'nullable|url|max:2048',
            'failure_reason' => 'nullable|string',
            'retry_count' => 'nullable|integer|min:0',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        $task->update($validated);
        $task->load(['agent', 'repository', 'activities']);

        return TaskResource::make($task);
    }

    /**
     * Remove the specified task.
     * 
     * @param Task $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task): \Illuminate\Http\Response
    {
        $task->delete();
        
        return response()->noContent();
    }

    /**
     * Get task statistics.
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Task::count(),
            'by_status' => [
                'pending' => Task::where('status', 'pending')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'complete' => Task::where('status', 'complete')->count(),
                'failed' => Task::where('status', 'failed')->count(),
                'blocked' => Task::where('status', 'blocked')->count(),
            ],
            'by_priority' => [
                'low' => Task::where('priority', 'low')->count(),
                'medium' => Task::where('priority', 'medium')->count(),
                'high' => Task::where('priority', 'high')->count(),
                'critical' => Task::where('priority', 'critical')->count(),
            ],
            'by_view_mode' => [
                'list' => Task::where('view_mode', 'list')->count(),
                'board' => Task::where('view_mode', 'board')->count(),
                'executive' => Task::where('view_mode', 'executive')->count(),
            ],
            'by_step' => [
                'develop' => Task::where('step', 'develop')->count(),
                'qa' => Task::where('step', 'qa')->count(),
                'security' => Task::where('step', 'security')->count(),
                'staging' => Task::where('step', 'staging')->count(),
                'production' => Task::where('step', 'production')->count(),
            ],
            'completed_today' => Task::completedToday()->count(),
            'active_agents' => Agent::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get filter options.
     * 
     * @return JsonResponse
     */
    public function filters(): JsonResponse
    {
        return response()->json([
            'agents' => Agent::select('id', 'name', 'role', 'avatar')->get(),
            'statuses' => ['pending', 'in_progress', 'complete', 'failed', 'blocked'],
            'priorities' => ['low', 'medium', 'high', 'critical'],
            'task_types' => ['feature', 'bug', 'chore', 'hotfix', 'refactor'],
            'steps' => ['develop', 'qa', 'security', 'staging', 'production'],
            'view_modes' => ['list', 'board', 'executive'],
        ]);
    }

    /**
     * Bulk update tasks.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'required|integer|exists:tasks,id',
            'updates' => 'required|array',
        ]);

        $updates = $validated['updates'];
        $allowedFields = ['status', 'step', 'priority', 'assigned_to', 'view_mode'];
        $filteredUpdates = array_intersect_key($updates, array_flip($allowedFields));

        Task::whereIn('id', $validated['task_ids'])
            ->update($filteredUpdates);

        return response()->json([
            'message' => 'Tasks updated successfully',
            'updated_count' => count($validated['task_ids']),
        ]);
    }

    /**
     * Bulk delete tasks.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'required|integer|exists:tasks,id',
        ]);

        $deletedCount = Task::whereIn('id', $validated['task_ids'])->delete();

        return response()->json([
            'message' => 'Tasks deleted successfully',
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request): void
    {
        // Filter by status
        if ($request->has('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // Filter by agent
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $priorities = explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        // Filter by task type
        if ($request->has('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        // Filter by step
        if ($request->has('step')) {
            $query->where('step', $request->step);
        }

        // Filter by repository
        if ($request->has('repository_id')) {
            $query->where('repository_id', $request->repository_id);
        }

        // Filter by view mode
        if ($request->has('view_mode')) {
            $query->where('view_mode', $request->view_mode);
        }

        // Filter by date range
        if ($request->has('created_after')) {
            $query->where('created_at', '>=', $request->created_after);
        }
        if ($request->has('created_before')) {
            $query->where('created_at', '<=', $request->created_before);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = [
            'id',
            'title',
            'created_at',
            'updated_at',
            'started_at',
            'completed_at',
            'status',
            'priority',
            'step',
            'task_type',
        ];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }
}
