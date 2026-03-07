<?php

namespace App\Services;

use App\Models\BoardSession;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Enums\Lab;

class ProjectAnalysisService
{
    /**
     * Analyze board decision and generate project metadata
     */
    public function analyzeBoardDecision(BoardSession $session): array
    {
        $risksConsiderations = $session->risks_benefits ?: 'None specified';
        
        $prompt = <<<PROMPT
Analyze this board decision and create a project.

**Board Question**: {$session->question}

**Final Decision**: {$session->final_decision}

**Risks**: {$risksConsiderations}

Return JSON only:
```json
{
  "project_name": "3-6 word action name",
  "project_description": "Brief description",
  "requirements": ["req1", "req2", "req3"]
}
```
PROMPT;

        try {
            $agent = new AnonymousAgent(
                instructions: 'You are a product manager. Extract clear project specifications from board discussions.',
                messages: [],
                tools: []
            );

            $response = $agent->prompt(
                $prompt,
                attachments: [],
                provider: 'ollama',
                model: 'gemma3:12b',  // Faster model
                timeout: 45
            );

            $content = (string) $response;
            
            // Extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }
            
            $analysis = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Log the raw response for debugging
                \Illuminate\Support\Facades\Log::error('AI JSON parse failed', [
                    'response' => substr($content, 0, 500),
                    'error' => json_last_error_msg(),
                ]);
                throw new \Exception('Invalid JSON from AI: ' . json_last_error_msg());
            }

            return [
                'success' => true,
                'name' => $analysis['project_name'] ?? $this->generateFallbackName($session),
                'description' => $analysis['project_description'] ?? $session->final_decision,
                'requirements' => $analysis['requirements'] ?? [],
            ];
        } catch (\Exception $e) {
            // Fallback if API fails or times out
            return [
                'success' => false,
                'name' => $this->generateFallbackName($session),
                'description' => $session->final_decision,
                'requirements' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate fallback project name from question
     */
    protected function generateFallbackName(BoardSession $session): string
    {
        // Extract key topics from question
        $question = $session->question;
        
        // Remove common question prefixes
        $question = preg_replace('/^(Should we|What about|How to|Can we|Is it)/i', '', $question);
        $question = trim($question, '? ');
        
        // Capitalize first letters
        $words = explode(' ', $question);
        $name = implode(' ', array_slice($words, 0, 6)); // Max 6 words
        
        return ucwords($name);
    }

    /**
     * Format board responses for analysis
     */
    protected function formatResponses($responses): string
    {
        return collect($responses)
            ->map(fn($r) => "**{$r->member_name}** ({$r->member_role}): {$r->response}")
            ->implode("\n\n");
    }
}
