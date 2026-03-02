<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * BoardDecisionConsolidator
 * 
 * Analyzes all persona positions from a board debate and generates
 * a unified decision with reasoning, confidence score, and dissenting opinions.
 */
class BoardDecisionConsolidator
{
    /**
     * @var string Model to use for consolidation
     */
    protected string $model = 'glm-5';

    public function __construct()
    {
        $this->model = config('executive-board.model', 'glm-5');
    }

    /**
     * Consolidate all persona responses into a unified decision.
     * 
     * @param string $question The original question
     * @param array $responses All persona responses from all rounds
     * @param string|null $context Additional context
     * @return array{
     *     recommendation: string,
     *     reasoning: string,
     *     risks_benefits: string|null,
     *     confidence_score: float,
     *     dissenting_opinions: array,
     *     key_themes: array,
     *     action_items: array
     * }
     */
    public function consolidate(string $question, array $responses, ?string $context = null): array
    {
        Log::info('BoardDecisionConsolidator: Starting consolidation', [
            'question' => $question,
            'response_count' => count($responses),
        ]);

        // Build transcript from responses
        $transcript = $this->buildTranscript($responses);

        // Analyze positions and generate decision
        $decision = $this->analyzeAndDecide($question, $transcript, $context);

        // Extract key themes
        $decision['key_themes'] = $this->extractKeyThemes($responses);

        // Extract action items
        $decision['action_items'] = $this->extractActionItems($decision['recommendation']);

        Log::info('BoardDecisionConsolidator: Consolidation complete', [
            'confidence' => $decision['confidence_score'],
            'dissenting_count' => count($decision['dissenting_opinions']),
        ]);

        return $decision;
    }

    /**
     * Build formatted transcript from responses.
     */
    protected function buildTranscript(array $responses): string
    {
        $lines = [];
        
        // Group by round
        $rounds = [];
        foreach ($responses as $response) {
            $round = $response['round'] ?? 1;
            if (!isset($rounds[$round])) {
                $rounds[$round] = [];
            }
            $rounds[$round][] = $response;
        }

        // Format by round
        foreach ($rounds as $round => $roundResponses) {
            $lines[] = "=== ROUND {$round} ===";
            
            foreach ($roundResponses as $r) {
                $lines[] = "**{$r['title']} ({$r['name']}):** {$r['response']}";
            }
            
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /**
     * Analyze all responses and generate unified decision.
     */
    protected function analyzeAndDecide(string $question, string $transcript, ?string $context): array
    {
        $systemPrompt = <<<'PROMPT'
You are analyzing an executive board debate to produce a unified decision.

Your task:
1. Analyze all executive perspectives
2. Identify areas of agreement and disagreement
3. Synthesize a clear, actionable recommendation
4. Assess confidence level (0.0 to 1.0)
5. Note any strong dissenting opinions

Format your response as JSON with this structure:
{
    "recommendation": "Clear, concise recommendation (2-3 sentences)",
    "reasoning": "Detailed reasoning explaining how you reached this conclusion",
    "risks_benefits": "Key risks and benefits in structured format",
    "confidence_score": 0.0-1.0,
    "dissenting_opinions": ["List of significant dissenting views, if any"],
    "key_tradeoffs": ["Main tradeoffs considered"]
}

Be fair to all perspectives. If there's genuine disagreement, acknowledge it.
If consensus is strong, reflect higher confidence.
PROMPT;

        $userPrompt = <<<'PROMPT'
BOARD QUESTION:
{$question}

{$contextText}

BOARD DEBATE TRANSCRIPT:
{$transcript}

Provide your analysis and recommendation in the specified JSON format.
PROMPT;

        $contextText = $context ? "CONTEXT:\n{$context}\n\n" : "";
        
        $userPrompt = str_replace('{$question}', $question, $userPrompt);
        $userPrompt = str_replace('{$contextText}', $contextText, $userPrompt);
        $userPrompt = str_replace('{$transcript}', $transcript, $userPrompt);

        try {
            $response = $this->callLLM($systemPrompt, $userPrompt);
            $parsed = $this->parseDecisionResponse($response);
            
            // Format risks & benefits as readable text
            $parsed['risks_benefits'] = $this->formatRisksBenefits($parsed);
            
            return $parsed;
        } catch (\Exception $e) {
            Log::error('BoardDecisionConsolidator: Failed to analyze', [
                'error' => $e->getMessage(),
            ]);

            // Return fallback decision
            return $this->createFallbackDecision($transcript);
        }
    }

    /**
     * Call LLM for analysis (OpenRouter API).
     */
    protected function callLLM(string $system, string $user): array
    {
        $modelMap = [
            'glm-5' => 'z-ai/glm-5',
            'haiku' => 'anthropic/claude-3-haiku-20240307',
            'sonnet' => 'anthropic/claude-3-5-sonnet-20241022',
        ];

        $openRouterModel = $modelMap[$this->model] ?? 'z-ai/glm-5';
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
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'max_tokens' => 1200,
            'temperature' => 0.3, // Lower temperature for more consistent analysis
        ]);

        if ($response->successful()) {
            $content = $response->json('choices.0.message.content');
            
            // Try to extract JSON from response
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                return json_decode($matches[0], true) ?? ['error' => 'Failed to parse JSON'];
            }
            
            return ['error' => 'No JSON found in response', 'raw' => $content];
        }

        throw new \Exception('LLM API call failed: ' . $response->status());
    }

    /**
     * Parse LLM response into structured decision.
     */
    protected function parseDecisionResponse(array $response): array
    {
        if (isset($response['error'])) {
            Log::warning('BoardDecisionConsolidator: LLM returned error', $response);
            
            return [
                'recommendation' => 'Unable to generate decision due to analysis error. Please review the debate transcript and make a manual decision.',
                'reasoning' => $response['raw'] ?? 'Parse error',
                'risks_benefits' => null,
                'confidence_score' => 0.0,
                'dissenting_opinions' => [],
            ];
        }

        return [
            'recommendation' => $response['recommendation'] ?? 'No recommendation generated.',
            'reasoning' => $response['reasoning'] ?? '',
            'risks_benefits' => null, // Will be formatted separately
            'confidence_score' => (float) ($response['confidence_score'] ?? 0.5),
            'dissenting_opinions' => $response['dissenting_opinions'] ?? [],
            'key_tradeoffs' => $response['key_tradeoffs'] ?? [],
        ];
    }

    /**
     * Format risks and benefits from parsed response.
     */
    protected function formatRisksBenefits(array $parsed): ?string
    {
        // If already formatted, return as-is
        if (isset($parsed['risks_benefits']) && is_string($parsed['risks_benefits'])) {
            return $parsed['risks_benefits'];
        }

        $output = [];

        // Try to extract from reasoning if not explicitly provided
        if (!empty($parsed['key_tradeoffs'])) {
            $output[] = "KEY TRADEOFFS:";
            foreach ($parsed['key_tradeoffs'] as $tradeoff) {
                $output[] = "- {$tradeoff}";
            }
        }

        if (!empty($parsed['risks'])) {
            $output[] = "\nRISKS:";
            foreach ($parsed['risks'] as $risk) {
                $output[] = "- {$risk}";
            }
        }

        if (!empty($parsed['benefits'])) {
            $output[] = "\nBENEFITS:";
            foreach ($parsed['benefits'] as $benefit) {
                $output[] = "- {$benefit}";
            }
        }

        return empty($output) ? null : implode("\n", $output);
    }

    /**
     * Create fallback decision when analysis fails.
     */
    protected function createFallbackDecision(string $transcript): array
    {
        return [
            'recommendation' => "The board discussed this question from multiple perspectives. Please review the full transcript above to make an informed decision.",
            'reasoning' => 'Automated analysis was unable to complete. Common themes from the discussion should be manually identified.',
            'risks_benefits' => null,
            'confidence_score' => 0.0,
            'dissenting_opinions' => [],
        ];
    }

    /**
     * Extract key themes from all responses.
     */
    protected function extractKeyThemes(array $responses): array
    {
        $themes = [];
        $keywords = ['prioritize', 'focus', 'critical', 'key', 'main', 'important', 'essential'];
        
        foreach ($responses as $response) {
            $text = mb_strtolower($response['response']);
            
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    // Simple extraction - in production, use LLM to extract themes
                    if (preg_match('/(?:' . $keyword . ')[s]?:?\s*([^.]+)/i', $response['response'], $matches)) {
                        $themes[] = trim($matches[1]);
                    }
                }
            }
        }

        return array_values(array_unique($themes));
    }

    /**
     * Extract action items from recommendation.
     */
    protected function extractActionItems(string $recommendation): array
    {
        $actions = [];
        
        // Simple pattern matching for action verbs
        $actionPatterns = [
            '/should\s+(\w+)\s+/i',
            '/need to\s+(\w+)\s+/i',
            '/must\s+(\w+)\s+/i',
            '/first\s+(\w+)\s+/i',
        ];

        foreach ($actionPatterns as $pattern) {
            if (preg_match_all($pattern, $recommendation, $matches)) {
                foreach ($matches[1] as $action) {
                    $actions[] = mb_ucfirst($action);
                }
            }
        }

        return array_values(array_unique($actions));
    }

    /**
     * Set model for consolidation.
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }
}

// Helper function if not available in PHP < 8.2
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst(string $string): string
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }
}
