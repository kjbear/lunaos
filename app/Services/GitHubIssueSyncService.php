<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectIssue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * GitHub Issue Sync Service
 * 
 * Syncs GitHub issues to the project_issues table.
 * Uses `gh` CLI (preferred) with REST API fallback.
 */
class GitHubIssueSyncService
{
    protected int $cacheTtl = 300; // 5 minutes cache
    protected int $rateLimitBuffer = 100; // Keep 100 requests in reserve
    protected int $retryDelay = 60; // Seconds to wait on rate limit

    /**
     * Sync issues for a project.
     * 
     * @param Project $project
     * @return array{synced: int, created: int, updated: int, errors: array}
     */
    public function syncIssues(Project $project): array
    {
        if (empty($project->repo_url)) {
            return [
                'synced' => 0,
                'created' => 0,
                'updated' => 0,
                'errors' => ['Project has no repo_url configured'],
            ];
        }

        $repoUrl = $project->repo_url;
        
        // Check rate limits before starting
        if (!$this->checkRateLimit($repoUrl)) {
            return [
                'synced' => 0,
                'created' => 0,
                'updated' => 0,
                'errors' => ['Rate limit exceeded, please try again later'],
            ];
        }

        try {
            $issues = $this->fetchIssues($repoUrl);
            return $this->upsertIssues($issues, $project);
        } catch (\Exception $e) {
            Log::error('GitHub issue sync failed', [
                'project_id' => $project->id,
                'repo_url' => $repoUrl,
                'error' => $e->getMessage(),
            ]);

            return [
                'synced' => 0,
                'created' => 0,
                'updated' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Fetch issues from GitHub repository.
     * 
     * @param string $repoUrl GitHub repository URL
     * @return array Array of issue data
     */
    public function fetchIssues(string $repoUrl): array
    {
        $ownerRepo = $this->parseRepoUrl($repoUrl);
        
        if (!$ownerRepo) {
            throw new \InvalidArgumentException("Invalid GitHub URL: {$repoUrl}");
        }

        // Check cache first
        $cacheKey = "github_issues:{$ownerRepo}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        // Prefer gh CLI if available
        if ($this->ghAvailable()) {
            $issues = $this->fetchViaGh($ownerRepo);
        } else {
            $issues = $this->fetchViaApi($ownerRepo);
        }

        // Cache the results
        Cache::put($cacheKey, $issues, $this->cacheTtl);

        return $issues;
    }

    /**
     * Upsert issues to the database.
     * 
     * @param array $issues GitHub issue data
     * @param Project $project Target project
     * @return array{synced: int, created: int, updated: int, errors: array}
     */
    public function upsertIssues(array $issues, Project $project): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($issues as $issueData) {
            try {
                $result = $this->upsertIssue($issueData, $project);
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = "Issue #{$issueData['number']}: " . $e->getMessage();
                Log::warning('Failed to upsert GitHub issue', [
                    'github_number' => $issueData['number'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'synced' => count($issues),
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Upsert a single issue.
     */
    protected function upsertIssue(array $issueData, Project $project): string
    {
        // Extract labels
        $labels = collect($issueData['labels'] ?? [])
            ->pluck('name')
            ->toArray();

        // Determine severity from labels or use default
        $severity = $this->determineSeverity($labels);

        // Map GitHub state to internal status
        $status = $this->mapStateToStatus($issueData['state'] ?? 'open');

        // Map GitHub assignees to single assigned_to field
        $assignedTo = null;
        if (!empty($issueData['assignees']) && count($issueData['assignees']) > 0) {
            $assignedTo = $issueData['assignees'][0]['login'] ?? null;
        }

        // Find existing issue by github_id or create new
        $issue = ProjectIssue::firstOrNew([
            'github_id' => (string) $issueData['id'],
        ]);

        $isNew = !$issue->exists;

        // Fill attributes
        $issue->fill([
            'project_id' => $project->id,
            'title' => $issueData['title'] ?? '',
            'description' => $issueData['body'] ?? '',
            'severity' => $severity,
            'status' => $status,
            'assigned_to' => $assignedTo,
            'github_id' => (string) $issueData['id'],
            'github_number' => $issueData['number'] ?? null,
            'github_url' => $issueData['html_url'] ?? null,
            'github_state' => $issueData['state'] ?? 'open',
            'github_labels' => $labels,
            'github_assignees' => collect($issueData['assignees'] ?? [])
                ->pluck('login')
                ->toArray(),
            'github_created_at' => isset($issueData['created_at'])
                ? \Carbon\Carbon::parse($issueData['created_at'])
                : null,
            'github_updated_at' => isset($issueData['updated_at'])
                ? \Carbon\Carbon::parse($issueData['updated_at'])
                : null,
        ]);

        $issue->save();

        return $isNew ? 'created' : 'updated';
    }

    /**
     * Parse GitHub URL to extract owner/repo.
     */
    protected function parseRepoUrl(string $url): ?string
    {
        // Handle various GitHub URL formats
        // https://github.com/owner/repo
        // git@github.com:owner/repo.git
        // owner/repo
        
        if (preg_match('#github\.com[:/]([^/]+/[^/.\s]+)#', $url, $matches)) {
            return $matches[1];
        }
        
        // Handle plain owner/repo format
        if (preg_match('#^([a-zA-Z0-9_-]+/[a-zA-Z0-9_-]+)$#', $url)) {
            return $url;
        }

        return null;
    }

    /**
     * Check if gh CLI is available.
     */
    protected function ghAvailable(): bool
    {
        $result = Process::run('which gh');
        return $result->successful();
    }

    /**
     * Fetch issues using gh CLI.
     */
    protected function fetchViaGh(string $ownerRepo): array
    {
        $result = Process::timeout(60)->run([
            'gh',
            'issue',
            'list',
            '--repo', $ownerRepo,
            '--state', 'all',
            '--limit', '100',
            '--json', 'id,number,title,body,state,labels,assignees,createdAt,updatedAt,htmlUrl',
        ]);

        if (!$result->successful()) {
            Log::warning('gh CLI fetch failed, falling back to API', [
                'error' => $result->errorOutput(),
            ]);
            return $this->fetchViaApi($ownerRepo);
        }

        $issues = json_decode($result->output(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse gh CLI output: ' . json_last_error_msg());
        }

        // Normalize field names to match API format
        return $this->normalizeGhOutput($issues);
    }

    /**
     * Fetch issues using GitHub REST API.
     */
    protected function fetchViaApi(string $ownerRepo): array
    {
        $token = config('services.github.token') ?? env('GITHUB_TOKEN');
        [$owner, $repo] = explode('/', $ownerRepo);

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'LunaOS/1.0',
            ...($token ? ['Authorization' => "Bearer {$token}"] : []),
        ])->get("https://api.github.com/repos/{$owner}/{$repo}/issues", [
            'state' => 'all',
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            if ($response->status() === 403) {
                throw new \RuntimeException('GitHub API rate limit exceeded');
            }
            throw new \RuntimeException('Failed to fetch GitHub issues: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Normalize gh CLI output to match API format.
     */
    protected function normalizeGhOutput(array $issues): array
    {
        return collect($issues)->map(function ($issue) {
            return [
                'id' => $issue['id'],
                'number' => $issue['number'],
                'title' => $issue['title'],
                'body' => $issue['body'],
                'state' => $issue['state'],
                'labels' => $issue['labels'] ?? [],
                'assignees' => collect($issue['assignees'] ?? [])->map(fn($a) => [
                    'login' => $a['login'] ?? $a,
                ])->toArray(),
                'created_at' => $issue['createdAt'] ?? null,
                'updated_at' => $issue['updatedAt'] ?? null,
                'html_url' => $issue['htmlUrl'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Check rate limit before making requests.
     */
    protected function checkRateLimit(string $repoUrl): bool
    {
        $token = config('services.github.token') ?? env('GITHUB_TOKEN');
        
        if (!$token) {
            // No token means lower rate limits, but still proceed
            return true;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'Authorization' => "Bearer {$token}",
        ])->get('https://api.github.com/rate_limit');

        if (!$response->successful()) {
            return true; // Assume OK if we can't check
        }

        $data = $response->json();
        $remaining = $data['resources']['core']['remaining'] ?? PHP_INT_MAX;

        return $remaining > $this->rateLimitBuffer;
    }

    /**
     * Determine severity from GitHub labels.
     */
    protected function determineSeverity(array $labels): string
    {
        $labelMap = [
            'critical' => 'critical',
            'high priority' => 'high',
            'high' => 'high',
            'bug' => 'high',
            'medium' => 'medium',
            'enhancement' => 'low',
            'minor' => 'low',
            'low' => 'low',
        ];

        foreach ($labels as $label) {
            $labelLower = strtolower($label);
            if (isset($labelMap[$labelLower])) {
                return $labelMap[$labelLower];
            }
        }

        return 'medium';
    }

    /**
     * Map GitHub state to internal status.
     */
    protected function mapStateToStatus(string $state): string
    {
        return match (strtolower($state)) {
            'open' => 'open',
            'closed' => 'closed',
            default => 'open',
        };
    }

    /**
     * Set cache TTL for testing.
     */
    public function setCacheTtl(int $seconds): self
    {
        $this->cacheTtl = $seconds;
        return $this;
    }
}