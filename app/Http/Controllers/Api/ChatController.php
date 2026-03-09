<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\TeamMember;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ChatController
 * 
 * API endpoints for individual agent chat feature.
 * 
 * Routes:
 * - GET /api/chat - List user's chat sessions
 * - GET /api/chat/{session} - Get session with messages
 * - POST /api/chat - Create new session with team_member_id
 * - POST /api/chat/{session}/message - Send message, get AI response
 * - DELETE /api/chat/{session} - Delete session
 */
class ChatController
{
    /**
     * ChatService instance
     */
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * List user's chat sessions.
     * 
     * GET /api/chat
     * 
     * Query params:
     * - team_member_id: Filter by team member (optional)
     * - limit: Number of results (default 50)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'team_member_id' => 'nullable|string|exists:team_members,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $teamMemberId = $request->input('team_member_id');
        $limit = $request->input('limit', 50);

        $sessions = $this->chatService->getSessions($teamMemberId, $limit);

        return response()->json([
            'success' => true,
            'data' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'team_member_id' => $session->team_member_id,
                    'team_member_name' => $session->teamMember?->name,
                    'team_member_emoji' => $session->teamMember?->emoji,
                    'title' => $session->title ?? 'Untitled Chat',
                    'updated_at' => $session->updated_at?->toIso8601String(),
                    'created_at' => $session->created_at?->toIso8601String(),
                    'message_count' => $session->messages()->count(),
                ];
            }),
        ]);
    }

    /**
     * Get a specific chat session with messages.
     * 
     * GET /api/chat/{session}
     * 
     * Query params:
     * - include_context: Include context window (default true)
     * - include_metadata: Include metadata (default true)
     */
    public function show(Request $request, string $sessionId): JsonResponse
    {
        $session = $this->chatService->getSession($sessionId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $includeContext = $request->input('include_context', true);
        $includeMetadata = $request->input('include_metadata', true);

        $data = [
            'id' => $session->id,
            'team_member_id' => $session->team_member_id,
            'team_member' => $session->teamMember ? [
                'id' => $session->teamMember->id,
                'name' => $session->teamMember->name,
                'emoji' => $session->teamMember->emoji,
                'title' => $session->teamMember->title,
            ] : null,
            'title' => $session->title ?? 'Untitled Chat',
            'created_at' => $session->created_at?->toIso8601String(),
            'updated_at' => $session->updated_at?->toIso8601String(),
            'messages' => $session->messages->map(function ($msg) use ($includeMetadata) {
                $messageData = [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at?->toIso8601String(),
                ];
                
                if ($includeMetadata) {
                    $messageData['tokens'] = $msg->tokens;
                    $messageData['metadata'] = $msg->metadata;
                }
                
                return $messageData;
            }),
        ];

        if ($includeContext) {
            $data['context'] = $session->context;
            $data['context_token_count'] = $session->getContextTokenCount();
        }

        if ($includeMetadata && $session->metadata) {
            $data['metadata'] = $session->metadata;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create a new chat session.
     * 
     * POST /api/chat
     * 
     * Body:
     * - team_member_id: Required - UUID of team member to chat with
     * - title: Optional - Title for the session
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'team_member_id' => 'required|string|exists:team_members,id',
            'title' => 'nullable|string|max:255',
        ]);

        $teamMember = TeamMember::find($request->team_member_id);

        if (!$teamMember) {
            return response()->json([
                'success' => false,
                'error' => 'Team member not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $session = $this->chatService->createSession(
            $request->team_member_id,
            $request->title
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'team_member_id' => $session->team_member_id,
                'team_member' => [
                    'id' => $teamMember->id,
                    'name' => $teamMember->name,
                    'emoji' => $teamMember->emoji,
                    'title' => $teamMember->title,
                ],
                'title' => $session->title,
                'created_at' => $session->created_at?->toIso8601String(),
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Send a message to a chat session and get AI response.
     * 
     * POST /api/chat/{session}/message
     * 
     * Body:
     * - content: Required - Message content
     * - stream: Optional - Whether to stream response (default false)
     */
    public function message(Request $request, string $sessionId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:32000',
            'stream' => 'nullable|boolean',
        ]);

        $session = $this->chatService->getSession($sessionId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $content = $request->input('content');
        $stream = $request->boolean('stream');

        // For now, we don't support true streaming via API
        // Livewire frontend will handle the streaming UI
        $result = $this->chatService->sendMessage($session, $content, false);

        return response()->json([
            'success' => true,
            'data' => [
                'user_message' => [
                    'id' => $result['user_message']->id,
                    'role' => $result['user_message']->role,
                    'content' => $result['user_message']->content,
                    'tokens' => $result['user_message']->tokens,
                    'created_at' => $result['user_message']->created_at?->toIso8601String(),
                ],
                'assistant_message' => [
                    'id' => $result['assistant_message']->id,
                    'role' => $result['assistant_message']->role,
                    'content' => $result['assistant_message']->content,
                    'tokens' => $result['assistant_message']->tokens,
                    'metadata' => $result['assistant_message']->metadata,
                    'created_at' => $result['assistant_message']->created_at?->toIso8601String(),
                ],
                'session' => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'context_token_count' => $session->getContextTokenCount(),
                ],
            ],
        ]);
    }

    /**
     * Delete a chat session.
     * 
     * DELETE /api/chat/{session}
     */
    public function destroy(string $sessionId): JsonResponse
    {
        $deleted = $this->chatService->deleteSession($sessionId);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully',
        ]);
    }

    /**
     * Stream response (SSE endpoint for Livewire).
     * 
     * GET /api/chat/{session}/stream
     * 
     * This endpoint is designed for Server-Sent Events (SSE) streaming.
     * Livewire can dispatch events as tokens arrive.
     */
    public function stream(Request $request, string $sessionId): Response
    {
        $request->validate([
            'content' => 'required|string|max:32000',
        ]);

        $session = $this->chatService->getSession($sessionId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Set headers for SSE
        $response = response()->stream(function () use ($session, $request) {
            $content = $request->input('content');
            
            try {
                foreach ($this->chatService->streamResponse($session, $content) as $token) {
                    echo "data: " . json_encode(['token' => $token]) . "\n\n";
                    ob_flush();
                    flush();
                }
            } catch (\Exception $e) {
                echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
            }

            echo "data: " . json_encode(['done' => true]) . "\n\n";
        }, Response::HTTP_OK, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);

        return $response;
    }

    /**
     * Send a message and broadcast response via WebSockets.
     * 
     * POST /api/chat/{session}/broadcast
     * 
     * This endpoint uses the streamMessageWithBroadcast method to send
     * real-time updates via Laravel Reverb WebSockets.
     * 
     * Body:
     * - content: Required - Message content
     * 
     * Response is immediate, but WebSockets broadcast:
     * - UserMessageSent event immediately
     * - AiTokenReceived events for each token
     * - AiResponseComplete event when done
     */
    public function broadcast(Request $request, string $sessionId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:32000',
        ]);

        $session = $this->chatService->getSession($sessionId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $content = $request->input('content');

        // Process the message and broadcast via WebSockets
        // This runs synchronously but broadcasts events as it progresses
        try {
            $result = $this->chatService->streamMessageWithBroadcast($session, $content);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => [
                        'id' => $result['user_message']->id,
                        'role' => $result['user_message']->role,
                        'content' => $result['user_message']->content,
                        'tokens' => $result['user_message']->tokens,
                        'created_at' => $result['user_message']->created_at?->toIso8601String(),
                    ],
                    'assistant_message' => [
                        'id' => $result['assistant_message']->id,
                        'role' => $result['assistant_message']->role,
                        'content' => $result['assistant_message']->content,
                        'tokens' => $result['assistant_message']->tokens,
                        'metadata' => $result['assistant_message']->metadata,
                        'created_at' => $result['assistant_message']->created_at?->toIso8601String(),
                    ],
                    'broadcast_channel' => "presence-chat.{$sessionId}",
                    'session' => [
                        'id' => $session->id,
                        'title' => $session->title,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process message: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}