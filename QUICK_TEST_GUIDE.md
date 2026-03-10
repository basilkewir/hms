# Quick Test - Installation Step 6/8 Recovery

## What Was Fixed

Two critical issues were blocking installation at steps 5-6:

1. **Database Seeding Conflict** - Tables not existing when seeders tried to run
2. **Route Function Redeclaration** - Caching was trying to redeclare global functions

## How to Proceed

On your Ubuntu server (192.168.20.85), run:

```bash
# Pull latest fixes
cd /root/hms
git pull origin master

# Clean up old database and installation files (optional but recommended)
sudo systemctl stop nginx mysql php8.2-fpm
sudo rm -rf /opt/hms
mysql -u root -e "DROP DATABASE IF EXISTS hms_db;"
sudo rm -f /var/log/hms_install.log
sudo rm -f /root/hms_credentials.txt

# Run fresh installation
sudo bash install.sh
```

## Expected Output

When you reach **Step 6/8 - Database Migrations**, you should see:

```
═══════════════════════════════════
 6/8 - Database Migrations
═══════════════════════════════════

[INFO]  Running migrations and seeding database...
[INFO]  Seeding additional data...
[INFO]  Building caches...
   INFO  Clearing cached bootstrap files.
   ...
```

**No errors** about missing tables or function redeclaration.

## Installation Timeline Estimate

| Step | Task | Est. Time |
|------|------|-----------|
| 1/8 | PHP/Nginx/MySQL/Node/Composer detection & install | 5-10 min |
| 2/8 | Configuration prompts (domain, hotel info) | 2-3 min |
| 3/8 | Deploy application files | 1 min |
| 4/8 | Create .env file | <1 min |
| 5/8 | Composer & npm dependencies | 10-15 min |
| 6/8 | Database migrations & seeding | 5-10 min |
| 7/8 | Nginx configuration | 1 min |
| 8/8 | Background services setup | <1 min |
| **Total** | **Complete installation** | **~30 minutes** |

## After Installation Completes

You'll see:
```
═══════════════════════════════════════════════════════════
  ✓ Installation Complete!
═══════════════════════════════════════════════════════════

Access Information:
─────────────────────────────────────────────────────────
  URL:      http://192.168.20.85
  
Admin Login:
─────────────────────────────────────────────────────────
  Email:    admin@hotel.com
  Password: password
```

Then:
1. **Open browser**: `http://192.168.20.85`
2. **Login** with admin@hotel.com / password
3. **Change password** immediately
4. **Update hotel info** in Settings

## Troubleshooting

If you hit any errors, check:

```bash
# View full installation log
sudo tail -100 /var/log/hms_install.log

# Check database status
sudo systemctl status mysql
mysql -u root -e "SHOW DATABASES;"

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# View recent errors
sudo tail -20 /opt/hms/storage/logs/laravel.log
```

## Git Commits

Latest fixes are in:
- **Commit 2b39f8b**: "fix: resolve database seeding and route function redeclaration issues"
- **Commit 212a366**: "docs: add installation fix documentation for Phase 10"

Both committed to origin/master.

---

**Ready to test?** Run the installer and report any remaining issues!
