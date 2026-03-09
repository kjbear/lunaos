<?php

namespace App\Services;

use App\Models\TeamMember;
use Illuminate\Support\Collection;

/**
 * Org Chart Data Service
 * 
 * Transforms TeamMember hierarchy into vis.js Network library format.
 */
class OrgChartDataService
{
    /**
     * Get graph data in vis.js format (nodes and edges arrays).
     * 
     * @return array [nodes => array, edges => array]
     */
    public function getGraphData(): array
    {
        $members = TeamMember::with('children', 'parent')->get();
        
        $nodes = $members->map(fn($m) => [
            'id' => $m->id,
            'label' => $m->name,
            'title' => $m->title,
            'group' => $this->getVisJsGroup($m->role),
            'color' => $this->getRoleColor($m->role),
            'shape' => 'box',
            'font' => ['color' => '#ffffff', 'size' => 14],
            'borderWidth' => 2,
            'physics' => true,
        ]);
        
        $edges = $members->filter(fn($m) => $m->parent_id)
            ->map(fn($m) => [
                'from' => $m->parent_id,
                'to' => $m->id,
                'arrows' => 'to',
                'smooth' => ['type' => 'continuous'],
            ]);
        
        return ['nodes' => $nodes->values()->toArray(), 'edges' => $edges->values()->toArray()];
    }

    /**
     * Get vis.js group name based on role.
     */
    public function getVisJsGroup(string $role): string
    {
        return match($role) {
            'executive' => 'executive',
            'board_member' => 'board',
            'worker' => 'worker',
            default => 'worker',
        };
    }

    /**
     * Get color for role-based visualization.
     */
    public function getRoleColor(string $role): string
    {
        return match($role) {
            'executive' => '#8b5cf6', // purple
            'board_member' => '#3b82f6', // blue
            'worker' => '#10b981', // green
            default => '#64748b', // slate
        };
    }

    /**
     * Get nodes only (for tree structure display).
     */
    public function getNodes(): Collection
    {
        return TeamMember::with('children', 'parent')->get()->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'title' => $m->title,
            'role' => $m->role,
            'type' => $m->type,
            'model' => $m->model,
            'emoji' => $m->emoji,
            'parent_id' => $m->parent_id,
            'color' => $this->getRoleColor($m->role),
            'group' => $this->getVisJsGroup($m->role),
        ]);
    }

    /**
     * Build hierarchical tree structure for display.
     */
    public function getHierarchyTree(): Collection
    {
        $members = TeamMember::with('children', 'parent')->get();
        
        // Build lookup
        $lookup = $members->keyBy('id');
        
        // Find roots (no parent or parent is null)
        $roots = $members->filter(fn($m) => !$m->parent_id || !$lookup->has($m->parent_id));
        
        return $roots->map(fn($root) => $this->buildTree($root, $lookup));
    }

    /**
     * Recursively build tree structure.
     */
    protected function buildTeamMember(TeamMember $member, Collection $lookup): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'title' => $member->title,
            'role' => $member->role,
            'type' => $member->type,
            'model' => $member->model,
            'emoji' => $member->emoji,
            'children' => $member->children->map(fn($child) => $this->buildTeamMember($child, $lookup))->toArray(),
        ];
    }

    /**
     * Recursively build tree structure (alias for buildTeamMember).
     */
    protected function buildTree(TeamMember $member, Collection $lookup): array
    {
        return $this->buildTeamMember($member, $lookup);
    }
}
