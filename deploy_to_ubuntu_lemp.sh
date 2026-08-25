#!/usr/bin/env bash
set -euo pipefail

# Deployment / bootstrap script for Ubuntu (run as a user with sudo)
# Usage on your machine:
# scp deploy_to_ubuntu_lemp.sh user@SERVER:/tmp && ssh user@SERVER 'sudo bash /tmp/deploy_to_ubuntu_lemp.sh'

APP_DIR=/var/www/hms
REPO_PATH="$(pwd)"
PHP_PACKAGES="php php-fpm php-cli php-mysql php-xml php-mbstring php-curl php-zip php-intl php-bcmath php-gd unzip git curl"
NODE_SETUP_SCRIPT="https://deb.nodesource.com/setup_18.x"

echo "Starting bootstrap on $(hostname)"

# Update and upgrade
sudo apt update && sudo apt upgrade -y

# Install base packages
sudo apt install -y $PHP_PACKAGES nginx mariadb-server software-properties-common

# Install Node.js 18+ and npm
curl -fsSL $NODE_SETUP_SCRIPT | sudo -E bash -
sudo apt install -y nodejs build-essential

# Install Composer globally
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  sudo chmod +x /usr/local/bin/composer
fi

# Create application directory
sudo mkdir -p $APP_DIR
sudo chown $USER:$USER $APP_DIR
sudo chmod 755 $APP_DIR

# If repo available locally and you plan to copy, skip git clone step. Otherwise clone from remote.
if [ -d "$APP_DIR/.git" ]; then
  echo "Repo already present in $APP_DIR — pulling latest"
  cd $APP_DIR && git pull
else
  echo "Please clone your app into $APP_DIR (or run this script from the repo and use rsync/scp)."
  echo "Skipping automatic clone to avoid overwriting."
fi

# If user ran this from inside the repo and wants to copy, uncomment the rsync block below and run locally:
# rsync -av --exclude vendor --exclude node_modules ./ $USER@SERVER:$APP_DIR/

# Install PHP dependencies
cd $APP_DIR
if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Copy example env if missing
if [ -f .env.example ] && [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate || true
fi

# Set permissions
sudo chown -R www-data:www-data $APP_DIR
sudo find $APP_DIR -type f -exec chmod 644 {} \;
sudo find $APP_DIR -type d -exec chmod 755 {} \;

# Run Laravel tasks if artisan exists
if [ -f artisan ]; then
  php artisan migrate --force || true
  php artisan storage:link || true
fi

# Build frontend assets if package.json exists
if [ -f package.json ]; then
  npm ci
  npm run production || npm run build || true
fi

# Nginx site config
NGINX_CONF="/etc/nginx/sites-available/hms"
sudo tee $NGINX_CONF > /dev/null <<'NGINX'
server {
    listen 80;
    server_name _;
    root /var/www/hms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX

sudo ln -sf $NGINX_CONF /etc/nginx/sites-enabled/hms
sudo nginx -t && sudo systemctl reload nginx

# Firewall (UFW)
if command -v ufw >/dev/null 2>&1; then
  sudo ufw allow 'Nginx Full'
  sudo ufw --force enable
fi

echo "Bootstrap complete. Review the .env file, secure MariaDB (run mysql_secure_installation), and verify services."

exit 0
