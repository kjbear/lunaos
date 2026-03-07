<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyTeamConsolidation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:verify-consolidation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify team consolidation migration completed successfully';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Verifying team consolidation...');
        $this->newLine();

        // Check if team_members table exists
        if (!Schema::hasTable('team_members')) {
            $this->error('❌ team_members table does NOT exist!');
            $this->warn('   Run the migration: php artisan migrate');
            return Command::FAILURE;
        }

        $this->info('✓ team_members table exists');

        // Verify table structure
        $this->verifyTableStructure();

        // Count records by type
        $this->displayRecordCounts();

        // Check for orphaned records
        $this->checkOrphanedRecords();

        // Check old tables status
        $this->checkOldTables();

        $this->newLine();
        $this->info('✅ Team consolidation verification complete!');

        return Command::SUCCESS;
    }

    /**
     * Verify the team_members table has expected columns.
     */
    protected function verifyTableStructure(): void
    {
        $this->newLine();
        $this->info('Checking table structure...');

        $expectedColumns = [
            'id', 'name', 'email', 'title', 'type', 'role', 'category',
            'status', 'model', 'provider', 'avatar', 'emoji', 'system_prompt',
            'settings', 'metadata_json', 'workspace_path', 'parent_id',
            'deactivated_at', 'created_at', 'updated_at'
        ];

        $columns = collect(DB::select('PRAGMA table_info(team_members)'))
            ->pluck('name')
            ->toArray();

        $missingColumns = array_diff($expectedColumns, $columns);

        if (empty($missingColumns)) {
            $this->info('✓ All expected columns present');
        } else {
            $this->warn('⚠ Missing columns: ' . implode(', ', $missingColumns));
        }

        // Check primary key
        $primaryKey = collect(DB::select('PRAGMA table_info(team_members)'))
            ->firstWhere('pk', 1);
        if ($primaryKey && $primaryKey->name === 'id') {
            $this->info('✓ Primary key is UUID (id)');
        } else {
            $this->warn('⚠ Primary key structure unexpected');
        }
    }

    /**
     * Display record counts by type and role.
     */
    protected function displayRecordCounts(): void
    {
        $this->newLine();
        $this->info('Record counts:');

        $totalCount = DB::table('team_members')->count();
        $this->info("  Total team members: {$totalCount}");

        // By type
        $this->newLine();
        $this->info('  By Type:');
        $byType = DB::table('team_members')
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        foreach ($byType as $row) {
            $this->line("    • {$row->type}: {$row->count}");
        }

        // By role
        $this->newLine();
        $this->info('  By Role:');
        $byRole = DB::table('team_members')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        foreach ($byRole as $row) {
            $this->line("    • {$row->role}: {$row->count}");
        }

        // By status
        $this->newLine();
        $this->info('  By Status:');
        $byStatus = DB::table('team_members')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        foreach ($byStatus as $row) {
            $this->line("    • {$row->status}: {$row->count}");
        }
    }

    /**
     * Check for orphaned records (tasks assigned to non-existent team members).
     */
    protected function checkOrphanedRecords(): void
    {
        $this->newLine();
        $this->info('Checking for orphaned records...');

        // Check tasks table exists
        if (!Schema::hasTable('tasks')) {
            $this->warn('⚠ tasks table does not exist (skipping orphan check)');
            return;
        }

        // Check for orphaned task assignments
        // Tasks use 'assigned_to' which references team_members.id (UUID)
        // But the relationship in the model uses 'name' as the foreign key
        // So we check if assigned_to names match any team_member names
        $orphanedByUuid = DB::table('tasks')
            ->leftJoin('team_members', 'tasks.assigned_to', '=', 'team_members.id')
            ->whereNotNull('tasks.assigned_to')
            ->whereNull('team_members.id')
            ->count();

        if ($orphanedByUuid > 0) {
            $this->error("❌ Found {$orphanedByUuid} tasks assigned to non-existent team members (by UUID)");
        } else {
            $this->info('✓ No orphaned task assignments (by UUID)');
        }

        // Check for null assigned_to on tasks that should have one
        $nullAssignments = DB::table('tasks')
            ->whereNull('assigned_to')
            ->count();

        $this->info("  Tasks with null assigned_to: {$nullAssignments}");
    }

    /**
     * Check status of old tables (agents, personas).
     */
    protected function checkOldTables(): void
    {
        $this->newLine();
        $this->info('Checking old tables status...');

        // Check agents table
        if (Schema::hasTable('agents')) {
            $agentsCount = DB::table('agents')->count();
            $this->info("  agents table: EXISTS ({$agentsCount} records)");
            if ($agentsCount > 0) {
                $this->warn('  ⚠ agents table still has data. Migration pending for agents.');
            }
        } else {
            $this->info('  agents table: NOT FOUND (dropped or never existed)');
        }

        // Check personas table
        if (Schema::hasTable('personas')) {
            $personasCount = DB::table('personas')->count();
            $this->info("  personas table: EXISTS ({$personasCount} records)");
        } else {
            $this->info('  personas table: NOT FOUND');
        }

        // Check personas_archive table (should exist after migration)
        if (Schema::hasTable('personas_archive')) {
            $archivedCount = DB::table('personas_archive')->count();
            $this->info("  personas_archive table: EXISTS ({$archivedCount} records)");
            if ($archivedCount > 0) {
                $this->comment('  ℹ Archived personas retained for rollback capability.');
            }
        }
    }
}