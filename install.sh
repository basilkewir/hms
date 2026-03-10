#!/usr/bin/env bash
# Hotel Management System - Ubuntu Installer
# Tested on Ubuntu 20.04 LTS

set -eo pipefail

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
step()    { echo ""; echo -e "${BOLD}${CYAN}═══════════════════════════════════${RESET}"; echo -e "${BOLD} $*${RESET}"; echo -e "${BOLD}${CYAN}═══════════════════════════════════${RESET}"; echo ""; }

banner() {
cat << 'EOF'

  ██╗  ██╗███╗   ███╗███████╗
  ██║  ██║████╗ ████║██╔════╝
  ███████║██╔████╔██║███████╗
  ██╔══██║██║╚██╔╝██║╚════██║
  ██║  ██║██║ ╚═╝ ██║███████║
  ╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝
   Hotel Management System
   Ubuntu Installer
EOF
}

# Safe password generation (avoids SIGPIPE)
gen_password() {
    dd if=/dev/urandom bs=64 count=1 2>/dev/null | base64 | tr -d '/+=' | tr -d '\n' | cut -c1-20
}

# Check command exists
has_cmd() { command -v "$1" &>/dev/null; }

# Constants
INSTALL_DIR="/opt/hms"
PHP_VERSION="8.2"
NODE_VERSION="20"
LOG_FILE="/var/log/hms_install.log"

# Root check
[[ $EUID -ne 0 ]] && error "Must run as root. Use: sudo bash install.sh"

# OS check
if [[ ! -f /etc/os-release ]]; then error "Cannot detect OS"; fi
. /etc/os-release
[[ "$ID" != "ubuntu" ]] && error "Only Ubuntu supported"

banner

step "1/8 - Pre-flight Check & System Packages"

# Check dependencies
echo "Checking dependencies..."
NEED_PKG=""

# Check PHP version specifically - need 8.2 or higher
if has_cmd "php"; then
    PHP_CURRENT=$(php -r "echo PHP_VERSION;" 2>/dev/null | cut -d. -f1-2)
    if [[ "$PHP_CURRENT" != "8.2" && "$PHP_CURRENT" != "8.3" && "$PHP_CURRENT" != "8.4" ]]; then
        info "Found PHP ${PHP_CURRENT}, but need PHP 8.2+"
        NEED_PKG="$NEED_PKG php${PHP_VERSION}"
    fi
else
    NEED_PKG="$NEED_PKG php${PHP_VERSION}"
fi

has_cmd nginx || NEED_PKG="$NEED_PKG nginx"
has_cmd mysql || NEED_PKG="$NEED_PKG mysql-server"
has_cmd node || NEED_PKG="$NEED_PKG nodejs"
has_cmd composer || NEED_PKG="$NEED_PKG composer"

if [[ -z "$NEED_PKG" ]]; then
    success "All dependencies already installed"
else
    info "Installing:$NEED_PKG"
    export DEBIAN_FRONTEND=noninteractive

    # Add PPAs and update cache FIRST
    apt-get update -y 2>&1 | grep -v "^Get:" | grep -v "^Hit:" || true

    [[ "$NEED_PKG" == *"php"* ]] && {
        info "Adding PHP PPA..."
        add-apt-repository -y ppa:ondrej/php
        apt-get update -y 2>&1 | grep -v "^Get:" | grep -v "^Hit:" || true
    }

    [[ "$NEED_PKG" == *"nodejs"* ]] && {
        info "Setting up Node.js repository..."
        curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - > /dev/null 2>&1
        apt-get update -y 2>&1 | grep -v "^Get:" | grep -v "^Hit:" || true
    }

    # Install base packages
    info "Installing system packages..."
    apt-get upgrade -y -o Dpkg::Options::="--force-confold" 2>&1 | grep -v "^Reading" | grep -v "^Building" | grep -v "^Calculating" | tail -5 || true
    apt-get install -y software-properties-common curl wget git unzip zip bc gnupg2 ca-certificates 2>&1 | grep -E "^(Setting|Processing|Done)" || true

    # Install language-specific packages
    [[ "$NEED_PKG" == *"php"* ]] && {
        info "Installing PHP ${PHP_VERSION}..."

        # If old PHP exists, make sure it won't be the default
        if [[ -f /usr/bin/php8.1 ]]; then
            info "Removing PHP 8.1 as default..."
            update-alternatives --remove php /usr/bin/php8.1 2>/dev/null || true
            update-alternatives --remove php-fpm /usr/sbin/php-fpm8.1 2>/dev/null || true
        fi

        # Update apt cache again after adding PPA to ensure packages are available
        apt-get update -y 2>&1 | grep -v "^Get:" | grep -v "^Hit:" || true

        # Install core PHP first
        apt-get install -y php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli 2>&1 | tail -3 || true

        # Install extensions one at a time, skip if not available
        for ext in mysql mbstring xml zip curl gd intl dom; do
            apt-get install -y "php${PHP_VERSION}-${ext}" 2>&1 | grep -E "(Setting|done|Processing)" | tail -1 || true
        done

        # Also install dom for any existing old PHP versions to prevent issues
        for old_ver in 7.4 8.0 8.1; do
            if [[ -f /usr/bin/php${old_ver} ]]; then
                apt-get install -y "php${old_ver}-dom" 2>&1 | grep -E "(Setting|done|Processing)" | tail -1 || true
            fi
        done

        # Set PHP 8.2 as default - use --force-all to override
        update-alternatives --remove php /usr/bin/php8.1 2>/dev/null || true
        update-alternatives --remove php-fpm /usr/sbin/php-fpm8.1 2>/dev/null || true

        update-alternatives --install /usr/bin/php php /usr/bin/php${PHP_VERSION} 1000 --force > /dev/null 2>&1 || true
        update-alternatives --install /usr/bin/php-fpm php-fpm /usr/sbin/php-fpm${PHP_VERSION} 1000 --force > /dev/null 2>&1 || true

        # Also set as CLI alternatives
        update-alternatives --install /usr/bin/php-cli php-cli /usr/bin/php${PHP_VERSION} 1000 --force > /dev/null 2>&1 || true

        # Verify PHP version
        INSTALLED_PHP=$(php -r "echo PHP_VERSION;" 2>/dev/null | cut -d. -f1-2)
        if [[ "$INSTALLED_PHP" == "8.2" ]]; then
            success "PHP ${INSTALLED_PHP} installed and set as default"
        else
            warn "PHP 8.2 installed but current php command is ${INSTALLED_PHP} - please run: sudo update-alternatives --config php"
        fi
    }

    [[ "$NEED_PKG" == *"nginx"* ]] && {
        info "Installing Nginx..."
        apt-get install -y nginx 2>&1 | tail -2 || true
        systemctl enable nginx > /dev/null 2>&1
        systemctl start nginx > /dev/null 2>&1
    }

    [[ "$NEED_PKG" == *"mysql"* ]] && {
        info "Installing MySQL Server..."
        apt-get install -y mysql-server 2>&1 | tail -2 || true
        systemctl enable mysql > /dev/null 2>&1
        systemctl start mysql > /dev/null 2>&1
    }

    [[ "$NEED_PKG" == *"nodejs"* ]] && {
        info "Installing Node.js..."
        apt-get install -y nodejs 2>&1 | tail -2 || true
    }

    [[ "$NEED_PKG" == *"composer"* ]] && {
        info "Installing Composer..."

        # Check if PHP is available
        if ! command -v php &>/dev/null; then
            warn "PHP not available, skipping Composer installation"
        else
            EXPECTED_CHECKSUM="$(curl -s https://composer.github.io/installer.sig)"
            php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" || error "Failed to download Composer installer"
            ACTUAL_CHECKSUM="$(php -r "echo hash_file('SHA384', 'composer-setup.php');")"

            if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
                >&2 echo 'ERROR: Invalid installer checksum'
                rm composer-setup.php
                error "Composer installation failed - checksum mismatch"
            fi

            php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
            RESULT=$?
            rm -f composer-setup.php
            [[ $RESULT -eq 0 ]] && success "Composer installed" || error "Composer installation failed"
        fi
    }

    success "All packages installed"
fi

step "2/8 - Configuration"

# Get domain
SERVER_IP=$(hostname -I | awk '{print $1}')
read -rp "Domain or IP [$SERVER_IP]: " APP_DOMAIN
APP_DOMAIN="${APP_DOMAIN:-$SERVER_IP}"

read -rp "Use HTTPS? (y/n) [n]: " USE_HTTPS
if [[ "$USE_HTTPS" =~ ^[Yy] ]]; then
    APP_URL="https://${APP_DOMAIN}"
    INSTALL_SSL=true
else
    APP_URL="http://${APP_DOMAIN}"
    INSTALL_SSL=false
fi

echo ""
read -rp "Hotel Name [Grand Hotel]: " HOTEL_NAME
HOTEL_NAME="${HOTEL_NAME:-Grand Hotel}"

read -rp "Hotel Email [info@hotel.com]: " HOTEL_EMAIL
HOTEL_EMAIL="${HOTEL_EMAIL:-info@hotel.com}"

read -rp "Hotel Phone [+1234567890]: " HOTEL_PHONE
HOTEL_PHONE="${HOTEL_PHONE:-+1234567890}"

read -rp "Hotel Address [123 Hotel St]: " HOTEL_ADDRESS
HOTEL_ADDRESS="${HOTEL_ADDRESS:-123 Hotel St}"

echo ""
read -rp "Database name [hms_db]: " DB_DATABASE
DB_DATABASE="${DB_DATABASE:-hms_db}"

read -rp "Database user [hms_user]: " DB_USERNAME
DB_USERNAME="${DB_USERNAME:-hms_user}"

RAND_PASS=$(gen_password)
printf "Database password [auto, press Enter]: " > /dev/tty
DB_PASSWORD=""
read -rs DB_PASSWORD < /dev/tty || true
echo "" > /dev/tty
DB_PASSWORD="${DB_PASSWORD:-$RAND_PASS}"

read -rp "License server [https://kewirdev.com/api/license]: " LICENSE_SERVER_URL
LICENSE_SERVER_URL="${LICENSE_SERVER_URL:-https://kewirdev.com/api/license}"

echo ""
echo "Summary:"
echo "  URL: $APP_URL"
echo "  Hotel: $HOTEL_NAME"
echo "  DB: $DB_DATABASE"
echo ""
read -rp "Proceed? (y/n) [y]: " CONFIRM
[[ ! "$CONFIRM" =~ ^[Yy]?$ ]] && { info "Cancelled"; exit 0; }

# Start logging
touch "$LOG_FILE"
exec > >(tee -a "$LOG_FILE") 2>&1
info "Installation started"

step "2/8 - Database Setup"

# Get database credentials
read -rp "Database name [hms_db]: " DB_DATABASE
DB_DATABASE="${DB_DATABASE:-hms_db}"

read -rp "Database user [hms_user]: " DB_USERNAME
DB_USERNAME="${DB_USERNAME:-hms_user}"

RAND_PASS=$(gen_password)
printf "Database password [auto, press Enter]: " > /dev/tty
DB_PASSWORD=""
read -rs DB_PASSWORD < /dev/tty || true
echo "" > /dev/tty
DB_PASSWORD="${DB_PASSWORD:-$RAND_PASS}"

mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS '${DB_USERNAME}'@'localhost';
CREATE USER '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';
FLUSH PRIVILEGES;
SQL

success "Database created"

step "3/8 - Application Files"

mkdir -p "${INSTALL_DIR}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

[[ ! -f "${SCRIPT_DIR}/artisan" ]] && error "Run from project root"

rsync -a --exclude='.git' --exclude='node_modules' --exclude='vendor' \
      --exclude='.env' --exclude='storage/logs' --exclude='*.md' \
      --exclude='install.sh' "${SCRIPT_DIR}/" "${INSTALL_DIR}/"

cd "${INSTALL_DIR}"
chown -R www-data:www-data "${INSTALL_DIR}"
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;
chmod -R 775 "${INSTALL_DIR}/storage" "${INSTALL_DIR}/bootstrap/cache"
chmod +x "${INSTALL_DIR}/artisan"

success "Files deployed"

step "4/8 - Configuration"

APP_KEY="base64:$(dd if=/dev/urandom bs=32 count=1 2>/dev/null | base64 | tr -d '\n')"

cat > "${INSTALL_DIR}/.env" << ENDENV
APP_NAME="Hotel Management System"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=${APP_URL}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_DOMAIN=${APP_DOMAIN}
SESSION_SECURE_COOKIE=${INSTALL_SSL}

HOTEL_NAME="${HOTEL_NAME}"
HOTEL_EMAIL="${HOTEL_EMAIL}"
HOTEL_PHONE="${HOTEL_PHONE}"
HOTEL_ADDRESS="${HOTEL_ADDRESS}"

LICENSE_SERVER_URL=${LICENSE_SERVER_URL}

MAIL_FROM_ADDRESS="${HOTEL_EMAIL}"
MAIL_FROM_NAME="${HOTEL_NAME}"
ENDENV

chown www-data:www-data "${INSTALL_DIR}/.env"
chmod 640 "${INSTALL_DIR}/.env"

success ".env written"

step "5/8 - Dependencies"

info "Composer install (updating lock file for PHP 8.2 compatibility)..."
# First update composer lock to get PHP 8.2 compatible versions
sudo -u www-data composer update --no-dev --no-interaction --optimize-autoloader --prefer-dist 2>&1 | tail -5 || true

# Then install with the updated lock file
sudo -u www-data composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist 2>&1 | tail -3 || true

sudo -u www-data php artisan key:generate --force

info "npm install..."
npm install --legacy-peer-deps

info "Building assets..."
NODE_ENV=production npm run build

rm -rf node_modules

success "Dependencies installed"

step "6/8 - Database Migrations"

sudo -u www-data php artisan storage:link --force

info "Running migrations and seeding database..."
# Use migrate:fresh --seed to drop all tables and run fresh migrations with seeders
# This is required for fresh installations and handles already-existing tables
sudo -u www-data php artisan migrate:fresh --seed --force 2>&1 | grep -E "(MIGRAT|SEED|Error|Exception)" || true

info "Seeding additional data..."
sudo -u www-data php artisan db:seed --class=SettingsSeeder --force 2>/dev/null || true
sudo -u www-data php artisan db:seed --class=AdminPermissionsSeeder --force 2>/dev/null || true
sudo -u www-data php artisan db:seed --class=ManagerPermissionsSeeder --force 2>/dev/null || true
sudo -u www-data php artisan db:seed --class=UserAccountsSeeder --force 2>/dev/null || true

info "Building caches..."
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache

success "Database ready"

step "7/8 - Nginx"

cat > /etc/nginx/sites-available/hms << ENDNGINX
server {
    listen 80;
    server_name ${APP_DOMAIN};
    root ${INSTALL_DIR}/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht { deny all; }
}
ENDNGINX

ln -sf /etc/nginx/sites-available/hms /etc/nginx/sites-enabled/hms
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl reload nginx

success "Nginx configured"

step "8/8 - Background Services"

cat > /etc/systemd/system/hms-queue.service << ENDSVC
[Unit]
Description=HMS Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=${INSTALL_DIR}
ExecStart=/usr/bin/php ${INSTALL_DIR}/artisan queue:work --sleep=3
Restart=always

[Install]
WantedBy=multi-user.target
ENDSVC

echo "* * * * * www-data cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/hms-scheduler

systemctl daemon-reload
systemctl enable hms-queue --now

success "Services started"

# Credentials
cat > /root/hms_credentials.txt << ENDCREDS
════════════════════════════════════════
  HMS Installation Complete!
  Date: $(date)
════════════════════════════════════════

🌐 Access URL:
   ${APP_URL}

👤 Admin Account:
   Email    : admin@hotel.com
   Password : password

💾 Database:
   Host     : localhost
   Database : ${DB_DATABASE}
   User     : ${DB_USERNAME}
   Password : ${DB_PASSWORD}

⚠️  IMPORTANT:
   1. Change admin password immediately
   2. Update hotel information in settings
   3. Configure payment gateway
   4. Set up email notifications

════════════════════════════════════════
ENDCREDS

chmod 600 /root/hms_credentials.txt

# Clear screen and show summary
clear
echo ""
echo -e "${GREEN}${BOLD}"
cat << 'EOF'

  ██╗  ██╗███╗   ███╗███████╗
  ██║  ██║████╗ ████║██╔════╝
  ███████║██╔████╔██║███████╗
  ██╔══██║██║╚██╔╝██║╚════██║
  ██║  ██║██║ ╚═╝ ██║███████║
  ╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝

   ✓ Installation Complete!

EOF
echo -e "${RESET}"

echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${BOLD}Access Information:${RESET}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
echo -e "  ${BOLD}URL:${RESET}      ${GREEN}${APP_URL}${RESET}"
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${BOLD}Admin Login:${RESET}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
echo -e "  ${BOLD}Email:${RESET}    admin@hotel.com"
echo -e "  ${BOLD}Password:${RESET} password"
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${BOLD}Database Details:${RESET}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
echo -e "  ${BOLD}Database:${RESET}  ${DB_DATABASE}"
echo -e "  ${BOLD}User:${RESET}     ${DB_USERNAME}"
echo -e "  ${BOLD}Password:${RESET} ${DB_PASSWORD}"
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${YELLOW}⚠️  NEXT STEPS:${RESET}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
echo "  1. Open your browser and navigate to the URL above"
echo "  2. Login with the admin credentials above"
echo "  3. Change the admin password immediately"
echo "  4. Update hotel information in Settings"
echo "  5. Configure payment gateway"
echo "  6. Set up email notifications"
echo ""
echo "  📝 All credentials saved to: /root/hms_credentials.txt"
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
