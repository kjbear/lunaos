<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityIngestController extends Controller
{
    public function __construct(
        private ActivityIngestService $ingestService
    ) {}

    /**
     * Receive activity from OpenClaw webhook
     * 
     * POST /api/activity/ingest
     * 
     * Expected payload:
     * {
     *   "agent": "Luna",
     *   "action_type": "task",
     *   "action_name": "Fixed database error",
     *   "context": {"module": "standups"},
     *   "impact": "high",
     *   "status": "success",
     *   "timestamp": "2026-02-22T12:00:00Z"
     * }
     */
    public function ingest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent' => 'required|string|max:100',
            'action_type' => 'required|string|max:50',
            'action_name' => 'required|string|max:255',
            'context' => 'nullable|array',
            'impact' => 'nullable|string|in:low,medium,high',
            'status' => 'nullable|string|in:success,failed,pending',
            'timestamp' => 'nullable|date',
        ]);

        $activity = $this->ingestService->ingestFromWebhook($validated);

        return response()->json([
            'success' => true,
            'activity_id' => $activity->id,
        ]);
    }

    /**
     * Trigger manual poll from OpenClaw (fallback)
     * 
     * POST /api/activity/poll
     */
    public function poll(): JsonResponse
    {
        $count = $this->ingestService->pollFromOpenClaw();

        return response()->json([
            'success' => true,
            'ingested_count' => $count,
        ]);
    }

    /**
     * Health check for webhook endpoint
     * 
     * GET /api/activity/health
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}