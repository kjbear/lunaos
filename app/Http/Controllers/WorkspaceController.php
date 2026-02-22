<?php

namespace App\Http\Controllers;

use App\Services\WorkspaceService;

class WorkspaceController extends Controller
{
    protected WorkspaceService $workspaceService;

    public function __construct(WorkspaceService $workspaceService)
    {
        $this->workspaceService = $workspaceService;
    }

    /**
     * List all workspace files
     */
    public function index()
    {
        return response()->json([
            'files' => $this->workspaceService->listFiles(),
        ]);
    }

    /**
     * Get a specific file's content
     */
    public function show(string $path)
    {
        $file = $this->workspaceService->readFile($path);

        if (!$file) {
            return response()->json([
                'error' => 'File not found or access denied',
            ], 404);
        }

        return response()->json($file);
    }
}