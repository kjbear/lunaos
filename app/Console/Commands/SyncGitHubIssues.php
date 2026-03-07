<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\GitHubIssueSyncService;
use Illuminate\Console\Command;

class SyncGitHubIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:sync-issues 
                            {project : Project ID or name} 
                            {--force : Force sync even with rate limits}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync GitHub issues to project_issues table';

    protected GitHubIssueSyncService $syncService;

    /**
     * Create a new command instance.
     */
    public function __construct(GitHubIssueSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectIdentifier = $this->argument('project');
        
        // Find project by ID or name
        $project = Project::where('id', $projectIdentifier)
            ->orWhere('name', $projectIdentifier)
            ->first();

        if (!$project) {
            $this->error("Project not found: {$projectIdentifier}");
            return self::FAILURE;
        }

        if (empty($project->repo_url)) {
            $this->error("Project '{$project->name}' has no repo_url configured.");
            $this->info("Set repo_url via: php artisan tinker");
            $this->info("  \$project = Project::find('{$project->id}');");
            $this->info("  \$project->repo_url = 'https://github.com/owner/repo';");
            $this->info("  \$project->save();");
            return self::FAILURE;
        }

        $this->info("Syncing GitHub issues for: {$project->name}");
        $this->info("Repository: {$project->repo_url}");

        $result = $this->syncService->syncIssues($project);

        if (!empty($result['errors'])) {
            $this->warn('Errors occurred:');
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        $this->info("Sync complete:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Issues fetched', $result['synced']],
                ['Created', $result['created']],
                ['Updated', $result['updated']],
                ['Errors', count($result['errors'])],
            ]
        );

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}