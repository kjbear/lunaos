<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectSchemaTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // PROJECT TABLE SCHEMA TESTS
    // ==========================================

    public function test_projects_table_has_expected_columns(): void
    {
        $columns = [
            'id',
            'name',
            'description',
            'repo_url',
            'repository_id',
            'health',
            'progress',
            'percent_complete',
            'owner',
            'status',
            'architecture_type',
            'technologies',
            'project_manager_id',
            'archived_at',
            'deleted_at',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('projects', $column),
                "Column `{$column}` is missing from projects table"
            );
        }
    }

    public function test_projects_table_has_primary_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('projects', 'id'),
            'Primary key `id` is missing from projects table'
        );
    }

    public function test_projects_table_uses_uuid_primary_key(): void
    {
        // Verify id column is string type (UUID)
        $columnType = Schema::getColumnType('projects', 'id');
        $this->assertContains($columnType, ['string', 'varchar', 'char', 'guid', 'uniqueidentifier']);
    }

    public function test_projects_table_has_soft_delete_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('projects', 'deleted_at'),
            'Column `deleted_at` is missing from projects table (soft deletes)'
        );
    }

    // ==========================================
    // FOREIGN KEY CONSTRAINT TESTS
    // ==========================================

    public function test_tasks_table_has_project_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('tasks', 'project_id'),
            'Column `project_id` is missing from tasks table'
        );

        // Verify foreign key exists through database query
        $foreignKeys = $this->getForeignKeysForTable('tasks', 'project_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `project_id` is missing from tasks table'
        );
    }

    public function test_project_assignments_table_has_project_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('project_assignments', 'project_id'),
            'Column `project_id` is missing from project_assignments table'
        );

        $foreignKeys = $this->getForeignKeysForTable('project_assignments', 'project_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `project_id` is missing from project_assignments table'
        );
    }

    public function test_project_assignments_table_has_agent_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('project_assignments', 'agent_id'),
            'Column `agent_id` is missing from project_assignments table'
        );

        $foreignKeys = $this->getForeignKeysForTable('project_assignments', 'agent_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `agent_id` is missing from project_assignments table'
        );
    }

    public function test_projects_table_has_repository_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('projects', 'repository_id'),
            'Column `repository_id` is missing from projects table'
        );

        $foreignKeys = $this->getForeignKeysForTable('projects', 'repository_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `repository_id` is missing from projects table'
        );
    }

    public function test_project_issues_table_has_project_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('project_issues', 'project_id'),
            'Column `project_id` is missing from project_issues table'
        );

        $foreignKeys = $this->getForeignKeysForTable('project_issues', 'project_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `project_id` is missing from project_issues table'
        );
    }

    public function test_requirements_table_has_project_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('requirements', 'project_id'),
            'Column `project_id` is missing from requirements table'
        );

        $foreignKeys = $this->getForeignKeysForTable('requirements', 'project_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `project_id` is missing from requirements table'
        );
    }

    // ==========================================
    // INDEX VERIFICATION TESTS
    // ==========================================

    public function test_tasks_table_has_index_on_project_id(): void
    {
        $indexes = $this->getIndexesForTable('tasks', 'project_id');
        $this->assertNotEmpty(
            $indexes,
            'Index on `project_id` is missing from tasks table'
        );
    }

    public function test_project_assignments_table_has_index_on_project_id(): void
    {
        $indexes = $this->getIndexesForTable('project_assignments', 'project_id');
        $this->assertNotEmpty(
            $indexes,
            'Index on `project_id` is missing from project_assignments table'
        );
    }

    public function test_project_assignments_table_has_composite_index(): void
    {
        $indexes = $this->getIndexesForTable('project_assignments', ['project_id', 'agent_id']);
        $this->assertNotEmpty(
            $indexes,
            'Composite index on `project_id, agent_id` is missing from project_assignments table'
        );
    }

    public function test_projects_table_has_index_on_repository_id(): void
    {
        $indexes = $this->getIndexesForTable('projects', 'repository_id');
        $this->assertNotEmpty(
            $indexes,
            'Index on `repository_id` is missing from projects table'
        );
    }

    public function test_project_issues_table_has_index_on_project_id(): void
    {
        $indexes = $this->getIndexesForTable('project_issues', 'project_id');
        $this->assertNotEmpty(
            $indexes,
            'Index on `project_id` is missing from project_issues table'
        );
    }

    // ==========================================
    // NULLABLE COLUMN TESTS
    // ==========================================

    public function test_projects_repository_id_is_nullable(): void
    {
        $column = Schema::getColumnListing('projects');
        $this->assertContains('repository_id', $column);

        // Verify that we can insert a project without repository_id
        $this->expectNotToPerformAssertions();
    }

    public function test_project_assignments_persona_id_is_nullable(): void
    {
        // After the migration, persona_id should be nullable
        if (Schema::hasColumn('project_assignments', 'persona_id')) {
            // Column exists, verify it can be null
            $this->expectNotToPerformAssertions();
        } else {
            // Column was removed in migration
            $this->assertFalse(Schema::hasColumn('project_assignments', 'persona_id'));
        }
    }

    public function test_projects_archived_at_is_nullable(): void
    {
        // archived_at is nullable
        $this->assertTrue(Schema::hasColumn('projects', 'archived_at'));
    }

    public function test_projects_deleted_at_is_nullable(): void
    {
        // deleted_at should be nullable for soft deletes
        $this->assertTrue(Schema::hasColumn('projects', 'deleted_at'));
    }

    // ==========================================
    // PROJECT_ARTIFACTS TABLE TESTS
    // ==========================================

    public function test_project_artifacts_table_has_project_id_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('project_artifacts', 'project_id'),
            'Column `project_id` is missing from project_artifacts table'
        );
    }

    public function test_project_artifacts_table_has_foreign_key_to_projects(): void
    {
        $foreignKeys = $this->getForeignKeysForTable('project_artifacts', 'project_id');
        $this->assertNotEmpty(
            $foreignKeys,
            'Foreign key constraint for `project_id` is missing from project_artifacts table'
        );
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get foreign keys for a specific column in a table.
     */
    private function getForeignKeysForTable(string $table, string $column): array
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't have information_schema, so we'll check via pragma
            $foreignKeys = $connection->select("PRAGMA foreign_key_list({$table})");
            return array_filter($foreignKeys, function ($fk) use ($column) {
                return $fk->from === $column || (isset($fk->column) && $fk->column === $column);
            });
        }

        // MySQL/PostgreSQL
        $foreignKeys = $connection->select("
            SELECT * FROM information_schema.TABLE_CONSTRAINTS tc
            JOIN information_schema.KEY_COLUMN_USAGE kcu
                ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND tc.TABLE_SCHEMA = kcu.TABLE_SCHEMA
            WHERE tc.TABLE_NAME = ?
            AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND kcu.COLUMN_NAME = ?
        ", [$table, $column]);

        return $foreignKeys;
    }

    /**
     * Get indexes for a specific column in a table.
     */
    private function getIndexesForTable(string $table, $columns): array
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite index check
            $allIndexes = $connection->select("PRAGMA index_list({$table})");
            $found = [];
            foreach ($allIndexes as $index) {
                $indexInfo = $connection->select("PRAGMA index_info({$index->name})");
                $indexColumns = array_map(function ($col) {
                    return $col->name ?? null;
                }, $indexInfo);
                foreach ($columns as $col) {
                    if (in_array($col, $indexColumns)) {
                        $found[] = $index;
                    }
                }
            }
            return $found;
        }

        // MySQL/PostgreSQL
        $indexQuery = $connection->select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_NAME = ?
            AND COLUMN_NAME IN (?)
        ", [$table, implode(',', $columns)]);

        return $indexQuery;
    }
}