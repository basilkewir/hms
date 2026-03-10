#!/bin/bash

# HMS Database Restore Script
# Usage: sudo bash restore.sh [backup_file]
# Example: sudo bash restore.sh /root/hms_backups/hms_db_auto_20260310_143022.sql

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
INSTALL_DIR="/opt/hms"
BACKUP_DIR="/root/hms_backups"

# Helper functions
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    error "This script must be run as root. Use: sudo bash restore.sh"
fi

# Check if HMS is installed
if [ ! -d "$INSTALL_DIR" ]; then
    error "HMS is not installed at $INSTALL_DIR"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║        HMS Database Restore                                ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# If backup file not provided, list available backups
BACKUP_FILE="${1}"

if [ -z "$BACKUP_FILE" ]; then
    info "No backup file specified. Available backups:"
    echo ""
    ls -lh "$BACKUP_DIR"/*.sql 2>/dev/null | awk '{print "  " $9 " (" $5 ")"}'
    echo ""
    error "Please specify a backup file: sudo bash restore.sh /path/to/backup.sql"
fi

# Verify backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    error "Backup file not found: $BACKUP_FILE"
fi

# Get database credentials from .env
DB_HOST=$(grep "^DB_HOST=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
DB_USER=$(grep "^DB_USERNAME=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
DB_PASS=$(grep "^DB_PASSWORD=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
DB_NAME=$(grep "^DB_DATABASE=" "$INSTALL_DIR/.env" | cut -d'=' -f2)

# Show what will happen
echo "Restore Details:"
echo "  Backup file: $BACKUP_FILE"
echo "  Database: $DB_NAME"
echo "  Host: $DB_HOST"
echo "  User: $DB_USER"
echo ""
echo "⚠️  WARNING: This will overwrite the current database!"
echo ""
read -p "Are you sure you want to restore? Type 'yes' to confirm: " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    info "Restore cancelled."
    exit 0
fi

echo ""
info "Restoring database..."
echo ""

# Create backup of current database before restoring
CURRENT_BACKUP="$BACKUP_DIR/hms_db_before_restore_$(date +%Y%m%d_%H%M%S).sql"
info "Creating safety backup of current database..."

if [ -z "$DB_PASS" ]; then
    mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$CURRENT_BACKUP" 2>/dev/null
else
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$CURRENT_BACKUP" 2>/dev/null
fi

if [ -f "$CURRENT_BACKUP" ]; then
    BACKUP_SIZE=$(du -h "$CURRENT_BACKUP" | cut -f1)
    success "Safety backup created: $CURRENT_BACKUP ($BACKUP_SIZE)"
fi

echo ""
info "Restoring from backup..."

# Restore database
if [ -z "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < "$BACKUP_FILE"
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_FILE"
fi

if [ $? -eq 0 ]; then
    success "Database restored successfully!"
    echo ""
    echo "Next steps:"
    echo "  1. Clear Laravel caches: cd /opt/hms && php artisan cache:clear"
    echo "  2. Restart services: sudo systemctl restart php8.2-fpm"
    echo "  3. Verify application: http://192.168.20.85"
    echo ""
    info "If restore failed, restore the safety backup:"
    info "  mysql -u $DB_USER -p $DB_NAME < $CURRENT_BACKUP"
else
    error "Restore failed! Safety backup available at: $CURRENT_BACKUP"
fi

echo ""
