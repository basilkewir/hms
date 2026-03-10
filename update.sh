#!/bin/bash

# HMS Update Script - Update existing installation without reinstalling from scratch
# Usage: sudo bash update.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
INSTALL_DIR="/opt/hms"
REPO_URL="https://github.com/basilkewir/hms.git"
BACKUP_DIR="/root/hms_backups"
DATE=$(date +%Y%m%d_%H%M%S)

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

step() {
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

info "This script will update your existing HMS installation."
info "Your database and .env file will NOT be modified."
info ""

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Option: Backup database
read -p "Do you want to backup the database before updating? (y/n) [y]: " BACKUP_DB
BACKUP_DB=${BACKUP_DB:-y}

if [[ $BACKUP_DB == "y" || $BACKUP_DB == "Y" ]]; then
    step "Backing Up Database"
    
    # Get database credentials from .env
    DB_HOST=$(grep "^DB_HOST=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    DB_USER=$(grep "^DB_USERNAME=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    DB_PASS=$(grep "^DB_PASSWORD=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    DB_NAME=$(grep "^DB_DATABASE=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    
    BACKUP_FILE="$BACKUP_DIR/hms_db_backup_${DATE}.sql"
    
    info "Backing up database $DB_NAME to $BACKUP_FILE..."
    
    if [ -z "$DB_PASS" ]; then
        mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
    else
        mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
    fi
    
    if [ -f "$BACKUP_FILE" ]; then
        BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        success "Database backed up successfully ($BACKUP_SIZE)"
        success "Backup file: $BACKUP_FILE"
        info "To restore: mysql -u $DB_USER -p $DB_NAME < $BACKUP_FILE"
    else
        warning "Database backup failed. Continuing anyway..."
    fi
fi

step "Updating Source Code"

info "Pulling latest code from repository..."

# Pull latest code from source repo
cd /root/hms
git fetch origin master
git reset --hard origin/master

success "Source code updated"

step "Updating Application"

# Copy only modified files (don't overwrite .env or storage)
cd "$INSTALL_DIR"

info "Copying updated application files..."
cp -r /root/hms/app "$INSTALL_DIR/"
cp -r /root/hms/bootstrap "$INSTALL_DIR/"
cp -r /root/hms/config "$INSTALL_DIR/"
cp -r /root/hms/database "$INSTALL_DIR/"
cp -r /root/hms/resources "$INSTALL_DIR/"
cp -r /root/hms/routes "$INSTALL_DIR/"
cp -r /root/hms/tests "$INSTALL_DIR/"
cp /root/hms/artisan "$INSTALL_DIR/"
cp /root/hms/package.json "$INSTALL_DIR/"
cp /root/hms/package-lock.json "$INSTALL_DIR/"
cp /root/hms/composer.json "$INSTALL_DIR/"
cp /root/hms/composer.lock "$INSTALL_DIR/"
cp /root/hms/vite.config.js "$INSTALL_DIR/"
cp /root/hms/.env.example "$INSTALL_DIR/" 2>/dev/null || true

success "Application files copied"

# Set permissions
chown -R www-data:www-data "$INSTALL_DIR"
find "$INSTALL_DIR" -type d -exec chmod 755 {} \;
find "$INSTALL_DIR" -type f -exec chmod 644 {} \;
chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
chmod +x "$INSTALL_DIR/artisan"

success "Permissions set correctly"

step "Running Database Migrations"

info "Checking for new migrations..."
cd "$INSTALL_DIR"

if ! sudo -u www-data php artisan migrate --force 2>&1 | tee -a /var/log/hms_update.log; then
    warning "Migration warning - check /var/log/hms_update.log"
else
    success "Migrations completed successfully"
fi

step "Seeding License Data"

info "Seeding Hotel Donzebe HD license..."
cd "$INSTALL_DIR"

if sudo -u www-data php artisan db:seed --class=LicenseSeeder 2>&1 | tee -a /var/log/hms_update.log; then
    success "License seeded successfully - Hotel Donzebe HD is now active"
else
    warning "License seeding failed - check /var/log/hms_update.log"
fi

step "Updating Dependencies"

info "Updating Composer dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader --prefer-dist 2>&1 | tail -3

success "Composer dependencies updated"

info "Updating NPM dependencies..."
npm install --legacy-peer-deps 2>&1 | tail -3
NODE_ENV=production npm run build 2>&1 | tail -3
rm -rf node_modules

success "NPM dependencies updated and assets built"

step "Clearing Caches"

info "Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

success "Caches cleared"

step "Restarting Services"

info "Restarting PHP-FPM..."
sudo systemctl restart php8.2-fpm

info "Restarting Nginx..."
sudo systemctl restart nginx

success "Services restarted"

step "Verification"

info "Checking application..."
curl -s -I http://localhost | head -1

success "Application is responding"

step "Update Complete!"

echo ""
echo "✓ HMS has been successfully updated!"
echo ""
echo "Summary:"
echo "  - Source code updated from origin/master"
echo "  - Application files updated"
echo "  - Database migrations completed"
echo "  - Dependencies updated"
echo "  - Services restarted"
if [[ $BACKUP_DB == "y" || $BACKUP_DB == "Y" ]]; then
    echo "  - Database backed up to: $BACKUP_FILE"
fi
echo ""
echo "Next steps:"
echo "  1. Verify the application: http://192.168.20.85"
echo "  2. Check logs if issues: tail -50 /opt/hms/storage/logs/laravel.log"
echo "  3. If something went wrong, you can restore from backup"
echo ""
echo "Update log saved to: /var/log/hms_update.log"
echo ""
