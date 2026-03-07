<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Agent;
use App\Models\ProjectAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a paginated listing of projects with filtering and sorting.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['repository', 'projectManager', 'agents.agent']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Include soft-deleted if requested
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $projects = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $projects->items(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'from' => $projects->firstItem(),
                'to' => $projects->lastItem(),
            ],
            'links' => [
                'first' => $projects->url(1),
                'last' => $projects->url($projects->lastPage()),
                'prev' => $projects->previousPageUrl(),
                'next' => $projects->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created project.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'repo_url' => 'nullable|url|max:2048',
            'repository_id' => 'nullable|string|exists:repositories,id',
            'status' => 'nullable|in:planning,active,completed,archived',
            'health' => 'nullable|in:healthy,at_risk,blocked',
            'architecture_type' => 'nullable|string|max:100',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:100',
            'project_manager_id' => 'nullable|exists:agents,id',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $project = Project::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'repo_url' => $validated['repo_url'] ?? null,
            'repository_id' => $validated['repository_id'] ?? null,
            'status' => $validated['status'] ?? 'planning',
            'health' => $validated['health'] ?? 'healthy',
            'progress' => $validated['progress'] ?? 0,
            'architecture_type' => $validated['architecture_type'] ?? null,
            'technologies' => $validated['technologies'] ?? [],
            'project_manager_id' => $validated['project_manager_id'] ?? null,
        ]);

        $project->load(['repository', 'projectManager']);

        return response()->json([
            'data' => $project,
        ], 201);
    }

    /**
     * Display the specified project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function show(Project $project): JsonResponse
    {
        $project->load([
            'repository',
            'projectManager',
            'agents.agent',
            'tasks' => fn($q) => $q->limit(10)->orderBy('created_at', 'desc'),
            'issues' => fn($q) => $q->limit(10)->orderBy('created_at', 'desc'),
        ]);

        return response()->json([
            'data' => $project,
        ]);
    }

    /**
     * Update the specified project.
     *
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(Request $request, Project $project): JsonResponse
    {

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'repo_url' => 'nullable|url|max:2048',
            'repository_id' => 'nullable|string|exists:repositories,id',
            'status' => 'sometimes|in:planning,active,completed,archived',
            'health' => 'sometimes|in:healthy,at_risk,blocked',
            'architecture_type' => 'nullable|string|max:100',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:100',
            'project_manager_id' => 'nullable|exists:agents,id',
            'progress' => 'nullable|integer|min:0|max:100',
            'percent_complete' => 'nullable|numeric|min:0|max:100',
        ]);

        $project->update($validated);
        $project->load(['repository', 'projectManager', 'agents.agent']);

        return response()->json([
            'data' => $project,
        ]);
    }

    /**
     * Soft delete the specified project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project): JsonResponse
    {
        // Cascades to tasks, agents, issues via model boot
        $project->delete();

        return response()->json([
            'message' => 'Project archived successfully',
            'data' => [
                'id' => $project->id,
                'deleted_at' => $project->deleted_at,
            ],
        ]);
    }

    /**
     * Restore a soft-deleted project.
     *
     * @param string $project
     * @return JsonResponse
     */
    public function restore(string $project): JsonResponse
    {
        $projectModel = Project::withTrashed()->findOrFail($project);
        
        if (!$projectModel->trashed()) {
            return response()->json([
                'message' => 'Project is not deleted',
                'data' => $projectModel,
            ], 400);
        }

        $projectModel->restore();

        return response()->json([
            'message' => 'Project restored successfully',
            'data' => $projectModel,
        ]);
    }

    /**
     * Permanently delete a project.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function forceDelete(string $id): JsonResponse
    {
        $project = Project::withTrashed()->findOrFail($id);
        
        $project->forceDelete();

        return response()->json([
            'message' => 'Project permanently deleted',
        ], 204);
    }

    /**
     * Get project statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Project::count(),
            'by_status' => [
                'planning' => Project::where('status', 'planning')->count(),
                'active' => Project::where('status', 'active')->count(),
                'completed' => Project::where('status', 'completed')->count(),
                'archived' => Project::where('status', 'archived')->count(),
            ],
            'by_health' => [
                'healthy' => Project::where('health', 'healthy')->count(),
                'at_risk' => Project::where('health', 'at_risk')->count(),
                'blocked' => Project::where('health', 'blocked')->count(),
            ],
            'trashed' => Project::onlyTrashed()->count(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Assign an agent to the project.
     *
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function assignAgent(Request $request, Project $project): JsonResponse
    {

        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'role' => 'required|in:project_manager,architect,developer,qa,reviewer',
        ]);

        // Check if already assigned
        $existingAssignment = ProjectAssignment::where('project_id', $project->id)
            ->where('agent_id', $validated['agent_id'])
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'Agent already assigned to this project',
                'data' => $existingAssignment,
            ], 409);
        }

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $validated['agent_id'],
            'role' => $validated['role'],
            'assigned_at' => now(),
        ]);

        $assignment->load('agent');

        return response()->json([
            'message' => 'Agent assigned successfully',
            'data' => $assignment,
        ], 201);
    }

    /**
     * Remove an agent from the project.
     *
     * @param Project $project
     * @param string $agentId
     * @return JsonResponse
     */
    public function removeAgent(Project $project, string $agentId): JsonResponse
    {
        $assignment = ProjectAssignment::where('project_id', $project->id)
            ->where('agent_id', $agentId)
            ->firstOrFail();

        $assignment->delete();

        return response()->json([
            'message' => 'Agent removed from project',
        ], 204);
    }

    /**
     * Get filter options for projects.
     *
     * @return JsonResponse
     */
    public function filters(): JsonResponse
    {
        return response()->json([
            'data' => [
                'statuses' => ['planning', 'active', 'completed', 'archived'],
                'health_states' => ['healthy', 'at_risk', 'blocked'],
                'architecture_types' => ['monolith', 'microservices', 'serverless', 'hybrid'],
            ],
        ]);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request): void
    {
        // Filter by status
        if ($request->has('status')) {
            $statuses = is_array($request->status) 
                ? $request->status 
                : explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // Filter by health
        if ($request->has('health')) {
            $healthStates = is_array($request->health)
                ? $request->health
                : explode(',', $request->health);
            $query->whereIn('health', $healthStates);
        }

        // Filter by architecture type
        if ($request->has('architecture_type')) {
            $query->where('architecture_type', $request->architecture_type);
        }

        // Filter by repository
        if ($request->has('repository_id')) {
            $query->where('repository_id', $request->repository_id);
        }

        // Filter by project manager
        if ($request->has('project_manager_id')) {
            $query->where('project_manager_id', $request->project_manager_id);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->has('created_after')) {
            $query->where('created_at', '>=', $request->created_after);
        }
        if ($request->has('created_before')) {
            $query->where('created_at', '<=', $request->created_before);
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['id', 'name', 'status', 'health', 'progress', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }
}