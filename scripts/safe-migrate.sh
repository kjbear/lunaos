#!/bin/bash

# LunaOS Pre-Migration Safety Check
# Usage: ./scripts/safe-migrate.sh [--check-only]
#
# This script ensures it's safe to run migrations by:
# 1. Verifying you're in the correct environment
# 2. Checking for existing data
# 3. Ensuring recent backup exists
# 4. Requiring explicit confirmation

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_ROOT/database/backups"
MAX_BACKUP_AGE_HOURS=24

echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     LunaOS Pre-Migration Safety Check                  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check 1: Verify environment
echo -e "${YELLOW}✓ Checking environment...${NC}"
cd "$PROJECT_ROOT"

if [ -f ".env" ]; then
    APP_ENV=$(grep "^APP_ENV=" .env | cut -d'=' -f2)
    APP_ENV=${APP_ENV//\"/}  # Remove quotes if present
    echo "  Environment: $APP_ENV"
    
    if [ "$APP_ENV" == "production" ]; then
        echo -e "${RED}✗ Production environment detected!${NC}"
        echo -e "${YELLOW}  Recommended: Use staging environment for migration testing${NC}"
        echo "  Staging location: ../lunaos-staging/"
        read -p "  Continue anyway? (yes/no): " confirm
        if [ "$confirm" != "yes" ]; then
            echo -e "${GREEN}Aborted. Smart choice!${NC}"
            exit 0
        fi
    fi
else
    echo -e "${RED}✗ .env file not found!${NC}"
    exit 1
fi

# Check 2: Verify database exists
echo -e "${YELLOW}✓ Checking database...${NC}"
if [ -f ".env" ]; then
    DB_PATH=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
    DB_PATH=${DB_PATH//\"/}
    
    if [ -f "$DB_PATH" ]; then
        echo "  Database: $DB_PATH ($(du -h "$DB_PATH" | cut -f1))"
    else
        echo -e "${GREEN}  No database file found (fresh install)${NC}"
    fi
fi

# Check 3: Check for existing data
echo -e "${YELLOW}✓ Checking for existing data...${NC}"

if [ -f "$DB_PATH" ]; then
    # Use sqlite3 to count records if it's SQLite
    if [[ "$DB_PATH" == *.sqlite ]]; then
        if command -v sqlite3 &> /dev/null; then
            MEMBER_COUNT=$(sqlite3 "$DB_PATH" "SELECT COUNT(*) FROM team_members;" 2>/dev/null || echo "0")
            echo "  Team members: $MEMBER_COUNT"
            
            if [ "$MEMBER_COUNT" -gt 0 ]; then
                echo -e "${YELLOW}  ⚠ Data exists in database${NC}"
                DATA_EXISTS=true
            else
                echo -e "${GREEN}  ✓ Database is empty (safe to migrate)${NC}"
                DATA_EXISTS=false
            fi
        else
            echo -e "${YELLOW}  ⚠ sqlite3 not installed, skipping data check${NC}"
            DATA_EXISTS="unknown"
        fi
    else
        echo -e "${YELLOW}  Non-SQLite database, skipping count check${NC}"
        DATA_EXISTS="unknown"
    fi
else
    echo -e "${GREEN}  ✓ No database file (safe to migrate)${NC}"
    DATA_EXISTS=false
fi

# Check 4: Verify backup exists if data exists
if [ "$DATA_EXISTS" == "true" ]; then
    echo -e "${YELLOW}✓ Checking for recent backup...${NC}"
    
    if [ ! -d "$BACKUP_DIR" ]; then
        echo -e "${RED}✗ Backup directory not found: $BACKUP_DIR${NC}"
        echo -e "${YELLOW}  Run backup first: ./scripts/backup-team-data.sh${NC}"
        exit 1
    fi
    
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/backup-manifest-*.json 2>/dev/null | head -n1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        echo -e "${RED}✗ No backup manifest found!${NC}"
        echo -e "${YELLOW}  Run backup first: ./scripts/backup-team-data.sh${NC}"
        exit 1
    fi
    
    BACKUP_AGE_SECONDS=$(( $(date +%s) - $(stat -f%m "$LATEST_BACKUP" 2>/dev/null || stat -c%Y "$LATEST_BACKUP" 2>/dev/null) ))
    BACKUP_AGE_HOURS=$((BACKUP_AGE_SECONDS / 3600))
    
    echo "  Latest backup: $(basename "$LATEST_BACKUP")"
    echo "  Backup age: ${BACKUP_AGE_HOURS}h ago"
    
    if [ "$BACKUP_AGE_HOURS" -gt "$MAX_BACKUP_AGE_HOURS" ]; then
        echo -e "${RED}✗ Backup is older than ${MAX_BACKUP_AGE_HOURS} hours!${NC}"
        echo -e "${YELLOW}  Run fresh backup: ./scripts/backup-team-data.sh${NC}"
        exit 1
    else
        echo -e "${GREEN}  ✓ Recent backup found${NC}"
    fi
fi

# Check 5: Require explicit confirmation if data exists
if [ "$DATA_EXISTS" == "true" ] && [ "--check-only" != "$1" ]; then
    echo ""
    echo -e "${YELLOW}╔════════════════════════════════════════════════════════╗${NC}"
    echo -e "${YELLOW}║  WARNING: Database contains data                       ║${NC}"
    echo -e "${YELLOW}╚════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Before running migrations, ensure you have:"
    echo "  ✓ Recent backup (verified above)"
    echo "  ✓ Tested migration on staging (recommended)"
    echo "  ✓ Read the migration runbook: docs/MIGRATION_RUNBOOK.md"
    echo "  ✓ Reviewed rollback procedure: docs/ROLLBACK_RUNBOOK.md"
    echo ""
    read -p "Proceed with migration? (type 'yes' to confirm): " confirm
    
    if [ "$confirm" != "yes" ]; then
        echo -e "${GREEN}Migration aborted. Your data is safe.${NC}"
        exit 0
    fi
    
    # Double confirmation for production
    if [ "$APP_ENV" == "production" ]; then
        read -p "⚠ PRODUCTION ENVIRONMENT ⚠ Are you ABSOLUTELY sure? (type 'I accept the risk'): " final_confirm
        if [ "$final_confirm" != "I accept the risk" ]; then
            echo -e "${GREEN}Aborted. Wise decision.${NC}"
            exit 0
        fi
    fi
fi

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✓ All safety checks passed                            ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
echo ""

if [ "--check-only" == "$1" ]; then
    echo -e "${GREEN}Ready to migrate. Run: php artisan migrate${NC}"
    exit 0
fi

# Run migration
echo -e "${BLUE}Running migrations...${NC}"
php artisan migrate

echo ""
echo -e "${GREEN}✓ Migration completed successfully!${NC}"
echo ""
echo "Next steps:"
echo "  1. Verify data integrity: php artisan tinker"
echo "  2. Check application: open http://lunaos.test"
echo "  3. Run tests: php artisan test"
