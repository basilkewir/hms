#!/usr/bin/env bash
# =============================================================================
#  Hotel Management System — Uninstaller
# =============================================================================
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

[[ $EUID -ne 0 ]] && { echo -e "${RED}Run as root: sudo bash uninstall.sh${RESET}"; exit 1; }

INSTALL_DIR="/opt/hms"
NGINX_SITE="hms"
SERVICE_NAME="hms-queue"
PHP_VERSION="8.2"

echo -e "${BOLD}${RED}"
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║   HMS Uninstaller                            ║"
echo "  ╚══════════════════════════════════════════════╝"
echo -e "${RESET}"
echo -e "${YELLOW}This will remove the application files, Nginx config, and queue service.${RESET}"
echo -e "${YELLOW}The database will NOT be dropped unless you confirm below.${RESET}"
echo ""
read -rp "Are you sure you want to uninstall HMS? (yes/no): " CONFIRM
[[ ! "$CONFIRM" =~ ^[Yy] ]] && { echo "Cancelled."; exit 0; }

read -rp "Also DROP the database? (yes/no) [no]: " DROP_DB
DROP_DB="${DROP_DB:-no}"

if [[ "$DROP_DB" =~ ^[Yy] ]]; then
    # Read DB name from .env
    DB_DATABASE=$(grep '^DB_DATABASE=' "${INSTALL_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' || echo "")
    DB_USERNAME=$(grep '^DB_USERNAME=' "${INSTALL_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' || echo "")
    if [[ -n "$DB_DATABASE" ]]; then
        mysql -u root -e "DROP DATABASE IF EXISTS \`${DB_DATABASE}\`; DROP USER IF EXISTS '${DB_USERNAME}'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null || true
        echo -e "${GREEN}Database dropped.${RESET}"
    fi
fi

# Stop & disable services
systemctl stop "${SERVICE_NAME}"  2>/dev/null || true
systemctl disable "${SERVICE_NAME}" 2>/dev/null || true
rm -f "/etc/systemd/system/${SERVICE_NAME}.service"
systemctl daemon-reload

# Remove cron
rm -f /etc/cron.d/hms-scheduler

# Remove Nginx config
rm -f "/etc/nginx/sites-enabled/${NGINX_SITE}"
rm -f "/etc/nginx/sites-available/${NGINX_SITE}"
nginx -t && systemctl reload nginx 2>/dev/null || true

# Remove application files
rm -rf "${INSTALL_DIR}"

# Remove PHP tuning
rm -f "/etc/php/${PHP_VERSION}/fpm/conf.d/99-hms.ini"
systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || true

echo -e "${GREEN}HMS uninstalled successfully.${RESET}"
