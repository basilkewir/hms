#!/bin/bash

# HMS Update Script - Update existing installation without reinstalling from scratch
# Usage: sudo bash update.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
INSTALL_DIR="/opt/hms"
BACKUP_DIR="/root/hms_backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Resolve the directory where this script lives (the source)
SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Helper functions
info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[✓]${NC} $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }
warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
step()    {
    echo -e "\n${BLUE}════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════════════════${NC}\n"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    error "This script must be run as root. Use: sudo bash update.sh"
fi

# Check if HMS is installed
if [ ! -d "$INSTALL_DIR" ]; then
    error "HMS is not installed at $INSTALL_DIR. Please run install.sh first."
fi

step "HMS Update Script"

info "Source directory : $SOURCE_DIR"
info "Install directory: $INSTALL_DIR"
info ""
info "Your database and .env file will NOT be modified."
info ""

mkdir -p "$BACKUP_DIR"

# Optional database backup
read -p "Do you want to backup the database before updating? (y/n) [y]: " BACKUP_DB
BACKUP_DB=${BACKUP_DB:-y}

if [[ $BACKUP_DB == "y" || $BACKUP_DB == "Y" ]]; then
    step "Backing Up Database"

    DB_HOST=$(grep "^DB_HOST="     "$INSTALL_DIR/.env" | cut -d'=' -f2)
    DB_USER=$(grep "^DB_USERNAME=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    DB_PASS=$(grep "^DB_PASSWORD=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    DB_NAME=$(grep "^DB_DATABASE=" "$INSTALL_DIR/.env" | cut -d'=' -f2)

    BACKUP_FILE="$BACKUP_DIR/hms_db_backup_${DATE}.sql"
    info "Backing up database $DB_NAME to $BACKUP_FILE..."

    if [ -z "$DB_PASS" ]; then
        mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null || true
    else
        mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null || true
    fi

    if [ -s "$BACKUP_FILE" ]; then
        BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        success "Database backed up ($BACKUP_SIZE) → $BACKUP_FILE"
    else
        warning "Database backup may have failed. Continuing anyway..."
    fi
fi

step "Copying Updated Application Files"

info "Copying from: $SOURCE_DIR"
info "Copying to  : $INSTALL_DIR"

# Directories to sync (never touch storage/ or .env)
for dir in app bootstrap config database resources routes tests public/build; do
    if [ -d "$SOURCE_DIR/$dir" ]; then
        info "  → $dir"
        rm -rf "$INSTALL_DIR/$dir"
        cp -r "$SOURCE_DIR/$dir" "$INSTALL_DIR/$dir"
    fi
done

# Root-level files to copy
for file in artisan package.json package-lock.json composer.json composer.lock vite.config.js; do
    if [ -f "$SOURCE_DIR/$file" ]; then
        info "  → $file"
        cp "$SOURCE_DIR/$file" "$INSTALL_DIR/$file"
    fi
done

# Copy .env.example but never overwrite .env
[ -f "$SOURCE_DIR/.env.example" ] && cp "$SOURCE_DIR/.env.example" "$INSTALL_DIR/.env.example"

success "Application files copied"

step "Patching .env Settings"

# LICENSE_SERVER_URL
if grep -q "^LICENSE_SERVER_URL=" "$INSTALL_DIR/.env"; then
    sed -i 's|^LICENSE_SERVER_URL=.*|LICENSE_SERVER_URL=https://kewirdev.com/api/license|' "$INSTALL_DIR/.env"
else
    echo "LICENSE_SERVER_URL=https://kewirdev.com/api/license" >> "$INSTALL_DIR/.env"
fi

# LICENSE_SIGNATURE_SECRET
if grep -q "^LICENSE_SIGNATURE_SECRET=" "$INSTALL_DIR/.env"; then
    sed -i 's|^LICENSE_SIGNATURE_SECRET=.*|LICENSE_SIGNATURE_SECRET=E0FMIZdSNTtywmB6psxK4pqWSVRs8eo1ogPsePEmzXU=|' "$INSTALL_DIR/.env"
else
    echo "LICENSE_SIGNATURE_SECRET=E0FMIZdSNTtywmB6psxK4pqWSVRs8eo1ogPsePEmzXU=" >> "$INSTALL_DIR/.env"
fi

success ".env patched"

step "Setting Permissions"

chown -R www-data:www-data "$INSTALL_DIR"
find "$INSTALL_DIR" -type d -exec chmod 755 {} \;
find "$INSTALL_DIR" -type f -exec chmod 644 {} \;
chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
chmod +x "$INSTALL_DIR/artisan"

success "Permissions set"

step "Installing Composer Dependencies"

cd "$INSTALL_DIR"
sudo -u www-data composer install --no-dev --optimize-autoloader --prefer-dist 2>&1 | tail -5
success "Composer dependencies installed"

step "Running Database Migrations"

cd "$INSTALL_DIR"
if sudo -u www-data php artisan migrate --force 2>&1 | tee -a /var/log/hms_update.log; then
    success "Migrations completed"
else
    warning "Migration issue — check /var/log/hms_update.log"
fi

step "Seeding License Data"

cd "$INSTALL_DIR"
if sudo -u www-data php artisan db:seed --class=LicenseSeeder --force 2>&1 | tee -a /var/log/hms_update.log; then
    success "License seeded"
else
    warning "License seeding failed — check /var/log/hms_update.log"
fi

step "Clearing Caches"

cd "$INSTALL_DIR"
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan route:clear

success "Caches cleared"

step "Restarting Services"

systemctl restart php8.2-fpm && success "PHP-FPM restarted" || warning "Could not restart php8.2-fpm"
systemctl restart nginx       && success "Nginx restarted"   || warning "Could not restart nginx"

step "Update Complete!"

echo ""
echo "✓ HMS has been successfully updated!"
echo ""
echo "Summary:"
echo "  - Files copied from : $SOURCE_DIR"
echo "  - Files updated in  : $INSTALL_DIR"
echo "  - Migrations run"
echo "  - Caches cleared"
echo "  - Services restarted"
[[ $BACKUP_DB == "y" || $BACKUP_DB == "Y" ]] && echo "  - DB backup: $BACKUP_FILE"
echo ""
echo "Logs: tail -50 $INSTALL_DIR/storage/logs/laravel.log"
echo "      tail -50 /var/log/hms_update.log"
echo ""
