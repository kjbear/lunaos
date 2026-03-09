<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ChatService
 * 
 * Handles message sending, context management, and AI interactions.
 * Integrates with SkillService for enhanced prompts.
 */
class ChatService
{
    /**
     * Ollama API base URL
     */
    protected string $ollamaUrl;

    /**
     * Default model for chat completions
     */
    protected string $defaultModel;

    /**
     * Maximum tokens for context window
     */
    protected int $maxContextTokens;

    /**
     * Maximum messages to keep in sliding window
     */
    protected int $maxContextMessages;

    /**
     * Request timeout in seconds
     */
    protected int $requestTimeout;

    public function __construct()
    {
        // Use services.ollama.host for the base URL (Ollama native API)
        $this->ollamaUrl = config('services.ollama.host', 'http://192.168.2.2:11434');
        $this->defaultModel = config('chat.default_model', 'glm-5:cloud');
        $this->maxContextTokens = config('chat.max_context_tokens', 8000);
        $this->maxContextMessages = config('chat.max_context_messages', 20);
        $this->requestTimeout = config('chat.request_timeout', 120);
    }

    /**
     * Create a new chat session with a team member.
     */
    public function createSession(string $teamMemberId, ?string $title = null): ChatSession
    {
        $session = ChatSession::create([
            'team_member_id' => $teamMemberId,
            'title' => $title,
            'context' => [],
            'metadata' => [
                'model' => $this->defaultModel,
                'created_via' => 'api',
            ],
        ]);

        return $session;
    }

    /**
     * Get the correct Ollama model name for a team member.
     * Maps short names to full model names with :cloud suffix.
     */
    protected function getOllamaModel(?string $model): string
    {
        if (!$model) {
            return $this->defaultModel;
        }

        // Map short names to full Ollama model names
        $modelMap = [
            'glm-5' => 'glm-5:cloud',
            'haiku' => 'haiku:cloud',
            'dolphin' => 'dolphin:cloud',
        ];

        return $modelMap[$model] ?? $model;
    }

    /**
     * Send a message and get AI response.
     * 
     * @param ChatSession $session
     * @param string $userMessage
     * @param bool $stream Whether to stream response (returns generator if true)
     * @return array|ChatMessage[] Returns ['user_message' => ChatMessage, 'assistant_message' => ChatMessage]
     */
    public function sendMessage(ChatSession $session, string $userMessage, bool $stream = false): array
    {
        $teamMember = $session->teamMember;

        // Save user message
        $userMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $userMessage,
            'tokens' => $this->estimateTokens($userMessage),
            'metadata' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        // Update title from first message if not set
        if (!$session->title) {
            $session->generateTitle();
        }

        // Build prompt context
        $prompt = $this->buildPrompt($session, $teamMember, $userMessage);

        // Get correct Ollama model name
        $model = $this->getOllamaModel($teamMember->ai_model);

        // Get AI response with stats
        $startTime = microtime(true);
        $result = $this->callOllama($prompt, $model);
        $latency = round((microtime(true) - $startTime) * 1000);

        $assistantContent = $result['content'];
        $promptTokens = $result['prompt_eval_count'] ?? $this->estimateTokens($userMessage);
        $completionTokens = $result['eval_count'] ?? $this->estimateTokens($assistantContent);

        // Save assistant message with actual token counts
        $assistantMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $assistantContent,
            'tokens' => $completionTokens,
            'metadata' => [
                'model' => $model,
                'latency_ms' => $latency,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        // Update context
        $session->updateContext($this->maxContextTokens, $this->maxContextMessages);

        return [
            'user_message' => $userMsg,
            'assistant_message' => $assistantMsg,
        ];
    }

    /**
     * Stream a message and get AI response token-by-token.
     * Returns a generator that yields each token and final stats.
     * 
     * @param ChatSession $session
     * @param string $userMessage
     * @return \Generator Yields ['token' => string] or final ['done' => true, 'stats' => array]
     */
    public function streamMessage(ChatSession $session, string $userMessage): \Generator
    {
        $teamMember = $session->teamMember;

        // Save user message first
        $userMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $userMessage,
            'tokens' => $this->estimateTokens($userMessage),
            'metadata' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        // Update title from first message if not set
        if (!$session->title) {
            $session->generateTitle();
        }

        // Build prompt context
        $prompt = $this->buildPrompt($session, $teamMember, $userMessage);

        // Get correct Ollama model name
        $model = $this->getOllamaModel($teamMember->ai_model);

        // Stream from Ollama
        $url = "{$this->ollamaUrl}/api/chat";
        $startTime = microtime(true);
        
        $fullContent = '';
        $promptTokens = 0;
        $completionTokens = 0;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => $prompt,
                'stream' => true,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->requestTimeout,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close() has no effect since PHP 8.0, removed

        if ($httpCode !== 200) {
            Log::error('Ollama streaming error', ['code' => $httpCode, 'response' => $response]);
            yield ['token' => 'Sorry, I encountered an error. Please try again.'];
            yield ['done' => true, 'error' => true];
            return;
        }

        // Process streaming response line by line
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = json_decode($line, true);
            if (!$data) continue;

            // Yield token content
            if (isset($data['message']['content']) && !empty($data['message']['content'])) {
                $fullContent .= $data['message']['content'];
                yield ['token' => $data['message']['content']];
            }

            // Capture stats from final response
            if (isset($data['done']) && $data['done'] === true) {
                $promptTokens = $data['prompt_eval_count'] ?? 0;
                $completionTokens = $data['eval_count'] ?? 0;
            }
        }

        $latency = round((microtime(true) - $startTime) * 1000);

        // Fallback token estimation if not provided
        if ($completionTokens === 0) {
            $completionTokens = $this->estimateTokens($fullContent);
        }

        // Save assistant message
        $assistantMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $fullContent,
            'tokens' => $completionTokens,
            'metadata' => [
                'model' => $model,
                'latency_ms' => $latency,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        // Update context
        $session->updateContext($this->maxContextTokens, $this->maxContextMessages);

        // Yield final stats
        yield [
            'done' => true,
            'user_message' => $userMsg,
            'assistant_message' => $assistantMsg,
            'stats' => [
                'model' => $model,
                'latency_ms' => $latency,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
            ],
        ];
    }

    /**
     * Build the prompt for AI completion.
     * Combines system prompt + skills + conversation history + new message.
     */
    protected function buildPrompt(ChatSession $session, TeamMember $member, string $newMessage): array
    {
        $messages = [];

        // 1. Build system prompt with member identity
        $systemPrompt = $this->buildSystemPrompt($member);
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // 2. Load skills and enhance prompt
        $skillsContent = $this->loadSkillsForMember($member);
        if ($skillsContent) {
            $messages[] = ['role' => 'system', 'content' => "## Relevant Skills:\n\n" . $skillsContent];
        }

        // 3. Add conversation history (context sliding window)
        $context = $session->context ?? [];
        foreach ($context as $contextMsg) {
            // Skip system messages from context (they're regenerated above)
            if (($contextMsg['role'] ?? '') !== 'system') {
                $messages[] = [
                    'role' => $contextMsg['role'],
                    'content' => $contextMsg['content'],
                ];
            }
        }

        // 4. Add the new message
        $messages[] = ['role' => 'user', 'content' => $newMessage];

        return $messages;
    }

    /**
     * Build the system prompt for a team member.
     */
    protected function buildSystemPrompt(TeamMember $member): string
    {
        $name = $member->name;
        $title = $member->title ?? '';
        $emoji = $member->emoji ?? '🤖';
        $personaDescription = $member->persona_description ?? '';
        $specialInstructions = $member->special_instructions ?? '';

        $prompt = "You are {$name}";
        if ($title) {
            $prompt .= " ({$title})";
        }
        $prompt .= ".\n\n";

        if ($personaDescription) {
            $prompt .= "## Your Role\n\n{$personaDescription}\n\n";
        }

        if ($specialInstructions) {
            $prompt .= "## Special Instructions\n\n{$specialInstructions}\n\n";
        }

        // Include member's system prompt if set
        if ($member->system_prompt) {
            $prompt .= "## System Prompt\n\n{$member->system_prompt}\n\n";
        }

        return trim($prompt);
    }

    /**
     * Load skills for a team member and return combined skill content.
     */
    protected function loadSkillsForMember(TeamMember $member): ?string
    {
        // Check if member has capabilities that map to skills
        $capabilities = $member->capabilities ?? [];
        
        if (empty($capabilities)) {
            return null;
        }

        $skillsPath = base_path('skills');
        $skillContents = [];

        foreach ($capabilities as $capability) {
            // Try to find skill by name (map common capabilities to skill files)
            $skillFile = $this->findSkillPath($capability, $skillsPath);
            
            if ($skillFile && file_exists($skillFile)) {
                $content = file_get_contents($skillFile);
                if ($content !== false) {
                    // Extract just the markdown content after frontmatter
                    $skillContents[] = $this->extractSkillContent($content, $capability);
                }
            }
        }

        return empty($skillContents) ? null : implode("\n\n---\n\n", $skillContents);
    }

    /**
     * Find skill file path for a capability.
     */
    protected function findSkillPath(string $capability, string $skillsPath): ?string
    {
        // Map capability names to skill directories
        $mapping = [
            'devops' => 'devops-engineer',
            'devops-engineer' => 'devops-engineer',
            'laravel' => 'laravel-specialist',
            'laravel-specialist' => 'laravel-specialist',
            'qa' => 'qa-engineer',
            'qa-engineer' => 'qa-engineer',
            'product-manager' => 'product-manager',
            'pm' => 'product-manager',
        ];

        $skillName = $mapping[$capability] ?? $capability;
        $skillFile = "{$skillsPath}/{$skillName}/skill.md";

        return $skillFile;
    }

    /**
     * Extract skill content from markdown file (skip frontmatter).
     */
    protected function extractSkillContent(string $content, string $capability): string
    {
        // Remove YAML frontmatter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        
        return "# Skill: {$capability}\n\n" . trim($content);
    }

    /**
     * Call Ollama API for chat completion.
     * Returns content and token stats.
     */
    protected function callOllama(array $messages, string $model): array
    {
        $url = "{$this->ollamaUrl}/api/chat";

        Log::info('Calling Ollama', [
            'url' => $url,
            'model' => $model,
            'messages_count' => count($messages),
        ]);

        try {
            $response = Http::timeout($this->requestTimeout)
                ->post($url, [
                    'model' => $model,
                    'messages' => $messages,
                    'stream' => false,
                ]);

            Log::info('Ollama response', [
                'status' => $response->status(),
                'body_length' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                Log::error('Ollama API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'content' => "I apologize, but I encountered an error processing your request. Please try again.",
                    'prompt_eval_count' => 0,
                    'eval_count' => 0,
                ];
            }

            $data = $response->json();
            
            Log::info('Ollama response data', [
                'has_message' => isset($data['message']),
                'has_content' => isset($data['message']['content']),
                'prompt_eval_count' => $data['prompt_eval_count'] ?? 'not set',
                'eval_count' => $data['eval_count'] ?? 'not set',
            ]);

            return [
                'content' => $data['message']['content'] ?? '',
                'prompt_eval_count' => $data['prompt_eval_count'] ?? 0,
                'eval_count' => $data['eval_count'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Ollama API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'content' => "I apologize, but I'm currently unavailable. Please try again in a moment.",
                'prompt_eval_count' => 0,
                'eval_count' => 0,
            ];
        }
    }

    /**
     * Estimate token count for text.
     * Simple approximation: ~4 chars per token for English.
     */
    protected function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Get sessions for a user (or all sessions filtered by team member).
     */
    public function getSessions(?string $teamMemberId = null, int $limit = 50)
    {
        $query = ChatSession::with('teamMember')
            ->orderBy('updated_at', 'desc');

        if ($teamMemberId) {
            $query->where('team_member_id', $teamMemberId);
        }

        return $query->take($limit)->get();
    }

    /**
     * Get a session with messages.
     */
    public function getSession(string $sessionId): ?ChatSession
    {
        return ChatSession::with(['teamMember', 'messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->find($sessionId);
    }

    /**
     * Delete a session and all its messages.
     */
    public function deleteSession(string $sessionId): bool
    {
        $session = ChatSession::find($sessionId);
        
        if (!$session) {
            return false;
        }

        // Cascading delete is handled by foreign key constraint
        $session->delete();
        return true;
    }
}