<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenClawService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('lunaos.openclaw_url', 'http://localhost:3000');
    }

    /**
     * Get list of agents from OpenClaw.
     */
    public function getAgents(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/agents");

            if ($response->successful()) {
                return $response->json('agents', []);
            }

            Log::warning('OpenClawService: Failed to get agents', [
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('OpenClawService: Exception getting agents', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get agent details from OpenClaw.
     */
    public function getAgent(string $agentId): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/agents/{$agentId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenClawService: Exception getting agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get session status from OpenClaw.
     */
    public function getSessionStatus(string $sessionKey): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/sessions/{$sessionKey}/status");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenClawService: Exception getting session status', [
                'session_key' => $sessionKey,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if OpenClaw is reachable.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}