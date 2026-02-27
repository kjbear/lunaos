<?php

namespace App\Services;

use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityIngestService
{
    /**
     * Ingest activity from OpenClaw webhook
     */
    public function ingestFromWebhook(array $payload): ActivityLog
    {
        return ActivityLog::create([
            'agent' => $payload['agent'] ?? 'Unknown',
            'action_type' => $payload['action_type'] ?? 'unknown',
            'action_name' => $payload['action_name'] ?? 'Unknown action',
            'context' => $payload['context'] ?? null,
            'impact' => $payload['impact'] ?? 'low',
            'status' => $payload['status'] ?? 'success',
            'created_at' => $payload['timestamp'] ?? now(),
        ]);
    }

    /**
     * Poll OpenClaw sessions for activity
     */
    public function pollFromOpenClaw(): int
    {
        $count = 0;
        
        // Get recent sessions
        $sessions = $this->fetchOpenClawSessions();
        
        foreach ($sessions as $session) {
            // Check if we've already logged this session's activity
            $existingActivity = ActivityLog::where('context->session_key', $session['key'] ?? null)->first();
            
            if (!$existingActivity) {
                $this->ingestSession($session);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Fetch sessions from OpenClaw Gateway
     */
    private function fetchOpenClawSessions(): array
    {
        // OpenClaw Gateway RPC endpoint
        $url = config('lunaos.openclaw_url', 'http://127.0.0.1:18789');
        $token = config('lunaos.openclaw_token');
        
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            
            // Use Gateway RPC to list sessions
            $response = $client->post("{$url}/rpc", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'method' => 'sessions.list',
                    'params' => [
                        'limit' => 20,
                        'activeMinutes' => 60,
                    ],
                ],
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data['result']['sessions'] ?? [];
        } catch (\Exception $e) {
            \Log::warning('Failed to poll OpenClaw sessions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert OpenClaw session to activity log
     */
    private function ingestSession(array $session): ActivityLog
    {
        $agentName = $this->determineAgent($session);
        $actionType = $this->determineActionType($session);
        $actionName = $this->determineActionName($session);
        
        return ActivityLog::create([
            'agent' => $agentName,
            'action_type' => $actionType,
            'action_name' => $actionName,
            'context' => json_encode([
                'session_key' => $session['key'] ?? null,
                'model' => $session['model'] ?? null,
                'messages' => $session['message_count'] ?? 0,
                'tokens_in' => $session['tokens_in'] ?? 0,
                'tokens_out' => $session['tokens_out'] ?? 0,
            ]),
            'impact' => $this->determineImpact($session),
            'status' => 'success',
            'created_at' => Carbon::parse($session['updated_at'] ?? now()),
        ]);
    }

    /**
     * Determine agent name from session
     */
    private function determineAgent(array $session): string
    {
        $key = $session['key'] ?? '';
        
        // Map session keys to agent names
        if (str_contains($key, 'subagent')) {
            return 'Subagent-' . strtoupper(substr($key, -1));
        }
        
        return 'Luna';
    }

    /**
     * Determine action type from session
     */
    private function determineActionType(array $session): string
    {
        $lastMessage = $session['last_message'] ?? '';
        
        if (str_contains(strtolower($lastMessage), 'commit')) return 'commit';
        if (str_contains(strtolower($lastMessage), 'create') || str_contains(strtolower($lastMessage), 'write')) return 'create';
        if (str_contains(strtolower($lastMessage), 'update') || str_contains(strtolower($lastMessage), 'edit')) return 'update';
        if (str_contains(strtolower($lastMessage), 'delete')) return 'delete';
        if (str_contains(strtolower($lastMessage), 'search')) return 'search';
        if (str_contains(strtolower($lastMessage), 'email')) return 'email';
        
        return 'task';
    }

    /**
     * Determine action name from session
     */
    private function determineActionName(array $session): string
    {
        $lastMessage = $session['last_message'] ?? '';
        
        // Extract first 50 chars of last user message
        return substr($lastMessage, 0, 50) ?: 'Session activity';
    }

    /**
     * Determine impact from session
     */
    private function determineImpact(array $session): string
    {
        $tokensOut = $session['tokens_out'] ?? 0;
        
        if ($tokensOut > 5000) return 'high';
        if ($tokensOut > 1000) return 'medium';
        
        return 'low';
    }
}