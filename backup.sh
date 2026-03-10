#!/bin/bash

# HMS Database Backup Script
# Usage: sudo bash backup.sh [backup_name]
# Example: sudo bash backup.sh "before_update"

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
INSTALL_DIR="/opt/hms"
BACKUP_DIR="/root/hms_backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="${1:-auto}"

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

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    error "This script must be run as root. Use: sudo bash backup.sh"
fi

# Check if HMS is installed
if [ ! -d "$INSTALL_DIR" ]; then
    error "HMS is not installed at $INSTALL_DIR"
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║        HMS Database Backup                                 ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Get database credentials from .env
DB_HOST=$(grep "^DB_HOST=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
DB_USER=$(grep "^DB_USERNAME=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
DB_PASS=$(grep "^DB_PASSWORD=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
DB_NAME=$(grep "^DB_DATABASE=" "$INSTALL_DIR/.env" | cut -d'=' -f2)

info "Database: $DB_NAME"
info "Host: $DB_HOST"
info "User: $DB_USER"
echo ""

# Create backup filename
BACKUP_FILE="$BACKUP_DIR/hms_db_${BACKUP_NAME}_${DATE}.sql"

info "Backing up database..."
info "Output: $BACKUP_FILE"
echo ""

# Run mysqldump
if [ -z "$DB_PASS" ]; then
    if ! mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE"; then
        error "Backup failed!"
    fi
else
    if ! mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"; then
        error "Backup failed!"
    fi
fi

# Verify backup
if [ -f "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    success "Backup completed successfully!"
    echo ""
    echo "Backup Details:"
    echo "  Name: $BACKUP_FILE"
    echo "  Size: $BACKUP_SIZE"
    echo "  Date: $(date)"
    echo ""
    echo "To restore this backup, run:"
    echo "  mysql -u $DB_USER -p $DB_NAME < $BACKUP_FILE"
    echo ""
    
    # List recent backups
    echo "Recent backups:"
    ls -lh "$BACKUP_DIR" | tail -5 | awk '{print "  " $9 " (" $5 ")"}'
else
    error "Backup file was not created!"
fi

echo ""
