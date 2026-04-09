#!/usr/bin/env bash
# =============================================================================
#  Unified Uninstaller — reads INSTALL_MODE from /opt/hms/.env
# =============================================================================
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

[[ $EUID -ne 0 ]] && { echo -e "${RED}Run as root: sudo bash uninstall.sh${RESET}"; exit 1; }

INSTALL_DIR="/opt/hms"
PHP_VERSION="8.2"

# ── Read install mode from .env ───────────────────────────────────────────────
INSTALL_MODE="both"   # safe default — removes everything
if [[ -f "${INSTALL_DIR}/.env" ]]; then
    INSTALL_MODE=$(grep '^INSTALL_MODE=' "${INSTALL_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' || echo "both")
fi

case "$INSTALL_MODE" in
    iptv) MODE_LABEL="IPTV Management only"; NGINX_SITE="iptv" ;;
    hms)  MODE_LABEL="Hotel Management System only"; NGINX_SITE="hms" ;;
    *)    MODE_LABEL="IPTV + HMS (full install)"; NGINX_SITE="hms" ;;
esac

echo -e "${BOLD}${RED}"
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║   Uninstaller                                ║"
echo "  ╚══════════════════════════════════════════════╝"
echo -e "${RESET}"
echo -e "${YELLOW}Detected install mode: ${MODE_LABEL}${RESET}"
echo -e "${YELLOW}This will remove application files, Nginx config, and queue service.${RESET}"
echo -e "${YELLOW}The database will NOT be dropped unless you confirm below.${RESET}"
echo ""
read -rp "Are you sure you want to uninstall? (yes/no): " CONFIRM
[[ ! "$CONFIRM" =~ ^[Yy] ]] && { echo "Cancelled."; exit 0; }

read -rp "Also DROP the database? (yes/no) [no]: " DROP_DB
DROP_DB="${DROP_DB:-no}"

if [[ "$DROP_DB" =~ ^[Yy] ]]; then
    DB_DATABASE=$(grep '^DB_DATABASE=' "${INSTALL_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' || echo "")
    DB_USERNAME=$(grep '^DB_USERNAME=' "${INSTALL_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' || echo "")
    if [[ -n "$DB_DATABASE" ]]; then
        mysql -u root -e "DROP DATABASE IF EXISTS \`${DB_DATABASE}\`; DROP USER IF EXISTS '${DB_USERNAME}'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null || true
        echo -e "${GREEN}Database dropped.${RESET}"
    fi
fi

# Stop & disable queue worker
systemctl stop  hms-queue 2>/dev/null || true
systemctl disable hms-queue 2>/dev/null || true
rm -f /etc/systemd/system/hms-queue.service
systemctl daemon-reload

# Remove cron
rm -f /etc/cron.d/hms-scheduler

# Remove Nginx config
rm -f "/etc/nginx/sites-enabled/${NGINX_SITE}"
rm -f "/etc/nginx/sites-available/${NGINX_SITE}"
# Also clean up the alternate site name in case mode changed
rm -f /etc/nginx/sites-enabled/hms  2>/dev/null || true
rm -f /etc/nginx/sites-enabled/iptv 2>/dev/null || true
nginx -t && systemctl reload nginx 2>/dev/null || true

# Remove application files
rm -rf "${INSTALL_DIR}"

# Remove PHP tuning
rm -f "/etc/php/${PHP_VERSION}/fpm/conf.d/99-hms.ini"
systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || true

echo -e "${GREEN}Uninstalled successfully (mode was: ${MODE_LABEL}).${RESET}"
