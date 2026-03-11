<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Recreate tasks table with repository_id as varchar to match repositories.id (UUID string)
        DB::statement('
            CREATE TABLE tasks_new (
                id integer primary key autoincrement,
                title varchar not null,
                description text,
                assigned_to varchar,
                repository_id varchar,
                status varchar not null default "pending",
                step varchar not null default "develop",
                priority varchar not null default "medium",
                task_type varchar not null default "feature",
                view_mode varchar not null default "list",
                context_json text,
                branch_name varchar,
                pr_url varchar,
                artifacts_json text,
                retry_count integer not null default 0,
                started_at datetime,
                completed_at datetime,
                created_at datetime,
                updated_at datetime,
                project_id varchar,
                requirement_id varchar,
                deleted_at datetime,
                failure_reason text,
                foreign key(repository_id) references repositories(id) on delete set null on update no action,
                foreign key(project_id) references projects(id) on delete cascade,
                foreign key(requirement_id) references requirements(id) on delete set null
            )
        ');
        
        // Copy existing data
        if (DB::table('tasks')->count() > 0) {
            DB::statement('INSERT INTO tasks_new SELECT * FROM tasks');
        }
        
        // Replace old table
        DB::statement('DROP TABLE tasks');
        DB::statement('ALTER TABLE tasks_new RENAME TO tasks');
        
        // Recreate indexes
        DB::statement('CREATE INDEX tasks_assigned_to_index ON tasks (assigned_to)');
        DB::statement('CREATE INDEX tasks_created_at_index ON tasks (created_at)');
        DB::statement('CREATE INDEX tasks_status_step_index ON tasks (status, step)');
        DB::statement('CREATE INDEX tasks_view_mode_index ON tasks (view_mode)');
        DB::statement('CREATE INDEX tasks_project_id_status_index ON tasks (project_id, status)');
        DB::statement('CREATE INDEX tasks_assigned_to_status_index ON tasks (assigned_to, status)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException('Cannot reverse repository_id type migration');
    }
};
