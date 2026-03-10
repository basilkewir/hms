# HMS Production Deployment Guide

## Overview

This guide describes how to deploy HMS to production **without requiring Composer on the server**. 

### Two Approaches:

#### Approach 1: Build Locally, Deploy Pre-built (Recommended for Production)
- Build everything locally on your development machine
- Deploy only compiled/built files
- Server only needs: PHP, Nginx, MySQL, Node.js (for runtime, not build)
- **Advantages:** Faster deployment, smaller footprint, no build tools on server
- **Disadvantages:** Requires local setup before deployment

#### Approach 2: Build on Server (Current)
- Clone repository on server
- Run Composer and npm builds on server
- **Advantages:** Single command installation
- **Disadvantages:** Requires build tools, slower initial deployment, larger server footprint

---

## Approach 1: Build Locally, Deploy Pre-built

### Step 1: Prepare Locally (on your development machine)

```bash
# Clone the repository
git clone https://github.com/basilkewir/hms.git
cd hms

# Install dependencies
composer install --no-dev --optimize-autoloader --prefer-dist

# Generate application key
php artisan key:generate

# Install npm packages and build assets
npm install
npm run build  # or npm run production

# Clear any development caches
php artisan optimize:clear
```

### Step 2: Prepare Deployment Package

```bash
# Remove unnecessary files
rm -rf node_modules          # Don't need this on server
rm -rf .git                  # Don't need git history
rm -rf .env.example          # Use .env instead
rm -f composer.lock          # Rebuilt on deployment

# Create deployment tarball
tar -czf hms-production.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='bootstrap/cache/*' \
  --exclude='storage/logs/*' \
  --exclude='.env.example' \
  --exclude='*.md' \
  -C .. hms/
```

### Step 3: Deploy to Server

#### Option A: Via SCP/SFTP
```bash
# Copy to server
scp hms-production.tar.gz root@192.168.20.85:/root/

# SSH into server
ssh root@192.168.20.85
cd /root
tar -xzf hms-production.tar.gz -C /opt/
sudo chown -R www-data:www-data /opt/hms
```

#### Option B: Direct via GitHub (if public repo)
```bash
# On server
cd /opt
sudo git clone https://github.com/basilkewir/hms.git hms
cd hms

# Copy pre-built vendor and public/js/app.js from your local build
# (if keeping vendor in git or via artifact storage)
```

### Step 4: Minimal Server Setup

Instead of full `install.sh`, create a lightweight setup script:

```bash
#!/bin/bash
# setup-production.sh - Minimal setup for pre-built deployment

set -e

INSTALL_DIR="/opt/hms"
PHP_VERSION="8.2"

# Install only runtime dependencies (not build tools)
sudo apt-get update
sudo apt-get install -y \
    php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli \
    php${PHP_VERSION}-mysql php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip php${PHP_VERSION}-curl php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl nginx mysql-server

# Set permissions
sudo chown -R www-data:www-data "${INSTALL_DIR}"
find "${INSTALL_DIR}" -type d -exec chmod 755 {} \;
find "${INSTALL_DIR}" -type f -exec chmod 644 {} \;
chmod -R 775 "${INSTALL_DIR}/storage" "${INSTALL_DIR}/bootstrap/cache"

# Create .env file (user provides values)
cat > "${INSTALL_DIR}/.env" << 'EOF'
APP_NAME="Hotel Management System"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=http://192.168.20.85

DB_HOST=127.0.0.1
DB_DATABASE=hms_db
DB_USERNAME=hms_user
DB_PASSWORD=YOUR_PASSWORD_HERE

# ... other config
EOF

# Run migrations only (no build needed)
cd "${INSTALL_DIR}"
php artisan migrate --force

# Configure Nginx
sudo systemctl enable nginx
sudo systemctl start nginx

echo "✓ Setup complete! Configure .env and run migrations"
```

---

## Comparison Table

| Task | Approach 1 | Approach 2 |
|------|-----------|-----------|
| **Local Setup** | Required | None |
| **Server Packages** | PHP, Nginx, MySQL | PHP, Nginx, MySQL, Composer, Node.js |
| **Build Time** | ~5 min (local) | ~10-15 min (server) |
| **Server Prep** | ~2 min | ~5-10 min |
| **Total Deploy Time** | ~7 min | ~15-25 min |
| **Server Disk** | ~500MB | ~1.5GB |
| **Server Resources** | Lower | Higher |
| **Security** | Better (no build tools) | Standard |

---

## Current Implementation (Approach 2)

Your `install.sh` currently uses **Approach 2** which:
- ✅ Works for first-time setup
- ✅ Simple: one command
- ❌ Requires Composer on server
- ❌ Takes longer to deploy
- ❌ Uses more server resources

---

## Recommendation

**For production:** Use **Approach 1** (Build locally, deploy pre-built)

This means:
1. Keep `install.sh` simple - only installs runtime deps
2. Pre-build locally before deployment
3. Deploy only production files

**For testing/development:** Keep current `install.sh` as is

---

## Quick Setup: Skip Composer on Server

If you want to modify the current `install.sh` to skip Composer:

```bash
# In install.sh, change the Composer installation to:
[[ "$NEED_PKG" == *"composer"* ]] && {
    warn "Composer not required for production deployment"
    # Skip installation - assume pre-built deployment
}
```

**Would you like me to:**
1. Create a lightweight production-deployment version of `install.sh`? 
2. Create a build script for local preparation?
3. Remove Composer from the current `install.sh`?

Let me know which approach you prefer! 🚀
