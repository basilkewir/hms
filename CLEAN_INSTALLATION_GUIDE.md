# Complete Clean Installation Guide

## One-Command Complete Reset & Install

Run these commands on your Ubuntu server to completely clean everything and perform a fresh installation:

```bash
# Pull latest code (includes cleanup script)
cd /root/hms
git pull origin master

# Run complete cleanup
sudo bash cleanup.sh

# After cleanup completes, run fresh installation
sudo bash install.sh
```

---

## What the Cleanup Script Does

✅ **Stops Services**
- Nginx
- PHP-FPM 8.2
- MySQL

✅ **Removes Files**
- `/opt/hms` - Application directory
- `/root/hms_credentials.txt` - Saved credentials
- `/var/log/hms_install.log` - Installation log

✅ **Removes Configurations**
- Nginx site configuration
- Systemd services (hms-queue, hms-scheduler)
- Cron jobs

✅ **Cleans Database**
- Drops `hms_db` database
- Removes `hms_user` database user
- Flushes privileges

✅ **Clears Caches**
- PHP session cache
- npm package cache
- Node temporary files

✅ **Updates Repository**
- Fetches latest changes from GitHub
- Resets to latest master branch

---

## Step-by-Step Instructions

### Step 1: Pull Latest Code

```bash
cd /root/hms
git pull origin master
```

Expected output:
```
From github.com:basilkewir/hms
   ffc7695..ae08b72  master     -> origin/master
Fast-forward
 cleanup.sh | 134 +++++++++++++++++++++++++++++++++++++++++
 1 file changed, 134 insertions(+)
```

### Step 2: Run Cleanup Script

```bash
sudo bash cleanup.sh
```

The script will:
1. Ask for confirmation (type `YES`)
2. Stop all services
3. Remove all HMS files
4. Clean database
5. Update git repository

Example output:
```
═══════════════════════════════════════════════════════════
  HMS Complete Cleanup & Reset
═══════════════════════════════════════════════════════════

⚠️  WARNING: This will PERMANENTLY DELETE:
  • /opt/hms application directory
  • /root/hms_credentials.txt
  • /var/log/hms_install.log
  • MySQL database 'hms_db'
  • All HMS-related database users

Are you sure? Type 'YES' to continue: YES
[OK]    Cleanup Complete!
```

### Step 3: Verify Cleanup

```bash
# Check database is empty
mysql -u root -e "SHOW DATABASES;" | grep hms_db

# Check application directory is gone
ls /opt/hms 2>&1 | grep "No such file"

# Check logs are gone
ls /var/log/hms_install.log 2>&1 | grep "No such file"
```

### Step 4: Fresh Installation

```bash
cd /root/hms
sudo bash install.sh
```

---

## Expected Installation Output

Step 6/8 should now show clean migrations:

```
═══════════════════════════════════════════════════════════
 6/8 - Database Migrations
═══════════════════════════════════════════════════════════

[INFO]  The [public/storage] link has been connected to [storage/app/public].
[INFO]  Running migrations and seeding database...

   INFO  Dropping all tables ................................... 11s DONE
   INFO  Preparing database.
   ...
   2026_01_16_222127_create_room_amenities_table ...................... 4s DONE
   2026_01_16_222645_add_features_to_rooms_table ...................... 4s DONE
   [✅ NO DUPLICATE MIGRATION ERROR]
   2026_01_19_000002_fix_room_amenities_table ................... 3.94ms DONE
   ...
   
[OK]    Database ready
```

---

## Troubleshooting

### If cleanup fails with permission error
```bash
# Make sure you're using sudo
sudo bash cleanup.sh

# Or check MySQL is running
sudo systemctl start mysql
sudo bash cleanup.sh
```

### If database won't drop
```bash
# Force MySQL restart
sudo systemctl restart mysql

# Try manual drop
mysql -u root -e "DROP DATABASE hms_db;"
mysql -u root -e "DROP USER 'hms_user'@'localhost';"
```

### If git pull fails
```bash
# Reset git to master
cd /root/hms
git fetch origin
git reset --hard origin/master
git checkout master
```

---

## Complete Clean Timeline

```
Time    Action
────    ──────────────────────────────────────────
0:00    git pull origin master
0:10    sudo bash cleanup.sh  (2-3 minutes)
0:13    sudo bash install.sh  (25-30 minutes)
0:40    ✅ Installation complete!
        Access at http://192.168.20.85
```

---

## After Installation Completes

1. **Open browser**: http://192.168.20.85
2. **Login**: admin@hotel.com / password
3. **Change password** immediately
4. **Update hotel info** in Settings
5. **Configure payment gateway**
6. **Set up email notifications**

---

## Quick Reference Commands

```bash
# View cleanup script
cat cleanup.sh

# View installation log
sudo tail -f /var/log/hms_install.log

# Check database status
mysql -u root -e "SHOW DATABASES;"

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# View application logs
sudo tail -f /opt/hms/storage/logs/laravel.log
```

---

**Latest Commit**: `ae08b72`  
**Script**: `cleanup.sh`  
**Ready for deployment**: ✅ YES

Clean slate ready for fresh installation! 🚀
