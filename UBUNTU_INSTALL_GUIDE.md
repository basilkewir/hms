# Hotel Management System — Ubuntu Server Installation Guide

> **One-command install**, similar to XtreamUI / other panel-style apps.
> Supports Ubuntu 20.04, 22.04, and 24.04 LTS.

---

## Requirements

| Component | Minimum |
|-----------|---------|
| OS        | Ubuntu 20.04 / 22.04 / 24.04 LTS |
| RAM       | 1 GB (2 GB recommended) |
| Disk      | 10 GB free |
| CPU       | 1 vCPU (2+ recommended) |
| Access    | Root or sudo |

---

## Quick Install (3 steps)

### 1. Transfer the project to your server

From your **Windows machine**, use `scp` or an SFTP client (e.g. WinSCP):

```powershell
# From Windows PowerShell — copy entire project to server
scp -r "C:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system" root@YOUR_SERVER_IP:/tmp/hms-src
```

Or use WinSCP with drag-and-drop to `/tmp/hms-src`.

### 2. SSH into your server

```bash
ssh root@YOUR_SERVER_IP
```

### 3. Run the installer

```bash
cd /tmp/hms-src
chmod +x install.sh
sudo bash install.sh
```

The installer will ask a few questions interactively:

| Prompt | Example |
|--------|---------|
| Domain or server IP | `hotel.yourdomain.com` or `192.168.1.100` |
| Use HTTPS? | `yes` (needs a real domain) or `no` |
| Hotel Name | `Grand Hotel` |
| Hotel Email | `info@grandhotel.com` |
| Hotel Phone | `+1234567890` |
| Hotel Address | `123 Main Street, City` |
| Database name | `hms_db` *(or press Enter for default)* |
| Database user | `hms_user` *(or press Enter for default)* |
| Database password | *(press Enter to auto-generate a secure one)* |
| License server URL | *(press Enter for default: `https://kewirdev.com/api/license`)* |

Then confirm and the installer will handle everything automatically.

---

## What the Installer Does

```
Step 1 — System packages
        PHP 8.2, Nginx, MySQL, Node.js 20, Composer

Step 2 — Database
        Creates DB + user with the credentials you provided

Step 3 — Application files
        Copies project to /opt/hms (excludes .git, node_modules, vendor)

Step 4 — .env file
        Writes full .env with your DB credentials, hotel details,
        license server URL, production settings

Step 5 — Dependencies & build
        composer install --no-dev --optimize-autoloader
        npm install && npm run build (Vite front-end)

Step 6 — Migrations & seeders
        php artisan migrate --force
        Seeds rooms, users, permissions, settings, etc.

Step 7 — Nginx virtual host
        /etc/nginx/sites-available/hms
        Optional: Let's Encrypt SSL (certbot --nginx)

Step 8 — System services
        Queue worker: systemd service (hms-queue)
        Scheduler: /etc/cron.d/hms-scheduler (every minute)
        PHP-FPM opcache tuning for production
```

---

## After Installation

### Access the panel

```
http://YOUR_DOMAIN_OR_IP
```

### Default admin credentials *(change immediately!)*

| Field | Value |
|-------|-------|
| Email | `admin@hotel.com` |
| Password | `password` |

### Credentials file

All passwords are saved to:
```
/root/hms_credentials.txt
```

---

## Service Management

```bash
# Queue worker
systemctl status hms-queue
systemctl restart hms-queue
systemctl stop hms-queue

# Web server
systemctl reload nginx
systemctl status nginx

# PHP
systemctl reload php8.2-fpm

# Database
systemctl status mysql
```

---

## Useful Artisan Commands

```bash
cd /opt/hms

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate --force

# Run a specific seeder
php artisan db:seed --class=UserAccountsSeeder --force

# Check license
php artisan tinker --execute="app(\App\Services\LicenseValidationService::class)->isSystemLicensed();"

# View logs (live)
tail -f storage/logs/laravel.log

# Queue worker (manual)
php artisan queue:work --sleep=3 --tries=3
```

---

## Log Files

| Log | Path |
|-----|------|
| Install log | `/var/log/hms_install.log` |
| Queue worker | `/var/log/hms-queue.log` |
| Nginx access | `/var/log/nginx/access.log` |
| Nginx error | `/var/log/nginx/error.log` |
| Laravel app | `/opt/hms/storage/logs/laravel.log` |

---

## Add SSL After Installation

If you installed without SSL and now have a domain pointing to the server:

```bash
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com --non-interactive --agree-tos -m admin@yourdomain.com --redirect
```

---

## Update the Application

```bash
cd /opt/hms

# Pull latest code (if using git)
git pull

# Re-install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run new migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
systemctl restart hms-queue
```

---

## Uninstall

```bash
cd /tmp/hms-src   # or wherever install.sh is
sudo bash uninstall.sh
```

This removes:
- Application files (`/opt/hms`)
- Nginx virtual host
- Queue worker service
- PHP tuning config
- Cron job

Optionally also drops the database (you will be asked).

---

## Firewall Summary

The installer automatically configures UFW:

| Port | Purpose |
|------|---------|
| 22 | SSH |
| 80 | HTTP |
| 443 | HTTPS |

---

## Troubleshooting

### White screen / 500 error after install
```bash
# Check Laravel logs
tail -50 /opt/hms/storage/logs/laravel.log

# Fix permissions
chown -R www-data:www-data /opt/hms
chmod -R 775 /opt/hms/storage /opt/hms/bootstrap/cache

# Clear caches
cd /opt/hms && php artisan optimize:clear
```

### Database connection refused
```bash
# Check MySQL is running
systemctl status mysql

# Test credentials
mysql -u hms_user -p hms_db
```

### Nginx 502 Bad Gateway
```bash
# Check PHP-FPM is running
systemctl status php8.2-fpm

# Check Nginx error log
tail -20 /var/log/nginx/error.log
```

### Queue jobs not processing
```bash
systemctl restart hms-queue
journalctl -u hms-queue -f
```
