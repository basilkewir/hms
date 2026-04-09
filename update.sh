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
STAGING_DIR="/root/hms"
BACKUP_DIR="/root/hms_backups"
DATE=$(date +%Y%m%d_%H%M%S)
SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
EFFECTIVE_SOURCE_DIR="$SOURCE_DIR"

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

info "Install directory: $INSTALL_DIR"
info "Source directory : $SOURCE_DIR"
info "Staging directory: $STAGING_DIR"
info ""
info "Code flow: source directory -> $STAGING_DIR -> $INSTALL_DIR"
info "Your database and .env file will NOT be modified by this script."
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

step "Verifying Pulled Code"

if ! git -C "$SOURCE_DIR" rev-parse --is-inside-work-tree &>/dev/null; then
    warning "$SOURCE_DIR is not a git repository. Make sure latest code is already present."
else
    SRC_BRANCH=$(git -C "$SOURCE_DIR" rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    SRC_COMMIT=$(git -C "$SOURCE_DIR" rev-parse --short HEAD 2>/dev/null || echo "unknown")
    info "Source branch: $SRC_BRANCH"
    info "Source commit: $SRC_COMMIT"
fi

if ! git -C "$INSTALL_DIR" rev-parse --is-inside-work-tree &>/dev/null; then
    warning "$INSTALL_DIR is not a git repository. Ensure updated code is already present before continuing."
else
    CURRENT_BRANCH=$(git -C "$INSTALL_DIR" rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    CURRENT_COMMIT=$(git -C "$INSTALL_DIR" rev-parse --short HEAD 2>/dev/null || echo "unknown")
    info "Detected git repo in $INSTALL_DIR"
    info "Current branch: $CURRENT_BRANCH"
    info "Current commit: $CURRENT_COMMIT"
fi

step "Staging Source Code"

if [ "$SOURCE_DIR" != "$STAGING_DIR" ]; then
    if ! command -v rsync >/dev/null 2>&1; then
        error "rsync is required for safe source staging. Install it with: apt-get install -y rsync"
    fi

    mkdir -p "$STAGING_DIR"
    info "Syncing source from $SOURCE_DIR to $STAGING_DIR"

    rsync -a --delete \
        --exclude '.env' \
        --exclude 'storage/' \
        --exclude 'bootstrap/cache/' \
        --exclude 'node_modules/' \
        "$SOURCE_DIR/" "$STAGING_DIR/"

    success "Source staged in $STAGING_DIR"
    EFFECTIVE_SOURCE_DIR="$STAGING_DIR"
else
    info "Source is already in $STAGING_DIR"
    EFFECTIVE_SOURCE_DIR="$STAGING_DIR"
fi

step "Syncing Application Code"

if [ "$EFFECTIVE_SOURCE_DIR" = "$INSTALL_DIR" ]; then
    info "Source and install directory are the same. No sync needed."
else
    if ! command -v rsync >/dev/null 2>&1; then
        error "rsync is required for safe code sync. Install it with: apt-get install -y rsync"
    fi

    info "Syncing code from $EFFECTIVE_SOURCE_DIR to $INSTALL_DIR"
    info "Preserving: .env, storage/, bootstrap/cache/, node_modules/, .git/"

    rsync -a --delete \
        --exclude '.git/' \
        --exclude '.env' \
        --exclude 'storage/' \
        --exclude 'bootstrap/cache/' \
        --exclude 'node_modules/' \
        "$EFFECTIVE_SOURCE_DIR/" "$INSTALL_DIR/"

    success "Application code synced"
fi

step "Setting Permissions"

chown -R www-data:www-data "$INSTALL_DIR"
find "$INSTALL_DIR" -type d -exec chmod 755 {} +
# Exclude node_modules — chmod 644 would strip execute bits from vite and other CLI binaries
find "$INSTALL_DIR" -type f -not -path "*/node_modules/*" -exec chmod 644 {} +
chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
chmod +x "$INSTALL_DIR/artisan"

success "Permissions set"

step "Installing Composer Dependencies"

cd "$INSTALL_DIR"
info "Running composer install..."
timeout 600 env COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
success "Composer dependencies installed"

step "Running Database Migrations"

cd "$INSTALL_DIR"
if php artisan migrate --force 2>&1 | tee -a /var/log/hms_update.log; then
    success "Migrations completed"
else
    warning "Migration issue — check /var/log/hms_update.log"
fi

step "Seeding License Data"

cd "$INSTALL_DIR"
if php artisan db:seed --class=LicenseSeeder --force 2>&1 | tee -a /var/log/hms_update.log; then
    success "License seeded"
else
    warning "License seeding failed — check /var/log/hms_update.log"
fi

step "Seeding Room Amenities"

cd "$INSTALL_DIR"
if php artisan db:seed --class=RoomAmenitiesSeeder --force 2>&1 | tee -a /var/log/hms_update.log; then
    success "Room amenities seeded"
else
    warning "Room amenities seeding failed — check /var/log/hms_update.log"
fi

step "Optional Financial Reset"

read -p "Clear registered financial transactions (sales, folios, payments, expenses, procurement history) and set product stock to zero? (y/n) [n]: " RESET_FINANCIALS
RESET_FINANCIALS=${RESET_FINANCIALS:-n}

if [[ $RESET_FINANCIALS == "y" || $RESET_FINANCIALS == "Y" ]]; then
    cd "$INSTALL_DIR"
    if php artisan hms:reset-financials --force 2>&1 | tee -a /var/log/hms_update.log; then
        success "Financial data reset completed"
    else
        warning "Financial reset failed — check /var/log/hms_update.log"
    fi
else
    info "Financial reset skipped"
fi

step "Clearing Caches"

cd "$INSTALL_DIR"
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Reload PHP OPcache if available
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared\n'; }" 2>/dev/null || true

success "Caches cleared"

step "Rebuilding Frontend Assets"

cd "$INSTALL_DIR"

# Read install mode — IPTV-only has no Blade UI, skip the npm build entirely
INSTALL_MODE=$(grep '^INSTALL_MODE=' "$INSTALL_DIR/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' || echo "both")

if [[ "$INSTALL_MODE" == "iptv" ]]; then
    info "Skipping front-end build — IPTV-only installation has no Blade UI"
else
    info "Running npm install..."
    timeout 600 npm install --prefer-offline --no-audit --no-fund 2>&1 | tail -5

    # Restore execute bits on node_modules/.bin (npm install --prefer-offline may skip this)
    info "Restoring node_modules/.bin permissions..."
    find "$INSTALL_DIR/node_modules/.bin" \( -type f -o -type l \) -exec chmod +x {} + 2>/dev/null || true

    # Generate Ziggy route file so app.js can resolve ../../vendor/tightenco/ziggy
    info "Generating Ziggy routes..."
    php artisan ziggy:generate 2>/dev/null || true

    info "Running npm run build..."
    if timeout 900 npm run build 2>&1 | tail -5; then
        success "Frontend assets rebuilt"
    else
        warning "npm build failed — pages may show old assets. Check logs."
    fi
fi

success "Caches cleared"

step "Restarting Services"

cd "$INSTALL_DIR"
php artisan queue:restart && success "Laravel queue workers restarted" || warning "Could not signal Laravel queue workers to restart"

systemctl restart php8.2-fpm && success "PHP-FPM restarted" || warning "Could not restart php8.2-fpm"
systemctl restart nginx       && success "Nginx restarted"   || warning "Could not restart nginx"

step "Update Complete!"

echo ""
echo "✓ HMS has been successfully updated!"
echo ""
echo "Summary:"
echo "  - Source code from : $SOURCE_DIR"
echo "  - Staged in        : $STAGING_DIR"
echo "  - Updated code in  : $INSTALL_DIR"
echo "  - Migrations run"
[[ $RESET_FINANCIALS == "y" || $RESET_FINANCIALS == "Y" ]] && echo "  - Registered financial transaction data reset"
echo "  - Caches cleared"
echo "  - Services restarted"
[[ $BACKUP_DB == "y" || $BACKUP_DB == "Y" ]] && echo "  - DB backup: $BACKUP_FILE"
echo ""
echo "Logs: tail -50 $INSTALL_DIR/storage/logs/laravel.log"
echo "      tail -50 /var/log/hms_update.log"
echo ""
