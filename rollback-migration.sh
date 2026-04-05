#!/bin/bash

###############################################################################
# Script Rollback Migrasi Database
# 
# Script ini akan melakukan rollback database ke kondisi sebelum migrasi
#
# Usage: bash rollback-migration.sh [backup_file.sql]
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

confirm() {
    read -p "$1 (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        return 1
    fi
    return 0
}

###############################################################################
# Main
###############################################################################

print_header "Database Rollback"

# Check if backup file is provided
if [ -z "$1" ]; then
    print_error "Backup file not specified!"
    echo ""
    echo "Usage: bash rollback-migration.sh [backup_file.sql]"
    echo ""
    echo "Available backups:"
    ls -lh backups/*.sql 2>/dev/null || echo "  No backups found"
    exit 1
fi

BACKUP_FILE=$1

# Check if backup file exists
if [ ! -f ${BACKUP_FILE} ]; then
    print_error "Backup file not found: ${BACKUP_FILE}"
    exit 1
fi

print_success "Backup file found: ${BACKUP_FILE}"
BACKUP_SIZE=$(du -h ${BACKUP_FILE} | cut -f1)
print_info "Backup size: ${BACKUP_SIZE}"

echo ""
print_warning "⚠️  WARNING: This will RESTORE the database to the backup state!"
print_warning "⚠️  All changes after the backup will be LOST!"
echo ""

if ! confirm "Are you sure you want to rollback?"; then
    print_info "Rollback cancelled"
    exit 0
fi

echo ""

###############################################################################
# Step 1: Set Maintenance Mode
###############################################################################

print_header "Step 1: Maintenance Mode"

print_info "Enabling maintenance mode..."
php artisan down --message="Database rollback in progress" --retry=60
print_success "Maintenance mode enabled"

echo ""

###############################################################################
# Step 2: Backup Current State (Safety)
###############################################################################

print_header "Step 2: Safety Backup"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
SAFETY_BACKUP="backups/before_rollback_${TIMESTAMP}.sql"

print_info "Creating safety backup of current state..."

# Get database credentials
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

if command -v mysqldump &> /dev/null; then
    mysqldump -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > ${SAFETY_BACKUP}
    print_success "Safety backup created: ${SAFETY_BACKUP}"
else
    print_warning "mysqldump not found. Skipping safety backup."
fi

echo ""

###############################################################################
# Step 3: Drop Database
###############################################################################

print_header "Step 3: Drop Current Database"

print_warning "Dropping current database..."

mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} -e "DROP DATABASE IF EXISTS ${DB_DATABASE};"
print_success "Database dropped"

print_info "Creating fresh database..."
mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} -e "CREATE DATABASE ${DB_DATABASE} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
print_success "Database created"

echo ""

###############################################################################
# Step 4: Restore Backup
###############################################################################

print_header "Step 4: Restore Backup"

print_info "Restoring database from backup..."
mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} < ${BACKUP_FILE}
print_success "Database restored"

echo ""

###############################################################################
# Step 5: Verify Restoration
###############################################################################

print_header "Step 5: Verify Restoration"

print_info "Checking table count..."
TABLE_COUNT=$(mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} -e "SHOW TABLES;" | wc -l)
print_success "Tables found: $((TABLE_COUNT - 1))"

print_info "Checking data..."
USER_COUNT=$(mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} -e "SELECT COUNT(*) FROM users;" | tail -n 1)
print_success "Users: ${USER_COUNT}"

PRODUCT_COUNT=$(mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} -e "SELECT COUNT(*) FROM products;" | tail -n 1)
print_success "Products: ${PRODUCT_COUNT}"

echo ""

###############################################################################
# Step 6: Clear Cache
###############################################################################

print_header "Step 6: Clear Cache"

print_info "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Cache cleared"

echo ""

###############################################################################
# Step 7: Disable Maintenance Mode
###############################################################################

print_header "Step 7: Disable Maintenance Mode"

print_info "Disabling maintenance mode..."
php artisan up
print_success "Application is now live"

echo ""

###############################################################################
# Summary
###############################################################################

print_header "Rollback Summary"

echo "✅ Rollback completed successfully!"
echo ""
echo "Database restored from: ${BACKUP_FILE}"
echo "Safety backup saved to: ${SAFETY_BACKUP}"
echo ""
echo "Next steps:"
echo "  1. Test the application"
echo "  2. Verify data integrity"
echo "  3. Check error logs"
echo ""

print_success "🎉 Rollback complete!"
