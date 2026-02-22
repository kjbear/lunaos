<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ModelHealth;
use Illuminate\Http\JsonResponse;

class OrgChartController extends Controller
{
    /**
     * Get the full org chart hierarchy.
     */
    public function index(): JsonResponse
    {
        $tree = $this->buildTree();

        return response()->json([
            'tree' => $tree,
            'agents' => Agent::with('children')->get(),
        ]);
    }

    /**
     * Build hierarchical tree from agents.
     */
    protected function buildTree(): array
    {
        // Get all agents with children
        $agents = Agent::with('children')->whereNull('parent_id')->get();

        return $agents->map(fn ($agent) => $this->formatAgent($agent))->toArray();
    }

    /**
     * Format agent with recursive children.
     */
    protected function formatAgent(Agent $agent): array
    {
        return [
            'id' => $agent->id,
            'name' => $agent->name,
            'role' => $agent->role,
            'model' => $agent->model,
            'status' => $agent->status,
            'children' => $agent->children->map(fn ($child) => $this->formatAgent($child))->toArray(),
        ];
    }

    /**
     * Get agent details.
     */
    public function show(Agent $agent): JsonResponse
    {
        $agent->load(['parent', 'children', 'tasks' => fn ($q) => $q->latest()->limit(5)]);

        return response()->json($agent);
    }

    /**
     * Get model health status.
     */
    public function health(): JsonResponse
    {
        $health = ModelHealth::withRecent(10)
            ->get()
            ->groupBy('model');

        return response()->json($health);
    }

    /**
     * Get summary stats.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_agents' => Agent::count(),
            'online' => Agent::online()->count(),
            'offline' => Agent::offline()->count(),
            'models' => Agent::select('model')
                ->whereNotNull('model')
                ->distinct()
                ->pluck('model'),
        ]);
    }
}