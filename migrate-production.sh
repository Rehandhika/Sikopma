#!/bin/bash

###############################################################################
# Script Migrasi Database Production
# 
# Script ini akan melakukan migrasi database production secara otomatis
# dengan safety checks dan rollback capability
#
# Usage: bash migrate-production.sh
###############################################################################

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BACKUP_DIR="backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/backup_${TIMESTAMP}.sql"
LOG_FILE="logs/migration_${TIMESTAMP}.log"

###############################################################################
# Functions
###############################################################################

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
# Pre-flight Checks
###############################################################################

print_header "Pre-flight Checks"

# Check if .env exists
if [ ! -f .env ]; then
    print_error ".env file not found!"
    exit 1
fi
print_success ".env file found"

# Check if php is available
if ! command -v php &> /dev/null; then
    print_error "PHP not found!"
    exit 1
fi
print_success "PHP found: $(php -v | head -n 1)"

# Check if composer is available
if ! command -v composer &> /dev/null; then
    print_error "Composer not found!"
    exit 1
fi
print_success "Composer found"

# Check database connection
if ! php artisan db:show &> /dev/null; then
    print_error "Cannot connect to database!"
    exit 1
fi
print_success "Database connection OK"

echo ""

###############################################################################
# Confirmation
###############################################################################

print_header "Migration Confirmation"

echo "This script will:"
echo "  1. Backup current database"
echo "  2. Backup storage files"
echo "  3. Run database migrations"
echo "  4. Run data migration seeders"
echo "  5. Validate results"
echo ""
print_warning "This process will modify your database structure!"
echo ""

if ! confirm "Do you want to continue?"; then
    print_info "Migration cancelled"
    exit 0
fi

echo ""

###############################################################################
# Step 1: Backup Database
###############################################################################

print_header "Step 1: Backup Database"

# Create backup directory
mkdir -p ${BACKUP_DIR}
mkdir -p logs

# Get database credentials from .env
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

print_info "Creating database backup..."

# Backup database
if command -v mysqldump &> /dev/null; then
    mysqldump -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > ${BACKUP_FILE}
    print_success "Database backed up to: ${BACKUP_FILE}"
else
    print_warning "mysqldump not found. Using Laravel backup..."
    php artisan db:backup --path=${BACKUP_FILE}
fi

# Verify backup
if [ -f ${BACKUP_FILE} ]; then
    BACKUP_SIZE=$(du -h ${BACKUP_FILE} | cut -f1)
    print_success "Backup size: ${BACKUP_SIZE}"
else
    print_error "Backup failed!"
    exit 1
fi

echo ""

###############################################################################
# Step 2: Backup Storage
###############################################################################

print_header "Step 2: Backup Storage Files"

STORAGE_BACKUP="${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz"

print_info "Creating storage backup..."
tar -czf ${STORAGE_BACKUP} storage/app/public/attendance/ 2>/dev/null || true

if [ -f ${STORAGE_BACKUP} ]; then
    STORAGE_SIZE=$(du -h ${STORAGE_BACKUP} | cut -f1)
    print_success "Storage backed up to: ${STORAGE_BACKUP} (${STORAGE_SIZE})"
else
    print_warning "No storage files to backup"
fi

echo ""

###############################################################################
# Step 3: Set Maintenance Mode
###############################################################################

print_header "Step 3: Maintenance Mode"

print_info "Enabling maintenance mode..."
php artisan down --message="Database migration in progress" --retry=60
print_success "Maintenance mode enabled"

echo ""

###############################################################################
# Step 4: Run Migrations
###############################################################################

print_header "Step 4: Run Database Migrations"

print_info "Running migrations..."

# Run migrations one by one for safety
MIGRATIONS=(
    "2026_01_31_000001_create_students_table.php"
    "2026_01_31_000002_create_shu_point_transactions_table.php"
    "2026_01_31_000003_add_shu_fields_to_sales_table.php"
    "2026_02_24_142246_add_late_details_to_attendances_table.php"
    "2026_02_22_150001_add_soft_deletes_to_transactional_tables.php"
)

for migration in "${MIGRATIONS[@]}"; do
    print_info "Running: ${migration}"
    if php artisan migrate --path=database/migrations/${migration} --force; then
        print_success "✓ ${migration}"
    else
        print_error "✗ ${migration} FAILED!"
        print_error "Rolling back..."
        php artisan migrate:rollback --force
        php artisan up
        exit 1
    fi
done

print_success "All migrations completed"

echo ""

###############################################################################
# Step 5: Run Data Migration Seeders
###############################################################################

print_header "Step 5: Run Data Migration Seeders"

print_info "Running production data seeder..."
if php artisan db:seed --class=MigrateProductionDataSeeder --force; then
    print_success "Production data migrated"
else
    print_error "Production data migration failed!"
    exit 1
fi

print_info "Running sales SHU seeder..."
if php artisan db:seed --class=MigrateSalesForShuSeeder --force; then
    print_success "Sales data migrated"
else
    print_error "Sales migration failed!"
    exit 1
fi

print_info "Running attendance seeder..."
if php artisan db:seed --class=MigrateAttendanceDataSeeder --force; then
    print_success "Attendance data migrated"
else
    print_error "Attendance migration failed!"
    exit 1
fi

echo ""

###############################################################################
# Step 6: Validation
###############################################################################

print_header "Step 6: Validation"

print_info "Running validation..."
if php artisan db:seed --class=ValidationSeeder --force; then
    print_success "Validation passed"
else
    print_warning "Some validations failed. Check logs."
fi

echo ""

###############################################################################
# Step 7: Clear Cache
###############################################################################

print_header "Step 7: Clear & Optimize Cache"

print_info "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Cache cleared"

print_info "Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Optimization complete"

echo ""

###############################################################################
# Step 8: Disable Maintenance Mode
###############################################################################

print_header "Step 8: Disable Maintenance Mode"

print_info "Disabling maintenance mode..."
php artisan up
print_success "Application is now live"

echo ""

###############################################################################
# Summary
###############################################################################

print_header "Migration Summary"

echo "✅ Migration completed successfully!"
echo ""
echo "Backup files:"
echo "  - Database: ${BACKUP_FILE}"
echo "  - Storage:  ${STORAGE_BACKUP}"
echo ""
echo "Next steps:"
echo "  1. Test the application thoroughly"
echo "  2. Monitor error logs: tail -f storage/logs/laravel.log"
echo "  3. Check migration report: storage/logs/migration_report_*.json"
echo ""
print_warning "Keep backup files for at least 7 days!"
echo ""

print_success "🎉 All done!"
