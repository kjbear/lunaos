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
    protected ?string $dolphinUrl;

    // Model mapping for board members (OpenRouter model IDs)
    protected array $modelMap = [
        'glm-5' => 'z-ai/glm-5',
        'haiku' => 'anthropic/claude-3-haiku',
        'dolphin' => 'anthropic/claude-3-haiku', // fallback to haiku for now
    ];

    public function __construct()
    {
        $this->openclawUrl = config('lunaos.openclaw_url', 'http://127.0.0.1:18789');
        $this->openclawToken = config('lunaos.openclaw_token', '');
        $this->openrouterKey = config('services.openrouter.key') ?: env('OPENROUTER_API_KEY');
        $this->dolphinUrl = env('DOLPHIN_URL', 'http://192.168.2.2:8080/v1');
    }

    /**
     * Run a full board session.
     */
    public function runSession(string $sessionId, string $question, ?string $context = null): void
    {
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
                    'member_role' => $member->role ?? $member->title ?? 'Executive',
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

        $apiKey = config('services.openrouter.key') ?: env('OPENROUTER_API_KEY');
        
        if (empty($apiKey)) {
            Log::error('BoardOrchestrator: No OpenRouter API key configured');
            return null;
        }

        try {
            // Use OpenRouter directly for speed
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
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
                
                // GLM-5 reasoning models may return content in different fields
                return $content ?: $reasoning ?: null;
            }

            Log::error('BoardOrchestrator: API error', [
                'member' => $member->name,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BoardOrchestrator: Exception', [
                'member' => $member->name,
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
        
        $apiKey = config('services.openrouter.key') ?: env('OPENROUTER_API_KEY');
        
        if (empty($apiKey)) {
            Log::error('BoardOrchestrator: No OpenRouter API key for synthesis');
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
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 800,
                'temperature' => 0.5,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                
                return $this->parseDecision($content);
            }

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
