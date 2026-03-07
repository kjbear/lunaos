<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BoardSession;
use App\Models\Project;
use App\Models\ProjectArtifact;
use App\Models\ProjectAssignment;
use App\Models\Repository;
use App\Services\ProjectAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    protected ProjectAnalysisService $analyzer;

    public function __construct(ProjectAnalysisService $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    /**
     * Show project creation form (with optional board session data)
     */
    public function create(Request $request)
    {
        $analysis = null;
        $boardSession = null;
        
        // If coming from board session, analyze and pre-fill
        if ($request->has('board_session')) {
            $boardSession = BoardSession::findOrFail($request->board_session);
            
            if ($boardSession->status !== 'decided' || empty($boardSession->final_decision)) {
                return redirect()->back()
                    ->with('error', 'Cannot create project: Board session has no decision yet.');
            }
            
            // Run AI analysis
            $analysis = $this->analyzer->analyzeBoardDecision($boardSession);
        }

        return view('projects.create', [
            'analysis' => $analysis,
            'boardSession' => $boardSession,
        ]);
    }

    /**
     * Store new project with artifacts
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'repo_url' => 'nullable|url',
            'status' => 'nullable|in:planning,active,completed',
            'board_session_id' => 'nullable|uuid|exists:board_sessions,id',
            'requirements' => 'nullable|array',
            'requirements.*' => 'nullable|string|max:1000',
        ]);
        
        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'repo_url' => $validated['repo_url'] ?? null,
            'status' => $validated['status'] ?? 'planning',
            'health' => 'healthy',
            'progress' => 0,
        ]);

        // Create artifacts from board session if provided
        if ($request->has('board_session_id')) {
            $boardSession = BoardSession::findOrFail($request->board_session_id);
            
            // 1. Board Discussion Artifact
            ProjectArtifact::create([
                'project_id' => $project->id,
                'type' => 'board_discussion',
                'title' => 'Executive Board Session - ' . substr($boardSession->question, 0, 60),
                'content' => $this->formatBoardSession($boardSession),
                'source_type' => 'board_session',
                'source_id' => $boardSession->id,
                'order' => 1,
            ]);

            // 2. Board Decision Artifact
            if ($boardSession->final_decision) {
                ProjectArtifact::create([
                    'project_id' => $project->id,
                    'type' => 'decision',
                    'title' => 'Board Recommendation',
                    'content' => $boardSession->final_decision,
                    'source_type' => 'board_session',
                    'source_id' => $boardSession->id,
                    'order' => 2,
                ]);
            }

            // 3. Requirements from analysis
            if ($request->has('requirements') && is_array($request->requirements)) {
                foreach ($request->requirements as $index => $req) {
                    if (!empty(trim($req))) {
                        ProjectArtifact::create([
                            'project_id' => $project->id,
                            'type' => 'requirement',
                            'title' => 'Requirement #' . ($index + 1),
                            'content' => $req,
                            'source_type' => 'ai_generated',
                            'order' => 3 + $index,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Project created successfully with ' . ($request->board_session_id ? 'board artifacts' : 'no artifacts'));
    }

    /**
     * Display project with artifacts
     */
    public function show(Project $project)
    {
        $project->load(['artifacts' => function($q) {
            $q->orderBy('order');
        }]);
        
        // Group artifacts by type, ensuring all expected types exist
        $artifactsByType = $project->artifacts->groupBy('type');
        
        // Initialize empty collections for expected types to avoid "undefined array key" errors
        $expectedTypes = ['requirement', 'board_discussion', 'decision', 'note', 'doc'];
        foreach ($expectedTypes as $type) {
            if (!$artifactsByType->has($type)) {
                $artifactsByType[$type] = collect();
            }
        }

        return view('projects.show', compact('project', 'artifactsByType'));
    }

    /**
     * Update project details
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'repo_url' => 'nullable|url',
            'status' => 'sometimes|in:planning,active,completed,archived',
            'health' => 'sometimes|in:healthy,at_risk,blocked',
            'architecture_type' => 'nullable|string|max:100',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:100',
            'project_manager_id' => 'nullable|uuid|exists:agents,id',
            'percent_complete' => 'nullable|numeric|min:0|max:100',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully');
    }

    /**
     * Archive (soft delete) a project
     */
    public function destroy(Project $project)
    {
        $project->delete(); // Soft delete

        return redirect()->route('projects.index')
            ->with('success', 'Project archived');
    }

    /**
     * Create GitHub repository and link to project
     */
    public function createRepository(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'private' => 'boolean',
        ]);

        // Call GitHub API via gh CLI
        $repoName = $validated['name'];
        $description = $validated['description'] ?? $project->description;
        $private = $validated['private'] ?? true;

        try {
            // Execute gh CLI command
            $process = proc_open(
                "gh repo create {$repoName} --private=" . ($private ? 'true' : 'false') . " --source=/Users/kobear/.openclaw/workspace/lunaos --push",
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ],
                $pipes,
                '/Users/kobear/.openclaw/workspace/lunaos'
            );

            if (is_resource($process)) {
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                proc_close($process);

                if ($error) {
                    throw new \Exception("GitHub CLI error: {$error}");
                }

                // Extract repo URL from output
                preg_match('/created on GitHub \(https?:\/\/[^\s]+\)/', $output, $matches);
                $repoUrl = $matches[1] ?? "https://github.com/" . trim(getenv('GITHUB_USER')) . "/{$repoName}";

                // Create repository record
                $repository = Repository::create([
                    'name' => $repoName,
                    'path' => "/Users/kobear/.openclaw/workspace/{$repoName}",
                    'git_url' => $repoUrl,
                    'default_branch' => 'main',
                ]);

                // Link to project
                $project->update(['repository_id' => $repository->id]);

                return back()->with('success', "Repository {$repoName} created and linked!");
            }

            throw new \Exception('Failed to execute GitHub CLI');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create repository: ' . $e->getMessage());
        }
    }

    /**
     * Assign an agent to the project team
     */
    public function assignAgent(Request $request, Project $project)
    {
        $validated = $request->validate([
            'agent_id' => 'required|uuid|exists:agents,id',
            'role' => 'required|in:project_manager,architect,developer,qa,reviewer',
        ]);

        $assignment = ProjectAssignment::create([
            'project_id' => $project->id,
            'agent_id' => $validated['agent_id'],
            'role' => $validated['role'],
        ]);

        return back()->with('success', ucfirst(str_replace('_', ' ', $validated['role'])) . ' assigned to project');
    }

    /**
     * Remove an agent from the project team
     */
    public function removeAgent(Project $project, ProjectAssignment $assignment)
    {
        if ($assignment->project_id !== $project->id) {
            abort(403, 'Assignment does not belong to this project');
        }

        $assignment->delete();

        return back()->with('success', 'Agent removed from project');
    }

    /**
     * Format board session for artifact storage
     */
    protected function formatBoardSession(BoardSession $session): string
    {
        $output = "## Board Question\n{$session->question}\n\n";
        
        if ($session->context) {
            $output .= "## Context\n{$session->context}\n\n";
        }
        
        $output .= "## Discussion\n\n";
        foreach ($session->responses as $response) {
            $output .= "### {$response->member_name} ({$response->member_role})\n{$response->response}\n\n";
        }
        
        if ($session->final_decision) {
            $output .= "## Final Decision\n{$session->final_decision}\n\n";
        }
        
        if ($session->risks_benefits) {
            $output .= "## Risks & Considerations\n{$session->risks_benefits}";
        }
        
        return $output;
    }

    /**
     * Store new artifact (requirement/note/doc)
     */
    public function storeArtifact(Request $request, Project $project, string $type)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        ProjectArtifact::create([
            'project_id' => $project->id,
            'type' => $type,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'order' => $project->artifacts()->count() + 1,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', ucfirst($type) . ' added successfully!');
    }
}
