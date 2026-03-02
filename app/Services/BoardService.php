<?php

namespace App\Services;

use App\Models\BoardSession;
use App\Models\BoardParticipant;
use App\Models\BoardDiscussionEntry;
use App\Models\BoardDecision;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BoardService
{
    protected string $openrouterKey;

    protected array $personas = [
        'COO' => [
            'name' => 'Gwynne',
            'model' => 'z-ai/glm-5',
            'emoji' => '👔',
            'focus' => 'operations, efficiency, execution',
        ],
        'CFO' => [
            'name' => 'Warren',
            'model' => 'z-ai/glm-5',
            'emoji' => '💰',
            'focus' => 'finance, ROI, risk management',
        ],
        'CTO' => [
            'name' => 'Werner',
            'model' => 'z-ai/glm-5',
            'emoji' => '💻',
            'focus' => 'technology, architecture, scalability',
        ],
        'CMO' => [
            'name' => 'Bozoma',
            'model' => 'z-ai/glm-5',
            'emoji' => '📢',
            'focus' => 'marketing, brand, customer acquisition',
        ],
        'CPO' => [
            'name' => 'Fidji',
            'model' => 'z-ai/glm-5',
            'emoji' => '📦',
            'focus' => 'product, user experience, roadmap',
        ],
    ];

    public function __construct()
    {
        $this->openrouterKey = config('services.openrouter.key') ?: env('OPENROUTER_API_KEY');
    }

    /**
     * Start a board session with a question and list of personas.
     *
     * @param string $question The question to discuss
     * @param array $personas List of persona roles (e.g., ['COO', 'CFO', 'CTO'])
     * @return BoardSession
     */
    public function startSession(string $question, array $personas = []): BoardSession
    {
        if (empty($personas)) {
            $personas = ['COO', 'CFO', 'CTO', 'CMO', 'CPO'];
        }

        // Create the session
        $session = BoardSession::create([
            'question' => $question,
            'status' => 'pending',
        ]);

        // Initialize participants
        foreach ($personas as $role) {
            $role = strtoupper($role);
            if (!isset($this->personas[$role])) {
                Log::warning("BoardService: Unknown persona role '{$role}', skipping.");
                continue;
            }

            BoardParticipant::create([
                'session_id' => $session->id,
                'persona_role' => $role,
                'model_config' => [
                    'model' => $this->personas[$role]['model'],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ],
            ]);
        }

        // Mark as started
        $session->update([
            'status' => 'debating',
            'started_at' => now(),
        ]);

        Log::info('BoardService: Session started', [
            'session_id' => $session->id,
            'question' => $question,
            'participant_count' => count($personas),
        ]);

        return $session;
    }

    /**
     * Run one round of debate for all participants.
     *
     * @param string $sessionId Session ID
     * @param int $round Round number
     * @return array Results with participant responses
     */
    public function runDebateRound(string $sessionId, int $round): array
    {
        $session = BoardSession::find($sessionId);
        
        if (!$session) {
            throw new \Exception("Session not found: {$sessionId}");
        }

        if ($session->status !== 'debating') {
            throw new \Exception("Session is not in debating state: {$session->status}");
        }

        $participants = $session->participants()->get();
        $results = [];

        // Get previous round context
        $previousContext = $this->getPreviousRoundContext($sessionId, $round);

        foreach ($participants as $participant) {
            $response = $this->generateParticipantResponse($participant, $session, $round, $previousContext);
            
            if ($response) {
                BoardDiscussionEntry::create([
                    'session_id' => $sessionId,
                    'participant_id' => $participant->id,
                    'round' => $round,
                    'message' => $this->buildPromptMessage($participant, $session, $round, $previousContext),
                    'model_response' => $response,
                ]);

                $results[] = [
                    'participant_id' => $participant->id,
                    'persona_role' => $participant->persona_role,
                    'name' => $participant->getPersonaName(),
                    'emoji' => $participant->getAvatarEmoji(),
                    'response' => $response,
                    'round' => $round,
                ];
            }
        }

        Log::info('BoardService: Debate round completed', [
            'session_id' => $sessionId,
            'round' => $round,
            'responses' => count($results),
        ]);

        return $results;
    }

    /**
     * Consolidate the discussion into a final decision.
     *
     * @param string $sessionId Session ID
     * @return BoardDecision|null
     */
    public function consolidateDecision(string $sessionId): ?BoardDecision
    {
        $session = BoardSession::find($sessionId);
        
        if (!$session) {
            throw new \Exception("Session not found: {$sessionId}");
        }

        // Get full transcript
        $transcript = $this->getTranscript($sessionId);

        // Generate the decision using GLM-5
        $decision = $this->synthesizeDecision($session->question, $transcript);

        if (!$decision) {
            Log::error('BoardService: Failed to synthesize decision', [
                'session_id' => $sessionId,
            ]);
            return null;
        }

        // Parse the decision
        $parsed = $this->parseDecisionResponse($decision);

        // Create the decision record
        $boardDecision = BoardDecision::create([
            'session_id' => $sessionId,
            'decision_text' => $parsed['decision'],
            'confidence_score' => $parsed['confidence'] ?? null,
            'reasoning' => $parsed['reasoning'] ?? null,
        ]);

        // Update session status
        $session->update([
            'status' => 'decided',
            'decision_summary' => $parsed['decision'],
            'completed_at' => now(),
        ]);

        Log::info('BoardService: Decision consolidated', [
            'session_id' => $sessionId,
            'confidence' => $parsed['confidence'] ?? null,
        ]);

        return $boardDecision;
    }

    /**
     * Get the full discussion transcript.
     *
     * @param string $sessionId Session ID
     * @return array
     */
    public function getTranscript(string $sessionId): array
    {
        $entries = BoardDiscussionEntry::where('session_id', $sessionId)
            ->with('participant')
            ->orderBy('round')
            ->orderBy('created_at')
            ->get();

        return $entries->map(function ($entry) {
            return [
                'participant_id' => $entry->participant_id,
                'persona_role' => $entry->participant?->persona_role,
                'name' => $entry->participant?->getPersonaName(),
                'emoji' => $entry->participant?->getAvatarEmoji(),
                'round' => $entry->round,
                'message' => $entry->message,
                'response' => $entry->model_response,
                'created_at' => $entry->created_at->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Close a session.
     *
     * @param string $sessionId Session ID
     * @return BoardSession
     */
    public function closeSession(string $sessionId): BoardSession
    {
        $session = BoardSession::find($sessionId);
        
        if (!$session) {
            throw new \Exception("Session not found: {$sessionId}");
        }

        $session->update([
            'status' => 'closed',
            'completed_at' => now(),
        ]);

        Log::info('BoardService: Session closed', [
            'session_id' => $sessionId,
        ]);

        return $session;
    }

    /**
     * Generate a response from a specific participant.
     */
    protected function generateParticipantResponse(
        BoardParticipant $participant,
        BoardSession $session,
        int $round,
        array $previousContext = []
    ): ?string {
        $modelConfig = $participant->model_config ?? ['model' => 'z-ai/glm-5'];
        $prompt = $this->buildPromptMessage($participant, $session, $round, $previousContext);
        $systemPrompt = $participant->getSystemPrompt();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openrouterKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $modelConfig['model'] ?? 'z-ai/glm-5',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $modelConfig['max_tokens'] ?? 500,
                'temperature' => $modelConfig['temperature'] ?? 0.7,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $reasoning = $response->json('choices.0.message.reasoning');
                
                // GLM-5 reasoning models may return content in different fields
                return $content ?: $reasoning ?: '[No response content]';
            }

            Log::error('BoardService: API error', [
                'participant' => $participant->persona_role,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BoardService: Exception', [
                'participant' => $participant->persona_role,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build the prompt message for a participant.
     */
    protected function buildPromptMessage(
        BoardParticipant $participant,
        BoardSession $session,
        int $round,
        array $previousContext = []
    ): string {
        $prompt = "BOARD MEETING DISCUSSION\n\n";
        $prompt .= "QUESTION: {$session->question}\n\n";

        if (!empty($previousContext)) {
            $prompt .= "PREVIOUS DISCUSSION:\n";
            foreach ($previousContext as $entry) {
                $prompt .= "- {$entry['name']} ({$entry['persona_role']}): {$entry['response']}\n";
            }
            $prompt .= "\n";
        }

        $roundInfo = $round > 1 ? "This is round {$round} of discussion. " : '';
        $prompt .= "{$roundInfo}As the {$participant->persona_role}, provide your perspective on this question. ";
        
        if (!empty($previousContext)) {
            $prompt .= "Consider the points raised by other executives above. ";
            if ($round > 1) {
                $prompt .= "You can agree, disagree, or build on their ideas. ";
            }
        }

        $prompt .= "Be specific, opinionated, and concise (2-3 paragraphs max).";

        return $prompt;
    }

    /**
     * Get discussion context from previous rounds.
     */
    protected function getPreviousRoundContext(string $sessionId, int $currentRound): array
    {
        if ($currentRound <= 1) {
            return [];
        }

        $entries = BoardDiscussionEntry::where('session_id', $sessionId)
            ->with('participant')
            ->where('round', '<', $currentRound)
            ->orderBy('round')
            ->orderBy('created_at')
            ->get();

        return $entries->map(function ($entry) {
            return [
                'participant_id' => $entry->participant_id,
                'persona_role' => $entry->participant?->persona_role,
                'name' => $entry->participant?->getPersonaName(),
                'response' => $entry->model_response,
            ];
        })->toArray();
    }

    /**
     * Synthesize a final decision from all discussion entries.
     */
    protected function synthesizeDecision(string $question, array $transcript): ?string
    {
        $transcriptText = '';
        foreach ($transcript as $entry) {
            $transcriptText .= "{$entry['name']} ({$entry['persona_role']}): {$entry['response']}\n\n";
        }

        $systemPrompt = "You are synthesizing a board meeting into a final decision. " .
                        "Your job is to:\n" .
                        "1. Analyze all the perspectives\n" .
                        "2. Form a clear recommendation/decision\n" .
                        "3. Assess your confidence in this decision (0.0 to 1.0)\n" .
                        "4. Provide reasoning for your conclusion\n\n" .
                        "Format your response EXACTLY as:\n" .
                        "DECISION: [your clear decision/recommendation]\n\n" .
                        "CONFIDENCE: [a number between 0.0 and 1.0]\n\n" .
                        "REASONING: [brief explanation of why, incorporating key points from the discussion]";

        $userPrompt = "QUESTION: {$question}\n\n" .
                      "DISCUSSION TRANSCRIPT:\n\n{$transcriptText}\n\n" .
                      "Synthesize the board's discussion into a final decision.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openrouterKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'z-ai/glm-5',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 800,
                'temperature' => 0.5,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('BoardService: Exception synthesizing decision', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse the decision response into structured format.
     */
    protected function parseDecisionResponse(string $content): array
    {
        // Extract decision
        preg_match('/DECISION:\s*(.+?)(?=CONFIDENCE:|$)/s', $content, $decisionMatch);
        $decision = trim($decisionMatch[1] ?? $content);

        // Extract confidence
        preg_match('/CONFIDENCE:\s*([\d.]+)/', $content, $confidenceMatch);
        $confidence = isset($confidenceMatch[1]) ? (float) $confidenceMatch[1] : null;
        
        // Clamp confidence to 0.0-1.0
        if ($confidence !== null) {
            $confidence = max(0.0, min(1.0, $confidence));
        }

        // Extract reasoning
        preg_match('/REASONING:\s*(.+?)$/s', $content, $reasoningMatch);
        $reasoning = trim($reasoningMatch[1] ?? '');

        return [
            'decision' => $decision,
            'confidence' => $confidence,
            'reasoning' => $reasoning ?: null,
        ];
    }
}
