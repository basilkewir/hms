#!/usr/bin/env bash
# =============================================================================
#  Hotel Management System — Ubuntu Installer
#  Supports: Ubuntu 20.04 / 22.04 / 24.04 LTS
# =============================================================================
set -eo pipefail
# NOTE: -u (nounset) intentionally omitted — vars may be conditionally set.
# NOTE: pipefail kept but we use dd+base64 for random passwords to avoid
#       the tr|head SIGPIPE issue that caused silent exits in earlier versions.

# ─── Colours ─────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; exit 1; }
step()    {
    echo -e "\n${BOLD}${CYAN}══════════════════════════════════════════${RESET}"
    echo -e "${BOLD} $*${RESET}"
    echo -e "${BOLD}${CYAN}══════════════════════════════════════════${RESET}"
}

# ─── ASCII Banner ─────────────────────────────────────────────────────────────
banner() {
cat <<'EOF'

  ██╗  ██╗███╗   ███╗███████╗
  ██║  ██║████╗ ████║██╔════╝
  ███████║██╔████╔██║███████╗
  ██╔══██║██║╚██╔╝██║╚════██║
  ██║  ██║██║ ╚═╝ ██║███████║
  ╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝
   Hotel Management System
   Ubuntu One-Command Installer
   ─────────────────────────────

EOF
}

# ─── Safe random password generator ──────────────────────────────────────────
# Uses dd+base64 — avoids the tr|head SIGPIPE issue under set -eo pipefail
# which was the root cause of the silent exit bug.
gen_password() {
    local raw
    raw=$(dd if=/dev/urandom bs=64 count=1 2>/dev/null | base64 | tr -d '/+=' | tr -d '\n')
    echo "${raw:0:20}"
}

# ─── Constants ────────────────────────────────────────────────────────────────
INSTALL_DIR="/opt/hms"
NGINX_SITE="hms"
SERVICE_NAME="hms-queue"
PHP_VERSION="8.2"
NODE_VERSION="20"
MIN_RAM_MB=512
LOG_FILE="/var/log/hms_install.log"

# ─── Root check ───────────────────────────────────────────────────────────────
[[ $EUID -ne 0 ]] && error "This installer must be run as root. Use: sudo bash install.sh"

# ─── OS check ─────────────────────────────────────────────────────────────────
if [[ -f /etc/os-release ]]; then
    . /etc/os-release
else
    error "Cannot detect OS. /etc/os-release not found."
fi
[[ "$ID" != "ubuntu" ]] && error "Only Ubuntu is supported. Detected: ${ID:-unknown}"
VER_OK=$(awk "BEGIN{print (${VERSION_ID:-0} >= 20.04) ? 1 : 0}")
[[ "$VER_OK" -ne 1 ]] && error "Ubuntu 20.04+ required. Detected: ${VERSION_ID:-unknown}"

# ─── RAM check ────────────────────────────────────────────────────────────────
RAM_MB=$(free -m | awk '/Mem:/{print $2}')
if [[ $RAM_MB -lt $MIN_RAM_MB ]]; then
    warn "System has ${RAM_MB}MB RAM. Minimum recommended: ${MIN_RAM_MB}MB."
fi

# =============================================================================
#  PRE-FLIGHT: Detect what is already installed
# =============================================================================
banner

echo -e "${BOLD}${CYAN}══════════════════════════════════════════${RESET}"
echo -e "${BOLD} Pre-flight System Check${RESET}"
echo -e "${BOLD}${CYAN}══════════════════════════════════════════${RESET}"
echo ""

has_cmd() { command -v "$1" &>/dev/null; }

NEED_PHP=false
NEED_NGINX=false
NEED_MYSQL=false
NEED_NODE=false
NEED_COMPOSER=false
MYSQL_EXISTS=false

# PHP
if has_cmd "php${PHP_VERSION}"; then
    echo -e "  ${GREEN}✓${RESET}  PHP ${PHP_VERSION}: $(php${PHP_VERSION} -r 'echo PHP_VERSION;' 2>/dev/null)"
elif has_cmd php && php -r "exit(PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 2 ? 0 : 1);" 2>/dev/null; then
    echo -e "  ${GREEN}✓${RESET}  PHP: $(php -r 'echo PHP_VERSION;' 2>/dev/null)"
else
    echo -e "  ${RED}✗${RESET}  PHP ${PHP_VERSION}: NOT installed — will be installed"
    NEED_PHP=true
fi

# Nginx
if has_cmd nginx; then
    echo -e "  ${GREEN}✓${RESET}  Nginx: $(nginx -v 2>&1)"
else
    echo -e "  ${RED}✗${RESET}  Nginx: NOT installed — will be installed"
    NEED_NGINX=true
fi

# MySQL
if has_cmd mysql && (has_cmd mysqld || has_cmd mysqld_safe || systemctl list-units --type=service 2>/dev/null | grep -q mysql); then
    echo -e "  ${GREEN}✓${RESET}  MySQL: $(mysql --version 2>/dev/null | awk '{print $1,$2,$3}')"
    MYSQL_EXISTS=true
else
    echo -e "  ${RED}✗${RESET}  MySQL: NOT installed — will be installed"
    NEED_MYSQL=true
fi

# Node.js
NODE_OK=false
if has_cmd node; then
    NODE_MAJOR=$(node -v 2>/dev/null | tr -d 'v' | cut -d. -f1 || echo "0")
    if [[ "$NODE_MAJOR" -ge "$NODE_VERSION" ]]; then
        echo -e "  ${GREEN}✓${RESET}  Node.js: $(node -v)"
        NODE_OK=true
    else
        echo -e "  ${YELLOW}!${RESET}  Node.js: $(node -v) — too old (need v${NODE_VERSION}+) — will upgrade"
    fi
fi
[[ "$NODE_OK" == false ]] && NEED_NODE=true

# Composer
if has_cmd composer; then
    echo -e "  ${GREEN}✓${RESET}  Composer: $(composer --version --no-ansi 2>/dev/null | head -1)"
else
    echo -e "  ${RED}✗${RESET}  Composer: NOT installed — will be installed"
    NEED_COMPOSER=true
fi

echo ""
if [[ "$NEED_PHP" == false && "$NEED_NGINX" == false && "$NEED_MYSQL" == false && \
      "$NEED_NODE" == false && "$NEED_COMPOSER" == false ]]; then
    echo -e "  ${GREEN}All dependencies already installed.${RESET}"
else
    echo -e "  ${YELLOW}Missing dependencies will be installed automatically in Step 1.${RESET}"
fi
echo ""

# =============================================================================
#  STEP 0 — Interactive Setup
# =============================================================================
step "0/8 — Installation Setup"
echo ""

# ─── Domain / IP ─────────────────────────────────────────────────────────────
SERVER_IP=$(hostname -I | awk '{print $1}')
read -rp "$(echo -e "${BOLD}Enter your domain or server IP${RESET} [${SERVER_IP}]: ")" APP_DOMAIN
APP_DOMAIN="${APP_DOMAIN:-$SERVER_IP}"

# ─── HTTPS ────────────────────────────────────────────────────────────────────
read -rp "$(echo -e "${BOLD}Use HTTPS? (yes/no)${RESET} [no]: ")" USE_HTTPS
USE_HTTPS="${USE_HTTPS:-no}"
if [[ "$USE_HTTPS" =~ ^[Yy] ]]; then
    APP_URL="https://${APP_DOMAIN}"
    INSTALL_SSL=true
else
    APP_URL="http://${APP_DOMAIN}"
    INSTALL_SSL=false
fi

# ─── Hotel Details ────────────────────────────────────────────────────────────
echo ""
read -rp "$(echo -e "${BOLD}Hotel Name${RESET} [Grand Hotel]: ")" HOTEL_NAME
HOTEL_NAME="${HOTEL_NAME:-Grand Hotel}"

read -rp "$(echo -e "${BOLD}Hotel Email${RESET} [info@hotel.com]: ")" HOTEL_EMAIL
HOTEL_EMAIL="${HOTEL_EMAIL:-info@hotel.com}"

read -rp "$(echo -e "${BOLD}Hotel Phone${RESET} [+1234567890]: ")" HOTEL_PHONE
HOTEL_PHONE="${HOTEL_PHONE:-+1234567890}"

read -rp "$(echo -e "${BOLD}Hotel Address${RESET} [123 Hotel Street, City]: ")" HOTEL_ADDRESS
HOTEL_ADDRESS="${HOTEL_ADDRESS:-123 Hotel Street, City}"

read -rp "$(echo -e "${BOLD}Hotel Logo image path${RESET} [leave blank to skip]: ")" HOTEL_LOGO_PATH
HOTEL_LOGO_PATH="${HOTEL_LOGO_PATH:-}"

# ─── Database Setup ───────────────────────────────────────────────────────────
echo ""
info "Database Configuration"
read -rp "$(echo -e "${BOLD}Database name${RESET} [hms_db]: ")" DB_DATABASE
DB_DATABASE="${DB_DATABASE:-hms_db}"

read -rp "$(echo -e "${BOLD}Database user${RESET} [hms_user]: ")" DB_USERNAME
DB_USERNAME="${DB_USERNAME:-hms_user}"

# Safe password generation (dd+base64 avoids SIGPIPE from tr|head)
RAND_PASS=$(gen_password)

# Silent password read — write prompt and read via /dev/tty to bypass any redirects
printf "${BOLD}Database password${RESET} [auto-generated, press Enter to use]: " > /dev/tty
DB_PASSWORD=""
read -rs DB_PASSWORD < /dev/tty || true
echo "" > /dev/tty
DB_PASSWORD="${DB_PASSWORD:-$RAND_PASS}"

# ─── MySQL root password (only when MySQL already exists) ─────────────────────
MYSQL_ROOT_PASS=""
MYSQL_ROOT_ARGS="-u root"

if [[ "$MYSQL_EXISTS" == true ]]; then
    echo ""
    info "MySQL is already installed on this server."
    # Test socket/no-password auth first
    if mysql -u root -e "SELECT 1;" &>/dev/null 2>&1; then
        info "MySQL root: using Unix socket auth (no password needed)"
    else
        echo ""
        printf "${BOLD}MySQL root password${RESET} (needed to create the HMS database): " > /dev/tty
        read -rs MYSQL_ROOT_PASS < /dev/tty || true
        echo "" > /dev/tty
        # Verify the password
        if ! mysql -u root -p"${MYSQL_ROOT_PASS}" -e "SELECT 1;" &>/dev/null 2>&1; then
            echo ""
            error "MySQL root password incorrect. Re-run the installer with the correct password."
        fi
        MYSQL_ROOT_ARGS="-u root -p${MYSQL_ROOT_PASS}"
        info "MySQL root password verified."
    fi
fi

# ─── Check if target database already exists ──────────────────────────────────
DB_EXISTS=false
RECREATE_DB="no"
if [[ "$MYSQL_EXISTS" == true ]]; then
    DB_CHECK=$(mysql ${MYSQL_ROOT_ARGS} -e "SHOW DATABASES LIKE '${DB_DATABASE}';" \
               --silent --skip-column-names 2>/dev/null || true)
    if [[ -n "$DB_CHECK" ]]; then
        DB_EXISTS=true
        echo ""
        warn "Database '${DB_DATABASE}' already exists on this server."
        read -rp "$(echo -e "${BOLD}Drop and recreate it? (yes/no)${RESET} [no]: ")" RECREATE_DB
        RECREATE_DB="${RECREATE_DB:-no}"
    fi
fi

# ─── License Server ───────────────────────────────────────────────────────────
echo ""
read -rp "$(echo -e "${BOLD}License server URL${RESET} [https://kewirdev.com/api/license]: ")" LICENSE_SERVER_URL
LICENSE_SERVER_URL="${LICENSE_SERVER_URL:-https://kewirdev.com/api/license}"

# ─── Confirmation ─────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}─── Installation Summary ───────────────────────────────────${RESET}"
echo -e "  Install directory : ${INSTALL_DIR}"
echo -e "  App URL           : ${APP_URL}"
echo -e "  Hotel name        : ${HOTEL_NAME}"
echo -e "  Database name     : ${DB_DATABASE}"
echo -e "  Database user     : ${DB_USERNAME}"
echo -e "  PHP version       : ${PHP_VERSION}"
echo -e "  Node.js version   : ${NODE_VERSION}"
echo -e "  SSL               : ${INSTALL_SSL}"
echo -e "  License server    : ${LICENSE_SERVER_URL}"
echo -e "${BOLD}────────────────────────────────────────────────────────────${RESET}"
echo ""
echo -e "${BOLD}─── Will be installed / configured ─────────────────────────${RESET}"
echo -e "  ${CYAN}•${RESET}  Full system update (apt update + upgrade)"
[[ "$NEED_PHP"      == true ]] && echo -e "  ${CYAN}•${RESET}  PHP ${PHP_VERSION} + extensions"
[[ "$NEED_NGINX"    == true ]] && echo -e "  ${CYAN}•${RESET}  Nginx web server"
[[ "$NEED_MYSQL"    == true ]] && echo -e "  ${CYAN}•${RESET}  MySQL server"
[[ "$NEED_NODE"     == true ]] && echo -e "  ${CYAN}•${RESET}  Node.js ${NODE_VERSION}"
[[ "$NEED_COMPOSER" == true ]] && echo -e "  ${CYAN}•${RESET}  Composer"
echo -e "  ${CYAN}•${RESET}  HMS application → ${INSTALL_DIR}"
echo -e "  ${CYAN}•${RESET}  Database: ${DB_DATABASE} (user: ${DB_USERNAME})"
echo -e "  ${CYAN}•${RESET}  Nginx virtual host for ${APP_DOMAIN}"
echo -e "  ${CYAN}•${RESET}  UFW firewall (ports 22, 80, 443)"
echo -e "  ${CYAN}•${RESET}  Systemd queue-worker service"
echo -e "${BOLD}────────────────────────────────────────────────────────────${RESET}"
echo ""
read -rp "$(echo -e "${BOLD}Proceed with installation? (yes/no)${RESET} [yes]: ")" CONFIRM
CONFIRM="${CONFIRM:-yes}"
[[ ! "$CONFIRM" =~ ^[Yy] ]] && { info "Installation cancelled."; exit 0; }

# ─── Start logging NOW — after all interactive prompts ────────────────────────
# exec > >(tee) makes stdout a pipe. Pipe stdout breaks read -rs (silent reads).
# By deferring it until here, all password prompts run on the real terminal.
touch "$LOG_FILE"
exec > >(tee -a "$LOG_FILE") 2>&1
echo ""
info "Installation started at $(date)"
info "Log: ${LOG_FILE}"

# =============================================================================
#  STEP 1 — System Packages
# =============================================================================
step "1/8 — Installing System Dependencies"

export DEBIAN_FRONTEND=noninteractive

info "Running apt update..."
apt-get update -y

info "Running apt upgrade (may take a few minutes on a fresh server)..."
apt-get upgrade -y -o Dpkg::Options::="--force-confold"

# Always ensure base utilities are present
info "Installing base utilities..."
apt-get install -y \
    software-properties-common \
    curl \
    wget \
    git \
    unzip \
    zip \
    bc \
    gnupg2 \
    ca-certificates \
    lsb-release \
    apt-transport-https \
    rsync \
    file

# ─── PHP ──────────────────────────────────────────────────────────────────────
if [[ "$NEED_PHP" == true ]]; then
    info "Adding PHP ${PHP_VERSION} PPA (Ondrej)..."
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
    info "Installing PHP ${PHP_VERSION} and extensions..."
    apt-get install -y \
        php${PHP_VERSION} \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-redis \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-tokenizer \
        php${PHP_VERSION}-fileinfo
    success "PHP ${PHP_VERSION} installed"
else
    success "PHP ${PHP_VERSION} already installed — skipping"
fi

# ─── Nginx ────────────────────────────────────────────────────────────────────
if [[ "$NEED_NGINX" == true ]]; then
    info "Installing Nginx..."
    apt-get install -y nginx
    systemctl enable nginx --now
    success "Nginx installed"
else
    success "Nginx already installed — skipping"
    systemctl enable nginx --now 2>/dev/null || true
fi

# ─── MySQL ────────────────────────────────────────────────────────────────────
if [[ "$NEED_MYSQL" == true ]]; then
    info "Installing MySQL server..."
    apt-get install -y mysql-server
    systemctl enable mysql --now
    # After fresh install, root uses socket auth — set MYSQL_ROOT_ARGS accordingly
    MYSQL_ROOT_ARGS="-u root"
    success "MySQL installed"
else
    success "MySQL already installed — skipping"
    systemctl enable mysql --now 2>/dev/null || true
fi

# ─── Node.js ──────────────────────────────────────────────────────────────────
if [[ "$NEED_NODE" == true ]]; then
    info "Setting up Node.js ${NODE_VERSION} repository..."
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_VERSION}.x" | bash -
    info "Installing Node.js ${NODE_VERSION}..."
    apt-get install -y nodejs
    success "Node.js installed: $(node -v)"
else
    success "Node.js already installed: $(node -v)"
fi

# ─── Composer ─────────────────────────────────────────────────────────────────
if [[ "$NEED_COMPOSER" == true ]]; then
    info "Installing Composer..."
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
        rm -f composer-setup.php
        error "Composer installer checksum mismatch! Possible download error."
    fi
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f composer-setup.php
    success "Composer installed: $(composer --version --no-ansi | head -1)"
else
    success "Composer already installed — skipping"
fi

# =============================================================================
#  STEP 2 — Database Setup
# =============================================================================
step "2/8 — Setting Up Database"

# Drop database if user requested recreation
if [[ "$DB_EXISTS" == true && "$RECREATE_DB" =~ ^[Yy] ]]; then
    info "Dropping existing database '${DB_DATABASE}'..."
    mysql ${MYSQL_ROOT_ARGS} -e "DROP DATABASE IF EXISTS \`${DB_DATABASE}\`;" 2>/dev/null
    success "Database '${DB_DATABASE}' dropped"
fi

# Create database + HMS user
mysql ${MYSQL_ROOT_ARGS} <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS '${DB_USERNAME}'@'localhost';
CREATE USER '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';
FLUSH PRIVILEGES;
SQL
success "Database '${DB_DATABASE}' ready, user '${DB_USERNAME}' granted access"

# =============================================================================
#  STEP 3 — Deploy Application Files
# =============================================================================
step "3/8 — Deploying Application"

mkdir -p "${INSTALL_DIR}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ ! -f "${SCRIPT_DIR}/artisan" ]]; then
    error "Run this installer from the project root directory (where artisan lives)."
fi

info "Copying application files to ${INSTALL_DIR}..."
rsync -a \
      --exclude='.git' \
      --exclude='node_modules' \
      --exclude='vendor' \
      --exclude='.env' \
      --exclude='storage/logs/*.log' \
      --exclude='*.md' \
      --exclude='install.sh' \
      --exclude='check_license.php' \
      --exclude='*.py' \
      --exclude='*.bat' \
      --exclude='*.rsc' \
      --exclude='*.ps1' \
      "${SCRIPT_DIR}/" "${INSTALL_DIR}/"
success "Files copied to ${INSTALL_DIR}"

cd "${INSTALL_DIR}"
chown -R www-data:www-data "${INSTALL_DIR}"
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
chmod -R 775 "${INSTALL_DIR}/storage" "${INSTALL_DIR}/bootstrap/cache"
chmod +x "${INSTALL_DIR}/artisan"

# =============================================================================
#  STEP 4 — Environment Configuration (.env)
# =============================================================================
step "4/8 — Writing .env Configuration"

APP_KEY_PLACEHOLDER="base64:$(dd if=/dev/urandom bs=32 count=1 2>/dev/null | base64 | tr -d '\n')"

cat > "${INSTALL_DIR}/.env" <<ENVEOF
APP_NAME="Hotel Management System"
APP_ENV=production
APP_KEY=${APP_KEY_PLACEHOLDER}
APP_DEBUG=false
APP_URL=${APP_URL}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=${APP_DOMAIN}
SESSION_SECURE_COOKIE=${INSTALL_SSL}

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="${HOTEL_EMAIL}"
MAIL_FROM_NAME="${HOTEL_NAME}"

# Hotel Automation Settings
HOTEL_NO_SHOW_HOURS=2
HOTEL_AUTO_CHECKOUT_HOUR=11
HOTEL_PENDING_CONFIRMATION_HOURS=24
HOTEL_ENABLE_NOTIFICATIONS=true
HOTEL_ENABLE_ROOM_UPDATES=true
HOTEL_ENABLE_HOUSEKEEPING_TASKS=true

# Hotel Check-in/Check-out Settings
HOTEL_CHECK_IN_TIME=14:00
HOTEL_EARLIEST_CHECK_IN=12:00
HOTEL_LATEST_CHECK_IN=22:00
HOTEL_CHECK_OUT_TIME=11:00
HOTEL_LATEST_CHECK_OUT=12:00

# Hotel Policies
HOTEL_NO_SHOW_CHARGE=first_night
HOTEL_NO_SHOW_CHARGE_PERCENTAGE=100
HOTEL_NO_SHOW_CHARGE_FIXED=0
HOTEL_CANCELLATION_POLICY=flexible
HOTEL_CANCELLATION_DEADLINE_HOURS=24

# Hotel Notification Settings
HOTEL_EMAIL_NOTIFICATIONS=true
HOTEL_FROM_EMAIL=${HOTEL_EMAIL}
HOTEL_FROM_NAME="${HOTEL_NAME}"
HOTEL_SMS_NOTIFICATIONS=false
HOTEL_SMS_PROVIDER=twilio
HOTEL_IN_APP_NOTIFICATIONS=true

# Hotel Room Management
HOTEL_AUTO_RELEASE_NO_SHOW=true
HOTEL_AUTO_SET_NEEDS_CLEANING=true
HOTEL_ROOM_BUFFER_TIME=30

# Hotel Reporting Settings
HOTEL_DAILY_SUMMARY_TIME=23:59
HOTEL_WEEKLY_SUMMARY_DAY=sunday
HOTEL_MONTHLY_SUMMARY_DAY=1

# Hotel Specific Settings
HOTEL_NAME="${HOTEL_NAME}"
HOTEL_ADDRESS="${HOTEL_ADDRESS}"
HOTEL_PHONE="${HOTEL_PHONE}"
HOTEL_EMAIL="${HOTEL_EMAIL}"

# License Server
LICENSE_SERVER_URL=${LICENSE_SERVER_URL}

# IPTV Integration
IPTV_API_URL=http://localhost:8080/api
IPTV_API_KEY=your-iptv-api-key
IPTV_DEFAULT_CHANNELS=50

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
ENVEOF

chown www-data:www-data "${INSTALL_DIR}/.env"
chmod 640 "${INSTALL_DIR}/.env"
success ".env written"

# =============================================================================
#  STEP 5 — PHP Dependencies & Build
# =============================================================================
step "5/8 — Installing PHP & Node Dependencies"

cd "${INSTALL_DIR}"

info "Running composer install (production)..."
sudo -u www-data composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --prefer-dist
success "Composer dependencies installed"

sudo -u www-data php artisan key:generate --force
success "Application key generated"

info "Running npm install..."
npm install --legacy-peer-deps

info "Building front-end assets (Vite production build)..."
NODE_ENV=production npm run build
success "Front-end assets built"

info "Removing node_modules (not needed at runtime)..."
rm -rf "${INSTALL_DIR}/node_modules"
success "node_modules removed"

# =============================================================================
#  STEP 6 — Database Migrations
# =============================================================================
step "6/8 — Running Database Migrations"

cd "${INSTALL_DIR}"
sudo -u www-data php artisan storage:link --force

# PHP syntax check
info "Checking migration file syntax..."
MIGRATION_DIR="${INSTALL_DIR}/database/migrations"
SYNTAX_ERRORS=0
SYNTAX_BAD_FILES=()
while IFS= read -r -d '' FILE; do
    if ! php -l "$FILE" >/dev/null 2>&1; then
        SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
        SYNTAX_BAD_FILES+=("$(basename "$FILE")")
    fi
done < <(find "$MIGRATION_DIR" -name "*.php" -print0)

if [[ $SYNTAX_ERRORS -gt 0 ]]; then
    echo -e "${RED}${BOLD}Migration syntax errors found:${RESET}"
    for f in "${SYNTAX_BAD_FILES[@]}"; do echo -e "  ${RED}✗${RESET}  $f"; done
    exit 1
fi
TOTAL_MIGRATIONS=$(find "$MIGRATION_DIR" -name "*.php" | wc -l)
success "Syntax check passed — ${TOTAL_MIGRATIONS} migration files valid"

# Run migrations
info "Running database migrations..."
MIGRATE_OUTPUT=$(sudo -u www-data php artisan migrate --force 2>&1)
MIGRATE_EXIT=$?
echo "$MIGRATE_OUTPUT"
if [[ $MIGRATE_EXIT -ne 0 ]]; then
    echo -e "${RED}${BOLD}Migration FAILED (exit code ${MIGRATE_EXIT})${RESET}"
    echo -e "Fix the error and re-run: sudo bash install.sh"
    echo -e "Log: ${LOG_FILE}"
    exit 1
fi

# Verify core tables
info "Verifying core tables..."
REQUIRED_TABLES=("users" "rooms" "reservations" "guests" "room_types" "roles" "permissions" "settings" "licenses")
TABLE_ERRORS=0
for TABLE in "${REQUIRED_TABLES[@]}"; do
    RESULT=$(mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
        -e "SELECT 1 FROM information_schema.tables WHERE table_schema='${DB_DATABASE}' AND table_name='${TABLE}';" \
        --silent --skip-column-names 2>/dev/null || true)
    if [[ -z "$RESULT" ]]; then
        echo -e "  ${RED}✗${RESET}  Missing: ${TABLE}"; TABLE_ERRORS=$((TABLE_ERRORS + 1))
    else
        echo -e "  ${GREEN}✓${RESET}  ${TABLE}"
    fi
done
[[ $TABLE_ERRORS -gt 0 ]] && error "${TABLE_ERRORS} required table(s) missing after migration."
success "All core tables verified"

# =============================================================================
#  STEP 6b — Essential Seeders
# =============================================================================
step "6b/8 — Seeding Essential Data"

cd "${INSTALL_DIR}"

run_seeder() {
    local CLASS="$1"
    local LABEL="$2"
    info "  Seeding: ${LABEL}..."
    SEED_OUT=$(sudo -u www-data php artisan db:seed --class="${CLASS}" --force 2>&1)
    SEED_EXIT=$?
    if [[ $SEED_EXIT -ne 0 ]]; then
        echo -e "${RED}  ✗ ${LABEL} failed${RESET}"
        echo "$SEED_OUT" | tail -10
        error "Seeder '${CLASS}' failed. Fix the error and re-run: sudo bash install.sh"
    fi
    echo -e "  ${GREEN}✓${RESET}  ${LABEL}"
}

run_seeder "SettingsSeeder"               "Settings & currencies"
run_seeder "AdminPermissionsSeeder"       "Admin permissions"
run_seeder "ManagerPermissionsSeeder"     "Manager permissions"
run_seeder "UserAccountsSeeder"           "User accounts & role assignments"
run_seeder "HotelSeeder"                  "Hotel record"
run_seeder "FloorSeeder"                  "Floors"
run_seeder "BuildingWingSeeder"           "Building wings"
run_seeder "BedTypeSeeder"                "Bed types"
run_seeder "RoomTypeSeeder"               "Room types"
run_seeder "GuestTypeSeeder"              "Guest types"
run_seeder "DepartmentsAndPositionsSeeder" "Departments & positions"
run_seeder "WorkShiftsSeeder"             "Work shifts"
run_seeder "RoomAmenitiesSeeder"          "Room amenities"
run_seeder "HotelServiceSeeder"           "Hotel services"
run_seeder "MaintenanceCategoriesSeeder"  "Maintenance categories"

# Update settings table with hotel details
info "Updating Settings table with hotel details..."
mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" 2>/dev/null <<SQL || true
UPDATE settings SET value='${HOTEL_NAME}'    WHERE \`key\`='hotel_name';
UPDATE settings SET value='${HOTEL_EMAIL}'   WHERE \`key\`='hotel_email';
UPDATE settings SET value='${HOTEL_PHONE}'   WHERE \`key\`='hotel_phone';
UPDATE settings SET value='${HOTEL_ADDRESS}' WHERE \`key\`='hotel_address';
SQL
success "Hotel details written to settings table"

# Upload hotel logo if provided
if [[ -n "${HOTEL_LOGO_PATH}" && -f "${HOTEL_LOGO_PATH}" ]]; then
    info "Encoding and storing hotel logo..."
    LOGO_MIME=$(file --mime-type -b "${HOTEL_LOGO_PATH}")
    LOGO_B64=$(base64 -w 0 "${HOTEL_LOGO_PATH}")
    LOGO_DATA_URI="data:${LOGO_MIME};base64,${LOGO_B64}"
    mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" 2>/dev/null <<LOGSQL || true
INSERT INTO settings (\`key\`, value, type, \`group\`, created_at, updated_at)
  VALUES ('hotel_logo', '${LOGO_DATA_URI}', 'string', 'general', NOW(), NOW())
  ON DUPLICATE KEY UPDATE value='${LOGO_DATA_URI}', updated_at=NOW();
LOGSQL
    success "Hotel logo stored"
elif [[ -n "${HOTEL_LOGO_PATH}" ]]; then
    warn "Logo file '${HOTEL_LOGO_PATH}' not found — skipping"
fi

# Laravel production caches
info "Building Laravel production caches..."
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
sudo -u www-data php artisan optimize
success "Laravel production caches built"

# File permission hardening
info "Hardening file permissions..."
chown -R www-data:www-data "${INSTALL_DIR}"
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;
chmod 640 "${INSTALL_DIR}/.env"
chmod -R ug+w "${INSTALL_DIR}/storage"
chmod -R ug+w "${INSTALL_DIR}/bootstrap/cache"
chmod +x "${INSTALL_DIR}/artisan"
success "File permissions hardened"

# =============================================================================
#  STEP 7 — Nginx Virtual Host
# =============================================================================
step "7/8 — Configuring Nginx"

NGINX_CONF="/etc/nginx/sites-available/${NGINX_SITE}"
cat > "$NGINX_CONF" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${APP_DOMAIN};

    root ${INSTALL_DIR}/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 1024;
    client_max_body_size 100M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/${NGINX_SITE}"
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
success "Nginx virtual host configured"

# Optional SSL
if [[ "$INSTALL_SSL" == true ]]; then
    info "Installing Certbot..."
    apt-get install -y certbot python3-certbot-nginx
    if [[ "$APP_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        warn "Cannot issue SSL for an IP address. Run later: certbot --nginx -d ${APP_DOMAIN}"
    else
        certbot --nginx -d "${APP_DOMAIN}" \
            --non-interactive --agree-tos \
            --email "${HOTEL_EMAIL}" --redirect \
            || warn "SSL failed — site accessible over HTTP."
    fi
fi

# =============================================================================
#  STEP 8 — Services
# =============================================================================
step "8/8 — Setting Up System Services"

cat > "/etc/systemd/system/${SERVICE_NAME}.service" <<UNIT
[Unit]
Description=HMS Laravel Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=${INSTALL_DIR}
ExecStart=/usr/bin/php ${INSTALL_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5
StandardOutput=append:/var/log/hms-queue.log
StandardError=append:/var/log/hms-queue.log

[Install]
WantedBy=multi-user.target
UNIT

echo "* * * * * www-data cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1" \
    > /etc/cron.d/hms-scheduler
chmod 644 /etc/cron.d/hms-scheduler

systemctl daemon-reload
systemctl enable "${SERVICE_NAME}" --now
success "Queue worker service started"

# PHP-FPM tuning
PHP_INI_DIR="/etc/php/${PHP_VERSION}/fpm/conf.d"
cat > "${PHP_INI_DIR}/99-hms.ini" <<PHPINI
; HMS Production Tuning
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
upload_max_filesize=100M
post_max_size=100M
memory_limit=512M
max_execution_time=300
PHPINI
systemctl reload "php${PHP_VERSION}-fpm"
success "PHP-FPM tuned for production"

# UFW Firewall
if command -v ufw &>/dev/null; then
    ufw allow 22/tcp  >/dev/null 2>&1 || true
    ufw allow 80/tcp  >/dev/null 2>&1 || true
    ufw allow 443/tcp >/dev/null 2>&1 || true
    ufw --force enable >/dev/null 2>&1 || true
    success "UFW firewall: SSH, HTTP, HTTPS allowed"
fi

# MySQL security hardening
mysql ${MYSQL_ROOT_ARGS} 2>/dev/null <<SQL2 || true
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
FLUSH PRIVILEGES;
SQL2

# =============================================================================
#  SAVE CREDENTIALS
# =============================================================================
CREDS_FILE="/root/hms_credentials.txt"
cat > "$CREDS_FILE" <<CREDS
====================================================
  Hotel Management System — Installation Credentials
  Generated: $(date)
====================================================

  App URL      : ${APP_URL}
  Install dir  : ${INSTALL_DIR}

  Database
  ─────────────────────────
  Host         : 127.0.0.1
  Database     : ${DB_DATABASE}
  Username     : ${DB_USERNAME}
  Password     : ${DB_PASSWORD}

  Default Admin Login (change immediately!)
  ─────────────────────────
  Email        : admin@hotel.com
  Password     : password

  Service Management
  ─────────────────────────
  Queue worker : systemctl {start|stop|restart|status} ${SERVICE_NAME}
  Nginx        : systemctl {start|stop|reload} nginx
  PHP-FPM      : systemctl {start|stop|reload} php${PHP_VERSION}-fpm
  MySQL        : systemctl {start|stop} mysql

  Log files
  ─────────────────────────
  Install log  : ${LOG_FILE}
  Queue log    : /var/log/hms-queue.log
  Nginx access : /var/log/nginx/access.log
  Nginx error  : /var/log/nginx/error.log
  App log      : ${INSTALL_DIR}/storage/logs/laravel.log

  Useful Commands
  ─────────────────────────
  Restart queue  : systemctl restart ${SERVICE_NAME}
  Clear cache    : cd ${INSTALL_DIR} && php artisan optimize:clear
  Run migrations : cd ${INSTALL_DIR} && php artisan migrate --force
  Artisan tinker : cd ${INSTALL_DIR} && php artisan tinker

====================================================
CREDS
chmod 600 "$CREDS_FILE"

# =============================================================================
#  DONE
# =============================================================================
echo ""
echo -e "${GREEN}${BOLD}"
echo "  ╔══════════════════════════════════════════════════════╗"
echo "  ║   ✓  Hotel Management System installed!              ║"
echo "  ╚══════════════════════════════════════════════════════╝"
echo -e "${RESET}"
echo -e "  ${BOLD}Access your panel:${RESET}  ${CYAN}${APP_URL}${RESET}"
echo -e "  ${BOLD}Admin login:${RESET}        admin@hotel.com  /  password"
echo -e "  ${BOLD}Credentials saved:${RESET}  ${CREDS_FILE}"
echo ""
echo -e "  ${YELLOW}⚠  Change the default admin password immediately!${RESET}"
echo ""
echo -e "  ${BOLD}Manage services:${RESET}"
echo -e "    systemctl status ${SERVICE_NAME}"
echo -e "    systemctl status nginx"
echo ""
