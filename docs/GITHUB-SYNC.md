# GitHub Issue Sync Service

Syncs GitHub repository issues to the `project_issues` table for LunaOS projects.

## Overview

The `GitHubIssueSyncService` fetches issues from GitHub repositories and stores them locally. This enables:

- Issue tracking within LunaOS projects
- Correlation of GitHub issues with local tasks
- Issue severity and status tracking

## Features

- **Dual fetch method**: Prefers `gh` CLI, falls back to REST API
- **Rate limiting**: Respects GitHub API limits with configurable buffer
- **Caching**: Prevents repeated API calls (5-minute default TTL)
- **Upsert logic**: Creates new or updates existing issues by `github_id`
- **Label mapping**: Maps GitHub labels to severity levels

## Installation

### Requirements

- PHP 8.2+
- Laravel 11+
- GitHub Personal Access Token (optional, for private repos and higher rate limits)

### Configuration

Add to your `.env` file:

```env
# Optional: GitHub Personal Access Token
# Required for private repos, increases API rate limits
GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
```

### Database Migration

Run the migration to add GitHub fields to `project_issues`:

```bash
php artisan migrate
```

This adds:
- `github_id` - Unique GitHub issue ID
- `github_number` - Issue number in repo
- `github_url` - Link to GitHub issue
- `github_state` - GitHub state (open/closed)
- `github_labels` - JSON array of label names
- `github_assignees` - JSON array of assignee logins
- `github_created_at` - Issue creation timestamp
- `github_updated_at` - Last update timestamp

## Usage

### Via Artisan Command

```bash
# Sync issues for a project by ID
php artisan github:sync-issues project-uuid-here

# Sync issues for a project by name
php artisan github:sync-issues "My Project Name"

# Force sync even with low rate limit
php artisan github:sync-issues project-uuid --force
```

### Via Service (Programmatic)

```php
use App\Models\Project;
use App\Services\GitHubIssueSyncService;

$project = Project::find($projectUuid);
$service = app(GitHubIssueSyncService::class);

$result = $service->syncIssues($project);

// Result structure:
// [
//     'synced' => 25,    // Total issues fetched
//     'created' => 10,   // New issues created
//     'updated' => 15,   // Existing issues updated
//     'errors' => [],    // Error messages
// ]
```

### Fetch Issues Only (No Database Write)

```php
$service = app(GitHubIssueSyncService::class);
$issues = $service->fetchIssues('https://github.com/owner/repo');

// Each issue has:
// - id, number, title, body, state
// - html_url, labels, assignees
// - created_at, updated_at
```

## Severity Mapping

GitHub labels are mapped to internal severity levels:

| Labels | Severity |
|--------|----------|
| `critical` | critical |
| `high`, `high priority`, `bug` | high |
| `medium` | medium |
| `enhancement`, `minor`, `low` | low |
| (default) | medium |

## Status Mapping

| GitHub State | Internal Status |
|--------------|-----------------|
| open | open |
| closed | closed |

## Rate Limiting

The service checks GitHub's rate limit before sync:

- Reserves 100 requests as buffer (configurable)
- Returns early with error if rate limit is low
- Private repos require authentication token

### Rate Limits (GitHub defaults)

| Auth Type | Limit |
|-----------|-------|
| No token | 60 req/hour |
| Token (public) | 5,000 req/hour |
| Token (private) | 15,000 req/hour |

## Caching

Issues are cached for 5 minutes by default to prevent repeated API calls:

```php
// Customize cache TTL
$service->setCacheTtl(3600); // 1 hour

// Clear cache manually
Cache::forget('github_issues:owner/repo');
```

## API Fallback

If `gh` CLI is unavailable, the service falls back to GitHub REST API:

1. Checks `gh` availability via `which gh`
2. Falls back to `api.github.com` if unavailable
3. Uses `GITHUB_TOKEN` from `.env` for authentication

## Error Handling

The service handles errors gracefully:

1. **Invalid repo_url**: Returns error, no sync
2. **Rate limit exceeded**: Returns error with message
3. **Network errors**: Returns error with exception message
4. **Individual issue failures**: Logs warning, continues with other issues

All errors are returned in the result array and logged via Laravel's `Log` facade.

## Testing

```bash
# Run service unit tests
php artisan test --filter=GitHubIssueSyncServiceTest

# Test with actual project
php artisan tinker
>>> $project = Project::first();
>>> $project->repo_url = 'https://github.com/laravel/laravel';
>>> $project->save();
>>> app(GitHubIssueSyncService::class)->syncIssues($project);
```

## Console Log

Check logs for sync activity:

```bash
tail -f storage/logs/laravel.log | grep -i github
```

## Changelog

### v1.0.0 (2026-03-07)

- Initial release
- `gh` CLI support with REST API fallback
- Rate limiting with configurable buffer
- Issue caching
- Label-to-severity mapping
- Artisan command for manual sync