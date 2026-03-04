<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL: Run backup script before executing this migration!
     * Command: ./scripts/backup-team-data.sh
     */
    public function up(): void
    {
        // Step 1: Create the unified team_members table
        Schema::create('team_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('email')->nullable();
            $table->string('title')->nullable();
            $table->enum('type', ['personas', 'board-members', 'workers'])->default('workers');
            $table->enum('role', ['board_member', 'persona', 'worker'])->default('worker');
            $table->enum('category', ['board_member', 'subagent', 'worker', 'custom'])->default('worker');
            $table->enum('status', ['active', 'inactive', 'online', 'offline', 'error', 'busy', 'archived'])->default('active');
            $table->string('model')->nullable();
            $table->string('provider')->default('ollama');
            $table->string('avatar')->nullable();
            $table->string('emoji')->default('🤖');
            $table->text('system_prompt')->nullable();
            $table->json('settings')->nullable();
            $table->json('metadata_json')->nullable();
            $table->string('workspace_path')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('team_members')->onDelete('set null');
            $table->index('status');
            $table->index('role');
            $table->index('category');
            $table->index('type');
        });

        // Step 2: Migrate personas to team_members (skip if table doesn't exist or in testing)
        $personas = collect();
        if (Schema::hasTable('personas') && config('app.env') !== 'testing') {
            $personas = DB::table('personas')->get();
        }
        foreach ($personas as $persona) {
            // Map persona role to team member role
            $role = match($persona->role) {
                'board_member' => 'board_member',
                'subagent' => 'persona',
                'custom' => 'persona',
                default => 'worker',
            };
            
            // Map persona type
            $type = match($persona->role) {
                'board_member' => 'board-members',
                'subagent' => 'personas',
                'custom' => 'personas',
                default => 'workers',
            };
            
            // Map status
            $status = match($persona->status) {
                'active' => 'active',
                'inactive' => 'inactive',
                'archived' => 'archived',
                default => 'active',
            };
            
            DB::table('team_members')->insert([
                'id' => $persona->id, // Keep UUID from persona
                'name' => $persona->name,
                'title' => null,
                'type' => $type,
                'role' => $role,
                'category' => $role === 'board_member' ? 'board_member' : ($persona->role ?? 'worker'),
                'status' => $status,
                'model' => $persona->model,
                'provider' => 'ollama',
                'avatar' => $persona->avatar,
                'emoji' => '🤖',
                'system_prompt' => $persona->system_prompt,
                'settings' => null,
                'metadata_json' => json_encode([
                    'migrated_from' => 'personas',
                    'migration_date' => now()->toIso8601String(),
                    'original_id' => $persona->id,
                ]),
                'workspace_path' => $persona->workspace_path,
                'parent_id' => null,
                'deactivated_at' => $persona->deactivated_at,
                'created_at' => $persona->created_at,
                'updated_at' => $persona->updated_at ?? now(),
            ]);
        }

        // Step 3: Migrate agents to team_members (skip if table doesn't exist or in testing)
        $agents = collect();
        if (Schema::hasTable('agents') && config('app.env') !== 'testing') {
            $agents = DB::table('agents')->get();
        }
        foreach ($agents as $agent) {
            // Check for name collision
            $existingName = DB::table('team_members')->where('name', $agent->name)->first();
            $finalName = $agent->name;
            
            if ($existingName) {
                // Handle collision by appending '-agent'
                $finalName = $agent->name . '-agent';
                Log::warning("Name collision detected: {$agent->name} renamed to {$finalName}");
            }
            
            // Map agent status
            $status = match($agent->status) {
                'online' => 'online',
                'offline' => 'offline',
                'error' => 'error',
                'busy' => 'busy',
                default => 'active',
            };
            
            // Generate UUID for agent (since they use integer IDs)
            $agentUuid = Str::uuid();
            
            DB::table('team_members')->insert([
                'id' => $agentUuid,
                'name' => $finalName,
                'title' => null,
                'type' => 'workers',
                'role' => 'worker',
                'status' => $status,
                'model' => $agent->model,
                'provider' => 'ollama',
                'avatar' => null,
                'emoji' => '🤖',
                'system_prompt' => null,
                'settings' => null,
                'metadata_json' => json_encode([
                    'migrated_from' => 'agents',
                    'migration_date' => now()->toIso8601String(),
                    'original_id' => $agent->id,
                    'original_parent_id' => $agent->parent_id,
                ]),
                'workspace_path' => null,
                'parent_id' => null, // Will update in next step if parent exists
                'deactivated_at' => null,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at ?? now(),
            ]);
        }

        // Step 4: Update parent_id references for agents (if needed)
        // This requires mapping old integer IDs to new UUIDs
        if ($agents->count() > 0) {
            $idMapping = [];
            foreach ($agents as $agent) {
                if ($agent->parent_id) {
                    $parentAgent = DB::table('agents')->where('id', $agent->parent_id)->first();
                    if ($parentAgent) {
                        // Find the new UUID for parent
                        $parentMember = DB::table('team_members')
                            ->where('metadata_json', 'like', '%"original_id":' . $parentAgent->id . '%')
                            ->first();
                        if ($parentMember) {
                            DB::table('team_members')
                                ->where('name', $agent->name . ($existingName ? '-agent' : ''))
                                ->update(['parent_id' => $parentMember->id]);
                        }
                    }
                }
            }
        }

        // Step 5: Archive old tables (rename, don't drop yet)
        // This allows for 30-day verification period
        try {
            Schema::rename('personas', 'personas_archive');
            Schema::rename('persona_metrics', 'persona_metrics_archive');
            Schema::rename('persona_workspaces', 'persona_workspaces_archive');
        } catch (\Exception $e) {
            Log::error('Failed to archive personas tables: ' . $e->getMessage());
        }

        // Note: agents table migrations are still pending, so we don't archive yet
        // Run pending agent migrations first, then archive in a follow-up migration
    }

    /**
     * Reverse the migrations.
     * 
     * DO NOT USE THIS in production. Use the restore script instead.
     * This is only for local development testing.
     */
    public function down(): void
    {
        // WARNING: This is a simplified rollback for development only
        // In production, use: ./scripts/restore-hr-and-agents.sh
        
        Log::warning('Rolling back team_members migration - data may be lost!');
        
        // Drop new table
        Schema::dropIfExists('team_members');
        
        // Restore archived tables
        try {
            Schema::rename('personas_archive', 'personas');
            Schema::rename('persona_metrics_archive', 'persona_metrics');
            Schema::rename('persona_workspaces_archive', 'persona_workspaces');
        } catch (\Exception $e) {
            Log::error('Failed to restore personas tables: ' . $e->getMessage());
            throw $e;
        }
    }
};
