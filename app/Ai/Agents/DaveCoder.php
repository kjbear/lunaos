<?php

namespace App\Ai\Agents;

use App\Ai\Tools\WriteFile;
use App\Ai\Tools\ReadFile;
use App\Ai\Tools\ListDirectory;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Dave Coder Agent
 * 
 * PHP/Laravel development specialist using Qwen3-Coder via Ollama Cloud.
 * Generates code, creates files, and implements features based on task requirements.
 * 
 * Note: Does NOT implement HasStructuredOutput because Ollama provider doesn't
 * properly support structured output. Uses JSON-in-text response with manual parsing.
 */
class DaveCoder implements Agent, HasTools
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

**CRITICAL: Output Format**
You MUST return a valid JSON object with this exact structure:
```json
{
  "summary": "Brief explanation of what was implemented",
  "files": [
    {
      "path": "app/Path/To/File.php",
      "content": "<?php\n\n... complete file content ...",
      "action": "created"
    }
  ],
  "tests_created": false,
  "requires_migration": false
}
```

**Output Requirements:**
- Return ONLY the JSON object (no markdown code blocks, no extra text)
- Include COMPLETE file contents (no placeholders like "// ... rest of code")
- All files must be ready to commit and run
- Use "action": "created" for new files, "action": "modified" for existing files

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
     * Create a new instance of the agent with a specific task
     */
    public static function forTask(string $taskTitle, string $taskDescription): self
    {
        $agent = new self;
        return $agent;
    }
}
