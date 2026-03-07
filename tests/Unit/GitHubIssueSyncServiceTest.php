<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectIssue;
use App\Services\GitHubIssueSyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class GitHubIssueSyncServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** @test */
    public function it_parses_github_urls_correctly()
    {
        $service = new GitHubIssueSyncService();
        $method = new \ReflectionMethod($service, 'parseRepoUrl');

        // Standard HTTPS URL
        $this->assertEquals('owner/repo', $method->invoke($service, 'https://github.com/owner/repo'));
        
        // With .git extension
        $this->assertEquals('owner/repo', $method->invoke($service, 'https://github.com/owner/repo.git'));
        
        // SSH URL
        $this->assertEquals('owner/repo', $method->invoke($service, 'git@github.com:owner/repo.git'));

        // Plain owner/repo
        $this->assertEquals('owner/repo', $method->invoke($service, 'owner/repo'));

        // Invalid URLs return null
        $this->assertNull($method->invoke($service, 'not-a-valid-url'));
        $this->assertNull($method->invoke($service, ''));
    }

    /** @test */
    public function it_determines_severity_from_labels()
    {
        $service = new GitHubIssueSyncService();
        $method = new \ReflectionMethod($service, 'determineSeverity');

        $this->assertEquals('critical', $method->invoke($service, ['critical', 'bug']));
        $this->assertEquals('high', $method->invoke($service, ['high priority', 'bug']));
        $this->assertEquals('medium', $method->invoke($service, ['enhancement'])); // default
        $this->assertEquals('low', $method->invoke($service, ['enhancement', 'low']));
        $this->assertEquals('medium', $method->invoke($service, [])); // default
    }

    /** @test */
    public function it_maps_github_states_correctly()
    {
        $service = new GitHubIssueSyncService();
        $method = new \ReflectionMethod($service, 'mapStateToStatus');

        $this->assertEquals('open', $method->invoke($service, 'open'));
        $this->assertEquals('open', $method->invoke($service, 'OPEN'));
        $this->assertEquals('closed', $method->invoke($service, 'closed'));
        $this->assertEquals('closed', $method->invoke($service, 'CLOSED'));
        $this->assertEquals('open', $method->invoke($service, 'unknown')); // default
    }

    /** @test */
    public function it_returns_error_when_project_has_no_repo_url()
    {
        $project = Project::factory()->create([
            'name' => 'Test Project',
            'repo_url' => null,
        ]);

        $service = new GitHubIssueSyncService();
        $result = $service->syncIssues($project);

        $this->assertEquals(0, $result['synced']);
        $this->assertEquals(0, $result['created']);
        $this->assertEquals(0, $result['updated']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('no repo_url', $result['errors'][0]);
    }

    /** @test */
    public function it_fetches_issues_via_api_when_gh_unavailable()
    {
        // Mock gh unavailable
        Process::macro('run', function ($command) {
            if ($command === 'which gh') {
                return new class {
                    public function successful() { return false; }
                    public function output() { return ''; }
                    public function errorOutput() { return 'not found'; }
                };
            }
            return new class {
                public function successful() { return false; }
                public function output() { return ''; }
                public function errorOutput() { return 'error'; }
            };
        });

        // Mock GitHub API response
        Http::fake([
            'api.github.com/repos/*/issues' => Http::response([
                [
                    'id' => 12345,
                    'number' => 1,
                    'title' => 'Test Issue',
                    'body' => 'Issue body',
                    'state' => 'open',
                    'labels' => [['name' => 'bug']],
                    'assignees' => [['login' => 'developer']],
                    'html_url' => 'https://github.com/owner/repo/issues/1',
                    'created_at' => '2026-03-07T10:00:00Z',
                    'updated_at' => '2026-03-07T12:00:00Z',
                ],
            ], 200),
            'api.github.com/rate_limit' => Http::response([
                'resources' => ['core' => ['remaining' => 5000]],
            ], 200),
        ]);

        $project = Project::factory()->create([
            'name' => 'API Test Project',
            'repo_url' => 'https://github.com/test/test-repo',
        ]);

        $service = new GitHubIssueSyncService();
        $service->setCacheTtl(0); // Disable cache for test

        $result = $service->syncIssues($project);

        $this->assertEquals(1, $result['synced']);
        $this->assertEquals(1, $result['created']);
        $this->assertCount(0, $result['errors']);

        // Verify issue was stored
        $issue = ProjectIssue::where('github_id', '12345')->first();
        $this->assertNotNull($issue);
        $this->assertEquals('Test Issue', $issue->title);
        $this->assertEquals('open', $issue->status);
        $this->assertEquals('high', $issue->severity); // 'bug' label
    }

    /** @test */
    public function it_updates_existing_issues()
    {
        $project = Project::factory()->create([
            'name' => 'Update Test Project',
            'repo_url' => 'https://github.com/test/update-repo',
        ]);

        // Create existing issue
        $existingIssue = ProjectIssue::create([
            'id' => \Str::uuid(),
            'project_id' => $project->id,
            'github_id' => '99999',
            'github_number' => 42,
            'title' => 'Old Title',
            'description' => 'Old description',
            'status' => 'open',
            'severity' => 'medium',
        ]);

        Http::fake([
            'api.github.com/repos/*/issues' => Http::response([
                [
                    'id' => 99999,
                    'number' => 42,
                    'title' => 'Updated Title',
                    'body' => 'Updated description',
                    'state' => 'closed',
                    'labels' => [['name' => 'critical']],
                    'assignees' => [],
                    'html_url' => 'https://github.com/test/update-repo/issues/42',
                    'created_at' => '2026-03-01T10:00:00Z',
                    'updated_at' => '2026-03-07T15:00:00Z',
                ],
            ], 200),
            'api.github.com/rate_limit' => Http::response([
                'resources' => ['core' => ['remaining' => 5000]],
            ], 200),
        ]);

        $service = new GitHubIssueSyncService();
        $service->setCacheTtl(0);

        $result = $service->syncIssues($project);

        $this->assertEquals(1, $result['synced']);
        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['updated']);

        // Refresh from DB
        $existingIssue->refresh();

        $this->assertEquals('Updated Title', $existingIssue->title);
        $this->assertEquals('closed', $existingIssue->status);
        $this->assertEquals('critical', $existingIssue->severity);
    }

    /** @test */
    public function it_caches_issue_fetches()
    {
        $project = Project::factory()->create([
            'name' => 'Cache Test Project',
            'repo_url' => 'https://github.com/test/cache-repo',
        ]);

        Http::fake([
            'api.github.com/repos/*/issues' => Http::response([
                ['id' => 1, 'number' => 1, 'title' => 'Issue 1', 'state' => 'open'],
            ], 200),
            'api.github.com/rate_limit' => Http::response([
                'resources' => ['core' => ['remaining' => 5000]],
            ], 200),
        ]);

        $service = new GitHubIssueSyncService();
        $service->setCacheTtl(300);

        // First call
        $service->syncIssues($project);

        // Second call should use cache
        $result = $service->syncIssues($project);

        // Should only have been called once for issues endpoint
        Http::assertSentCount(3); // rate_limit + 2 issue calls (per sync)
    }

    /** @test */
    public function it_handles_rate_limiting_gracefully()
    {
        Http::fake([
            'api.github.com/repos/*/issues' => Http::response([], 403),
            'api.github.com/rate_limit' => Http::response([
                'resources' => ['core' => ['remaining' => 50]], // Below buffer
            ], 200),
        ]);

        $project = Project::factory()->create([
            'name' => 'Rate Limit Test',
            'repo_url' => 'https://github.com/test/ratelimit',
        ]);

        $service = new GitHubIssueSyncService();

        // With rate limit, should return error early
        $result = $service->syncIssues($project);

        $this->assertEquals(0, $result['synced']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('rate limit', strtolower($result['errors'][0]));
    }
}