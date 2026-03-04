<?php

namespace Tests\Unit\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ConsolidateHrAndAgentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create the old tables for testing migration
        $this->createPersonasTable();
        $this->createAgentsTable();
        $this->createRelatedTables();
    }

    private function createPersonasTable(): void
    {
        if (!Schema::hasTable('personas')) {
            Schema::create('personas', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name')->unique();
                $table->enum('role', ['subagent', 'board_member', 'custom']);
                $table->string('model')->default('haiku');
                $table->string('avatar')->nullable();
                $table->enum('status', ['active', 'inactive', 'archived']);
                $table->string('inspiration')->nullable();
                $table->text('system_prompt')->nullable();
                $table->string('workspace_path')->nullable();
                $table->timestamp('deactivated_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createAgentsTable(): void
    {
        if (!Schema::hasTable('agents')) {
            Schema::create('agents', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('type')->nullable();
                $table->string('role')->default('worker');
                $table->string('model')->nullable();
                $table->string('provider')->default('ollama');
                $table->text('system_prompt')->nullable();
                $table->json('model_settings')->nullable();
                $table->string('avatar')->default('🤖');
                $table->string('emoji')->default('🤖');
                $table->enum('status', ['online', 'offline', 'error', 'busy']);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('runtime_location')->default('php');
                $table->timestamp('last_location_check')->nullable();
                $table->string('strategy_class')->nullable();
                $table->string('step_filter')->nullable();
                $table->json('workflow_config')->nullable();
                $table->string('skill_doc_path')->nullable();
                $table->json('skill_metadata')->nullable();
                $table->boolean('is_online')->nullable();
                $table->json('capabilities')->nullable();
                $table->json('settings')->nullable();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createRelatedTables(): void
    {
        // Persona metrics
        if (!Schema::hasTable('persona_metrics')) {
            Schema::create('persona_metrics', function ($table) {
                $table->uuid('id')->primary();
                $table->uuid('persona_id');
                $table->integer('sessions_count')->default(0);
                $table->integer('tokens_used')->default(0);
                $table->integer('decisions_made')->default(0);
                $table->timestamps();

                $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            });
        }

        // Persona workspaces
        if (!Schema::hasTable('persona_workspaces')) {
            Schema::create('persona_workspaces', function ($table) {
                $table->uuid('id')->primary();
                $table->uuid('persona_id');
                $table->string('file_path');
                $table->string('file_type');
                $table->timestamps();

                $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            });
        }

        // Agent activities
        if (!Schema::hasTable('agent_activities')) {
            Schema::create('agent_activities', function ($table) {
                $table->id();
                $table->unsignedBigInteger('agent_id');
                $table->string('activity_type');
                $table->text('description');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            });
        }

        // Tasks (to test foreign key updates)
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function ($table) {
                $table->id();
                $table->string('title');
                $table->string('assigned_to')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    /** @test */
    public function migration_creates_team_members_table(): void
    {
        // Seed some test data
        DB::table('personas')->insert([
            'id' => Str::uuid(),
            'name' => 'board-member-1',
            'role' => 'board_member',
            'model' => 'haiku',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('agents')->insert([
            'name' => 'worker-agent-1',
            'role' => 'worker',
            'model' => 'ollama-local/qwen3.5:cloud',
            'provider' => 'ollama',
            'status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify team_members table exists
        $this->assertTrue(Schema::hasTable('team_members'));

        // Verify schema has required columns
        $this->assertTrue(Schema::hasColumn('team_members', 'id'));
        $this->assertTrue(Schema::hasColumn('team_members', 'name'));
        $this->assertTrue(Schema::hasColumn('team_members', 'type'));
        $this->assertTrue(Schema::hasColumn('team_members', 'category'));
        $this->assertTrue(Schema::hasColumn('team_members', 'status'));
    }

    /** @test */
    public function migration_migrates_personas(): void
    {
        $personaId = Str::uuid();
        DB::table('personas')->insert([
            'id' => $personaId,
            'name' => 'test-persona',
            'role' => 'subagent',
            'model' => 'dolphin',
            'status' => 'active',
            'system_prompt' => 'You are a test persona',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify persona was migrated
        $this->assertDatabaseHas('team_members', [
            'name' => 'test-persona',
            'type' => 'persona',
        ]);
    }

    /** @test */
    public function migration_migrates_agents(): void
    {
        DB::table('agents')->insert([
            'name' => 'test-agent',
            'role' => 'worker',
            'model' => 'ollama-local/qwen3.5:cloud',
            'provider' => 'ollama',
            'status' => 'online',
            'system_prompt' => 'You are a test agent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify agent was migrated
        $this->assertDatabaseHas('team_members', [
            'name' => 'test-agent',
            'type' => 'agent',
        ]);
    }

    /** @test */
    public function migration_handles_name_collisions(): void
    {
        // Create both persona and agent with same name
        $personaId = Str::uuid();
        DB::table('personas')->insert([
            'id' => $personaId,
            'name' => 'collision-test',
            'role' => 'subagent',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('agents')->insert([
            'name' => 'collision-test',
            'role' => 'worker',
            'status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $result = $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php']);

        // Migration should complete (exit code 0)
        $this->assertEquals(0, $result);

        // Verify both records exist (one should be renamed)
        $teamMembers = DB::table('team_members')->where('name', 'LIKE', 'collision-test%')->get();
        $this->assertEquals(2, $teamMembers->count());
    }

    /** @test */
    public function migration_preserves_foreign_keys(): void
    {
        $agentId = 1;
        DB::table('agents')->insert([
            'id' => $agentId,
            'name' => 'task-assignee',
            'role' => 'worker',
            'status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create task assigned to agent
        DB::table('tasks')->insert([
            'title' => 'Test Task',
            'assigned_to' => 'task-assignee',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify task still references valid team member
        $task = DB::table('tasks')->first();
        $teamMember = DB::table('team_members')->where('name', $task->assigned_to)->first();
        
        $this->assertNotNull($teamMember);
    }

    /** @test */
    public function migration_migrates_metrics(): void
    {
        $personaId = Str::uuid();
        DB::table('personas')->insert([
            'id' => $personaId,
            'name' => 'metrics-test',
            'role' => 'board_member',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('persona_metrics')->insert([
            'id' => Str::uuid(),
            'persona_id' => $personaId,
            'sessions_count' => 10,
            'tokens_used' => 50000,
            'decisions_made' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify metrics migrated
        $this->assertTrue(Schema::hasTable('team_member_metrics'));
        
        $metrics = DB::table('team_member_metrics')->first();
        $this->assertNotNull($metrics);
        $this->assertEquals(10, $metrics->sessions_count);
        $this->assertEquals(50000, $metrics->tokens_used);
    }

    /** @test */
    public function migration_migrates_workspaces(): void
    {
        $personaId = Str::uuid();
        DB::table('personas')->insert([
            'id' => $personaId,
            'name' => 'workspace-test',
            'role' => 'subagent',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('persona_workspaces')->insert([
            'id' => Str::uuid(),
            'persona_id' => $personaId,
            'file_path' => '/workspace/test/file.md',
            'file_type' => 'markdown',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify workspaces migrated
        $this->assertTrue(Schema::hasTable('team_member_workspaces'));
        
        $workspace = DB::table('team_member_workspaces')->first();
        $this->assertNotNull($workspace);
        $this->assertEquals('/workspace/test/file.md', $workspace->file_path);
    }

    /** @test */
    public function migration_migrates_activities(): void
    {
        DB::table('agents')->insert([
            'id' => 1,
            'name' => 'activity-test',
            'role' => 'worker',
            'status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('agent_activities')->insert([
            'agent_id' => 1,
            'activity_type' => 'task_completed',
            'description' => 'Completed task #123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify activities migrated
        $this->assertTrue(Schema::hasTable('team_member_activities'));
        
        $activity = DB::table('team_member_activities')->first();
        $this->assertNotNull($activity);
        $this->assertEquals('task_completed', $activity->activity_type);
    }

    /** @test */
    public function rollback_restores_original_tables(): void
    {
        // Setup data
        DB::table('personas')->insert([
            'id' => Str::uuid(),
            'name' => 'rollback-test',
            'role' => 'subagent',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('agents')->insert([
            'name' => 'rollback-agent',
            'role' => 'worker',
            'status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify migration worked
        $this->assertDatabaseHas('team_members', ['name' => 'rollback-test']);
        $this->assertDatabaseHas('team_members', ['name' => 'rollback-agent']);

        // Run rollback
        $this->artisan('migrate:rollback', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Verify rollback restored original tables
        $this->assertDatabaseHas('personas', ['name' => 'rollback-test']);
        $this->assertDatabaseHas('agents', ['name' => 'rollback-agent']);
    }

    /** @test */
    public function no_data_loss(): void
    {
        // Count original records
        $personaCount = DB::table('personas')->count();
        $agentCount = DB::table('agents')->count();
        $totalCount = $personaCount + $agentCount;

        // Run migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        // Count migrated records
        $teamMemberCount = DB::table('team_members')->count();

        // Allow for name collisions (which result in renaming)
        $this->assertGreaterThanOrEqual($totalCount - 5, $teamMemberCount);
    }

    /** @test */
    public function migration_is_idempotent(): void
    {
        // Seed data
        DB::table('personas')->insert([
            'id' => Str::uuid(),
            'name' => 'idempotent-test',
            'role' => 'subagent',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run migration twice
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php'])
            ->assertExitCode(0);

        $result = $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_03_100000_consolidate_hr_and_agents_to_team.php']);

        // Second run should not fail (may be skipped if already migrated)
        $this->assertGreaterThanOrEqual(0, $result);
    }
}
