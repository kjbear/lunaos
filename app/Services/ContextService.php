<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ContextService
 * 
 * Provides automatic project context injection for agent chats.
 * Detects project mentions in user messages and loads relevant summaries.
 */
class ContextService
{
    /**
     * Path to the projects directory (relative to LunaOS base or workspace)
     */
    protected string $projectsPath;

    /**
     * Cache for loaded project summaries (per-request)
     */
    protected array $projectCache = [];

    /**
     * Project detection patterns
     */
    protected array $projectPatterns = [
        'IHSSP' => [
            '/\bIHSSP\b/i',
            '/in[- ]?home services?(?:\s+(?:platform|saas))?/i',
            '/in[- ]?home[- ]?special[- ]?services/i',
        ],
        'SPA' => [
            '/\bSPA\b/i',
            '/status[- ]?page[- ]?aggregator/i',
            '/status[- ]?page\s+(?:dashboard|monitor)/i',
            '/onewatch\.cloud/i',
        ],
        'LunaOS' => [
            '/\bLunaOS\b/i',
            '/\bluna[- ]?os\b/i',
            '/\bluna\s+os\b/i',
            '/ai[- ]?team[- ]?dashboard/i',
        ],
    ];

    /**
     * Maximum tokens for project context (approximate)
     */
    protected int $maxProjectContextTokens = 2000;

    public function __construct()
    {
        // Projects can be in workspace/projects or lunaos/../projects
        $workspacePath = base_path('../projects');
        $lunaosPath = base_path('projects');
        
        $this->projectsPath = is_dir($workspacePath) 
            ? $workspacePath 
            : $lunaosPath;
    }

    /**
     * Build context for a user message.
     * Detects project mentions and loads relevant summaries.
     * 
     * @param string $message User message
     * @return string Context section for prompt (empty if no matches)
     */
    public function buildContext(string $message): string
    {
        $detectedProjects = $this->detectProjects($message);

        if (empty($detectedProjects)) {
            return '';
        }

        $contextParts = [];

        foreach ($detectedProjects as $projectName) {
            $summary = $this->loadProjectSummary($projectName);
            if ($summary) {
                $contextParts[] = "## Project Context: {$projectName}\n\n{$summary}";
            }
        }

        return implode("\n\n---\n\n", $contextParts);
    }

    /**
     * Detect project references in a message.
     * 
     * @param string $message User message
     * @return array Array of detected project names
     */
    public function detectProjects(string $message): array
    {
        $detected = [];

        foreach ($this->projectPatterns as $projectName => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    $detected[] = $projectName;
                    break; // Found match for this project, move to next
                }
            }
        }

        // Remove duplicates if a message matches multiple patterns for same project
        return array_unique($detected);
    }

    /**
     * Load project summary from SUMMARY.md or README.md.
     * Caches result for this request.
     * 
     * @param string $projectName Project name (e.g., "IHSSP", "SPA", "LunaOS")
     * @return string|null Project summary or null if not found
     */
    public function loadProjectSummary(string $projectName): ?string
    {
        // Check cache first
        if (isset($this->projectCache[$projectName])) {
            return $this->projectCache[$projectName];
        }

        // Try SUMMARY.md first (preferred)
        $summaryPath = "{$this->projectsPath}/{$projectName}/SUMMARY.md";
        if (file_exists($summaryPath)) {
            $content = file_get_contents($summaryPath);
            if ($content !== false) {
                $summary = trim($content);
                $this->projectCache[$projectName] = $summary;
                return $summary;
            }
        }

        // Fall back to README.md if no SUMMARY.md
        $readmePath = "{$this->projectsPath}/{$projectName}/README.md";
        if (file_exists($readmePath)) {
            $content = file_get_contents($readmePath);
            if ($content !== false) {
                // Extract summary from README (first section or first N lines)
                $summary = $this->extractSummaryFromReadme($content);
                $this->projectCache[$projectName] = $summary;
                return $summary;
            }
        }

        Log::warning("ContextService: Project summary not found", [
            'project' => $projectName,
            'checked_paths' => [$summaryPath, $readmePath]
        ]);

        return null;
    }

    /**
     * Extract a concise summary from README content.
     * Takes first section or first few paragraphs.
     * 
     * @param string $readmeContent Full README content
     * @return string Extracted summary
     */
    protected function extractSummaryFromReadme(string $readmeContent): string
    {
        // Remove the title line if present
        $content = preg_replace('/^#\s+.*\n/', '', $readmeContent);
        
        // Try to get first meaningful section (up to first ## heading)
        $sections = preg_split('/\n##\s+/', $content, 2);
        if (!empty($sections[0])) {
            $summary = trim($sections[0]);
        } else {
            // Fall back to first 500 characters
            $summary = Str::limit(trim($content), 500);
        }

        // Ensure reasonable size
        if (strlen($summary) > 1000) {
            $summary = Str::limit($summary, 1000, '...');
        }

        return $summary;
    }

    /**
     * Load all active project summaries.
     * 
     * @return string[] Array of project summaries keyed by name
     */
    public function loadAllActiveProjects(): array
    {
        $summaries = [];

        $knownProjects = ['IHSSP', 'SPA', 'LunaOS'];

        foreach ($knownProjects as $projectName) {
            $summary = $this->loadProjectSummary($projectName);
            if ($summary) {
                $summaries[$projectName] = $summary;
            }
        }

        return $summaries;
    }

    /**
     * Truncate content to fit within token budget.
     * 
     * @param string $content Content to truncate
     * @param int $maxTokens Maximum tokens (approximate)
     * @return string Truncated content
     */
    public function truncateForTokens(string $content, int $maxTokens): string
    {
        // Approximate: 4 chars per token
        $maxChars = $maxTokens * 4;

        if (strlen($content) <= $maxChars) {
            return $content;
        }

        return Str::limit($content, $maxChars, '...');
    }

    /**
     * Get the configured projects path.
     * 
     * @return string
     */
    public function getProjectsPath(): string
    {
        return $this->projectsPath;
    }
}