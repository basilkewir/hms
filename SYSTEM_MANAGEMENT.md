# HMS System Management Guide

## Overview

HMS provides three main scripts for installation and maintenance:

```
install.sh    → Fresh installation from scratch
update.sh     → Update existing installation without reinstalling
backup.sh     → Backup database before updates
restore.sh    → Restore database from backup
```

---

## 1. Fresh Installation

### When to Use
- Setting up HMS on a new server
- Complete fresh start needed
- Starting from scratch

### Command
```bash
cd /root/hms
sudo bash install.sh
```

### What It Does
1. Installs all system packages (PHP, MySQL, Nginx, Node.js)
2. Creates new database
3. Generates .env file
4. Runs migrations
5. Builds assets
6. Starts services

### Duration
~30 minutes

### Prompts You'll See
- Domain/IP address
- HTTPS preference (y/n)
- Hotel information
- Database credentials
- License server URL

---

## 2. Update Existing Installation

### When to Use
- Adding new features
- Getting bug fixes
- Applying security updates
- HMS already running

### Command
```bash
cd /root/hms
git pull origin master
sudo bash update.sh
```

### What It Does
1. Pulls latest code from GitHub
2. Copies updated application files
3. Runs new database migrations
4. Seeds/refreshes the Hotel Donzebe HD license
5. Updates Composer dependencies
6. Rebuilds assets with npm
7. Restarts services

### What It PRESERVES
✅ Database (all your data)
✅ .env file (configuration)
✅ Storage directory (user uploads)
✅ Admin accounts

### Duration
~5-10 minutes

### No Prompts
- Automatic process
- No user input needed

---

## 3. Database Backup

### When to Use
- Before updating
- Before maintenance
- Regular backups
- Before major changes

### Command
```bash
# Auto-named backup
sudo bash backup.sh

# Named backup (recommended before updates)
sudo bash backup.sh "before_update"
```

### What Gets Backed Up
✅ All database tables
✅ All hotel data
✅ All user accounts
✅ All reservations/bookings
✅ All settings

### Backup Location
```
/root/hms_backups/hms_db_[name]_[timestamp].sql
```

### Duration
1-5 minutes depending on database size

### Example Output
```
[✓] Backup completed successfully!
Backup Details:
  Name: /root/hms_backups/hms_db_before_update_20260310_143022.sql
  Size: 45M
  Date: Fri Mar 10 14:30:22 UTC 2026
```

### List Backups
```bash
ls -lh /root/hms_backups/
```

---

## 4. Database Restore

### When to Use
- Update went wrong
- Need to revert to previous state
- Accidentally deleted data
- Corrupted database

### Command
```bash
# List available backups
ls -lh /root/hms_backups/

# Restore from backup
sudo bash restore.sh /root/hms_backups/hms_db_before_update_20260310_143022.sql
```

### Safety Features
✅ Creates safety backup before restoring
✅ Confirmation prompt required
✅ Shows instructions if failed
✅ Preserves original backup

### Duration
1-5 minutes depending on database size

### Process
1. Lists available backups (if not specified)
2. Shows restoration details
3. Asks for confirmation ("yes" to proceed)
4. Creates safety backup of current database
5. Restores from selected backup
6. Verifies restoration successful

### Example
```bash
$ sudo bash restore.sh

[INFO] No backup file specified. Available backups:
  /root/hms_backups/hms_db_before_update_20260310_143022.sql (45M)
  /root/hms_backups/hms_db_auto_20260310_120000.sql (42M)

[ERROR] Please specify a backup file
```

```bash
$ sudo bash restore.sh /root/hms_backups/hms_db_before_update_20260310_143022.sql

Restore Details:
  Backup file: /root/hms_backups/hms_db_before_update_20260310_143022.sql
  Database: hms_db
  Host: 127.0.0.1
  User: hms_user

⚠️  WARNING: This will overwrite the current database!

Are you sure you want to restore? Type 'yes' to confirm: yes
[✓] Database restored successfully!
```

---

## Typical Workflows

### Weekly Update with Backup

```bash
# 1. Backup current database
sudo bash backup.sh "weekly_$(date +%Y%m%d)"

# 2. Pull and update
cd /root/hms && git pull origin master && sudo bash update.sh

# 3. Verify
curl -I http://192.168.20.85
tail -20 /opt/hms/storage/logs/laravel.log

# 4. If all good, continue
# 5. If problems, restore
sudo bash restore.sh /root/hms_backups/hms_db_weekly_20260310.sql
```

### Emergency Restore

```bash
# Restore from most recent backup
LATEST_BACKUP=$(ls -t /root/hms_backups/*.sql | head -1)
sudo bash restore.sh "$LATEST_BACKUP"

# Restart services
sudo systemctl restart php8.2-fpm nginx

# Check application
curl -I http://192.168.20.85
```

### Quick Update (Experienced Users)

```bash
# Skip backup if confident (not recommended)
cd /root/hms && git pull origin master && sudo bash update.sh
```

---

## Troubleshooting

### Installation Fails
```bash
# Check installation log
tail -100 /var/log/hms_install.log

# Try again
sudo bash install.sh
```

### Update Fails
```bash
# Check update log
tail -100 /var/log/hms_update.log

# Restore from backup
sudo bash restore.sh /root/hms_backups/hms_db_before_update_[timestamp].sql
```

### Can't Access Application After Update
```bash
# Restart services
sudo systemctl restart nginx php8.2-fpm

# Check services
sudo systemctl status nginx php8.2-fpm mysql

# Check application log
tail -50 /opt/hms/storage/logs/laravel.log
```

### Backup/Restore Fails
```bash
# Check MySQL is running
sudo systemctl status mysql

# If not running
sudo systemctl start mysql

# Try backup again
sudo bash backup.sh "retry"
```

---

## Backup Management

### Automatic Cleanup
```bash
# Remove backups older than 30 days
find /root/hms_backups -name "*.sql" -mtime +30 -delete

# Remove backups older than 7 days
find /root/hms_backups -name "*.sql" -mtime +7 -delete
```

### Backup Statistics
```bash
# Total size of all backups
du -sh /root/hms_backups/

# Count backups
ls -1 /root/hms_backups/*.sql | wc -l

# Size of latest backup
ls -lh /root/hms_backups/*.sql | tail -1
```

### Off-Site Backup
```bash
# Copy to another server
scp /root/hms_backups/hms_db_*.sql user@backup-server:/backups/

# Or to cloud storage
aws s3 cp /root/hms_backups/ s3://my-bucket/hms-backups/ --recursive
```

---

## Quick Commands Reference

```bash
# Install fresh
sudo bash install.sh

# Update existing
git pull origin master && sudo bash update.sh

# Backup before update
sudo bash backup.sh "before_update"

# Restore from backup
sudo bash restore.sh /root/hms_backups/hms_db_before_update_*.sql

# List backups
ls -lh /root/hms_backups/

# Check services
sudo systemctl status nginx mysql php8.2-fpm hms-queue

# Restart services
sudo systemctl restart nginx php8.2-fpm

# View logs
tail -50 /opt/hms/storage/logs/laravel.log

# Clear caches
cd /opt/hms && php artisan cache:clear
```

---

## Best Practices

✅ **Always backup before updating**
✅ **Test updates on staging first if possible**
✅ **Keep multiple backups (weekly, monthly)**
✅ **Document changes made to system**
✅ **Monitor logs after updates**
✅ **Update regularly for security**
✅ **Store backups in multiple locations**

---

## 5. License Management

### Automatic License Seeding

HMS automatically seeds the **Hotel Donzebe HD** license during installation and updates. This allows the system to work immediately without external license validation.

### License Details
```
License Key:    E7503BB1-99D9EBED-42568D93-E249B472
Hotel:          Hotel Donzebe HD
Type:           PERPETUAL (Never Expires)
Status:         ACTIVE
```

### Device Allocation
```
TV Devices:     0/80
Smart Devices:  0/80
API Access:     0/1
```

### Verify License

```bash
# Check database
mysql -u hms_user -p hms_db -e "SELECT license_key, customer_name, status FROM licenses;"

# Check in application
# Login as admin → Settings → License
```

### Update License (if needed)

```bash
# Edit the seeder
nano database/seeders/LicenseSeeder.php

# Reseed
php artisan db:seed --class=LicenseSeeder
```

### Full License Guide

For detailed license information, see: `LICENSE_SEEDING_GUIDE.md`

---

## 6. File Locations

| Item | Path |
|------|------|
| **Application** | `/opt/hms` |
| **Source** | `/root/hms` |
| **Config** | `/opt/hms/.env` |
| **Logs** | `/opt/hms/storage/logs/` |
| **Backups** | `/root/hms_backups/` |
| **Database** | MySQL `hms_db` |
| **Credentials** | `/root/hms_credentials.txt` |
| **Install Log** | `/var/log/hms_install.log` |
| **Update Log** | `/var/log/hms_update.log` |

---

**Need help?** Check:
1. Installation log: `/var/log/hms_install.log`
2. Update log: `/var/log/hms_update.log`
3. Application log: `/opt/hms/storage/logs/laravel.log`
