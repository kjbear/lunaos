<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Laravel AI facade alias
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Ai', \Laravel\Ai\Facades\Ai::class);
    }

    /**
     * Bootstrap any application services.
     * 
     * PRODUCTION SAFETY GUARDS
     * Prevents accidental data destruction in production/staging environments
     */
    public function boot(): void
    {
        // Only apply guards in non-local environments
        if (!app()->environment('local')) {
            $this->applyProductionGuards();
        }
    }

    /**
     * Apply safety guards for production/staging environments
     */
    protected function applyProductionGuards(): void
    {
        // Guard 1: Block destructive artisan commands
        $this->blockDestructiveCommands();

        // Guard 2: Pre-migration data check
        $this->checkDataBeforeMigration();
    }

    /**
     * Block destructive artisan commands in production
     */
    protected function blockDestructiveCommands(): void
    {
        $dangerousCommands = [
            'migrate:fresh',
            'migrate:reset',
            'db:wipe',
            'db:seed', // Only if --class not specified
        ];

        $currentCommand = $this->getCurrentCommand();
        
        if (in_array($currentCommand, $dangerousCommands)) {
            // Allow migrate:fresh with --force flag only in staging (for testing)
            if ($currentCommand === 'migrate:fresh' && app()->environment('staging')) {
                // Allow in staging for migration testing
                return;
            }

            abort(403, sprintf(
                '🚫 Destructive command "%s" is disabled in %s environment. ' .
                'Use staging environment for testing: php copy-to-staging.sh && cd lunaos-staging',
                $currentCommand,
                app()->environment()
            ));
        }
    }

    /**
     * Check for existing data before running migrations
     * Fails fast if data exists without backup
     */
    protected function checkDataBeforeMigration(): void
    {
        // Only for production environment
        if (!app()->environment('production')) {
            return;
        }

        // Check if we're running migrations
        $currentCommand = $this->getCurrentCommand();
        if (!str_starts_with($currentCommand, 'migrate')) {
            return;
        }

        // Check for critical tables with data
        try {
            if (Schema::hasTable('team_members')) {
                $memberCount = DB::table('team_members')->count();
                
                if ($memberCount > 0) {
                    // Data exists - check if backup was run recently
                    $backupDir = database_path('backups');
                    if (!is_dir($backupDir)) {
                        abort(403, '🚫 Backup directory not found! Run: ./scripts/backup-team-data.sh');
                    }

                    $latestBackup = $this->getLatestBackup();
                    if (!$latestBackup) {
                        abort(403, '🚫 No backup found! Run: ./scripts/backup-team-data.sh');
                    }

                    $backupAge = time() - filemtime($latestBackup);
                    $maxAge = 24 * 60 * 60; // 24 hours

                    if ($backupAge > $maxAge) {
                        abort(403, sprintf(
                            '🚫 Backup is older than 24 hours (age: %dh). ' .
                            'Run fresh backup: ./scripts/backup-team-data.sh',
                            round($backupAge / 3600)
                        ));
                    }
                }
            }
        } catch (\Exception $e) {
            // If we can't check, allow migration to proceed
            // but log the issue
            logger()->warning('Could not verify backup before migration: ' . $e->getMessage());
        }
    }

    /**
     * Get the current artisan command name
     */
    protected function getCurrentCommand(): string
    {
        if (app()->runningInConsole()) {
            return request()->route() ? request()->route()->getName() : '';
        }
        
        return '';
    }

    /**
     * Find the latest backup file
     */
    protected function getLatestBackup(): ?string
    {
        $backupDir = database_path('backups');
        $files = glob($backupDir . '/backup-manifest-*.json');
        
        if (empty($files)) {
            return null;
        }

        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files[0];
    }
}
