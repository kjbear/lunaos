<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardSession;
use App\Models\BoardParticipant;
use App\Models\BoardDiscussionEntry;
use App\Models\BoardDecision;
use App\Services\BoardService;
use App\Jobs\ProcessBoardDebate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * BoardController - RESTful API for board sessions
 * 
 * Endpoints:
 * POST   /api/board/sessions        - Create a new board session
 * GET    /api/board/sessions        - List all sessions
 * GET    /api/board/sessions/{id}   - Get session details
 * POST   /api/board/sessions/{id}/round - Run a debate round
 * POST   /api/board/sessions/{id}/consolidate - Consolidate decision
 * GET    /api/board/sessions/{id}/transcript - Get transcript
 * DELETE /api/board/sessions/{id}   - Close/delete session
 */
class BoardController extends Controller
{
    protected BoardService $boardService;

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    /**
     * Create a new board session.
     * 
     * POST /api/board/sessions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function createSession(Request $request): JsonResponse
    {
        $this->validate($request, [
            'question' => 'required|string|min:10|max:2000',
            'personas' => 'array',
            'personas.*' => 'string|in:COO,CFO,CTO,CMO,CPO',
        ]);

        $personas = $request->input('personas', ['COO', 'CFO', 'CTO', 'CMO', 'CPO']);

        try {
            $session = $this->boardService->startSession($request->input('question'), $personas);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $session->id,
                    'question' => $session->question,
                    'status' => $session->status,
                    'participant_count' => $session->participants()->count(),
                    'started_at' => $session->started_at?->toIso8601String(),
                ],
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('BoardController: Failed to create session', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all board sessions.
     * 
     * GET /api/board/sessions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listSessions(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $limit = $request->query('limit', 20);
        
        $query = BoardSession::query()->with('decision')->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $sessions = $query->limit((int) $limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $sessions->map(function ($session) {
                return [
                    'session_id' => $session->id,
                    'question' => $session->question,
                    'status' => $session->status,
                    'decision_summary' => $session->decision_summary,
                    'started_at' => $session->started_at?->toIso8601String(),
                    'completed_at' => $session->completed_at?->toIso8601String(),
                    'created_at' => $session->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Get session details.
     * 
     * GET /api/board/sessions/{id}
     * 
     * @param string $sessionId
     * @return JsonResponse
     */
    public function getSession(string $sessionId): JsonResponse
    {
        $session = BoardSession::with(['participants', 'decision', 'discussionEntries.participant'])
            ->find($sessionId);
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->id,
                'question' => $session->question,
                'status' => $session->status,
                'decision_summary' => $session->decision_summary,
                'participants' => $session->participants->map(function ($p) {
                    return [
                        'participant_id' => $p->id,
                        'persona_role' => $p->persona_role,
                        'model' => $p->model,
                        'name' => $p->getPersonaName(),
                        'emoji' => $p->getAvatarEmoji(),
                    ];
                }),
                'decision' => $session->decision ? [
                    'id' => $session->decision->id,
                    'decision_text' => $session->decision->decision_text,
                    'confidence_score' => $session->decision->confidence_score,
                    'confidence_level' => $session->decision->confidence_level,
                    'reasoning' => $session->decision->reasoning,
                    'created_at' => $session->decision->created_at->toIso8601String(),
                ] : null,
                'entry_count' => $session->discussionEntries()->count(),
                'started_at' => $session->started_at?->toIso8601String(),
                'completed_at' => $session->completed_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Run a debate round.
     * 
     * POST /api/board/sessions/{id}/round
     * 
     * @param Request $request
     * @param string $sessionId
     * @return JsonResponse
     */
    public function runRound(Request $request, string $sessionId): JsonResponse
    {
        $this->validate($request, [
            'round' => 'integer|min:1',
        ]);
        
        $round = $request->input('round', 1);
        
        try {
            $results = $this->boardService->runDebateRound($sessionId, $round);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $sessionId,
                    'round' => $round,
                    'responses' => $results,
                    'response_count' => count($results),
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('BoardController: Debate round failed', [
                'session_id' => $sessionId,
                'round' => $round,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consolidate the final decision.
     * 
     * POST /api/board/sessions/{id}/consolidate
     * 
     * @param string $sessionId
     * @return JsonResponse
     */
    public function consolidateDecision(string $sessionId): JsonResponse
    {
        try {
            $decision = $this->boardService->consolidateDecision($sessionId);
            
            if (!$decision) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to consolidate decision',
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $decision->id,
                    'session_id' => $sessionId,
                    'decision_text' => $decision->decision_text,
                    'confidence_score' => $decision->confidence_score,
                    'confidence_level' => $decision->confidence_level,
                    'reasoning' => $decision->reasoning,
                    'created_at' => $decision->created_at->toIso8601String(),
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('BoardController: Consolidation failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the session transcript.
     * 
     * GET /api/board/sessions/{id}/transcript
     * 
     * @param string $sessionId
     * @return JsonResponse
     */
    public function getTranscript(string $sessionId): JsonResponse
    {
        $session = BoardSession::find($sessionId);
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }
        
        $transcript = $this->boardService->getTranscript($sessionId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $sessionId,
                'question' => $session->question,
                'transcript' => $transcript,
                'entry_count' => count($transcript),
            ],
        ]);
    }

    /**
     * Close a board session.
     * 
     * DELETE /api/board/sessions/{id}
     * 
     * @param string $sessionId
     * @return JsonResponse
     */
    public function closeSession(string $sessionId): JsonResponse
    {
        try {
            $session = $this->boardService->closeSession($sessionId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $sessionId,
                    'status' => $session->status,
                    'completed_at' => $session->completed_at?->toIso8601String(),
                ],
                'message' => 'Session closed',
            ]);
            
        } catch (\Exception $e) {
            Log::error('BoardController: Close session failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get session status (for polling).
     * 
     * GET /api/board/sessions/{sessionId}/status
     * 
     * @param string $sessionId
     * @return JsonResponse
     */
    public function getSessionStatus($sessionId): JsonResponse
    {
        $session = BoardSession::find($sessionId);
        
        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'status' => $session->status,
            'question' => $session->question,
            'has_decision' => $session->final_decision !== null,
        ]);
    }

    /**
     * Start background processing of board session via queue.
     * 
     * POST /api/board/sessions/{sessionId}/start
     * 
     * @param string $sessionId
     * @return JsonResponse
     */
    public function startProcessing($sessionId): JsonResponse
    {
        $session = BoardSession::find($sessionId);
        
        if (!$session) {
            return response()->json(['success' => false, 'error' => 'Session not found'], 404);
        }

        // Update status
        $session->update(['status' => 'debating']);
        
        // Dispatch to queue (non-blocking)
        ProcessBoardDebate::dispatch($sessionId);
        
        Log::info('BoardController: Processing queued', [
            'session_id' => $sessionId,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Processing started',
            'status' => 'debating',
        ]);
    }
}
