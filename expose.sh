#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════════════
#  HMS — Expose to Internet via Cloudflare Tunnel (FREE, no paid service)
#
#  What this does:
#   • Installs cloudflared (Cloudflare's free tunnel daemon)
#   • Creates a secure HTTPS tunnel from this server to the internet
#   • No open router ports, no static IP, no paid plan required
#   • Admin panel, booking API, and room availability all become accessible
#     from anywhere in the world
#
#  Modes:
#   1. Quick   — Temporary URL (no Cloudflare account needed, URL changes on restart)
#   2. Named   — Permanent subdomain on your own domain (free Cloudflare account)
#
#  Usage:
#   sudo bash expose.sh
# ═══════════════════════════════════════════════════════════════════════════

set -eo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; exit 1; }
step()    {
    echo ""
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════${RESET}"
    echo -e "${BOLD} $*${RESET}"
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════${RESET}"
    echo ""
}

print_api_info() {
    local URL="$1"
    echo ""
    echo "  ┌────────────────────────────────────────────────────────────────┐"
    echo "  │  BOOKING WEBSITE INTEGRATION                                   │"
    echo "  ├────────────────────────────────────────────────────────────────┤"
    printf "  │  Hotel Info:     GET  %-40s│\n" "${URL}/api/public/hotel-info"
    printf "  │  Room Types:     GET  %-40s│\n" "${URL}/api/public/room-types"
    printf "  │  Availability:   GET  %-40s│\n" "${URL}/api/booking/availability"
    echo "  │                        ?check_in=YYYY-MM-DD&check_out=YYYY-MM-DD │"
    printf "  │  Create Booking: POST %-40s│\n" "${URL}/api/booking/create"
    echo "  │                        Header: X-Booking-Token: <token>          │"
    printf "  │  Webhook:        POST %-40s│\n" "${URL}/api/booking/webhook"
    echo "  ├────────────────────────────────────────────────────────────────┤"
    printf "  │  Admin Panel:    %-46s│\n" "${URL}/admin"
    printf "  │  Reports:        %-46s│\n" "${URL}/admin/reports"
    echo "  └────────────────────────────────────────────────────────────────┘"
    echo ""
    echo "  Set your booking site's API base URL to: ${URL}/api"
    echo "  Generate a booking token in: Settings → Integration tab"
    echo ""
}

HMS_DIR="/opt/hms"
SERVICE_NAME="hms-tunnel"
CREDENTIALS_FILE="/root/hms_credentials.txt"

# ─── 1. Root check ────────────────────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
    error "Run as root: sudo bash expose.sh"
fi

# ─── 2. Find HMS installation ─────────────────────────────────────────────────
if [[ ! -f "$HMS_DIR/.env" ]]; then
    HMS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    [[ ! -f "$HMS_DIR/.env" ]] && error "HMS not found. Install HMS first with: sudo bash install.sh"
fi

APP_PORT=80
if grep -q "APP_ENV=local" "$HMS_DIR/.env" 2>/dev/null; then
    APP_PORT=8000
    info "Development mode detected — tunneling to port $APP_PORT"
fi

step "1/3 — Installing cloudflared"

install_cloudflared() {
    if command -v cloudflared &>/dev/null; then
        success "cloudflared already installed: $(cloudflared --version 2>&1 | head -1)"
        return
    fi

    info "Downloading cloudflared..."
    ARCH=$(dpkg --print-architecture 2>/dev/null || uname -m)

    case "$ARCH" in
        amd64|x86_64)  CF_ARCH="amd64" ;;
        arm64|aarch64) CF_ARCH="arm64" ;;
        armhf|armv7l)  CF_ARCH="arm"   ;;
        *)             CF_ARCH="amd64" ;;
    esac

    CF_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-${CF_ARCH}.deb"

    if command -v apt-get &>/dev/null; then
        curl -fsSL "$CF_URL" -o /tmp/cloudflared.deb
        apt-get install -y /tmp/cloudflared.deb
        rm -f /tmp/cloudflared.deb
    else
        curl -fsSL "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-${CF_ARCH}" \
            -o /usr/local/bin/cloudflared
        chmod +x /usr/local/bin/cloudflared
    fi

    success "cloudflared installed: $(cloudflared --version 2>&1 | head -1)"
}

install_cloudflared

# ─── 3. Choose mode ───────────────────────────────────────────────────────────
step "2/3 — Choose tunnel mode"

echo "  ┌─────────────────────────────────────────────────────────┐"
echo "  │  Mode 1 — QUICK  (No account, URL changes on restart)   │"
echo "  │  Mode 2 — NAMED  (Free Cloudflare account, permanent)   │"
echo "  └─────────────────────────────────────────────────────────┘"
echo ""
read -rp "  Choose mode [1/2, default=1]: " TUNNEL_MODE
TUNNEL_MODE="${TUNNEL_MODE:-1}"

# ─── Mode 1: Quick tunnel ─────────────────────────────────────────────────────
if [[ "$TUNNEL_MODE" == "1" ]]; then
    step "3/3 — Starting quick tunnel (port $APP_PORT)"

    # Stop any old tunnel service first
    systemctl stop "$SERVICE_NAME" 2>/dev/null || true

    cat > /etc/systemd/system/${SERVICE_NAME}.service << EOF
[Unit]
Description=HMS Cloudflare Quick Tunnel
After=network-online.target
Wants=network-online.target

[Service]
ExecStart=/usr/bin/cloudflared tunnel --url http://localhost:${APP_PORT} --no-autoupdate
Restart=always
RestartSec=15
User=root
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

    # If cloudflared is in a different path, fix it
    CF_BIN=$(command -v cloudflared)
    sed -i "s|/usr/bin/cloudflared|${CF_BIN}|" /etc/systemd/system/${SERVICE_NAME}.service

    systemctl daemon-reload
    systemctl enable "$SERVICE_NAME"
    systemctl restart "$SERVICE_NAME"

    info "Waiting for tunnel URL (up to 30 seconds)..."
    TUNNEL_URL=""
    for i in $(seq 1 30); do
        TUNNEL_URL=$(journalctl -u "$SERVICE_NAME" --since "1 minute ago" --no-pager 2>/dev/null \
            | grep -oP 'https://[a-z0-9\-]+\.trycloudflare\.com' | tail -1)
        [[ -n "$TUNNEL_URL" ]] && break
        sleep 1
    done

    if [[ -z "$TUNNEL_URL" ]]; then
        warn "Could not auto-detect URL yet. Check with: journalctl -u $SERVICE_NAME -f"
        TUNNEL_URL="(run: journalctl -u $SERVICE_NAME -f  to find your URL)"
    else
        # Update APP_URL in HMS .env
        sed -i "s|^APP_URL=.*|APP_URL=${TUNNEL_URL}|" "$HMS_DIR/.env"
        cd "$HMS_DIR" && php artisan config:clear &>/dev/null || true
        success "APP_URL updated to $TUNNEL_URL"
    fi

    echo ""
    echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
    echo -e "${GREEN}  ✓ Quick Tunnel Active!${RESET}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
    echo ""
    echo -e "  Public URL:  ${BOLD}${TUNNEL_URL}${RESET}"
    echo ""
    echo "  ⚠  This URL changes every time the tunnel restarts."
    echo "     For a permanent URL use Mode 2 (named tunnel)."
    print_api_info "$TUNNEL_URL"
    echo -e "${CYAN}  Tunnel status: systemctl status $SERVICE_NAME${RESET}"
    echo -e "${CYAN}  View logs:     journalctl -u $SERVICE_NAME -f${RESET}"
    exit 0
fi

# ─── Mode 2: Named tunnel (permanent) ─────────────────────────────────────────
step "3/3 — Named tunnel setup"

echo ""
echo "  You need a FREE Cloudflare account and a domain on Cloudflare."
echo "  Free domain options: https://register.us.kg  or  https://duckdns.org"
echo ""
echo "  Steps:"
echo "  1. Sign up at https://dash.cloudflare.com (free, no credit card)"
echo "  2. Add your domain to Cloudflare"
echo "  3. Come back here and press Enter"
echo ""
read -rp "  Ready to authenticate? (y/n) [y]: " READY
READY="${READY:-y}"
[[ "$READY" != "y" && "$READY" != "Y" ]] && error "Cancelled. Run again when ready."

info "Opening Cloudflare authentication (follow the URL shown)..."
cloudflared tunnel login

read -rp "  Tunnel name (e.g. 'hms-hotel'): " TUNNEL_NAME
TUNNEL_NAME="${TUNNEL_NAME:-hms-hotel}"

info "Creating tunnel: $TUNNEL_NAME"
cloudflared tunnel create "$TUNNEL_NAME"
TUNNEL_ID=$(cloudflared tunnel list 2>/dev/null | grep "$TUNNEL_NAME" | awk '{print $1}')
[[ -z "$TUNNEL_ID" ]] && error "Failed to create tunnel. Run: cloudflared tunnel list"
success "Tunnel ID: $TUNNEL_ID"

read -rp "  Subdomain to use (e.g. 'hms.yourhotel.com'): " PUBLIC_DOMAIN
[[ -z "$PUBLIC_DOMAIN" ]] && error "Domain is required."

# Create cloudflared config
CONFIG_DIR="/root/.cloudflared"
mkdir -p "$CONFIG_DIR"
cat > "$CONFIG_DIR/config.yml" << EOF
tunnel: ${TUNNEL_ID}
credentials-file: ${CONFIG_DIR}/${TUNNEL_ID}.json

ingress:
  - hostname: ${PUBLIC_DOMAIN}
    service: http://localhost:${APP_PORT}
  - service: http_status:404
EOF

info "Creating DNS CNAME for $PUBLIC_DOMAIN..."
cloudflared tunnel route dns "$TUNNEL_NAME" "$PUBLIC_DOMAIN" || \
    warn "DNS creation skipped — add manually in Cloudflare: CNAME ${PUBLIC_DOMAIN} → ${TUNNEL_ID}.cfargotunnel.com"

# Install as system service
cloudflared service install
systemctl daemon-reload
systemctl enable cloudflared
systemctl restart cloudflared

PUBLIC_URL="https://${PUBLIC_DOMAIN}"

sed -i "s|^APP_URL=.*|APP_URL=${PUBLIC_URL}|" "$HMS_DIR/.env"
cd "$HMS_DIR" && php artisan config:clear &>/dev/null || true

# Save to credentials file
{
    echo ""
    echo "═══════════════════════════════════════════════════════════"
    echo "  ONLINE ACCESS (Cloudflare Tunnel)"
    echo "═══════════════════════════════════════════════════════════"
    echo "  Public URL:   $PUBLIC_URL"
    echo "  Tunnel Name:  $TUNNEL_NAME"
    echo "  Tunnel ID:    $TUNNEL_ID"
    echo "  Booking API:  ${PUBLIC_URL}/api/booking/availability"
    echo "  Admin Panel:  ${PUBLIC_URL}/admin"
    echo "═══════════════════════════════════════════════════════════"
} >> "$CREDENTIALS_FILE" 2>/dev/null || true

echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
echo -e "${GREEN}  ✓ Named Tunnel Active!${RESET}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
echo -e "  Public URL: ${BOLD}${PUBLIC_URL}${RESET}"
print_api_info "$PUBLIC_URL"
echo -e "${CYAN}  Tunnel status: systemctl status cloudflared${RESET}"
echo -e "${CYAN}  View logs:     journalctl -u cloudflared -f${RESET}"


set -eo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; exit 1; }
step()    {
    echo ""
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════${RESET}"
    echo -e "${BOLD} $*${RESET}"
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════${RESET}"
    echo ""
}

HMS_DIR="/opt/hms"
SERVICE_NAME="hms-tunnel"
CREDENTIALS_FILE="/root/hms_credentials.txt"

# ─── 1. Root check ────────────────────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
    error "Run as root: sudo bash expose.sh"
fi

# ─── 2. Find HMS installation ─────────────────────────────────────────────────
if [[ ! -f "$HMS_DIR/.env" ]]; then
    # Try local dev path
    HMS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    [[ ! -f "$HMS_DIR/.env" ]] && error "HMS not found. Install HMS first with: sudo bash install.sh"
fi

APP_PORT=80
# Detect if running under php artisan serve (dev mode)
if grep -q "APP_ENV=local" "$HMS_DIR/.env" 2>/dev/null; then
    APP_PORT=8000
    info "Development mode detected — tunneling to port $APP_PORT"
fi

step "1/3 — Installing cloudflared"

install_cloudflared() {
    if command -v cloudflared &>/dev/null; then
        success "cloudflared already installed: $(cloudflared --version 2>&1 | head -1)"
        return
    fi

    info "Downloading cloudflared..."
    ARCH=$(dpkg --print-architecture 2>/dev/null || uname -m)

    case "$ARCH" in
        amd64|x86_64)  CF_ARCH="amd64" ;;
        arm64|aarch64) CF_ARCH="arm64" ;;
        armhf|armv7l)  CF_ARCH="arm"   ;;
        *)             CF_ARCH="amd64" ;;
    esac

    CF_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-${CF_ARCH}.deb"

    if command -v apt-get &>/dev/null; then
        curl -fsSL "$CF_URL" -o /tmp/cloudflared.deb
        apt-get install -y /tmp/cloudflared.deb
        rm -f /tmp/cloudflared.deb
    else
        # Fallback: direct binary
        curl -fsSL "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-${CF_ARCH}" \
            -o /usr/local/bin/cloudflared
        chmod +x /usr/local/bin/cloudflared
    fi

    success "cloudflared installed: $(cloudflared --version 2>&1 | head -1)"
}

install_cloudflared

# ─── 3. Choose mode ───────────────────────────────────────────────────────────
step "2/3 — Choose tunnel mode"

echo "  ┌─────────────────────────────────────────────────────────┐"
echo "  │  Mode 1 — QUICK  (No account, URL changes on restart)   │"
echo "  │  Mode 2 — NAMED  (Free Cloudflare account, permanent)   │"
echo "  └─────────────────────────────────────────────────────────┘"
echo ""
read -rp "  Choose mode [1/2, default=1]: " TUNNEL_MODE
TUNNEL_MODE="${TUNNEL_MODE:-1}"

# ─── Mode 1: Quick tunnel ─────────────────────────────────────────────────────
if [[ "$TUNNEL_MODE" == "1" ]]; then
    step "3/3 — Starting quick tunnel (port $APP_PORT)"

    cat > /etc/systemd/system/${SERVICE_NAME}.service << EOF
[Unit]
Description=HMS Cloudflare Quick Tunnel
After=network-online.target
Wants=network-online.target

[Service]
ExecStart=/usr/local/bin/cloudflared tunnel --url http://localhost:${APP_PORT} --no-autoupdate
Restart=always
RestartSec=15
User=root
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    systemctl enable "$SERVICE_NAME"
    systemctl restart "$SERVICE_NAME"

    # Wait for the tunnel URL to appear in the journal
    info "Waiting for tunnel URL (up to 30 seconds)..."
    TUNNEL_URL=""
    for i in $(seq 1 30); do
        TUNNEL_URL=$(journalctl -u "$SERVICE_NAME" --since "30 seconds ago" --no-pager 2>/dev/null \
            | grep -oP 'https://[a-z0-9\-]+\.trycloudflare\.com' | tail -1)
        [[ -n "$TUNNEL_URL" ]] && break
        sleep 1
    done

    if [[ -z "$TUNNEL_URL" ]]; then
        warn "Could not auto-detect URL. Run: journalctl -u $SERVICE_NAME -f"
        TUNNEL_URL="(check: journalctl -u $SERVICE_NAME)"
    fi

    # Update APP_URL in HMS .env
    sed -i "s|^APP_URL=.*|APP_URL=${TUNNEL_URL}|" "$HMS_DIR/.env"
    cd "$HMS_DIR" && php artisan config:clear &>/dev/null || true

    echo ""
    echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
    echo -e "${GREEN}  ✓ Quick Tunnel Active!${RESET}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
    echo ""
    echo -e "  Public URL:    ${BOLD}${TUNNEL_URL}${RESET}"
    echo ""
    echo "  ⚠  This URL changes every time the tunnel restarts."
    echo "     For a permanent URL use Mode 2 (named tunnel)."
    echo ""
    print_api_info "$TUNNEL_URL"
    exit 0
fi

# ─── Mode 2: Named tunnel (permanent) ────────────────────────────────────────
step "3/3 — Named tunnel setup"

echo ""
echo "  You need a FREE Cloudflare account and your domain on Cloudflare."
echo "  (Free domain from freenom.tf or duckdns.org also works)"
echo ""
echo "  Steps:"
echo "  1. Go to https://dash.cloudflare.com → sign up (free)"
echo "  2. Add your domain to Cloudflare (or use a free subdomain service)"
echo "  3. Come back here and press Enter when ready."
echo ""
read -rp "  Ready to authenticate? (y/n) [y]: " READY
READY="${READY:-y}"
[[ "$READY" != "y" && "$READY" != "Y" ]] && error "Setup cancelled. Run again when ready."

# Cloudflare login (opens browser link)
info "Starting Cloudflare authentication..."
cloudflared tunnel login

# Get tunnel name
read -rp "  Tunnel name (e.g. 'hms-hotel'): " TUNNEL_NAME
TUNNEL_NAME="${TUNNEL_NAME:-hms-hotel}"

# Create tunnel
info "Creating tunnel: $TUNNEL_NAME"
cloudflared tunnel create "$TUNNEL_NAME"
TUNNEL_ID=$(cloudflared tunnel list | grep "$TUNNEL_NAME" | awk '{print $1}')
[[ -z "$TUNNEL_ID" ]] && error "Failed to create tunnel. Check: cloudflared tunnel list"
success "Tunnel ID: $TUNNEL_ID"

# Domain/subdomain to use
read -rp "  Full subdomain to use (e.g. 'hms.yourdomain.com'): " PUBLIC_DOMAIN
[[ -z "$PUBLIC_DOMAIN" ]] && error "Domain is required."

# Create cloudflared config
CONFIG_DIR="$HOME/.cloudflared"
mkdir -p "$CONFIG_DIR"
cat > "$CONFIG_DIR/config.yml" << EOF
tunnel: ${TUNNEL_ID}
credentials-file: ${CONFIG_DIR}/${TUNNEL_ID}.json

ingress:
  - hostname: ${PUBLIC_DOMAIN}
    service: http://localhost:${APP_PORT}
  - service: http_status:404
EOF

# Create DNS record
info "Creating DNS CNAME for $PUBLIC_DOMAIN..."
cloudflared tunnel route dns "$TUNNEL_NAME" "$PUBLIC_DOMAIN" || \
    warn "DNS creation failed — do it manually in Cloudflare dashboard: CNAME $PUBLIC_DOMAIN → ${TUNNEL_ID}.cfargotunnel.com"

# Install as systemd service
cloudflared service install
systemctl daemon-reload
systemctl enable cloudflared
systemctl restart cloudflared

PUBLIC_URL="https://${PUBLIC_DOMAIN}"

# Update HMS .env APP_URL
sed -i "s|^APP_URL=.*|APP_URL=${PUBLIC_URL}|" "$HMS_DIR/.env"
cd "$HMS_DIR" && php artisan config:clear &>/dev/null || true

# Save to credentials file
{
    echo ""
    echo "═══════════════════════════════════════════════════════════"
    echo "  ONLINE ACCESS (Cloudflare Tunnel)"
    echo "═══════════════════════════════════════════════════════════"
    echo "  Public URL:   $PUBLIC_URL"
    echo "  Tunnel Name:  $TUNNEL_NAME"
    echo "  Tunnel ID:    $TUNNEL_ID"
    echo "  Mode:         Named (permanent)"
    echo ""
    echo "  Booking API:  ${PUBLIC_URL}/api/booking/availability"
    echo "  Admin Panel:  ${PUBLIC_URL}/admin"
    echo "═══════════════════════════════════════════════════════════"
} >> "$CREDENTIALS_FILE" 2>/dev/null || true

echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
echo -e "${GREEN}  ✓ Named Tunnel Active!${RESET}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════${RESET}"
print_api_info "$PUBLIC_URL"


# ─── Helper: print API integration info ───────────────────────────────────────
print_api_info() {
    local URL="$1"
    echo ""
    echo "  ┌─────────────────────────────────────────────────────────────┐"
    echo "  │  BOOKING WEBSITE INTEGRATION                                │"
    echo "  ├─────────────────────────────────────────────────────────────┤"
    echo "  │  Hotel Info:    GET  ${URL}/api/public/hotel-info      │"
    echo "  │  Room Types:    GET  ${URL}/api/public/room-types       │"
    echo "  │  Availability:  GET  ${URL}/api/booking/availability    │"
    echo "  │                      ?check_in=YYYY-MM-DD               │"
    echo "  │                      &check_out=YYYY-MM-DD              │"
    echo "  │                      &adults=2&children=0               │"
    echo "  │  Create Booking: POST ${URL}/api/booking/create         │"
    echo "  │                      Header: X-Booking-Token: <token>   │"
    echo "  │  Webhook:       POST ${URL}/api/booking/webhook         │"
    echo "  ├─────────────────────────────────────────────────────────────┤"
    echo "  │  Admin Panel:   ${URL}/admin                            │"
    echo "  │  Reports:       ${URL}/admin/reports                    │"
    echo "  └─────────────────────────────────────────────────────────────┘"
    echo ""
    echo "  Set your booking site's API base URL to: ${URL}/api"
    echo "  Generate a booking token in: Settings → Integration tab"
    echo ""
    echo -e "${CYAN}  Tunnel status: systemctl status $SERVICE_NAME${RESET}"
    echo -e "${CYAN}  View logs:     journalctl -u $SERVICE_NAME -f${RESET}"
    echo ""
}
