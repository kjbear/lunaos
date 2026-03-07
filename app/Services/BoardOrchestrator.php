<?php

namespace App\Services;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BoardOrchestrator
{
    protected string $openclawUrl;
    protected string $openclawToken;
    protected ?string $openrouterKey;
    protected ?string $ollamaCloudKey;
    protected string $ollamaCloudUrl;

    // Model mapping - use Ollama Cloud for glm-5
    protected array $modelMap = [
        'ollama-local/glm-5' => 'glm-5:cloud',
        'glm-5' => 'glm-5:cloud',
        'haiku' => 'claude-3-haiku',
        'dolphin' => 'dolphin-3.0',
    ];

    public function __construct()
    {
        $this->openclawUrl = config('lunaos.openclaw_url', 'http://127.0.0.1:18789');
        $this->openclawToken = config('lunaos.openclaw_token', '');
        $this->openrouterKey = config('services.openrouter.key') ?: env('OPENROUTER_API_KEY');
        $this->ollamaCloudKey = config('ai.providers.ollama.key') ?: env('OLLAMA_CLOUD_API_KEY');
        $this->ollamaCloudUrl = 'https://ollama.com/api/chat';
    }

    /**
     * Run a full board session.
     */
    public function runSession(string $sessionId, string $question, ?string $context = null): void
    {
        // Allow up to 5 minutes for board session execution
        set_time_limit(300);
        
        $members = Persona::where('role', 'board_member')
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        if ($members->isEmpty()) {
            Log::error('BoardOrchestrator: No active board members found');
            return;
        }

        $order = 0;
        $responses = [];

        // Phase 1: Each board member provides their perspective
        foreach ($members as $member) {
            $response = $this->getBoardMemberResponse($member, $question, $context, $order);
            
            if ($response) {
                BoardResponse::create([
                    'session_id' => $sessionId,
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'member_role' => $member->title ?? ($member->role === 'board_member' ? 'Executive' : $member->role),
                    'response' => $response,
                    'model_used' => $member->model,
                    'response_order' => $order,
                ]);
                
                $responses[] = [
                    'name' => $member->name,
                    'title' => $member->title,
                    'response' => $response,
                ];
            }
            
            $order++;
        }

        // Phase 2: CEO synthesizes final decision
        $decision = $this->synthesizeDecision($question, $responses);
        
        if ($decision) {
            BoardSession::where('id', $sessionId)->update([
                'status' => 'decided',
                'final_decision' => $decision['recommendation'] ?? 'Unable to reach a decision.',
                'risks_benefits' => $decision['risks_benefits'] ?? null,
                'decided_at' => now(),
            ]);
        }
    }

    /**
     * Get a response from a board member.
     */
    protected function getBoardMemberResponse(Persona $member, string $question, ?string $context, int $order): ?string
    {
        $model = $this->modelMap[$member->model] ?? $this->modelMap['glm-5'];
        
        $systemPrompt = $this->buildSystemPrompt($member);
        $userPrompt = $this->buildUserPrompt($member, $question, $context);

        // Use Ollama Cloud for ollama-local/glm-5 models, OpenRouter for others
        $useOllamaCloud = str_contains($member->model, 'ollama') || str_contains($member->model, 'glm-5');
        
        if ($useOllamaCloud && !empty($this->ollamaCloudKey)) {
            return $this->getOllamaCloudResponse($model, $systemPrompt, $userPrompt, $member->name);
        } else {
            return $this->getOpenRouterResponse($model, $systemPrompt, $userPrompt, $member->name);
        }
    }

    /**
     * Get response from Ollama Cloud API.
     */
    protected function getOllamaCloudResponse(string $model, string $systemPrompt, string $userPrompt, string $memberName): ?string
    {
        if (empty($this->ollamaCloudKey)) {
            Log::error('BoardOrchestrator: No Ollama Cloud API key configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->ollamaCloudKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->ollamaCloudUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'stream' => false,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                // Ollama Cloud returns: { message: { role, content } }
                return $body['message']['content'] ?? '[No content]';
            }

            Log::error('BoardOrchestrator: Ollama Cloud API error', [
                'member' => $memberName,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BoardOrchestrator: Ollama Cloud exception', [
                'member' => $memberName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get response from OpenRouter API (fallback).
     */
    protected function getOpenRouterResponse(string $model, string $systemPrompt, string $userPrompt, string $memberName): ?string
    {
        if (empty($this->openrouterKey)) {
            Log::error('BoardOrchestrator: No OpenRouter API key configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openrouterKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
            ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $reasoning = $response->json('choices.0.message.reasoning');
                
                return $content ?: $reasoning ?: null;
            }

            Log::error('BoardOrchestrator: OpenRouter API error', [
                'member' => $memberName,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BoardOrchestrator: OpenRouter exception', [
                'member' => $memberName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build the system prompt for a board member.
     */
    protected function buildSystemPrompt(Persona $member): string
    {
        $inspiration = $member->inspiration ?? 'a successful business leader';
        
        return "You are {$member->name}, the {$member->title} of a company. " .
               "You are inspired by {$inspiration}. " .
               "Provide your perspective as the {$member->title} in a concise, professional manner. " .
               "Focus on aspects relevant to your role. " .
               "Be opinionated and specific. " .
               "Limit your response to 2-3 paragraphs.";
    }

    /**
     * Build the user prompt for a board member.
     */
    protected function buildUserPrompt(Persona $member, string $question, ?string $context): string
    {
        $prompt = "BOARD MEETING\n\n";
        $prompt .= "Question: {$question}\n\n";
        
        if ($context) {
            $prompt .= "Context:\n{$context}\n\n";
        }
        
        $prompt .= "As the {$member->title}, what is your perspective on this question? " .
                   "Consider the strategic implications from your role's viewpoint. " .
                   "Be specific and actionable.";
        
        return $prompt;
    }

    /**
     * Synthesize the final decision from all responses.
     */
    protected function synthesizeDecision(string $question, array $responses): ?array
    {
        $model = $this->modelMap['glm-5'];
        
        // Use Ollama Cloud for synthesis (same as executives)
        if (empty($this->ollamaCloudKey)) {
            Log::error('BoardOrchestrator: No Ollama Cloud API key for synthesis');
            return null;
        }
        
        $transcript = collect($responses)
            ->map(fn($r) => "**{$r['title']} ({$r['name']}):** {$r['response']}")
            ->join("\n\n");

        $systemPrompt = "You are synthesizing a board meeting discussion into a clear recommendation. " .
                        "Provide:\n" .
                        "1. A concise RECOMMENDATION (2-3 sentences)\n" .
                        "2. Key RISKS to consider\n" .
                        "3. Key BENEFITS of the recommendation\n\n" .
                        "Format your response as:\n" .
                        "RECOMMENDATION: [your recommendation]\n\n" .
                        "RISKS:\n- [risk 1]\n- [risk 2]\n\n" .
                        "BENEFITS:\n- [benefit 1]\n- [benefit 2]";

        $userPrompt = "QUESTION: {$question}\n\n" .
                      "BOARD DISCUSSION:\n\n{$transcript}\n\n" .
                      "Provide the board's synthesized recommendation.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->ollamaCloudKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->ollamaCloudUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'stream' => false,
            ]);

            if ($response->successful()) {
                $content = $response->json('message.content');
                
                return $this->parseDecision($content);
            }

            Log::error('BoardOrchestrator: Ollama Cloud synthesis error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BoardOrchestrator: Exception synthesizing decision', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse the decision response into structured format.
     */
    protected function parseDecision(string $content): array
    {
        // Extract recommendation
        preg_match('/RECOMMENDATION:\s*(.+?)(?=RISKS:|$)/s', $content, $recMatch);
        $recommendation = trim($recMatch[1] ?? $content);

        // Extract risks
        preg_match('/RISKS:\s*(.+?)(?=BENEFITS:|$)/s', $content, $riskMatch);
        $risks = trim($riskMatch[1] ?? '');

        // Extract benefits
        preg_match('/BENEFITS:\s*(.+?)$/s', $content, $benMatch);
        $benefits = trim($benMatch[1] ?? '');

        $risksBenefits = '';
        if ($risks || $benefits) {
            $risksBenefits = "Risks:\n{$risks}\n\nBenefits:\n{$benefits}";
        }

        return [
            'recommendation' => $recommendation,
            'risks_benefits' => $risksBenefits ?: null,
        ];
    }

    /**
     * Check if the OpenRouter API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty(config('services.openrouter.key')) || !empty(env('OPENROUTER_API_KEY'));
    }
}
