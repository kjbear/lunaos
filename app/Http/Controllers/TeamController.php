<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Http\Resources\TeamResource;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamController extends Controller
{
    private TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Display a listing of team members.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'workers');
        $members = TeamMember::where('type', $activeTab)->orderBy('name')->get();
        return view('team.index', compact('activeTab', 'members'));
    }

    /**
     * Show the form for creating a new team member.
     */
    public function create(): View
    {
        return view('team.create');
    }

    /**
     * Store a newly created team member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:team_members,email',
            'role' => 'required|in:worker,persona,board_member',
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
        ]);

        $this->teamService->createTeamMember($validated);

        return redirect()->route('team')->with('success', 'Team member created successfully.');
    }

    /**
     * Display the specified team member.
     */
    public function show(TeamMember $id): View
    {
        return view('team.show', ['member' => $id]);
    }

    /**
     * Show the form for editing the specified team member.
     */
    public function edit(TeamMember $id): View
    {
        return view('team.edit', ['member' => $id]);
    }

    /**
     * Update the specified team member.
     */
    public function update(Request $request, TeamMember $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255|unique:team_members,email,' . $id->getKey(),
            'role' => 'sometimes|required|in:worker,persona,board_member',
            'type' => 'sometimes|required|in:workers,personas,board-members',
            'title' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:active,inactive,online,offline,error,busy',
            'model' => 'nullable|string|max:255',
            'provider' => 'nullable|string|max:255',
        ]);

        $this->teamService->updateTeamMember($id, $validated);

        return redirect()->to('/team/' . $id->getKey())->with('success', 'Team member updated successfully.');
    }

    /**
     * Remove the specified team member.
     */
    public function destroy(TeamMember $id): RedirectResponse
    {
        $id->delete();

        return redirect()->to('/team')->with('success', 'Team member deleted successfully.');
    }

    // ==========================================
    // API ENDPOINTS
    // ==========================================

    /**
     * API: Get all team members.
     */
    public function apiIndex(): JsonResponse
    {
        $members = TeamMember::all();
        return response()->json([
            'data' => TeamResource::collection($members)
        ]);
    }

    /**
     * API: Get a single team member.
     */
    public function apiShow(TeamMember $team): JsonResponse
    {
        return response()->json([
            'data' => TeamResource::make($team)
        ]);
    }

    /**
     * API: Create a new team member.
     */
    public function apiStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:team_members,email',
            'role' => 'required|in:worker,persona,board_member',
        ]);

        $member = $this->teamService->createTeamMember($validated);

        return response()->json([
            'data' => TeamResource::make($member)
        ], 201);
    }

    /**
     * API: Update a team member.
     */
    public function apiUpdate(Request $request, TeamMember $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:team_members,email,' . $team->getKey(),
            'role' => 'sometimes|required|in:worker,persona,board_member',
        ]);

        $this->teamService->updateTeamMember($team, $validated);

        return response()->json([
            'data' => TeamResource::make($team)
        ]);
    }

    /**
     * API: Delete a team member.
     */
    public function apiDestroy(TeamMember $team): JsonResponse
    {
        $team->delete();

        return response()->json([], 204);
    }

    /**
     * API: Get child members (for parent/children relationships).
     */
    public function members(TeamMember $team): JsonResponse
    {
        return response()->json([
            'data' => TeamResource::collection($team->children)
        ]);
    }
}
