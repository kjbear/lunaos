<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Agent;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskManagerController extends Controller
{
    /**
     * Display a listing of tasks with filtering and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with('agent');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by agent
        if ($request->has('agent_id') && $request->agent_id) {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['created_at', 'cost', 'tokens_used', 'status', 'priority'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $tasks = $query->paginate($request->get('per_page', 20));

        return response()->json($tasks);
    }

    /**
     * Display a specific task.
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['agent', 'logs']);

        return response()->json($task);
    }

    /**
     * Get task statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Task::count(),
            'pending' => Task::pending()->count(),
            'running' => Task::running()->count(),
            'completed' => Task::completed()->count(),
            'failed' => Task::failed()->count(),
            'total_tokens' => Task::sum('tokens_used'),
            'total_cost' => Task::sum('cost'),
            'active_agents' => Agent::online()->count(),
            'recent_tasks' => Task::with('agent')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get filter options (agents, statuses, priorities).
     */
    public function filters(): JsonResponse
    {
        return response()->json([
            'agents' => Agent::select('id', 'name', 'role')->get(),
            'statuses' => ['pending', 'running', 'completed', 'failed'],
            'priorities' => ['low', 'normal', 'high', 'critical'],
            'view_modes' => ['list', 'board', 'executive'],
        ]);
    }

    /**
     * Create a new task.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed,failed',
            'view_mode' => 'in:list,board,executive',
            'step' => 'in:develop,qa,security,staging,production',
            'priority' => 'in:low,medium,high,critical',
            'task_type' => 'in:feature,bugfix,refactor,test',
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed,failed',
            'view_mode' => 'in:list,board,executive',
            'step' => 'in:develop,qa,security,staging,production',
            'priority' => 'in:low,medium,high,critical',
            'task_type' => 'in:feature,bugfix,refactor,test',
        ]);

        $task->update($validated);

        return response()->json($task);
    }
}