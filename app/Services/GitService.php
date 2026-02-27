<?php

namespace App\Services;

use App\Models\Repository;
use Illuminate\Support\Facades\Process;

/**
 * Git Service
 * 
 * Handles all git operations with repository-aware configuration.
 * Uses repository settings for branch prefixes, paths, and remote URLs.
 */
class GitService
{
    /**
     * Repository configuration
     */
    protected ?Repository $repository;
    
    /**
     * Repository path
     */
    protected string $repoPath;
    
    /**
     * Create a new Git service instance
     */
    public function __construct(?Repository $repository = null)
    {
        $this->repository = $repository;
        $this->repoPath = $repository?->path ?? base_path();
    }
    
    /**
     * Set the repository path
     */
    public function setRepositoryPath(string $path): self
    {
        $this->repoPath = $path;
        return $this;
    }
    
    /**
     * Checkout or create a branch
     */
    public function checkoutBranch(string $branchName): bool
    {
        $result = $this->run("checkout -B {$branchName}");
        return $result->successful();
    }
    
    /**
     * Stage all changes
     */
    public function stageAll(): bool
    {
        $result = $this->run('add .');
        return $result->successful();
    }
    
    /**
     * Commit changes with message
     */
    public function commit(string $message): string
    {
        $result = $this->run("commit -m " . escapeshellarg($message));
        
        if (!$result->successful()) {
            throw new \RuntimeException("Git commit failed: {$result->errorOutput()}");
        }
        
        return trim($result->output());
    }
    
    /**
     * Get commit hash for a ref
     */
    public function getCommitHash(string $ref = 'HEAD'): string
    {
        $result = $this->run("rev-parse {$ref}");
        
        if (!$result->successful()) {
            throw new \RuntimeException("Failed to get commit hash: {$result->errorOutput()}");
        }
        
        return trim($result->output());
    }
    
    /**
     * Push branch to remote
     */
    public function push(string $branchName, string $remote = 'origin'): bool
    {
        $result = $this->run("push -u {$remote} {$branchName}");
        return $result->successful();
    }
    
    /**
     * Create a tag
     */
    public function createTag(string $tagName, string $message = ''): bool
    {
        $cmd = $message 
            ? "tag -a {$tagName} -m " . escapeshellarg($message)
            : "tag {$tagName}";
        
        $result = $this->run($cmd);
        return $result->successful();
    }
    
    /**
     * Fetch from remote
     */
    public function fetch(string $remote = 'origin'): bool
    {
        $result = $this->run("fetch {$remote}");
        return $result->successful();
    }
    
    /**
     * Pull from remote
     */
    public function pull(string $remote = 'origin', string $branch = 'main'): bool
    {
        $result = $this->run("pull {$remote} {$branch}");
        return $result->successful();
    }
    
    /**
     * Get current branch name
     */
    public function getCurrentBranch(): string
    {
        $result = $this->run('rev-parse --abbrev-ref HEAD');
        
        if (!$result->successful()) {
            throw new \RuntimeException("Failed to get current branch: {$result->errorOutput()}");
        }
        
        return trim($result->output());
    }
    
    /**
     * Get git status (short format)
     */
    public function getStatus(): string
    {
        $result = $this->run('status --short');
        return $result->successful() ? $result->output() : '';
    }
    
    /**
     * Get the last commit log
     */
    public function getLastCommitLog(): string
    {
        $result = $this->run('log -1 --pretty=%B');
        return $result->successful() ? trim($result->output()) : '';
    }
    
    /**
     * Run a git command
     */
    protected function run(string $command): \Illuminate\Process\ProcessResult
    {
        $fullCommand = "cd {$this->repoPath} && git {$command}";
        
        $result = Process::timeout(30)->run($fullCommand);
        
        if (!$result->successful() && config('app.debug')) {
            \Illuminate\Support\Facades\Log::warning("Git command failed", [
                'command' => $fullCommand,
                'error' => $result->errorOutput(),
                'output' => $result->output(),
            ]);
        }
        
        return $result;
    }
}
