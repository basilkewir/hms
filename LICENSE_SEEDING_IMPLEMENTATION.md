# License Seeding Implementation - Complete

## Summary

Implemented automatic database seeding of the **Hotel Donzebe HD** license so the system is immediately functional after installation or updates without needing external license validation.

## What Was Done

### 1. Created LicenseSeeder
**File:** `database/seeders/LicenseSeeder.php`
- Seeds Hotel Donzebe HD license automatically
- License Key: `E7503BB1-99D9EBED-42568D93-E249B472`
- Type: PERPETUAL (Never Expires)
- Status: ACTIVE
- All premium features enabled
- Uses `updateOrCreate()` so it's safe to run multiple times

### 2. Updated DatabaseSeeder
**File:** `database/seeders/DatabaseSeeder.php`
- Added call to `LicenseSeeder::class` at the end of run()
- Ensures license is seeded during fresh installations

### 3. Updated Installation Script
**File:** `install.sh` (Line ~387)
- Added explicit seeding of LicenseSeeder during Step 6 (Migrations)
- License is now ready immediately after fresh install

### 4. Updated Update Script
**File:** `update.sh` (Line ~165)
- Added new "Seeding License Data" step after migrations
- Automatically refreshes/seeds license on every update
- No additional prompts needed

### 5. Created License Seeding Guide
**File:** `LICENSE_SEEDING_GUIDE.md`
- Comprehensive documentation on license seeding
- How to verify license is seeded
- How to modify/update license
- Troubleshooting guide
- Benefits and security notes

### 6. Updated System Management Guide
**File:** `SYSTEM_MANAGEMENT.md`
- Added section 5: License Management
- Quick reference for license details
- Verification commands
- Link to full guide

## License Details

```
Hotel:      Hotel Donzebe HD
License:    E7503BB1-99D9EBED-42568D93-E249B472
Type:       PERPETUAL
Status:     ACTIVE
Expires:    Never
```

### Device Allocation
- TV Devices: 0/80
- Smart Devices: 0/80
- API Access: 0/1

### Enabled Features
✅ IPTV Streaming
✅ VOD Management
✅ Room Management
✅ Guest Management
✅ Reservation System
✅ Billing System
✅ Staff Management
✅ Reporting & Analytics
✅ API Access
✅ Unlimited Users

## Files Modified/Created

| File | Action | Purpose |
|------|--------|---------|
| `database/seeders/LicenseSeeder.php` | ✅ Created | License seeding logic |
| `database/seeders/DatabaseSeeder.php` | ✅ Modified | Added LicenseSeeder call |
| `install.sh` | ✅ Modified | Added license seeding |
| `update.sh` | ✅ Modified | Added license seeding step |
| `LICENSE_SEEDING_GUIDE.md` | ✅ Created | Complete license documentation |
| `SYSTEM_MANAGEMENT.md` | ✅ Modified | Added license management section |

## Installation & Update Workflow

### Fresh Installation
```bash
cd /root/hms
sudo bash install.sh
# License automatically seeded during Step 6
# Ready to use immediately after install completes
```

### System Update
```bash
cd /root/hms
git pull origin master
sudo bash update.sh
# License automatically seeded/refreshed
# No additional steps needed
```

### Manual Seeding (if needed)
```bash
cd /opt/hms
php artisan db:seed --class=LicenseSeeder
```

## Verification Steps

### 1. Check Database
```bash
mysql -u hms_user -p hms_db \
  -e "SELECT license_key, customer_name, status, expires_at FROM licenses;"
```

### 2. Check Web Interface
1. Login as admin
2. Go to Settings → License
3. Should show Hotel Donzebe HD as ACTIVE

### 3. Check Logs
```bash
# During install
tail -50 /var/log/hms_install.log | grep -i license

# During update
tail -50 /var/log/hms_update.log | grep -i license
```

## Benefits

✅ **Immediate Activation** - No manual license activation needed
✅ **Offline Operation** - Works without external API calls
✅ **Idempotent** - Safe to run seeding multiple times
✅ **Development Ready** - Perfect for testing and development
✅ **All Features Enabled** - Full system functionality available
✅ **No Expiration** - Perpetual license means no renewal worries

## How It Works

The `LicenseSeeder` uses Laravel's `updateOrCreate()` method:

```php
License::updateOrCreate(
    ['license_key' => 'E7503BB1-99D9EBED-42568D93-E249B472'],
    [
        'license_type' => 'perpetual',
        'customer_name' => 'Hotel Donzebe HD',
        // ... other fields
    ]
);
```

This means:
- **First run**: Creates the license record
- **Subsequent runs**: Updates the license record
- **Safe to run multiple times**: No errors on repeated seeding

## Next Steps (Optional)

If needed, you can:

1. **Customize License Details**
   - Edit `database/seeders/LicenseSeeder.php`
   - Modify hotel name, email, or other details
   - Run `php artisan db:seed --class=LicenseSeeder`

2. **Add Additional Licenses**
   - Create new seeder `CreateAdditionalLicensesSeeder`
   - Call it from `DatabaseSeeder`

3. **Integrate License Management**
   - Use the existing UI at Settings → License
   - Create/edit/delete licenses via web interface
   - Seeded license provides baseline, UI for management

## Testing

To test the seeding works:

```bash
# On fresh install
1. Run install.sh
2. Login as admin
3. Check Settings → License
4. Should show Hotel Donzebe HD as ACTIVE

# On update
1. Run update.sh
2. Check logs: tail -50 /var/log/hms_update.log
3. Should see: "License seeded successfully"
4. Verify: Check database or web interface
```

## Troubleshooting

**License not showing after install?**
```bash
cd /opt/hms
php artisan db:seed --class=LicenseSeeder
```

**License shows as expired?**
```bash
# Delete and reseed
mysql -u hms_user -p hms_db \
  -e "DELETE FROM licenses WHERE license_key = 'E7503BB1-99D9EBED-42568D93-E249B472';"
php artisan db:seed --class=LicenseSeeder
```

**Permission errors during seeding?**
```bash
cd /opt/hms
php artisan db:seed --class=LicenseSeeder --force
```

## Documentation

- **LICENSE_SEEDING_GUIDE.md** - Comprehensive license guide
- **SYSTEM_MANAGEMENT.md** - System management reference (updated)
- **INSTALL_FROM_SCRATCH.md** - Installation guide
- **update.sh** - Update script (with license seeding)
- **install.sh** - Installation script (with license seeding)

---

**Implementation Date:** March 10, 2026
**License Key:** E7503BB1-99D9EBED-42568D93-E249B472
**Hotel:** Hotel Donzebe HD
**Status:** ✅ Complete and Ready for Deployment
