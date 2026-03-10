# HMS Installation Guide - Ubuntu 20.04 LTS

## System Requirements

- **OS:** Ubuntu 20.04 LTS (Focal)
- **RAM:** 2GB minimum (4GB recommended)
- **Storage:** 10GB minimum
- **Network:** Internet connection required for package downloads

## Pre-Installation

```bash
# Update system
sudo apt-get update
sudo apt-get upgrade -y

# Install required tools (if not present)
sudo apt-get install -y git curl wget

# Optional but recommended
sudo apt-get install -y net-tools
```

## Installation

### Step 1: Clone Repository

```bash
cd /root
rm -rf hms  # Remove old installation if exists
git clone https://github.com/basilkewir/hms.git /root/hms
cd /root/hms
```

### Step 2: Run Installer

```bash
sudo bash install.sh
```

The installer will:

1. **Step 1/8 - Pre-flight Check & System Packages**
   - Detect what's installed
   - Install missing: PHP 8.2, Nginx, MySQL, Node.js, Composer
   - Takes ~5-10 minutes on first install

2. **Step 2/8 - Configuration**
   - Ask for domain/IP address
   - Ask for HTTPS preference
   - Ask for hotel information (name, email, phone, address)
   - Ask for database credentials

3. **Step 3/8 - Application Files**
   - Deploy application files to `/opt/hms`
   - Set proper permissions (www-data:www-data)

4. **Step 4/8 - Configuration**
   - Create `.env` file with database credentials
   - Generate application key

5. **Step 5/8 - Dependencies**
   - Run `composer install`
   - Run `npm install`
   - Build assets with npm

6. **Step 6/8 - Database Migrations**
   - Run database migrations
   - Seed initial data

7. **Step 7/8 - Nginx**
   - Configure Nginx virtual host
   - Enable site

8. **Step 8/8 - Background Services**
   - Start queue worker service
   - Configure scheduler

## Installation Prompts

### Domain/IP
```
Domain or IP [192.168.20.85]: 
```
Leave blank to use the server's IP, or enter a domain name

### HTTPS
```
Use HTTPS? (y/n) [n]:
```
Say `y` if you have an SSL certificate, otherwise `n` for HTTP

### Hotel Information
```
Hotel Name [Grand Hotel]: Your Hotel Name
Hotel Email [info@hotel.com]: admin@yourhotel.com
Hotel Phone [+1234567890]: +1234567890
Hotel Address [123 Hotel St]: Your Hotel Address
```

### Database Credentials
```
Database name [hms_db]: 
Database user [hms_user]: 
Database password [auto, press Enter]: 
```
- Database name and user will be created automatically
- Leave password blank to auto-generate a secure password
- Or enter a custom password

### License Server
```
License server [https://kewirdev.com/api/license]: 
```
Leave blank to use default, or enter custom license server URL

## After Installation

Once installation completes, you'll see:

```
═══════════════════════════════════════════════════════════
  ✓ Installation Complete!
═══════════════════════════════════════════════════════════

Access Information:
───────────────────────────────────────────────────────────
  URL:      http://192.168.20.85

───────────────────────────────────────────────────────────
Admin Login:
───────────────────────────────────────────────────────────
  Email:    admin@hotel.com
  Password: password

───────────────────────────────────────────────────────────
Database Details:
───────────────────────────────────────────────────────────
  Database:  hms_db
  User:      hms_user
  Password:  [generated password]

───────────────────────────────────────────────────────────
⚠️  NEXT STEPS:
───────────────────────────────────────────────────────────
  1. Open your browser and navigate to the URL above
  2. Login with the admin credentials above
  3. Change the admin password immediately
  4. Update hotel information in Settings
  5. Configure payment gateway
  6. Set up email notifications
```

### Accessing the Application

1. Open browser: `http://192.168.20.85` (or your domain/IP)
2. Login with:
   - **Email:** admin@hotel.com
   - **Password:** password

### Change Admin Password

**IMPORTANT:** Change the admin password immediately after first login!

1. Login to the system
2. Go to Profile/Settings
3. Change password

### Database Access (if needed)

```bash
# Connect to database
mysql -u hms_user -p hms_db

# Enter password when prompted
```

### View Installation Log

```bash
tail -f /var/log/hms_install.log
```

### Credentials File

All credentials are saved to:
```bash
cat /root/hms_credentials.txt
```

## Troubleshooting

### Installation Fails at PHP Installation

If PHP packages can't be found:
- The PPA might be unavailable
- Try running installer again
- Check internet connection

### Database Connection Error

If database connection fails after installation:
```bash
# Check MySQL status
sudo systemctl status mysql

# Restart MySQL if needed
sudo systemctl restart mysql

# Verify database exists
mysql -u root -e "SHOW DATABASES;"
```

### Nginx Not Starting

```bash
# Test Nginx configuration
sudo nginx -t

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log

# Restart Nginx
sudo systemctl restart nginx
```

### Application Not Accessible

1. Check if Nginx is running: `sudo systemctl status nginx`
2. Check if PHP-FPM is running: `sudo systemctl status php8.2-fpm`
3. Check firewall: `sudo ufw status`
4. If firewall enabled, allow port 80: `sudo ufw allow 80/tcp`

### Reinstall

To reinstall from scratch:

```bash
# Stop services
sudo systemctl stop nginx mysql php8.2-fpm

# Remove everything
sudo rm -rf /opt/hms
sudo rm -f /root/hms_credentials.txt
sudo rm -f /var/log/hms_install.log

# Remove database (CAREFUL!)
mysql -u root -e "DROP DATABASE IF EXISTS hms_db;"

# Clean up and reinstall
cd /root/hms
sudo bash install.sh
```

## System Services

After installation, these services are running:

- **Nginx:** `sudo systemctl status nginx`
- **MySQL:** `sudo systemctl status mysql`
- **PHP-FPM:** `sudo systemctl status php8.2-fpm`
- **Queue Worker:** `sudo systemctl status hms-queue`
- **Scheduler:** Runs via cron at `/etc/cron.d/hms-scheduler`

## File Locations

- **Application:** `/opt/hms`
- **Logs:** `/var/log/nginx/`, `/var/log/mysql/`
- **Config:** `/opt/hms/.env`
- **Database:** MySQL `hms_db`
- **Credentials:** `/root/hms_credentials.txt`

## Support

For issues or questions:
1. Check logs in `/var/log/hms_install.log`
2. Review Nginx error log: `sudo tail -f /var/log/nginx/error.log`
3. Check application logs: `sudo tail -f /opt/hms/storage/logs/laravel.log`
