#!/bin/bash
# HMS Complete Cleanup & Reset Script
# Removes all HMS installation artifacts and prepares for fresh installation
# Run this on Ubuntu server before running install.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

# Helper functions
info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; exit 1; }

echo ""
echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${RESET}"
echo -e "${BOLD}  HMS Complete Cleanup & Reset${RESET}"
echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${RESET}"
echo ""

# Root check
if [[ $EUID -ne 0 ]]; then
    error "This script must be run as root. Use: sudo bash cleanup.sh"
fi

# Confirmation
echo -e "${YELLOW}⚠️  WARNING: This will PERMANENTLY DELETE:${RESET}"
echo "  • /opt/hms application directory"
echo "  • /root/hms_credentials.txt"
echo "  • /var/log/hms_install.log"
echo "  • MySQL database 'hms_db'"
echo "  • All HMS-related database users"
echo ""
read -p "Are you sure? Type 'YES' to continue: " confirm
if [[ "$confirm" != "YES" ]]; then
    warn "Cleanup cancelled"
    exit 0
fi

echo ""
info "Starting complete cleanup..."
echo ""

# Step 1: Stop services
info "Stopping services..."
sudo systemctl stop nginx 2>/dev/null || true
sudo systemctl stop php8.2-fpm 2>/dev/null || true
sudo systemctl stop mysql 2>/dev/null || true
success "Services stopped"

# Step 2: Remove application files
info "Removing application files..."
sudo rm -rf /opt/hms 2>/dev/null || true
success "Application directory removed"

# Step 3: Remove credential files
info "Removing credential files..."
sudo rm -f /root/hms_credentials.txt 2>/dev/null || true
success "Credentials file removed"

# Step 4: Remove installation log
info "Removing installation log..."
sudo rm -f /var/log/hms_install.log 2>/dev/null || true
success "Installation log removed"

# Step 5: Remove Nginx configuration
info "Removing Nginx configuration..."
sudo rm -f /etc/nginx/sites-available/hms 2>/dev/null || true
sudo rm -f /etc/nginx/sites-enabled/hms 2>/dev/null || true
success "Nginx configuration removed"

# Step 6: Remove systemd services
info "Removing systemd services..."
sudo rm -f /etc/systemd/system/hms-queue.service 2>/dev/null || true
sudo rm -f /etc/cron.d/hms-scheduler 2>/dev/null || true
sudo systemctl daemon-reload 2>/dev/null || true
success "Systemd services removed"

# Step 7: Clean up database
info "Cleaning up database..."
sudo systemctl start mysql 2>/dev/null || true
sleep 2

# Drop database
mysql -u root -e "DROP DATABASE IF EXISTS hms_db;" 2>/dev/null || true
success "Database hms_db dropped"

# Drop users
mysql -u root -e "DROP USER IF EXISTS 'hms_user'@'localhost';" 2>/dev/null || true
mysql -u root -e "DROP USER IF EXISTS 'hms_user'@'127.0.0.1';" 2>/dev/null || true
success "Database users removed"

# Flush privileges
mysql -u root -e "FLUSH PRIVILEGES;" 2>/dev/null || true
success "Database privileges flushed"

# Step 8: Clear PHP-FPM cache
info "Clearing PHP-FPM cache..."
sudo rm -rf /var/lib/php/sessions/* 2>/dev/null || true
sudo rm -rf /tmp/php-* 2>/dev/null || true
success "PHP cache cleared"

# Step 9: Clean npm cache (if needed for fresh install)
info "Clearing npm cache..."
npm cache clean --force 2>/dev/null || true
success "npm cache cleared"

# Step 10: Update git repository
info "Updating git repository..."
cd /root/hms 2>/dev/null || true
git fetch origin 2>/dev/null || true
git reset --hard origin/master 2>/dev/null || true
success "Git repository updated to latest"

echo ""
echo -e "${BOLD}${GREEN}═══════════════════════════════════════════════════════════${RESET}"
echo -e "${GREEN}✓ Cleanup Complete!${RESET}"
echo -e "${BOLD}${GREEN}═══════════════════════════════════════════════════════════${RESET}"
echo ""
echo "Next steps:"
echo "  1. Verify database is clean:"
echo "     mysql -u root -e 'SHOW DATABASES;'"
echo ""
echo "  2. Run fresh installation:"
echo "     cd /root/hms"
echo "     sudo bash install.sh"
echo ""
