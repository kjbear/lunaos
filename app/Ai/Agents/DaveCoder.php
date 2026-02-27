<?php

namespace App\Ai\Agents;

use App\Ai\Tools\WriteFile;
use App\Ai\Tools\ReadFile;
use App\Ai\Tools\ListDirectory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Dave Coder Agent
 * 
 * PHP/Laravel development specialist using Qwen3-Coder via Ollama Cloud.
 * Generates code, creates files, and implements features based on task requirements.
 */
class DaveCoder implements Agent, HasTools, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are Dave, a senior PHP/Laravel developer specializing in modern Laravel applications.

**Your Expertise:**
- Laravel 12.x framework
- PHP 8.4 with strict types
- Livewire 3.x components
- Blade templating
- Tailwind CSS v4
- PostgreSQL/SQLite databases
- Git version control
- PHPUnit testing

**Your Task:**
Generate complete, working code based on the task description provided. 

**Code Quality Standards:**
1. Follow PSR-12 coding standards
2. Use strict types (`declare(strict_types=1);`)
3. Include comprehensive docblocks
4. Write testable, maintainable code
5. Follow Laravel best practices
6. Use modern PHP features (constructor property promotion, match expressions, etc.)
7. Include error handling and validation

**Output Requirements:**
- Return ONLY valid JSON with the file structure
- Include COMPLETE file contents (no placeholders like "// ... rest of code")
- All files must be ready to commit and run
- Explain your implementation approach in the summary

**When Creating Files:**
- Use appropriate namespaces
- Follow project directory structure
- Include necessary imports
- Create corresponding view files when needed
INSTRUCTIONS;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return array
     */
    public function tools(): iterable
    {
        return [
            new WriteFile,
            new ReadFile,
            new ListDirectory,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()
                ->required()
                ->description('Brief explanation of what was implemented'),
            
            'files' => $schema->array()
                ->required()
                ->description('Array of file objects created or modified'),
            
            'tests_created' => $schema->boolean()
                ->default(false)
                ->description('Whether test files were created'),
            
            'requires_migration' => $schema->boolean()
                ->default(false)
                ->description('Whether database migrations are needed'),
        ];
    }

    /**
     * Create a new instance of the agent with a specific task
     */
    public static function forTask(string $taskTitle, string $taskDescription): self
    {
        $agent = new self;
        return $agent;
    }
}
