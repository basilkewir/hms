# HMS Installation From Scratch - Complete Guide

## Prerequisites

### System Requirements
- **OS**: Ubuntu 20.04 LTS or 22.04 LTS
- **RAM**: 2GB minimum (4GB recommended)
- **Storage**: 10GB minimum free space
- **Network**: Internet connection for package downloads
- **Access**: Root or sudo access to the server

### What You'll Need
- SSH access to your Ubuntu server (via PuTTY or Terminal)
- A domain name (optional, can use IP address)
- License key from kewirdev.com (optional for demo)

---

## Step 1: Connect to Your Server

### Via PuTTY (Windows)
1. Open PuTTY
2. Enter Host Name: `root@192.168.20.85` (or your server IP)
3. Port: 22
4. Connection Type: SSH
5. Click "Open"
6. Enter your password when prompted

### Via Terminal (Mac/Linux)
```bash
ssh root@192.168.20.85
# Enter password when prompted
```

---

## Step 2: Prepare the System

```bash
# Update system packages
sudo apt-get update
sudo apt-get upgrade -y

# Install essential tools
sudo apt-get install -y git curl wget net-tools
```

**Time**: ~2-3 minutes

---

## Step 3: Clone the Repository

```bash
# Remove old installation if exists
cd /root
rm -rf hms

# Clone HMS repository
git clone https://github.com/basilkewir/hms.git /root/hms
cd /root/hms

# Verify files are present
ls -la
# Should show: install.sh, cleanup.sh, etc.
```

**Time**: ~1 minute

---

## Step 4: (Optional) Clean Up Previous Installation

If you had a previous installation and want a completely fresh start:

```bash
# Stop all services
sudo systemctl stop nginx mysql php8.2-fpm 2>/dev/null || true

# Remove old files
sudo rm -rf /opt/hms
sudo rm -f /root/hms_credentials.txt /var/log/hms_install.log

# Clean database (CAREFUL - deletes data!)
mysql -u root -e "DROP DATABASE IF EXISTS hms_db; DROP USER IF EXISTS 'hms_user'@'localhost';" 2>/dev/null || true

# Clear caches
sudo rm -rf /var/lib/php/sessions/*
npm cache clean --force 2>/dev/null || true
sudo composer cache clear 2>/dev/null || true
```

**⚠️ WARNING**: This deletes all previous HMS data. Only do if you want a fresh start.

**Prefer updating instead?** See the [Update Existing Installation](#updating-existing-installation) section below.

**Time**: ~1 minute

---

## Step 5: Run the Installation Script

```bash
cd /root/hms
sudo bash install.sh
```

The script will now guide you through 8 steps. Here's what to expect:

---

## Installation Steps & Prompts

### Step 1/8 - Pre-flight Check & System Packages
- Detects installed services
- Installs missing: PHP 8.2, Nginx, MySQL, Node.js, Composer
- **Time**: ~10 minutes (first install)
- **Action**: Just wait, no input needed

### Step 2/8 - Configuration Prompts

You'll be asked for:

**Domain or IP Address**:
```
Domain or IP [192.168.20.85]: 
```
- Leave blank to use server's IP
- Or enter: `your-domain.com` or another IP
- Press Enter to continue

**Use HTTPS?**:
```
Use HTTPS? (y/n) [n]:
```
- Type `n` for HTTP (recommended if no SSL certificate)
- Type `y` if you have an SSL certificate
- Press Enter

**Hotel Information** (example):
```
Hotel Name [Grand Hotel]: Donzebe Hotel
Hotel Email [info@hotel.com]: admin@donzebe.com
Hotel Phone [+1234567890]: +237123456789
Hotel Address [123 Hotel St]: Donzebe Street, Cameroon
```
- Customize with your hotel details
- Press Enter after each

**Database Credentials**:
```
Database name [hms_db]: 
Database user [hms_user]: 
Database password [auto, press Enter]: 
```
- Leave all blank to use defaults (recommended)
- Press Enter to auto-generate secure password

**License Server**:
```
License server [https://kewirdev.com/api/license]: 
```
- Leave blank for default
- Press Enter

### Step 3/8 - Application Files
- Deploys files to `/opt/hms`
- Sets permissions
- **Time**: ~2 minutes
- **Action**: Wait

### Step 4/8 - Configuration
- Creates `.env` file
- Generates APP_KEY
- **Time**: <1 minute
- **Action**: Wait

### Step 5/8 - Dependencies
- Runs `composer install`
- Runs `npm install`
- Builds assets
- **Time**: ~5 minutes
- **Action**: Wait

### Step 6/8 - Database Migrations
- Runs database migrations
- Seeds initial data
- **Time**: ~5 minutes
- **Action**: Wait, watch for errors

### Step 7/8 - Nginx Configuration
- Creates virtual host
- Enables site
- **Time**: <1 minute
- **Action**: Wait

### Step 8/8 - Background Services
- Starts queue worker
- Configures scheduler
- **Time**: <1 minute
- **Action**: Wait

---

## Installation Complete!

When done, you'll see:

```
═══════════════════════════════════════════════════════════
  ✓ Installation Complete!
═══════════════════════════════════════════════════════════

Access Information:
───────────────────────────────────────────────────────────
  URL:      http://192.168.20.85

Admin Login:
───────────────────────────────────────────────────────────
  Email:    admin@hotel.com
  Password: password

Database Details:
───────────────────────────────────────────────────────────
  Database:  hms_db
  User:      hms_user
  Password:  [generated password]

────────────────────────────────────────────────────────────
⚠️  NEXT STEPS:
────────────────────────────────────────────────────────────
  1. Open your browser and navigate to the URL above
  2. Login with the admin credentials above
  3. Change the admin password immediately
  4. Update hotel information in Settings
  5. Configure payment gateway
  6. Set up email notifications
```

---

## Post-Installation Steps

### 1. Access the Application

Open your browser and go to:
```
http://192.168.20.85
```
(Or use your domain/IP instead)

### 2. Login with Default Admin

- **Email**: admin@hotel.com
- **Password**: password

### 3. Change Admin Password IMMEDIATELY

1. Click on "Profile" or "Settings" (top right)
2. Go to "Password" or "Change Password"
3. Enter new password
4. Save

### 4. License Activation

If you see a license activation screen:

**Option A - Have a License Key?**
1. Enter your license key
2. Click "Activate License"
3. Done!

**Option B - Using Demo Mode?**
1. Use license key: `DEMO-HMS-ENTERPRISE`
2. Click "Activate License"
3. System works with full features in offline mode

**Option C - License Issues?**
See: `LICENSE_FIX_NOW.md` in the repository

### 5. Configure Hotel Information

Go to **Settings** and update:
- Hotel name
- Email
- Phone
- Address
- Other details

### 6. Check Services Are Running

```bash
# Check all services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status hms-queue
```

All should show: `● service_name.service - ... (running)`

---

## Troubleshooting

### Installation Fails - Check the Log

```bash
tail -100 /var/log/hms_install.log
```

### Common Issues

**PHP Packages Not Found**
```bash
sudo apt-get update
sudo bash install.sh  # Try again
```

**Database Connection Failed**
```bash
sudo systemctl restart mysql
sudo bash install.sh  # Try again
```

**Port 80 Already in Use**
```bash
# Find what's using port 80
sudo lsof -i :80

# Or try different port (edit install.sh)
```

**Permission Denied**
```bash
# Make sure you're using sudo
sudo bash install.sh
```

---

## Installation Timeline

| Component | Time |
|-----------|------|
| System Update | 2-3 min |
| Git Clone | 1 min |
| Step 1/8 (Packages) | 10 min |
| Step 2/8 (Config) | 2 min |
| Step 3/8 (Files) | 2 min |
| Step 4/8 (Env) | 1 min |
| Step 5/8 (Dependencies) | 5 min |
| Step 6/8 (Migrations) | 5 min |
| Step 7/8 (Nginx) | 1 min |
| Step 8/8 (Services) | 1 min |
| **TOTAL** | **~30 minutes** |

---

## Verify Installation Worked

### Check Application Loads
```bash
# Should return HTTP 200
curl -I http://192.168.20.85
```

### Check Database
```bash
# List all tables
mysql -u hms_user -phms_password hms_db -e "SHOW TABLES;" | wc -l
# Should show 100+ tables
```

### Check Services
```bash
sudo systemctl status nginx mysql php8.2-fpm hms-queue
# All should show "active (running)"
```

### Check Logs
```bash
# No errors should appear
tail -20 /opt/hms/storage/logs/laravel.log
```

---

## Key Credentials Saved

All credentials are saved to:
```bash
cat /root/hms_credentials.txt
```

Keep this file safe!

---

## System Information

**After Installation, You Have:**

- **Application**: `/opt/hms`
- **Source**: `/root/hms`
- **Database**: MySQL `hms_db`
- **Web Server**: Nginx on port 80
- **PHP**: PHP 8.2 with FPM
- **Queue Worker**: Running as systemd service
- **Logs**: `/var/log/nginx/`, `/opt/hms/storage/logs/`

---

## Next Guides

- `INSTALLATION_GUIDE.md` - Detailed reference
- `LICENSE_FIX_NOW.md` - If license activation fails
- `TROUBLESHOOTING.md` - Common issues and fixes
- `QUICK_START.md` - Quick reference

---

## Need Help?

Check these in order:
1. Installation log: `/var/log/hms_install.log`
2. Application log: `/opt/hms/storage/logs/laravel.log`
3. Nginx error log: `/var/log/nginx/error.log`
4. MySQL log: `sudo tail -50 /var/log/mysql/error.log`

---

**You're ready to install!** 🚀

Run this command to start:
```bash
sudo bash install.sh
```

---

## Updating Existing Installation

Instead of reinstalling from scratch, you can update your existing HMS installation:

### Update Only (Recommended)

```bash
cd /root/hms
git pull origin master
sudo bash update.sh
```

The update script will:
1. Pull latest code
2. Copy updated files
3. Run database migrations
4. Update dependencies
5. Rebuild assets
6. Restart services
7. **Preserve your database and .env file**

**Time**: ~5-10 minutes

### Backup Before Update

Always backup your database before updating:

```bash
sudo bash backup.sh "before_update"
```

This creates a timestamped SQL backup in `/root/hms_backups/`

### Restore From Backup

If something goes wrong during update:

```bash
# List available backups
ls -lh /root/hms_backups/

# Restore from a specific backup
sudo bash restore.sh /root/hms_backups/hms_db_before_update_20260310_143022.sql
```

---

## Maintenance Scripts

HMS includes three maintenance scripts:

### 1. Update Script (`update.sh`)
Update existing installation without reinstalling:
```bash
sudo bash update.sh
```

Features:
- Pulls latest code
- Updates application files
- Runs new migrations
- Updates dependencies
- Restarts services
- **Preserves database and .env**

### 2. Backup Script (`backup.sh`)
Create database backups:
```bash
# Auto-named backup
sudo bash backup.sh

# Named backup
sudo bash backup.sh "before_update"
```

Creates: `/root/hms_backups/hms_db_[name]_[timestamp].sql`

### 3. Restore Script (`restore.sh`)
Restore from backup:
```bash
# List backups
ls -lh /root/hms_backups/

# Restore
sudo bash restore.sh /root/hms_backups/hms_db_[filename].sql
```

Features:
- Creates safety backup before restore
- Restores database
- Shows restore instructions

---

## Comparing Install vs Update

| Task | Fresh Install | Update |
|------|---------------|--------|
| Time | ~30 minutes | ~5-10 minutes |
| Database | Creates new | Preserves |
| .env | Creates new | Preserves |
| Code | Downloads all | Pulls changes only |
| Use Case | New server | Existing system |

**Use Update script if**: You already have HMS running and just want the latest code
**Use Fresh Install if**: Starting completely from scratch

---

## Typical Update Workflow

```bash
# 1. Backup database
sudo bash backup.sh "before_update"

# 2. Pull latest code
cd /root/hms
git pull origin master

# 3. Update application
sudo bash update.sh

# 4. Verify
curl -I http://192.168.20.85

# 5. Check logs if issues
tail -50 /opt/hms/storage/logs/laravel.log

# 6. If problems, restore
sudo bash restore.sh /root/hms_backups/hms_db_before_update_[timestamp].sql
```

---

**You're ready to install!** 🚀

Run this command to start:
```bash
sudo bash install.sh
```
