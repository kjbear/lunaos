#!/bin/bash

# Backup Team Data (Personas + Agents)
# This script exports tables to JSON for rollback capability
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

echo -e "${GREEN}=== Team Data Backup Script ===${NC}"
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
            echo 'Exporting ${table}...' . PHP_EOL;
            \$data = DB::table('${table}')->get()->toArray();
            file_put_contents('${output_file}', json_encode(\$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo 'SUCCESS:' . count(\$data);
        } catch (\Exception \$e) {
            echo 'ERROR:' . \$e->getMessage();
        }
    " 2>&1)
    
    # Show output (filter Laravel debug info)
    echo "$export_result" | grep -E "(Exporting|SUCCESS|ERROR)" || true
    
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
    elif [[ "$export_result" == *"no such table"* ]] || [[ "$export_result" == *"doesn't exist"* ]]; then
        echo -e "${YELLOW}⚠ Table ${table} does not exist, skipping...${NC}"
        return 0
    else
        echo -e "${RED}✗ Backup failed for table: ${table}${NC}"
        echo "$export_result"
        return 1
    fi
    
    echo ""
}

# Backup Personas table and related tables
echo -e "${YELLOW}=== Backing up HR Personas tables ===${NC}"
backup_table "personas" || true
backup_table "persona_metrics" || true
backup_table "persona_workspaces" || true

# Backup Agents table and related tables
echo -e "${YELLOW}=== Backing up Agents tables ===${NC}"
backup_table "agents" || true
backup_table "agent_updates" || true
backup_table "agent_conversations" || true
backup_table "agent_activities" || true

# Create a manifest file
manifest_file="$BACKUP_DIR/backup-manifest-${TIMESTAMP}.json"
echo -e "${YELLOW}Creating backup manifest...${NC}"

cat > "$manifest_file" <<EOF
{
    "timestamp": "$TIMESTAMP",
    "created_at": "$(date -Iseconds)",
    "backup_type": "pre-migration",
    "tables_backed_up": [
EOF

# Add successfully backed up tables to manifest
first=true
for table in personas persona_metrics persona_workspaces agents agent_updates agent_conversations agent_activities; do
    file="$BACKUP_DIR/${table}-${TIMESTAMP}.json"
    if [ -f "$file" ]; then
        if [ "$first" = true ]; then
            first=false
        else
            echo "," >> "$manifest_file"
        fi
        count=$(jq length "$file" 2>/dev/null || echo "0")
        printf '        {"table": "%s", "file": "%s-%s.json", "records": %s}' "$table" "$table" "$TIMESTAMP" "$count" >> "$manifest_file"
    fi
done

echo "" >> "$manifest_file"
echo "    ]" >> "$manifest_file"
echo "}" >> "$manifest_file"

echo -e "${GREEN}✓ Manifest created: ${manifest_file}${NC}"
echo ""

# Create checksums file for integrity verification
checksums_file="$BACKUP_DIR/backup-checksums-${TIMESTAMP}.txt"
echo -e "${YELLOW}Generating checksums...${NC}"
cd "$BACKUP_DIR"
shasum -a 256 *-${TIMESTAMP}.json > "$checksums_file" 2>/dev/null || true
cd "$PROJECT_ROOT"
echo -e "${GREEN}✓ Checksums created: ${checksums_file}${NC}"
echo ""

# Summary
echo -e "${GREEN}=== Backup Summary ===${NC}"
echo -e "Backup completed at $(date)"
echo -e "Files created: $(ls -1 $BACKUP_DIR/*-${TIMESTAMP}.* 2>/dev/null | wc -l | tr -d ' ')"
echo -e "Backup location: $BACKUP_DIR"
echo ""
ls -lh "$BACKUP_DIR"/*-${TIMESTAMP}.* 2>/dev/null || echo "No backup files created"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Verify backup files are present and valid"
echo "2. Store backup in safe location (optional: copy to external storage)"
echo "3. Proceed with migration only after backup verification"
echo ""
echo -e "${GREEN}✓ Backup complete!${NC}"
