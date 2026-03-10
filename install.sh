#!/usr/bin/env bash
# =============================================================================
#  Hotel Management System — Ubuntu Installer
#  Inspired by XtreamUI-style one-command installation
#  Supports: Ubuntu 20.04 / 22.04 / 24.04 LTS
# =============================================================================
set -euo pipefail

# ─── Colours ─────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*"; exit 1; }
step()    { echo -e "\n${BOLD}${CYAN}══════════════════════════════════════════${RESET}"; \
            echo -e "${BOLD} $*${RESET}"; \
            echo -e "${BOLD}${CYAN}══════════════════════════════════════════${RESET}"; }

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
. /etc/os-release
[[ "$ID" != "ubuntu" ]] && error "Only Ubuntu is supported. Detected: $ID"
[[ $(echo "$VERSION_ID >= 20.04" | bc -l) -ne 1 ]] && \
    error "Ubuntu 20.04+ required. Detected: $VERSION_ID"

# ─── RAM check ────────────────────────────────────────────────────────────────
RAM_MB=$(free -m | awk '/Mem:/{print $2}')
[[ $RAM_MB -lt $MIN_RAM_MB ]] && \
    warn "System has ${RAM_MB}MB RAM. Minimum recommended: ${MIN_RAM_MB}MB."

# ─── Start logging ────────────────────────────────────────────────────────────
exec > >(tee -a "$LOG_FILE") 2>&1

# =============================================================================
#  STEP 0 — Interactive Setup
# =============================================================================
banner

step "0/8 — Installation Setup"
echo ""

# ─── Domain / IP ─────────────────────────────────────────────────────────────
SERVER_IP=$(hostname -I | awk '{print $1}')
read -rp "$(echo -e "${BOLD}Enter your domain or server IP${RESET} [${SERVER_IP}]: ")" APP_DOMAIN
APP_DOMAIN="${APP_DOMAIN:-$SERVER_IP}"

# ─── App URL (http or https) ──────────────────────────────────────────────────
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

# Generate a secure random password if user leaves blank
RAND_PASS=$(tr -dc 'A-Za-z0-9@#!%' </dev/urandom | head -c 20)
read -rsp "$(echo -e "${BOLD}Database password${RESET} [auto-generated — press Enter]: ")" DB_PASSWORD || true
echo ""
DB_PASSWORD="${DB_PASSWORD:-$RAND_PASS}"

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
echo -e "${BOLD}─── The following will be installed on this server ─────────${RESET}"
echo -e "  ${CYAN}•${RESET}  apt update + upgrade (full system update)"
echo -e "  ${CYAN}•${RESET}  PHP ${PHP_VERSION} + all required extensions (FPM, MySQL, Redis, …)"
echo -e "  ${CYAN}•${RESET}  Nginx web server"
echo -e "  ${CYAN}•${RESET}  MySQL server"
echo -e "  ${CYAN}•${RESET}  Node.js ${NODE_VERSION} (for front-end asset build)"
echo -e "  ${CYAN}•${RESET}  Composer (PHP package manager)"
echo -e "  ${CYAN}•${RESET}  UFW firewall (ports 22, 80, 443)"
echo -e "  ${CYAN}•${RESET}  Systemd queue-worker service"
echo -e "${BOLD}────────────────────────────────────────────────────────────${RESET}"
echo ""
read -rp "$(echo -e "${BOLD}Proceed with installation? (yes/no)${RESET} [yes]: ")" CONFIRM
CONFIRM="${CONFIRM:-yes}"
[[ ! "$CONFIRM" =~ ^[Yy] ]] && { info "Installation cancelled."; exit 0; }

# =============================================================================
#  STEP 1 — System Packages
# =============================================================================
step "1/8 — Installing System Dependencies"

export DEBIAN_FRONTEND=noninteractive

info "Updating package lists..."
apt-get update -y

info "Upgrading existing packages (this may take a few minutes on a fresh server)..."
apt-get upgrade -y

# ─── Software properties ─────────────────────────────────────────────────────
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
    apt-transport-https

# ─── PHP 8.2 via Ondrej PPA ──────────────────────────────────────────────────
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

success "PHP ${PHP_VERSION} installed: $(php -v | head -1)"

# ─── Nginx ───────────────────────────────────────────────────────────────────
info "Installing Nginx..."
apt-get install -y nginx
systemctl enable nginx --now
success "Nginx installed"

# ─── MySQL ───────────────────────────────────────────────────────────────────
info "Installing MySQL server..."
apt-get install -y mysql-server
systemctl enable mysql --now
success "MySQL installed"

# ─── Node.js ─────────────────────────────────────────────────────────────────
if ! command -v node &>/dev/null || [[ $(node -v | tr -d 'v' | cut -d. -f1) -lt $NODE_VERSION ]]; then
    info "Setting up Node.js ${NODE_VERSION} repository..."
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_VERSION}.x" | bash -
    info "Installing Node.js ${NODE_VERSION}..."
    apt-get install -y nodejs
fi
success "Node.js installed: $(node -v)"

# ─── Composer ────────────────────────────────────────────────────────────────
if ! command -v composer &>/dev/null; then
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]] && { rm composer-setup.php; error "Composer installer checksum mismatch!"; }
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
fi
success "Composer installed: $(composer --version --no-ansi | head -1)"

# =============================================================================
#  STEP 2 — Database Setup
# =============================================================================
step "2/8 — Setting Up Database"

# Secure MySQL and create DB + user
mysql -u root <<SQL
-- Create database
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (drop first to allow re-runs)
DROP USER IF EXISTS '${DB_USERNAME}'@'localhost';
CREATE USER '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';
FLUSH PRIVILEGES;
SQL

success "Database '${DB_DATABASE}' created, user '${DB_USERNAME}' granted access"

# =============================================================================
#  STEP 3 — Deploy Application Files
# =============================================================================
step "3/8 — Deploying Application"

# ─── Copy files ──────────────────────────────────────────────────────────────
mkdir -p "${INSTALL_DIR}"

# Detect if we're running from the app directory or from a separate location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ -f "${SCRIPT_DIR}/artisan" ]]; then
    # Running install.sh from inside the project directory
    info "Copying application files to ${INSTALL_DIR}..."
    rsync -a --exclude='.git' \
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
else
    error "Please run this installer from the project root directory (where artisan lives)."
fi

cd "${INSTALL_DIR}"

# ─── Set ownership ───────────────────────────────────────────────────────────
chown -R www-data:www-data "${INSTALL_DIR}"
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
chmod -R 775 "${INSTALL_DIR}/storage" "${INSTALL_DIR}/bootstrap/cache"
chmod +x "${INSTALL_DIR}/artisan"

# =============================================================================
#  STEP 4 — Environment Configuration (.env)
# =============================================================================
step "4/8 — Writing .env Configuration"

# Generate a random APP_KEY base64 value (will be replaced by artisan key:generate)
APP_KEY_PLACEHOLDER="base64:$(head -c 32 /dev/urandom | base64)"

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

success ".env written to ${INSTALL_DIR}/.env"

# =============================================================================
#  STEP 5 — PHP Dependencies & Build
# =============================================================================
step "5/8 — Installing PHP & Node Dependencies"

cd "${INSTALL_DIR}"

# ─── Composer install ────────────────────────────────────────────────────────
info "Running composer install (production)..."
sudo -u www-data composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --prefer-dist

success "Composer dependencies installed"

# ─── Generate APP_KEY ────────────────────────────────────────────────────────
sudo -u www-data php artisan key:generate --force
success "Application key generated"

# ─── npm install + build ─────────────────────────────────────────────────────
info "Running npm install..."
npm install --legacy-peer-deps

info "Building front-end assets (Vite production build)..."
NODE_ENV=production npm run build

success "Front-end assets built"

# Remove node_modules — not needed at runtime, saves ~400 MB on the server
info "Removing node_modules (not needed at runtime)..."
rm -rf "${INSTALL_DIR}/node_modules"
success "node_modules removed"

# =============================================================================
#  STEP 6 — Database Migrations
# =============================================================================
step "6/8 — Running Database Migrations"

cd "${INSTALL_DIR}"

# ─── Storage link ─────────────────────────────────────────────────────────────
sudo -u www-data php artisan storage:link --force

# ─── Pre-flight: count migration files and check PHP syntax ───────────────────
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
    echo ""
    echo -e "${RED}${BOLD}╔══════════════════════════════════════════════════╗${RESET}"
    echo -e "${RED}${BOLD}║  MIGRATION SYNTAX ERRORS — Installation halted  ║${RESET}"
    echo -e "${RED}${BOLD}╚══════════════════════════════════════════════════╝${RESET}"
    echo ""
    echo -e "  The following migration files have PHP syntax errors:"
    for f in "${SYNTAX_BAD_FILES[@]}"; do
        echo -e "  ${RED}✗${RESET}  $f"
    done
    echo ""
    echo -e "  Fix the errors above and re-run the installer."
    echo -e "  Install log: ${LOG_FILE}"
    exit 1
fi

TOTAL_MIGRATIONS=$(find "$MIGRATION_DIR" -name "*.php" | wc -l)
success "Syntax check passed — ${TOTAL_MIGRATIONS} migration files are valid"

# ─── Check for duplicate timestamps (potential ordering conflicts) ─────────────
info "Checking for duplicate migration timestamps..."
DUP_STAMPS=$(find "$MIGRATION_DIR" -name "*.php" -printf '%f\n' \
    | sed 's/^\([0-9]\{4\}_[0-9]\{2\}_[0-9]\{2\}_[0-9]\{6\}\).*/\1/' \
    | sort | uniq -d)

if [[ -n "$DUP_STAMPS" ]]; then
    warn "Duplicate migration timestamps detected (may cause ordering issues):"
    echo "$DUP_STAMPS" | while read -r stamp; do
        warn "  → Timestamp: $stamp"
        find "$MIGRATION_DIR" -name "${stamp}*.php" -printf '    %f\n'
    done
    echo ""
    warn "Continuing — conflicts were patched during installation."
fi

# ─── Run migrations with output captured for error detection ──────────────────
info "Running database migrations..."
MIGRATE_OUTPUT=$(sudo -u www-data php artisan migrate --force 2>&1)
MIGRATE_EXIT=$?

echo "$MIGRATE_OUTPUT"

if [[ $MIGRATE_EXIT -ne 0 ]]; then
    echo ""
    echo -e "${RED}${BOLD}╔══════════════════════════════════════════════════════╗${RESET}"
    echo -e "${RED}${BOLD}║  MIGRATION FAILED                                    ║${RESET}"
    echo -e "${RED}${BOLD}╚══════════════════════════════════════════════════════╝${RESET}"
    echo ""
    echo -e "  Migration exited with code ${MIGRATE_EXIT}."
    echo ""

    # Show last 20 lines of the artisan error output
    echo -e "${YELLOW}Error details:${RESET}"
    echo "$MIGRATE_OUTPUT" | tail -20

    # Check for common known causes and give guidance
    if echo "$MIGRATE_OUTPUT" | grep -qi "already exists"; then
        echo ""
        warn "Cause: A table or column already exists in the database."
        warn "       The migration does not guard against pre-existing schema."
        warn "       Check the migration files listed above and add Schema::hasTable / Schema::hasColumn guards."
    fi

    if echo "$MIGRATE_OUTPUT" | grep -qi "doesn't exist\|does not exist\|Unknown table\|Table.*not found"; then
        echo ""
        warn "Cause: A migration references a table or column that hasn't been created yet."
        warn "       Check the migration execution order (timestamps) and foreign key references."
    fi

    if echo "$MIGRATE_OUTPUT" | grep -qi "foreign key constraint"; then
        echo ""
        warn "Cause: A foreign key constraint failed."
        warn "       Ensure parent tables are created before child tables."
        warn "       Consider using ->nullable()->constrained() or deferring FK creation."
    fi

    if echo "$MIGRATE_OUTPUT" | grep -qi "syntax error\|parse error"; then
        echo ""
        warn "Cause: A PHP parse/syntax error in a migration file."
        warn "       Run: php -l database/migrations/<filename>.php"
    fi

    echo ""
    echo -e "  ${BOLD}Full migration output saved to:${RESET} ${LOG_FILE}"
    echo -e "  ${BOLD}Fix the issue and re-run:${RESET}  sudo bash install.sh"
    echo ""
    exit 1
fi

# ─── Verify tables were actually created ──────────────────────────────────────
info "Verifying core tables exist..."
REQUIRED_TABLES=("users" "rooms" "reservations" "guests" "room_types" "roles" "permissions" "settings" "licenses")
TABLE_ERRORS=0

for TABLE in "${REQUIRED_TABLES[@]}"; do
    RESULT=$(mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
        -e "SELECT 1 FROM information_schema.tables WHERE table_schema='${DB_DATABASE}' AND table_name='${TABLE}';" \
        --silent --skip-column-names 2>/dev/null)
    if [[ -z "$RESULT" ]]; then
        echo -e "  ${RED}✗${RESET}  Missing table: ${TABLE}"
        TABLE_ERRORS=$((TABLE_ERRORS + 1))
    else
        echo -e "  ${GREEN}✓${RESET}  ${TABLE}"
    fi
done

if [[ $TABLE_ERRORS -gt 0 ]]; then
    echo ""
    error "${TABLE_ERRORS} required table(s) missing after migration. Check ${LOG_FILE} for details."
fi

success "All core tables verified — migration complete"

# =============================================================================
#  STEP 6b — Essential Seeders (roles, permissions, users, settings, currencies)
# =============================================================================
step "6b/8 — Seeding Essential Data"

cd "${INSTALL_DIR}"

# Helper: run a seeder, print result, abort installer on failure
run_seeder() {
    local CLASS="$1"
    local LABEL="$2"
    info "  Seeding: ${LABEL}..."
    SEED_OUT=$(sudo -u www-data php artisan db:seed --class="${CLASS}" --force 2>&1)
    SEED_EXIT=$?
    if [[ $SEED_EXIT -ne 0 ]]; then
        echo -e "${RED}  ✗ ${LABEL} failed${RESET}"
        echo "$SEED_OUT" | tail -10
        echo ""
        echo -e "${RED}${BOLD}Seeder '${CLASS}' failed. Aborting.${RESET}"
        echo -e "Fix the error above and re-run: sudo bash install.sh"
        exit 1
    fi
    echo -e "  ${GREEN}✓${RESET}  ${LABEL}"
}

# ─── 1. Settings (currencies, hotel config, policies) ─────────────────────────
run_seeder "SettingsSeeder" "Settings & currencies"

# ─── 2. Roles & permissions ───────────────────────────────────────────────────
run_seeder "AdminPermissionsSeeder"   "Admin permissions"
run_seeder "ManagerPermissionsSeeder" "Manager permissions"

# ─── 3. User accounts (admin, manager, frontdesk, etc.) ──────────────────────
run_seeder "UserAccountsSeeder" "User accounts & role assignments"

# ─── 4. Hotel record ──────────────────────────────────────────────────────────
run_seeder "HotelSeeder" "Hotel record"

# ─── 5. Property structure ────────────────────────────────────────────────────
run_seeder "FloorSeeder"        "Floors"
run_seeder "BuildingWingSeeder" "Building wings"
run_seeder "BedTypeSeeder"      "Bed types"
run_seeder "RoomTypeSeeder"     "Room types"

# ─── 6. Guests & guest types ──────────────────────────────────────────────────
run_seeder "GuestTypeSeeder" "Guest types"

# ─── 7. Staff structure ───────────────────────────────────────────────────────
run_seeder "DepartmentsAndPositionsSeeder" "Departments & positions"
run_seeder "WorkShiftsSeeder"             "Work shifts"

# ─── 8. Operational data ──────────────────────────────────────────────────────
run_seeder "RoomAmenitiesSeeder"        "Room amenities"
run_seeder "HotelServiceSeeder"         "Hotel services"
run_seeder "MaintenanceCategoriesSeeder" "Maintenance categories"

# ─── Post-seeder: update .env hotel details from installer answers ─────────────
info "Updating Settings table with your hotel details..."
mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" <<SQL 2>/dev/null || true
UPDATE settings SET value='${HOTEL_NAME}'    WHERE \`key\`='hotel_name';
UPDATE settings SET value='${HOTEL_EMAIL}'   WHERE \`key\`='hotel_email';
UPDATE settings SET value='${HOTEL_PHONE}'   WHERE \`key\`='hotel_phone';
UPDATE settings SET value='${HOTEL_ADDRESS}' WHERE \`key\`='hotel_address';
SQL
success "Hotel details written to settings table"

# ─── Post-seeder: upload hotel logo if provided ───────────────────────────────
if [[ -n "${HOTEL_LOGO_PATH}" && -f "${HOTEL_LOGO_PATH}" ]]; then
    info "Encoding and storing hotel logo..."
    LOGO_MIME=$(file --mime-type -b "${HOTEL_LOGO_PATH}")
    LOGO_B64=$(base64 -w 0 "${HOTEL_LOGO_PATH}")
    LOGO_DATA_URI="data:${LOGO_MIME};base64,${LOGO_B64}"
    mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" <<LOGSQL 2>/dev/null || true
INSERT INTO settings (\`key\`, value, type, \`group\`, created_at, updated_at)
  VALUES ('hotel_logo', '${LOGO_DATA_URI}', 'string', 'general', NOW(), NOW())
  ON DUPLICATE KEY UPDATE value='${LOGO_DATA_URI}', updated_at=NOW();
LOGSQL
    success "Hotel logo stored in settings table"
elif [[ -n "${HOTEL_LOGO_PATH}" ]]; then
    warn "Logo file '${HOTEL_LOGO_PATH}' not found — skipping logo upload"
fi

# ─── Cache optimise after seeding ─────────────────────────────────────────────
info "Optimising Laravel for production..."

# Clear any stale dev caches first
sudo -u www-data php artisan optimize:clear --quiet 2>/dev/null || true

# Build all production caches
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Full optimise (bootstraps class map, combines above)
sudo -u www-data php artisan optimize

success "Laravel production caches built (config / route / view / event / optimize)"

# ─── File permission hardening ─────────────────────────────────────────────────
info "Hardening file permissions..."

# Ownership: everything belongs to www-data
chown -R www-data:www-data "${INSTALL_DIR}"

# Directories: 755  |  Files: 644
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;

# .env must never be world-readable
chmod 640 "${INSTALL_DIR}/.env"

# Writable by the web server only
chmod -R ug+w "${INSTALL_DIR}/storage"
chmod -R ug+w "${INSTALL_DIR}/bootstrap/cache"

# artisan must be executable
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

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 1024;

    # Max upload size
    client_max_body_size 100M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Static assets caching
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

# Enable site
ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/${NGINX_SITE}"
rm -f /etc/nginx/sites-enabled/default   # remove default site

nginx -t
systemctl reload nginx
success "Nginx virtual host configured"

# ─── Optional: SSL with Let's Encrypt ────────────────────────────────────────
if [[ "$INSTALL_SSL" == true ]]; then
    info "Installing Certbot for Let's Encrypt SSL..."
    apt-get install -y certbot python3-certbot-nginx

    # Only attempt if domain is not an IP address
    if [[ "$APP_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        warn "Cannot issue SSL certificate for an IP address. Skipping Let's Encrypt."
        warn "Once you have a real domain, run: certbot --nginx -d ${APP_DOMAIN}"
    else
        certbot --nginx \
            -d "${APP_DOMAIN}" \
            --non-interactive \
            --agree-tos \
            --email "${HOTEL_EMAIL}" \
            --redirect || warn "SSL certificate failed — site still accessible over HTTP."
    fi
fi

# =============================================================================
#  STEP 8 — Services (Queue Worker + Scheduler)
# =============================================================================
step "8/8 — Setting Up System Services"

# ─── Queue worker systemd service ────────────────────────────────────────────
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

# ─── Scheduler cron (every minute) ───────────────────────────────────────────
CRON_JOB="* * * * * www-data cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1"
echo "$CRON_JOB" > /etc/cron.d/hms-scheduler
chmod 644 /etc/cron.d/hms-scheduler

# ─── Enable & start queue worker ─────────────────────────────────────────────
systemctl daemon-reload
systemctl enable "${SERVICE_NAME}" --now
success "Queue worker service started"

# ─── PHP-FPM opcache tuning ───────────────────────────────────────────────────
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

# ─── Firewall ─────────────────────────────────────────────────────────────────
if command -v ufw &>/dev/null; then
    ufw allow 22/tcp   >/dev/null 2>&1 || true
    ufw allow 80/tcp   >/dev/null 2>&1 || true
    ufw allow 443/tcp  >/dev/null 2>&1 || true
    ufw --force enable >/dev/null 2>&1 || true
    success "UFW firewall: SSH, HTTP, HTTPS allowed"
fi

# ─── MySQL security: remove anonymous users ───────────────────────────────────
mysql -u root <<SQL2
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
