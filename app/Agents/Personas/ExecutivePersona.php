<?php

namespace App\Agents\Personas;

/**
 * Base class for executive board personas.
 * 
 * Each persona defines:
 * - System prompt for role-playing
 * - Expertise areas for focused responses
 * - Debate style for interaction patterns
 */
abstract class ExecutivePersona
{
    /**
     * Persona name (e.g., "Steven", "Gwynne")
     */
    public string $name;

    /**
     * Executive title (e.g., "CEO", "COO")
     */
    public string $title;

    /**
     * Emoji avatar for UI display
     */
    public string $avatar;

    /**
     * Inspiration figure (e.g., "Steve Jobs")
     */
    public string $inspiration;

    /**
     * Model to use for this persona (default: glm-5)
     */
    public string $model = 'glm-5';

    /**
     * Expertise areas for this role
     */
    protected array $expertiseAreas = [];

    /**
     * Debate style characteristics
     */
    protected string $debateStyle = 'professional';

    /**
     * Get the system prompt for this persona.
     * This defines how the AI should behave when role-playing this executive.
     */
    abstract public function getSystemPrompt(): string;

    /**
     * Get expertise areas as a formatted string.
     */
    public function getExpertiseString(): string
    {
        if (empty($this->expertiseAreas)) {
            return 'general business strategy';
        }

        return implode(', ', $this->expertiseAreas);
    }

    /**
     * Get debate style description.
     */
    public function getDebateStyle(): string
    {
        $styles = [
            'professional' => 'Professional and measured, focusing on data and logic',
            'assertive' => 'Direct and assertive, challenging assumptions boldly',
            'collaborative' => 'Collaborative and supportive, building on others\' ideas',
            'analytical' => 'Analytical and detail-oriented, questioning specifics',
            'visionary' => 'Visionary and big-picture, focusing on long-term impact',
            'pragmatic' => 'Pragmatic and realistic, grounded in practical constraints',
        ];

        return $styles[$this->debateStyle] ?? $styles['professional'];
    }

    /**
     * Build the full prompt for a debate round.
     * 
     * @param string $question The original question posed to the board
     * @param string|null $context Additional context provided
     * @param array $previousResponses Responses from previous rounds/other executives
     * @param int $round Current round number (1, 2, 3...)
     */
    public function buildPrompt(string $question, ?string $context, array $previousResponses, int $round): string
    {
        $prompt = "BOARD MEETING - ROUND {$round}\n";
        $prompt .= str_repeat('=', 50) . "\n\n";
        
        $prompt .= "QUESTION: {$question}\n\n";
        
        if ($context) {
            $prompt .= "CONTEXT:\n{$context}\n\n";
        }

        if (!empty($previousResponses)) {
            $prompt .= "PREVIOUS DISCUSSION:\n";
            foreach ($previousResponses as $response) {
                $prompt .= "- {$response['title']} ({$response['name']}): {$response['response']}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "As the {$this->title}, provide your perspective on this question.\n";
        $prompt .= "Your expertise: {$this->getExpertiseString()}.\n";
        $prompt .= "Your style: {$this->getDebateStyle()}.\n\n";
        
        if (!empty($previousResponses)) {
            $prompt .= "Reference other executives' points where relevant. Agree, disagree, or build upon their ideas.\n";
        }

        $prompt .= "Be specific, opinionated, and actionable. Limit to 2-3 paragraphs.";

        return $prompt;
    }

    /**
     * Get persona configuration as array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'avatar' => $this->avatar,
            'model' => $this->model,
            'inspiration' => $this->inspiration,
            'expertise' => $this->getExpertiseString(),
            'debate_style' => $this->getDebateStyle(),
        ];
    }
}
