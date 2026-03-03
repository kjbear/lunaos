<?php

namespace App\Services;

use App\Agents\Personas\ExecutivePersona;
use App\Models\BoardSession;
use App\Models\BoardResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * BoardDebateOrchestrator
 * 
 * Manages executive board debate sessions using OpenClaw agent orchestration.
 * Handles multi-round debates where personas can respond to each other.
 */
class BoardDebateOrchestrator
{
    /**
     * @var array<ExecutivePersona>
     */
    protected array $personas = [];

    /**
     * @var int Number of debate rounds
     */
    protected int $maxRounds = 2;

    /**
     * @var int Timeout per persona response in seconds
     */
    protected int $personaTimeout = 120;

    /**
     * @var string Model to use for all personas
     */
    protected string $model = 'glm-5';

    /**
     * @var array Collected responses during debate
     */
    protected array $responses = [];

    /**
     * @var string|null Current session ID
     */
    protected ?string $sessionId = null;

    /**
     * @var string|null Current question
     */
    protected ?string $question = null;

    /**
     * @var string|null Additional context
     */
    protected ?string $context = null;

    public function __construct()
    {
        // Load configuration
        $this->maxRounds = config('executive-board.rounds', 2);
        $this->personaTimeout = config('executive-board.timeout_seconds', 120);
        $this->model = config('executive-board.model', 'glm-5');
        
        // Initialize default personas
        $this->initializePersonas();
    }

    /**
     * Initialize executive personas from configuration.
     */
    protected function initializePersonas(): void
    {
        $personaConfigs = config('executive-board.personas', []);

        if (empty($personaConfigs)) {
            // Fallback to default personas
            $this->loadDefaultPersonas();
            return;
        }

        foreach ($personaConfigs as $config) {
            $personaClass = $config['class'] ?? null;
            if ($personaClass && class_exists($personaClass)) {
                $persona = new $personaClass();
                $persona->model = $config['model'] ?? $this->model;
                $this->personas[] = $persona;
            }
        }
    }

    /**
     * Load default personas when config is not available.
     */
    protected function loadDefaultPersonas(): void
    {
        $defaultPersonas = [
            \App\Agents\Personas\COOPersona::class,
            \App\Agents\Personas\CFOPersona::class,
            \App\Agents\Personas\CTOPersona::class,
            \App\Agents\Personas\CMOPersona::class,
            \App\Agents\Personas\CPOPersona::class,
        ];

        foreach ($defaultPersonas as $class) {
            $this->personas[] = new $class();
        }
    }

    /**
     * Run a full board debate session.
     * 
     * @param string $question The strategic question to debate
     * @param string|null $context Additional context
     * @return array{session_id: string, transcript: array, decision: array}
     */
    public function runDebate(string $question, ?string $context = null): array
    {
        // Allow up to 5 minutes for board debate execution
        set_time_limit(300);
        
        $this->question = $question;
        $this->context = $context;
        $this->responses = [];

        // Create board session record
        $session = BoardSession::create([
            'question' => $question,
            'context' => $context,
            'status' => 'debating',
            'rounds_planned' => $this->maxRounds,
        ]);

        $this->sessionId = $session->id;

        Log::info('BoardDebateOrchestrator: Starting debate session', [
            'session_id' => $this->sessionId,
            'question' => $question,
            'personas' => count($this->personas),
        ]);

        // Run debate rounds
        for ($round = 1; $round <= $this->maxRounds; $round++) {
            Log::info('BoardDebateOrchestrator: Starting round ' . $round);
            
            $roundResponses = $this->runRound($round);
            
            if (empty($roundResponses)) {
                Log::warning('BoardDebateOrchestrator: Round ' . $round . ' produced no responses');
                break;
            }

            $this->responses = array_merge($this->responses, $roundResponses);
        }

        // Consolidate decision
        $consolidator = new BoardDecisionConsolidator();
        $decision = $consolidator->consolidate(
            $question,
            $this->responses,
            $context
        );

        // Update session with final decision
        $session->update([
            'status' => 'decided',
            'final_decision' => $decision['recommendation'] ?? 'Unable to reach a decision.',
            'risks_benefits' => $decision['risks_benefits'] ?? null,
            'confidence_score' => $decision['confidence_score'] ?? 0.5,
            'dissenting_opinions' => json_encode($decision['dissenting_opinions'] ?? []),
            'decided_at' => now(),
        ]);

        Log::info('BoardDebateOrchestrator: Debate session complete', [
            'session_id' => $this->sessionId,
            'total_responses' => count($this->responses),
            'decision_confidence' => $decision['confidence_score'] ?? 0,
        ]);

        return [
            'session_id' => $this->sessionId,
            'transcript' => $this->responses,
            'decision' => $decision,
        ];
    }

    /**
     * Run a single debate round.
     * 
     * @param int $round Round number
     * @return array Responses from this round
     */
    protected function runRound(int $round): array
    {
        $roundResponses = [];
        $previousResponses = $this->getPreviousResponses($round);

        foreach ($this->personas as $persona) {
            $response = $this->getPersonaResponse($persona, $round, $previousResponses);
            
            if ($response) {
                // Store response
                $responseData = [
                    'persona_class' => get_class($persona),
                    'name' => $persona->name,
                    'title' => $persona->title,
                    'avatar' => $persona->avatar,
                    'round' => $round,
                    'response' => $response,
                    'model' => $persona->model,
                    'timestamp' => now(),
                ];

                $roundResponses[] = $responseData;

                // Save to database
                BoardResponse::create([
                    'session_id' => $this->sessionId,
                    'member_id' => null, // Persona-based responses don't have member_id
                    'member_name' => $persona->name,
                    'member_role' => $persona->title,
                    'response' => $response,
                    'model_used' => $persona->model,
                    'response_order' => count($this->responses) + count($roundResponses),
                    'round' => $round,
                ]);

                Log::debug('BoardDebateOrchestrator: Got response from ' . $persona->name, [
                    'round' => $round,
                    'length' => strlen($response),
                ]);
            }
        }

        return $roundResponses;
    }

    /**
     * Get responses from all previous rounds for context.
     */
    protected function getPreviousResponses(int $currentRound): array
    {
        if ($currentRound <= 1) {
            return [];
        }

        return array_filter($this->responses, fn($r) => $r['round'] < $currentRound);
    }

    /**
     * Get a response from a single persona using OpenClaw sessions_spawn.
     * 
     * @param ExecutivePersona $persona
     * @param int $round
     * @param array $previousResponses
     * @return string|null
     */
    protected function getPersonaResponse(ExecutivePersona $persona, int $round, array $previousResponses): ?string
    {
        $systemPrompt = $persona->getSystemPrompt();
        $userPrompt = $persona->buildPrompt(
            $this->question,
            $this->context,
            $previousResponses,
            $round
        );

        // Build the task for the sub-agent
        $task = $this->buildPersonaTask($systemPrompt, $userPrompt, $persona->model);

        try {
            // Spawn a sub-agent for this persona's response
            // Note: In a real implementation, this would call OpenClaw's sessions_spawn API
            // For now, we simulate the behavior with direct model calls or OpenRouter
            $response = $this->executePersonaTask($task, $persona->model, $this->personaTimeout);
            
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            Log::error('BoardDebateOrchestrator: Failed to get persona response', [
                'persona' => $persona->title,
                'round' => $round,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build the task description for a persona sub-agent.
     */
    protected function buildPersonaTask(string $systemPrompt, string $userPrompt, string $model): string
    {
        return <<<TASK
You are role-playing as an executive board member.

SYSTEM INSTRUCTIONS:
{$systemPrompt}

YOUR TASK:
{$userPrompt}

Provide your response as the executive. Be specific, opinionated, and actionable.
TASK;
    }

    /**
     * Execute the persona task and get response.
     * This is where OpenClaw's sessions_spawn would be integrated.
     * 
     * @param string $task
     * @param string $model
     * @param int $timeout
     * @return array|string
     */
    protected function executePersonaTask(string $task, string $model, int $timeout)
    {
        // TODO: Integrate with OpenClaw's sessions_spawn() method
        // This is a placeholder that uses direct OpenRouter API call
        
        return $this->callOpenRouter($task, $model);
    }

    /**
     * Call OpenRouter API directly (fallback implementation).
     * 
     * @param string $task
     * @param string $model
     * @return array|null
     */
    protected function callOpenRouter(string $task, string $model): ?array
    {
        $modelMap = [
            'glm-5' => 'z-ai/glm-5',
            'haiku' => 'anthropic/claude-3-haiku-20240307',
            'sonnet' => 'anthropic/claude-3-5-sonnet-20241022',
        ];

        $openRouterModel = $modelMap[$model] ?? 'z-ai/glm-5';
        $apiKey = config('services.openrouter.key') ?: env('OPENROUTER_API_KEY');

        if (empty($apiKey)) {
            throw new \Exception('OpenRouter API key not configured');
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url', 'http://localhost'),
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $openRouterModel,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an executive board member. Follow your role instructions precisely.'],
                ['role' => 'user', 'content' => $task],
            ],
            'max_tokens' => 600,
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('OpenRouter API call failed: ' . $response->status());
    }

    /**
     * Parse response from agent execution.
     */
    protected function parseResponse(array|string $response): ?string
    {
        if (is_array($response)) {
            return $response['choices'][0]['message']['content'] ?? null;
        }
        
        return is_string($response) ? $response : null;
    }

    /**
     * Get all personas.
     */
    public function getPersonas(): array
    {
        return $this->personas;
    }

    /**
     * Set custom personas.
     * 
     * @param array<ExecutivePersona> $personas
     */
    public function setPersonas(array $personas): void
    {
        $this->personas = $personas;
    }

    /**
     * Set max debate rounds.
     */
    public function setMaxRounds(int $rounds): void
    {
        $this->maxRounds = $rounds;
    }

    /**
     * Set model for all personas.
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }
}
