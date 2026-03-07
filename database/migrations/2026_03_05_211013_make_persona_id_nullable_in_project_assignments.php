<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * SQLite workaround: Since we can't ALTER COLUMN to change NULL constraint,
     * we'll just ignore it. In production (MySQL/PostgreSQL), this would use:
     * $table->string('persona_id', 36)->nullable()->change();
     * 
     * For SQLite, we just need to ensure new inserts don't require persona_id.
     * Existing rows can have NULL inserted via raw SQL.
     */
    public function up(): void
    {
        // For SQLite, we can use a raw SQL to make the column nullable
        if (config('database.default') === 'sqlite') {
            // Drop and recreate the table with nullable persona_id
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            
            // Get existing data
            $existingData = DB::table('project_assignments')->get();
            
            // Drop the table and recreate with nullable persona_id
            DB::statement('DROP TABLE IF EXISTS project_assignments_temp');
            DB::statement('
                CREATE TABLE project_assignments_temp (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id varchar NOT NULL,
                    persona_id varchar,
                    role varchar,
                    assigned_at datetime,
                    created_at datetime,
                    updated_at datetime,
                    agent_id varchar,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
                )
            ');
            
            // Migrate data
            foreach ($existingData as $row) {
                DB::table('project_assignments_temp')->insert([
                    'id' => $row->id,
                    'project_id' => $row->project_id,
                    'persona_id' => $row->persona_id,
                    'role' => $row->role,
                    'assigned_at' => $row->assigned_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'agent_id' => $row->agent_id ?? null,
                ]);
            }
            
            // Drop old table and rename new one
            DB::statement('DROP TABLE project_assignments');
            DB::statement('ALTER TABLE project_assignments_temp RENAME TO project_assignments');
            
            // Recreate index
            DB::statement('CREATE INDEX project_assignments_project_id_agent_id_index ON project_assignments (project_id, agent_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // In production, this would be:
        // $table->string('persona_id', 36)->nullable(false)->change();
    }
};
