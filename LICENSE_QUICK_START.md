# Quick Start - License Seeding

## The Short Version

✅ **Hotel Donzebe HD license is now automatically seeded!**

### Installation
```bash
cd /root/hms
sudo bash install.sh
# License ✅ automatically seeded in Step 6
```

### Update
```bash
cd /root/hms
git pull origin master
sudo bash update.sh
# License ✅ automatically seeded after migrations
```

### Verify
```bash
# Check database
mysql -u hms_user -p hms_db \
  -e "SELECT license_key, status FROM licenses LIMIT 1;"

# Check web UI
# Login → Settings → License
# Should show: Hotel Donzebe HD - ACTIVE ✅
```

---

## License Info

```
Key:    E7503BB1-99D9EBED-42568D93-E249B472
Hotel:  Hotel Donzebe HD
Type:   PERPETUAL (Never Expires)
Status: ACTIVE ✅
```

---

## What Changed

| File | Change |
|------|--------|
| `database/seeders/LicenseSeeder.php` | ✨ NEW - License seeding logic |
| `install.sh` | 🔧 Updated - Added license seeding |
| `update.sh` | 🔧 Updated - Added license seeding step |
| `database/seeders/DatabaseSeeder.php` | 🔧 Updated - Calls LicenseSeeder |
| `SYSTEM_MANAGEMENT.md` | 🔧 Updated - Added license section |

---

## Done! ✅

No more manual license activation needed.
The system is ready to go!
