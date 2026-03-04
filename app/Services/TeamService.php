<?php

namespace App\Services;

use App\Models\TeamMember;
use App\Models\Agent;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Team Service Layer
 * 
 * Handles all business logic for team member management including:
 * - CRUD operations for team members
 * - Migration from legacy Agent and Persona models
 * - Filtering, searching, and statistics
 * - Hierarchy management
 */
class TeamService
{
    /**
     * Get all team members with optional filters (paginated).
     * 
     * @param array $filters ['role' => string, 'status' => string, 'type' => string, 'per_page' => int, 'page' => int]
     * @return LengthAwarePaginator
     */
    public function getAllTeamMembers(array $filters = []): LengthAwarePaginator
    {
        $query = TeamMember::query();

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Default ordering by created_at descending, then by id for deterministic ordering
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        $perPage = $filters['per_page'] ?? 20;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get a team member by ID.
     * 
     * @param string $id
     * @return TeamMember|null
     */
    public function getTeamMemberById(string $id): ?TeamMember
    {
        return TeamMember::find($id);
    }

    /**
     * Get a team member by name.
     * 
     * @param string $name
     * @return TeamMember|null
     */
    public function getTeamMemberByName(string $name): ?TeamMember
    {
        return TeamMember::where('name', $name)->first();
    }

    /**
     * Create a new team member.
     * 
     * @param array $data
     * @return TeamMember
     * @throws \InvalidArgumentException
     */
    public function createTeamMember(array $data): TeamMember
    {
        // Validate required fields
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }

        // Check for duplicate name
        if (TeamMember::where('name', $data['name'])->exists()) {
            throw new \InvalidArgumentException('A team member with this name already exists');
        }

        return TeamMember::create($data);
    }

    /**
     * Update an existing team member.
     * 
     * @param TeamMember $member
     * @param array $data
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function updateTeamMember(TeamMember $member, array $data): bool
    {
        // If name is being updated, check for duplicates
        if (isset($data['name']) && $data['name'] !== $member->name) {
            if (TeamMember::where('name', $data['name'])->where('id', '!=', $member->id)->exists()) {
                throw new \InvalidArgumentException('A team member with this name already exists');
            }
        }

        return $member->update($data);
    }

    /**
     * Delete (archive) a team member.
     * 
     * @param TeamMember $member
     * @return bool
     */
    public function deleteTeamMember(TeamMember $member): bool
    {
        return $member->update([
            'status' => 'archived',
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Restore an archived team member.
     * 
     * @param TeamMember $member
     * @return bool
     */
    public function restoreTeamMember(TeamMember $member): bool
    {
        return $member->update([
            'status' => 'active',
            'deactivated_at' => null,
        ]);
    }

    /**
     * Search team members by name or title.
     * 
     * @param string $query
     * @return LengthAwarePaginator
     */
    public function searchTeamMembers(string $query): LengthAwarePaginator
    {
        return TeamMember::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('title', 'like', "%{$query}%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    }

    /**
     * Get team statistics.
     * 
     * @return array
     */
    public function getTeamStatistics(): array
    {
        return [
            'total' => TeamMember::count(),
            'by_type' => [
                'workers' => TeamMember::where('type', 'workers')->count(),
                'personas' => TeamMember::where('type', 'personas')->count(),
                'board-members' => TeamMember::where('type', 'board-members')->count(),
            ],
            'by_status' => [
                'active' => TeamMember::where('status', 'active')->count(),
                'inactive' => TeamMember::where('status', 'inactive')->count(),
                'online' => TeamMember::where('status', 'online')->count(),
                'offline' => TeamMember::where('status', 'offline')->count(),
                'error' => TeamMember::where('status', 'error')->count(),
                'busy' => TeamMember::where('status', 'busy')->count(),
                'archived' => TeamMember::where('status', 'archived')->count(),
            ],
        ];
    }

    /**
     * Bulk update status for multiple team members.
     * 
     * @param array $ids
     * @param string $status
     * @return bool
     */
    public function bulkUpdateStatus(array $ids, string $status): bool
    {
        return TeamMember::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Bulk delete (archive) multiple team members.
     * 
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids): bool
    {
        return TeamMember::whereIn('id', $ids)->update([
            'status' => 'archived',
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Assign a parent to a team member.
     * 
     * @param TeamMember $member
     * @param string|null $parentId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function assignParent(TeamMember $member, ?string $parentId): bool
    {
        // Prevent circular hierarchy
        if ($parentId) {
            $parent = TeamMember::find($parentId);
            if (!$parent) {
                throw new \InvalidArgumentException('Parent team member not found');
            }

            // Check if parent is a descendant of the member (would create circular reference)
            if ($this->isDescendant($parent, $member)) {
                throw new \InvalidArgumentException('Cannot create circular hierarchy');
            }
        }

        return $member->update(['parent_id' => $parentId]);
    }

    /**
     * Check if a member is a descendant of another.
     * 
     * @param TeamMember $potentialDescendant
     * @param TeamMember $potentialAncestor
     * @return bool
     */
    private function isDescendant(TeamMember $potentialDescendant, TeamMember $potentialAncestor): bool
    {
        $current = $potentialDescendant;
        while ($current->parent_id) {
            if ($current->parent_id === $potentialAncestor->id) {
                return true;
            }
            $current = TeamMember::find($current->parent_id);
            if (!$current) {
                break;
            }
        }
        return false;
    }

    /**
     * Get team members by type.
     * 
     * @param string $type
     * @return LengthAwarePaginator
     */
    public function getTeamMembersByType(string $type): LengthAwarePaginator
    {
        return TeamMember::where('type', $type)->paginate(100);
    }

    /**
     * Get all worker team members.
     * 
     * @return LengthAwarePaginator
     */
    public function getWorkers(): LengthAwarePaginator
    {
        return TeamMember::where('type', 'workers')->paginate(100);
    }

    /**
     * Get all board members.
     * 
     * @return LengthAwarePaginator
     */
    public function getBoardMembers(): LengthAwarePaginator
    {
        return TeamMember::where('type', 'board-members')->paginate(100);
    }

    /**
     * Get online team members.
     * 
     * @return LengthAwarePaginator
     */
    public function getOnlineMembers(): LengthAwarePaginator
    {
        return TeamMember::where('status', 'online')->paginate(100);
    }

    /**
     * Migrate an Agent record to TeamMember.
     * 
     * @param Agent $agent
     * @return TeamMember
     */
    public function migrateFromAgent(Agent $agent): TeamMember
    {
        // Check for name collision
        $name = $agent->name;
        $counter = 1;
        $originalName = $name;
        
        while (TeamMember::where('name', $name)->exists()) {
            $name = "{$originalName}-agent{$counter}";
            $counter++;
        }

        $data = [
            'name' => $name,
            'title' => $agent->title ?? 'Worker',
            'type' => 'agent',
            'role' => 'worker',
            'status' => $agent->status ?? 'active',
            'model' => $agent->model ?? null,
            'provider' => $agent->provider ?? 'ollama',
            'system_prompt' => $agent->system_prompt ?? null,
            'settings' => $agent->settings ?? null,
            'workspace_path' => $agent->workspace_path ?? null,
            'metadata_json' => [
                'migrated_from' => 'agents',
                'migration_date' => now()->toISOString(),
                'original_id' => $agent->id,
            ],
        ];

        return TeamMember::create($data);
    }

    /**
     * Migrate a Persona record to TeamMember.
     * 
     * @param mixed $persona
     * @return TeamMember
     */
    public function migrateFromPersona($persona): TeamMember
    {
        // Check for name collision
        $name = $persona->name;
        $counter = 1;
        $originalName = $name;
        
        while (TeamMember::where('name', $name)->exists()) {
            $name = "{$originalName}-persona{$counter}";
            $counter++;
        }

        // Determine role based on persona role field
        $role = $persona->role ?? 'subagent';
        
        // Map persona role to team member role
        if ($role === 'board_member') {
            $role = 'board_member';
        } elseif ($role === 'subagent' || $role === 'custom') {
            $role = 'persona';
        }

        $data = [
            'name' => $name,
            'title' => $persona->title ?? null,
            'type' => 'persona',
            'role' => $role,
            'status' => $persona->status ?? 'active',
            'model' => $persona->model ?? null,
            'provider' => $persona->provider ?? 'ollama',
            'system_prompt' => $persona->system_prompt ?? null,
            'settings' => $persona->settings ?? null,
            'workspace_path' => $persona->workspace_path ?? null,
            'metadata_json' => [
                'migrated_from' => 'personas',
                'migration_date' => now()->toISOString(),
                'original_id' => $persona->id,
            ],
        ];

        return TeamMember::create($data);
    }
}
