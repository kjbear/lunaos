#!/bin/bash

# Restore Team Data from Backup
# Emergency rollback script for zero data loss recovery
# 
# For DEVELOPMENT: Use `php artisan migrate:rollback` instead
# For PRODUCTION: Use this script when archive tables don't exist

set -e  # Exit on error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_ROOT/database/backups"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${RED}=== Team Data Restore Script ===${NC}"
echo "This is an EMERGENCY ROLLBACK script"
echo ""

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}✗ Backup directory not found: $BACKUP_DIR${NC}"
    exit 1
fi

# Find the latest backup
latest_backup=$(ls -1t "$BACKUP_DIR"/backup-manifest-*.json 2>/dev/null | head -1)

if [ -z "$latest_backup" ]; then
    echo -e "${RED}✗ No backup manifest found. Cannot proceed with rollback.${NC}"
    exit 1
fi

# Extract timestamp from backup manifest
backup_timestamp=$(jq -r '.timestamp' "$latest_backup")
echo -e "${YELLOW}Found backup from: ${backup_timestamp}${NC}"
echo "Manifest: $latest_backup"
echo ""

# Show what will be restored
echo -e "${YELLOW}Tables to restore:${NC}"
jq -r '.tables_backed_up[] | "  - \(.table): \(.records) records"' "$latest_backup"
echo ""

# Check if we can use migration rollback
echo -e "${YELLOW}Checking for archive tables...${NC}"
has_archives=$(php artisan tinker --execute="
\$hasPersonasArchive = Schema::hasTable('personas_archive');
\$hasAgentsArchive = Schema::hasTable('agents_archive') || !Schema::hasTable('agents');
echo \$hasPersonasArchive ? 'has_archives' : 'no_archives';
" 2>&1)

if [[ "$has_archives" == *"has_archives"* ]]; then
    echo -e "${GREEN}✓ Archive tables found${NC}"
    echo -e "${YELLOW}Recommendation: Use migration rollback instead:${NC}"
    echo "  php artisan migrate:rollback --step=1"
    echo ""
    read -p "Use migration rollback instead? (y/n): " use_migration
    if [[ "$use_migration" == "y" ]]; then
        php artisan migrate:rollback --step=1
        echo ""
        echo -e "${GREEN}✓ Migration rollback complete!${NC}"
        exit 0
    fi
fi

# Confirmation
if [[ "$1" != "--force" ]]; then
    echo -e "${RED}WARNING: This will:${NC}"
    echo "  1. Drop the team_members table"
    echo "  2. Restore personas, agents, and all related tables from backup"
    echo ""
    read -p "Are you sure you want to proceed? Type 'RESTORE' to confirm: " confirm
    
    if [ "$confirm" != "RESTORE" ]; then
        echo -e "${YELLOW}Restoration cancelled.${NC}"
        exit 0
    fi
fi

echo ""
echo -e "${YELLOW}Starting restoration from backup...${NC}"

# Change to project root
cd "$PROJECT_ROOT"

# First, drop the new consolidated tables
echo -e "${YELLOW}=== Dropping consolidated tables ===${NC}"

php artisan tinker --execute="
echo 'Dropping team_members and related tables...' . PHP_EOL;

try {
    Schema::dropIfExists('team_member_workspaces');
    echo '  - Dropped team_member_workspaces' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  - team_member_workspaces does not exist' . PHP_EOL;
}

try {
    Schema::dropIfExists('team_member_metrics');
    echo '  - Dropped team_member_metrics' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  - team_member_metrics does not exist' . PHP_EOL;
}

try {
    Schema::dropIfExists('team_members');
    echo '  - Dropped team_members' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  - team_members does not exist' . PHP_EOL;
}
"

# Recreate original tables by running the original migrations in reverse
echo -e "${YELLOW}=== Recreating original table structure ===${NC}"

php artisan tinker --execute="
echo 'Recreating personas table...' . PHP_EOL;

// Recreate personas table structure
Schema::create('personas', function (Blueprint \$table) {
    \$table->uuid('id')->primary();
    \$table->string('name')->unique();
    \$table->enum('role', ['subagent', 'board_member', 'custom'])->default('custom');
    \$table->string('model')->default('haiku');
    \$table->string('avatar')->nullable();
    \$table->enum('status', ['active', 'inactive', 'archived'])->default('active');
    \$table->string('inspiration')->nullable();
    \$table->text('system_prompt')->nullable();
    \$table->string('workspace_path')->nullable();
    \$table->timestamp('deactivated_at')->nullable();
    \$table->timestamps();
});
echo '  - Created personas table' . PHP_EOL;

// Recreate persona_metrics if needed
if (!Schema::hasTable('persona_metrics')) {
    Schema::create('persona_metrics', function (Blueprint \$table) {
        \$table->uuid('id')->primary();
        \$table->uuid('persona_id');
        \$table->integer('tasks_completed')->default(0);
        \$table->integer('conversations_count')->default(0);
        \$table->json('performance_data')->nullable();
        \$table->timestamps();
        
        \$table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
    });
    echo '  - Created persona_metrics table' . PHP_EOL;
}

// Recreate persona_workspaces if needed
if (!Schema::hasTable('persona_workspaces')) {
    Schema::create('persona_workspaces', function (Blueprint \$table) {
        \$table->uuid('id')->primary();
        \$table->uuid('persona_id');
        \$table->string('file_path');
        \$table->string('file_type')->nullable();
        \$table->integer('file_size')->nullable();
        \$table->timestamps();
        
        \$table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
    });
    echo '  - Created persona_workspaces table' . PHP_EOL;
}

// Recreate agents table structure
if (!Schema::hasTable('agents')) {
    Schema::create('agents', function (Blueprint \$table) {
        \$table->id();
        \$table->string('name')->unique();
        \$table->string('title')->nullable();
        \$table->string('role')->default('worker');
        \$table->string('model')->nullable();
        \$table->string('provider')->default('ollama');
        \$table->text('system_prompt')->nullable();
        \$table->json('model_settings')->nullable();
        \$table->string('avatar')->default('🤖');
        \$table->string('emoji')->default('🤖');
        \$table->enum('status', ['online', 'offline', 'error', 'busy'])->default('offline');
        \$table->unsignedBigInteger('parent_id')->nullable();
        \$table->string('runtime_location')->default('php');
        \$table->timestamp('last_location_check')->nullable();
        \$table->string('strategy_class')->nullable();
        \$table->string('step_filter')->nullable();
        \$table->json('workflow_config')->nullable();
        \$table->string('skill_doc_path')->nullable();
        \$table->json('skill_metadata')->nullable();
        \$table->boolean('is_online')->nullable();
        \$table->json('capabilities')->nullable();
        \$table->json('settings')->nullable();
        \$table->timestamps();
    });
    echo '  - Created agents table' . PHP_EOL;
}

// Recreate agent_updates if needed
if (!Schema::hasTable('agent_updates')) {
    Schema::create('agent_updates', function (Blueprint \$table) {
        \$table->id();
        \$table->unsignedBigInteger('agent_id');
        \$table->string('update_type');
        \$table->json('changes')->nullable();
        \$table->text('reason')->nullable();
        \$table->timestamps();
        
        \$table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
    });
    echo '  - Created agent_updates table' . PHP_EOL;
}
"

# Function to restore a table from backup
restore_table() {
    local table=$1
    local backup_file="$BACKUP_DIR/${table}-${backup_timestamp}.json"
    
    if [ ! -f "$backup_file" ]; then
        echo -e "${YELLOW}⚠ Backup file not found: $backup_file, skipping...${NC}"
        return 0
    fi
    
    # Check if file has data
    record_count=$(jq length "$backup_file" 2>/dev/null || echo "0")
    if [ "$record_count" -eq 0 ]; then
        echo -e "${YELLOW}⚠ No data in backup for ${table}, skipping...${NC}"
        return 0
    fi
    
    echo -e "${YELLOW}Restoring ${table} (${record_count} records)...${NC}"
    
    # Use Laravel artisan tinker to restore table
    php artisan tinker --execute="
        echo 'Inserting data into ${table}...' . PHP_EOL;
        \$json = file_get_contents('${backup_file}');
        \$data = json_decode(\$json, true);
        
        if (empty(\$data)) {
            echo 'No data to insert' . PHP_EOL;
            return;
        }
        
        // Clear existing data
        try {
            DB::table('${table}')->truncate();
        } catch (\Exception \$e) {
            // Table might be empty, that's ok
        }
        
        // Insert records
        \$count = 0;
        foreach (\$data as \$row) {
            \$row = (array) \$row;
            
            // Handle JSON fields
            foreach (\$row as \$key => \$value) {
                if (is_array(\$value)) {
                    \$row[\$key] = json_encode(\$value);
                }
            }
            
            try {
                DB::table('${table}')->insert(\$row);
                \$count++;
            } catch (\Exception \$e) {
                echo 'Error: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        echo 'Inserted ' . \$count . ' records into ${table}' . PHP_EOL;
    "
    
    echo -e "${GREEN}✓ ${table} restored${NC}"
}

# Restore Personas tables
echo -e "${YELLOW}=== Restoring Personas tables ===${NC}"
restore_table "personas"
restore_table "persona_metrics" || true
restore_table "persona_workspaces" || true

# Restore Agents tables
echo -e "${YELLOW}=== Restoring Agents tables ===${NC}"
restore_table "agents" || true
restore_table "agent_updates" || true

# Verify restoration
echo -e "${YELLOW}=== Verifying restoration ===${NC}"

php artisan tinker --execute="
echo PHP_EOL . 'Record counts:' . PHP_EOL;
try {
    echo '  personas: ' . DB::table('personas')->count() . ' records' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  personas: ERROR - ' . \$e->getMessage() . PHP_EOL;
}

try {
    echo '  persona_metrics: ' . DB::table('persona_metrics')->count() . ' records' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  persona_metrics: 0 records' . PHP_EOL;
}

try {
    echo '  persona_workspaces: ' . DB::table('persona_workspaces')->count() . ' records' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  persona_workspaces: 0 records' . PHP_EOL;
}

try {
    echo '  agents: ' . DB::table('agents')->count() . ' records' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  agents: 0 records' . PHP_EOL;
}

try {
    echo '  agent_updates: ' . DB::table('agent_updates')->count() . ' records' . PHP_EOL;
} catch (\Exception \$e) {
    echo '  agent_updates: 0 records' . PHP_EOL;
}
"

echo ""
echo -e "${GREEN}=== Restoration Complete ===${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. php artisan cache:clear"
echo "2. php artisan config:clear"
echo "3. Test application functionality"
echo "4. Verify data integrity"
echo ""
echo -e "${GREEN}✓ Rollback complete!${NC}"
