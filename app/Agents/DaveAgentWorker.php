<?php

declare(strict_types=1);

namespace App\Agents;

use App\Contracts\AgentWorker;
use Illuminate\Support\Facades\Log;
use Laravel\AI\AI;
use Laravel\AI\AI\Agent\Agent;
use Laravel\AI\AI\Agent\Schema;

/**
 * DaveAgentWorker is the concrete implementation of the AgentWorker interface
 * responsible for code generation using structured output via AI.
 */
class DaveAgentWorker implements AgentWorker
{
    /**
     * Generate code using AI with structured output enforcement.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>  Returns structured output matching schema
     */
    public function generateCodeWithAI(Agent $agent, string $prompt, array $context = []): array
    {
        try {
            // Log when structured output is used
            Log::info('DaveAgentWorker: Using structured output for AI code generation.', [
                'context_keys' => array_keys($context),
                'prompt_length' => strlen($prompt),
            ]);

            $schema = Schema::make()
                ->properties([
                    'summary' => Schema::string(),
                    'files' => Schema::array()
                        ->items(Schema::make()
                            ->properties([
                                'path' => Schema::string(),
                                'content' => Schema::string(),
                                'action' => Schema::string(),
                            ])
                            ->required(['path', 'content', 'action']),
                        ),
                    'tests_created' => Schema::boolean(),
                    'requires_migration' => Schema::boolean(),
                ])
                ->required(['summary', 'files', 'tests_created', 'requires_migration']);

            $result = $agent->structured($schema, $prompt, $context);

            // Ensure boolean fields are native booleans (AI SDK already does this, but double-check)
            $result['tests_created'] = filter_var($result['tests_created'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $result['requires_migration'] = filter_var($result['requires_migration'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return $result;
        } catch (\Exception $e) {
            Log::error('DaveAgentWorker: Structured AI generation failed.', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('AI code generation failed: ' . $e->getMessage(), 0, $e);
        }
    }
}