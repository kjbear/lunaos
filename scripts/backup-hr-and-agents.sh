#!/bin/bash

# Backup HR Personas and Agents Tables
# This script exports both tables to JSON for rollback capability
# Run BEFORE migration to ensure zero data loss

set -e  # Exit on error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_ROOT/database/backups"
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== HR & Agents Backup Script ===${NC}"
echo "Timestamp: $TIMESTAMP"
echo "Backup Directory: $BACKUP_DIR"
echo ""

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Change to project root
cd "$PROJECT_ROOT"

# Function to backup a table
backup_table() {
    local table=$1
    local output_file="$BACKUP_DIR/${table}-${TIMESTAMP}.json"
    
    echo -e "${YELLOW}Backing up table: ${table}...${NC}"
    
    # Use Laravel artisan tinker to export table (with error handling)
    export_result=$(php artisan tinker --execute="
        try {
            echo 'Exporting ${table} table...' . PHP_EOL;
            \$data = DB::table('${table}')->get()->toArray();
            file_put_contents('${output_file}', json_encode(\$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo 'SUCCESS:' . count(\$data);
        } catch (\Exception \$e) {
            echo 'ERROR:' . \$e->getMessage();
        }
    " 2>&1)
    
    echo "$export_result" | grep -v "^\[" | grep -v "^$"
    
    # Check if export was successful
    if [[ "$export_result" == *"SUCCESS:"* ]]; then
        record_count=$(echo "$export_result" | grep "SUCCESS:" | cut -d':' -f2)
        if [ -f "$output_file" ]; then
            local size=$(du -h "$output_file" | cut -f1)
            echo -e "${GREEN}✓ Backup created: ${output_file} (${size}) - ${record_count} records${NC}"
            
            # Verify JSON is valid
            if jq empty "$output_file" 2>/dev/null; then
                echo -e "${GREEN}✓ JSON validation passed${NC}"
            else
                echo -e "${RED}✗ JSON validation failed${NC}"
                return 1
            fi
        else
            echo -e "${RED}✗ Backup file not created: ${output_file}${NC}"
            return 1
        fi
    elif [[ "$export_result" == *"no such table"* ]]; then
        echo -e "${YELLOW}⚠ Table ${table} does not exist, skipping...${NC}"
        return 0
    else
        echo -e "${RED}✗ Backup failed for table: ${table}${NC}"
        return 1
    fi
    
    echo ""
}
    
    if [ -f "$output_file" ]; then
        local size=$(du -h "$output_file" | cut -f1)
        echo -e "${GREEN}✓ Backup created: ${output_file} (${size})${NC}"
        
        # Verify JSON is valid
        if jq empty "$output_file" 2>/dev/null; then
            echo -e "${GREEN}✓ JSON validation passed${NC}"
        else
            echo -e "${RED}✗ JSON validation failed${NC}"
            return 1
        fi
    else
        echo -e "${RED}✗ Backup failed for table: ${table}${NC}"
        return 1
    fi
    
    echo ""
}

# Backup Personas table and related tables
echo -e "${YELLOW}=== Backing up HR Personas tables ===${NC}"
backup_table "personas"
backup_table "persona_metrics"
backup_table "persona_workspaces"

# Backup Agents table and related tables
echo -e "${YELLOW}=== Backing up Agents tables ===${NC}"
backup_table "agents"
backup_table "agent_updates"
backup_table "agent_conversations"
backup_table "agent_activities"

# Create a manifest file
manifest_file="$BACKUP_DIR/backup-manifest-${TIMESTAMP}.json"
echo -e "${YELLOW}Creating backup manifest...${NC}"

cat > "$manifest_file" <<EOF
{
    "timestamp": "$TIMESTAMP",
    "created_at": "$(date -Iseconds)",
    "backup_type": "pre-migration",
    "tables": [
        "personas",
        "persona_metrics",
        "persona_workspaces",
        "agents",
        "agent_updates",
        "agent_conversations",
        "agent_activities"
    ],
    "record_counts": {
EOF

# Add record counts to manifest
php artisan tinker --execute="
\$counts = [
    'personas' => DB::table('personas')->count(),
    'persona_metrics' => DB::table('persona_metrics')->count(),
    'persona_workspaces' => DB::table('persona_workspaces')->count(),
    'agents' => DB::table('agents')->count(),
    'agent_updates' => DB::table('agent_updates')->count(),
    'agent_conversations' => DB::table('agent_conversations')->count(),
    'agent_activities' => DB::table('agent_activities')->count(),
];
file_put_contents('$manifest_file.tmp', json_encode(\$counts, JSON_PRETTY_PRINT));
"

# Append counts to manifest
cat "$manifest_file.tmp" >> "$manifest_file"
echo -e "    }," >> "$manifest_file"
echo -e "    \"files\": [" >> "$manifest_file"

# Add file list to manifest
for table in personas persona_metrics persona_workspaces agents agent_updates agent_conversations agent_activities; do
    file="$BACKUP_DIR/${table}-${TIMESTAMP}.json"
    if [ -f "$file" ]; then
        echo -e "        {\"table\": \"${table}\", \"file\": \"${table}-${TIMESTAMP}.json\"}," >> "$manifest_file"
    fi
done

# Remove trailing comma and close JSON
sed -i.bak '$ s/,$//' "$manifest_file"
echo -e "    ]" >> "$manifest_file"
echo -e "}" >> "$manifest_file"
rm -f "$manifest_file.bak" "$manifest_file.tmp"

echo -e "${GREEN}✓ Manifest created: ${manifest_file}${NC}"
echo ""

# Create a checksums file for integrity verification
checksums_file="$BACKUP_DIR/backup-checksums-${TIMESTAMP}.txt"
echo -e "${YELLOW}Generating checksums...${NC}"
cd "$BACKUP_DIR"
shasum -a 256 *-${TIMESTAMP}.json > "$checksums_file"
cd "$PROJECT_ROOT"
echo -e "${GREEN}✓ Checksums created: ${checksums_file}${NC}"
echo ""

# Summary
echo -e "${GREEN}=== Backup Summary ===${NC}"
echo -e "Backup completed successfully at $(date)"
echo -e "Total files created: $(ls -1 $BACKUP_DIR/*-${TIMESTAMP}.* | wc -l | tr -d ' ')"
echo -e "Backup location: $BACKUP_DIR"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Verify backup files are present and valid"
echo "2. Store backup in safe location (optional: copy to external storage)"
echo "3. Proceed with migration only after backup verification"
echo ""
echo -e "${GREEN}✓ Backup complete! You can now proceed with migration.${NC}"
