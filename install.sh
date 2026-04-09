#!/usr/bin/env bash
# =============================================================================
#  Unified Installer — IPTV Management / Hotel Management System / Both
#  Tested on Ubuntu 20.04, 22.04, 24.04 LTS
# =============================================================================
set -eo pipefail

# ── Colours ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; exit 1; }
step()    { echo ""; echo -e "${BOLD}${CYAN}═══════════════════════════════════${RESET}"; \
            echo -e "${BOLD} $*${RESET}"; \
            echo -e "${BOLD}${CYAN}═══════════════════════════════════${RESET}"; echo ""; }

gen_password() {
    dd if=/dev/urandom bs=64 count=1 2>/dev/null | base64 | tr -d '/+=' | tr -d '\n' | cut -c1-20
}
has_cmd() { command -v "$1" &>/dev/null; }

# ── Constants ─────────────────────────────────────────────────────────────────
INSTALL_DIR="/opt/hms"
PHP_VERSION="8.2"
NODE_VERSION="20"
LOG_FILE="/var/log/hms_install.log"

# ── Root / OS check ───────────────────────────────────────────────────────────
[[ $EUID -ne 0 ]] && error "Must run as root. Use: sudo bash install.sh"
[[ ! -f /etc/os-release ]] && error "Cannot detect OS"
. /etc/os-release
[[ "$ID" != "ubuntu" ]] && error "Only Ubuntu is supported"

# ─────────────────────────────────────────────────────────────────────────────
#  BANNER
# ─────────────────────────────────────────────────────────────────────────────
clear
echo -e "${BOLD}${CYAN}"
cat << 'EOF'

  ██╗  ██╗███╗   ███╗███████╗
  ██║  ██║████╗ ████║██╔════╝
  ███████║██╔████╔██║███████╗
  ██╔══██║██║╚██╔╝██║╚════██║
  ██║  ██║██║ ╚═╝ ██║███████║
  ╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝

   Unified Installer
EOF
echo -e "${RESET}"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 0 — Choose install mode
# ─────────────────────────────────────────────────────────────────────────────
step "0 — Choose Installation Mode"

echo -e "  ${BOLD}1)${RESET} IPTV Management only"
echo -e "     Installs the IPTV panel (Xtream Codes management, Android TV"
echo -e "     device registration, channel/VOD push). No hotel PMS features."
echo ""
echo -e "  ${BOLD}2)${RESET} Hotel Management System (HMS) only"
echo -e "     Full hotel PMS: reservations, billing, housekeeping, staff,"
echo -e "     POS, reports. No IPTV panel."
echo ""
echo -e "  ${BOLD}3)${RESET} Both — IPTV + HMS (full install)"
echo -e "     Everything: hotel PMS + IPTV management + Android TV API."
echo ""

while true; do
    read -rp "Enter choice [1/2/3]: " MODE_CHOICE
    case "$MODE_CHOICE" in
        1) INSTALL_MODE="iptv";  MODE_LABEL="IPTV Management only";        break ;;
        2) INSTALL_MODE="hms";   MODE_LABEL="Hotel Management System only"; break ;;
        3) INSTALL_MODE="both";  MODE_LABEL="IPTV + HMS (full install)";    break ;;
        *) warn "Please enter 1, 2, or 3." ;;
    esac
done

echo ""
success "Selected: ${MODE_LABEL}"
echo ""

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 1 — System packages
# ─────────────────────────────────────────────────────────────────────────────
step "1 — System Packages"

export DEBIAN_FRONTEND=noninteractive
NEED_PKG=""

# PHP
if has_cmd php; then
    PHP_CURRENT=$(php -r "echo PHP_VERSION;" 2>/dev/null | cut -d. -f1-2)
    if [[ "$PHP_CURRENT" != "8.2" && "$PHP_CURRENT" != "8.3" && "$PHP_CURRENT" != "8.4" ]]; then
        NEED_PKG="$NEED_PKG php${PHP_VERSION}"
    fi
else
    NEED_PKG="$NEED_PKG php${PHP_VERSION}"
fi

has_cmd nginx    || NEED_PKG="$NEED_PKG nginx"
has_cmd mysql    || NEED_PKG="$NEED_PKG mysql-server"
has_cmd composer || NEED_PKG="$NEED_PKG composer"

# Node.js only needed for HMS or Both (front-end Vite build)
if [[ "$INSTALL_MODE" != "iptv" ]]; then
    has_cmd node || NEED_PKG="$NEED_PKG nodejs"
fi

if [[ -z "$NEED_PKG" ]]; then
    success "All dependencies already installed"
else
    info "Installing:$NEED_PKG"
    apt-get update -y 2>&1 | grep -v "^Get:\|^Hit:" || true

    [[ "$NEED_PKG" == *"php"* ]] && {
        info "Adding PHP PPA..."
        add-apt-repository -y ppa:ondrej/php
        apt-get update -y 2>&1 | grep -v "^Get:\|^Hit:" || true
    }

    [[ "$NEED_PKG" == *"nodejs"* ]] && {
        info "Setting up Node.js ${NODE_VERSION} repository..."
        curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - >/dev/null 2>&1
        apt-get update -y 2>&1 | grep -v "^Get:\|^Hit:" || true
    }

    apt-get install -y software-properties-common curl wget git unzip zip bc gnupg2 ca-certificates 2>&1 | grep -E "^(Setting|Processing|Done)" || true

    [[ "$NEED_PKG" == *"php"* ]] && {
        info "Installing PHP ${PHP_VERSION}..."
        update-alternatives --remove php /usr/bin/php8.1 2>/dev/null || true
        apt-get update -y 2>&1 | grep -v "^Get:\|^Hit:" || true
        apt-get install -y php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli 2>&1 | tail -3 || true
        for ext in mysql mbstring xml zip curl gd intl dom; do
            apt-get install -y "php${PHP_VERSION}-${ext}" 2>&1 | grep -E "(Setting|done)" | tail -1 || true
        done
        update-alternatives --install /usr/bin/php php /usr/bin/php${PHP_VERSION} 1000 --force >/dev/null 2>&1 || true
        success "PHP ${PHP_VERSION} installed"
    }

    [[ "$NEED_PKG" == *"nginx"* ]] && {
        apt-get install -y nginx 2>&1 | tail -2 || true
        systemctl enable nginx >/dev/null 2>&1
        systemctl start  nginx >/dev/null 2>&1
        success "Nginx installed"
    }

    [[ "$NEED_PKG" == *"mysql"* ]] && {
        apt-get install -y mysql-server 2>&1 | tail -2 || true
        systemctl enable mysql >/dev/null 2>&1
        systemctl start  mysql >/dev/null 2>&1
        success "MySQL installed"
    }

    [[ "$NEED_PKG" == *"nodejs"* ]] && {
        apt-get install -y nodejs 2>&1 | tail -2 || true
        success "Node.js installed"
    }

    [[ "$NEED_PKG" == *"composer"* ]] && {
        info "Installing Composer..."
        EXPECTED_CHECKSUM="$(curl -s https://composer.github.io/installer.sig)"
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" || error "Failed to download Composer"
        ACTUAL_CHECKSUM="$(php -r "echo hash_file('SHA384', 'composer-setup.php');")"
        [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]] && { rm composer-setup.php; error "Composer checksum mismatch"; }
        php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
        rm -f composer-setup.php
        success "Composer installed"
    }

    success "All packages installed"
fi

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 2 — Configuration prompts
# ─────────────────────────────────────────────────────────────────────────────
step "2 — Configuration"

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

read -rp "Cloudflare Tunnel / Public URL (optional, leave blank): " TUNNEL_URL
TUNNEL_URL="${TUNNEL_URL:-}"

# HMS-specific prompts
if [[ "$INSTALL_MODE" == "hms" || "$INSTALL_MODE" == "both" ]]; then
    echo ""
    read -rp "Hotel Name [Grand Hotel]: "       HOTEL_NAME;    HOTEL_NAME="${HOTEL_NAME:-Grand Hotel}"
    read -rp "Hotel Email [info@hotel.com]: "   HOTEL_EMAIL;   HOTEL_EMAIL="${HOTEL_EMAIL:-info@hotel.com}"
    read -rp "Hotel Phone [+1234567890]: "       HOTEL_PHONE;   HOTEL_PHONE="${HOTEL_PHONE:-+1234567890}"
    read -rp "Hotel Address [123 Hotel St]: "   HOTEL_ADDRESS; HOTEL_ADDRESS="${HOTEL_ADDRESS:-123 Hotel St}"
else
    HOTEL_NAME="IPTV Server"
    HOTEL_EMAIL="admin@iptv.local"
    HOTEL_PHONE=""
    HOTEL_ADDRESS=""
fi

echo ""
read -rp "Database name [hms_db]: "   DB_DATABASE; DB_DATABASE="${DB_DATABASE:-hms_db}"
read -rp "Database user [hms_user]: " DB_USERNAME; DB_USERNAME="${DB_USERNAME:-hms_user}"

RAND_PASS=$(gen_password)
printf "Database password [auto, press Enter]: " >/dev/tty
DB_PASSWORD=""; read -rs DB_PASSWORD </dev/tty || true; echo "" >/dev/tty
DB_PASSWORD="${DB_PASSWORD:-$RAND_PASS}"

read -rp "License server [https://kewirdev.com/api/license]: " LICENSE_SERVER_URL
LICENSE_SERVER_URL="${LICENSE_SERVER_URL:-https://kewirdev.com/api/license}"

echo ""
echo -e "${BOLD}Summary:${RESET}"
echo "  Mode    : ${MODE_LABEL}"
echo "  URL     : ${APP_URL}"
[[ "$INSTALL_MODE" != "iptv" ]] && echo "  Hotel   : ${HOTEL_NAME}"
echo "  DB      : ${DB_DATABASE}"
echo ""
read -rp "Proceed? (y/n) [y]: " CONFIRM
[[ ! "$CONFIRM" =~ ^[Yy]?$ ]] && { info "Cancelled"; exit 0; }

touch "$LOG_FILE"
exec > >(tee -a "$LOG_FILE") 2>&1
info "Installation started — mode=${INSTALL_MODE}"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 3 — Database
# ─────────────────────────────────────────────────────────────────────────────
step "3 — Database Setup"

mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS '${DB_USERNAME}'@'localhost';
CREATE USER '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';
FLUSH PRIVILEGES;
SQL
success "Database created"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 4 — Application files
# ─────────────────────────────────────────────────────────────────────────────
step "4 — Application Files"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[[ ! -f "${SCRIPT_DIR}/artisan" ]] && error "Run from the project root (where artisan lives)"

mkdir -p "${INSTALL_DIR}"
rsync -a --exclude='.git' --exclude='node_modules' --exclude='vendor' \
         --exclude='.env' --exclude='storage/logs' --exclude='*.md' \
         --exclude='install.sh' --exclude='uninstall.sh' \
         "${SCRIPT_DIR}/" "${INSTALL_DIR}/"

cd "${INSTALL_DIR}"
chown -R www-data:www-data "${INSTALL_DIR}"
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;
chmod -R 775 "${INSTALL_DIR}/storage" "${INSTALL_DIR}/bootstrap/cache"
chmod +x "${INSTALL_DIR}/artisan"
success "Files deployed to ${INSTALL_DIR}"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 5 — .env
# ─────────────────────────────────────────────────────────────────────────────
step "5 — Environment Configuration"

APP_KEY="base64:$(dd if=/dev/urandom bs=32 count=1 2>/dev/null | base64 | tr -d '\n')"

# Feature flags driven by install mode
ENABLE_HMS="false";  ENABLE_IPTV="false"
[[ "$INSTALL_MODE" == "hms"  || "$INSTALL_MODE" == "both" ]] && ENABLE_HMS="true"
[[ "$INSTALL_MODE" == "iptv" || "$INSTALL_MODE" == "both" ]] && ENABLE_IPTV="true"

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
SESSION_SECURE_COOKIE=false

TUNNEL_URL=${TUNNEL_URL}

HOTEL_NAME="${HOTEL_NAME}"
HOTEL_EMAIL="${HOTEL_EMAIL}"
HOTEL_PHONE="${HOTEL_PHONE}"
HOTEL_ADDRESS="${HOTEL_ADDRESS}"

LICENSE_SERVER_URL=${LICENSE_SERVER_URL}
LICENSE_SIGNATURE_SECRET=E0FMIZdSNTtywmB6psxK4pqWSVRs8eo1ogPsePEmzXU=

MAIL_FROM_ADDRESS="${HOTEL_EMAIL}"
MAIL_FROM_NAME="${HOTEL_NAME}"

# ── Install mode flags (read by the application) ──────────────────────────
INSTALL_MODE=${INSTALL_MODE}
FEATURE_HMS=${ENABLE_HMS}
FEATURE_IPTV=${ENABLE_IPTV}
ENDENV

chown www-data:www-data "${INSTALL_DIR}/.env"
chmod 640 "${INSTALL_DIR}/.env"
success ".env written (INSTALL_MODE=${INSTALL_MODE})"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 6 — Dependencies & build
# ─────────────────────────────────────────────────────────────────────────────
step "6 — Dependencies"

info "Composer install..."
sudo -u www-data composer update --no-dev --no-interaction --optimize-autoloader --prefer-dist 2>&1 | tail -5 || true
sudo -u www-data composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist 2>&1 | tail -3 || true
sudo -u www-data php artisan key:generate --force

# Front-end build only needed for HMS or Both (IPTV-only has no Blade UI)
if [[ "$INSTALL_MODE" != "iptv" ]]; then
    info "npm install & build..."
    npm install --legacy-peer-deps
    NODE_ENV=production npm run build
    rm -rf node_modules
    success "Front-end assets built"
else
    info "Skipping front-end build (IPTV-only mode)"
fi

success "Dependencies installed"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 7 — Migrations & seeders
# ─────────────────────────────────────────────────────────────────────────────
step "7 — Database Migrations"

sudo -u www-data php artisan storage:link --force

info "Running migrations..."
sudo -u www-data php artisan migrate:fresh --seed --force 2>&1 || error "Migrations failed"

# Always seed core data
sudo -u www-data php artisan db:seed --class=SettingsSeeder          --force 2>/dev/null || true
sudo -u www-data php artisan db:seed --class=AdminPermissionsSeeder  --force 2>/dev/null || true
sudo -u www-data php artisan db:seed --class=UserAccountsSeeder      --force 2>/dev/null || true
sudo -u www-data php artisan db:seed --class=LicenseSeeder           --force 2>/dev/null || true

# HMS-specific seeders
if [[ "$INSTALL_MODE" == "hms" || "$INSTALL_MODE" == "both" ]]; then
    info "Seeding HMS data (rooms, staff, permissions)..."
    sudo -u www-data php artisan db:seed --class=ManagerPermissionsSeeder --force 2>/dev/null || true
fi

# IPTV note: device defaults live in SettingsSeeder (already run above)

info "Building caches..."
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:cache

success "Database ready"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 8 — Nginx
# ─────────────────────────────────────────────────────────────────────────────
step "8 — Nginx"

# Choose a descriptive site name
NGINX_SITE="hms"
[[ "$INSTALL_MODE" == "iptv" ]] && NGINX_SITE="iptv"

cat > /etc/nginx/sites-available/${NGINX_SITE} << ENDNGINX
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

ln -sf /etc/nginx/sites-available/${NGINX_SITE} /etc/nginx/sites-enabled/${NGINX_SITE}
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
success "Nginx configured (site: ${NGINX_SITE})"

# ─────────────────────────────────────────────────────────────────────────────
#  STEP 9 — Background services
# ─────────────────────────────────────────────────────────────────────────────
step "9 — Background Services"

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

echo "* * * * * www-data cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1" \
    > /etc/cron.d/hms-scheduler

systemctl daemon-reload
systemctl enable hms-queue --now
success "Queue worker started"

# ─────────────────────────────────────────────────────────────────────────────
#  Save credentials
# ─────────────────────────────────────────────────────────────────────────────
cat > /root/hms_credentials.txt << ENDCREDS
════════════════════════════════════════
  Installation Complete!
  Mode : ${MODE_LABEL}
  Date : $(date)
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

⚙  Install Mode : ${INSTALL_MODE}
   FEATURE_HMS  : ${ENABLE_HMS}
   FEATURE_IPTV : ${ENABLE_IPTV}

⚠️  IMPORTANT: Change admin password immediately!
════════════════════════════════════════
ENDCREDS
chmod 600 /root/hms_credentials.txt

# ─────────────────────────────────────────────────────────────────────────────
#  Done
# ─────────────────────────────────────────────────────────────────────────────
clear
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
echo -e "${BOLD}Mode:${RESET}     ${MODE_LABEL}"
echo -e "${BOLD}URL:${RESET}      ${GREEN}${APP_URL}${RESET}"
echo ""
echo -e "${BOLD}Admin Login:${RESET}"
echo -e "  Email    : admin@hotel.com"
echo -e "  Password : password"
echo ""
echo -e "${BOLD}Database:${RESET}"
echo -e "  Name     : ${DB_DATABASE}"
echo -e "  User     : ${DB_USERNAME}"
echo -e "  Password : ${DB_PASSWORD}"
echo ""
echo -e "${YELLOW}⚠  Change the admin password immediately after first login!${RESET}"
echo -e "   Credentials saved to: /root/hms_credentials.txt"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
