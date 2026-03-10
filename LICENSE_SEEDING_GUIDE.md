# Hotel Donzebe HD License Seeding Guide

## Overview

The HMS system now automatically seeds the **Hotel Donzebe HD** license during installation and updates. This eliminates the need for manual license validation and allows the system to work offline without requiring external API calls.

## License Details

```
License Key:    E7503BB1-99D9EBED-42568D93-E249B472
Hotel:          Hotel Donzebe HD
Type:           PERPETUAL (Never Expires)
Status:         ACTIVE
Device Limits:
  - TV Devices:     0/80
  - Smart Devices:  0/80
  - API Access:     0/1
```

## Automatic Seeding

### During Fresh Installation

The license is automatically seeded when you run:

```bash
sudo bash install.sh
```

The `install.sh` script will:
1. Run migrations
2. Seed all seeders (including LicenseSeeder)
3. Activate the Hotel Donzebe HD license
4. Make the system ready to use

### During System Updates

The license is automatically updated/refreshed when you run:

```bash
sudo bash update.sh
```

The `update.sh` script will:
1. Pull latest code
2. Run new migrations
3. Seed/refresh the Hotel Donzebe HD license
4. Update dependencies
5. Restart services

## Manual License Seeding

If you need to manually seed the license (without running full install/update):

```bash
cd /opt/hms
php artisan db:seed --class=LicenseSeeder
```

Expected output:
```
✓ Hotel Donzebe HD license seeded successfully!
```

## License Data Structure

The seeded license includes:

```php
[
    'license_key' => 'E7503BB1-99D9EBED-42568D93-E249B472',
    'license_type' => 'perpetual',
    'product_name' => 'Hotel Management System',
    'customer_name' => 'Hotel Donzebe HD',
    'customer_email' => 'admin@donzebe.com',
    'organization' => 'Donzebe Hotel',
    'max_devices' => 80,
    'max_rooms' => 100,
    'max_channels' => 500,
    'vod_enabled' => true,
    'premium_features' => true,
    'status' => 'active',
    'expires_at' => null, // PERPETUAL
    'allowed_features' => [
        'iptv_streaming' => true,
        'vod_management' => true,
        'room_management' => true,
        'guest_management' => true,
        'reservation_system' => true,
        'billing_system' => true,
        'staff_management' => true,
        'reporting_analytics' => true,
        'api_access' => true,
        'unlimited_users' => true,
    ]
]
```

## Verification

### Check License in Database

```bash
# View the seeded license
mysql -u hms_user -p hms_db -e "SELECT license_key, customer_name, status, expires_at FROM licenses;"
```

Expected output:
```
+----------------------------------------+--------------------+--------+------------+
| license_key                            | customer_name      | status | expires_at |
+----------------------------------------+--------------------+--------+------------+
| E7503BB1-99D9EBED-42568D93-E249B472   | Hotel Donzebe HD   | active | NULL       |
+----------------------------------------+--------------------+--------+------------+
```

### Check in Web Interface

1. Login to HMS as admin
2. Go to **Settings** → **License**
3. You should see:
   - License Key: `E7503BB1-99D9EBED-42568D93-E249B472`
   - Status: **ACTIVE**
   - Expires: **Never Expires** (Perpetual)
   - All features **ENABLED**

### Check License Validation

```bash
# Run the license check script (if available)
php check_license.php

# Or via API
curl -s http://192.168.20.85/api/license/status
```

## How It Works

### LicenseSeeder Class

The `database/seeders/LicenseSeeder.php` contains the license seed logic:

```php
class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        License::updateOrCreate(
            ['license_key' => 'E7503BB1-99D9EBED-42568D93-E249B472'],
            [
                'license_type' => 'perpetual',
                'customer_name' => 'Hotel Donzebe HD',
                // ... other details
            ]
        );
    }
}
```

**Key Features:**
- Uses `updateOrCreate()` - idempotent (safe to run multiple times)
- Creates if doesn't exist
- Updates if already exists (useful for updates)
- No external API calls needed
- Works offline

### Integration Points

1. **install.sh** - Line ~387
   ```bash
   sudo -u www-data php artisan db:seed --class=LicenseSeeder --force
   ```

2. **update.sh** - Line ~165
   ```bash
   sudo -u www-data php artisan db:seed --class=LicenseSeeder
   ```

3. **DatabaseSeeder.php** - Line ~75
   ```php
   $this->call(LicenseSeeder::class);
   ```

## Updating License Details

### Via Database

To modify the seeded license details:

```bash
mysql -u hms_user -p hms_db

UPDATE licenses 
SET customer_email = 'newemail@donzebe.com'
WHERE license_key = 'E7503BB1-99D9EBED-42568D93-E249B472';
```

### Via Code

Edit `database/seeders/LicenseSeeder.php`:

```php
License::updateOrCreate(
    ['license_key' => 'E7503BB1-99D9EBED-42568D93-E249B472'],
    [
        'customer_email' => 'new@donzebe.com',  // Change this
        // ... other fields
    ]
);
```

Then re-run:
```bash
php artisan db:seed --class=LicenseSeeder
```

## Troubleshooting

### License Not Showing After Update

1. Check if seeding ran:
   ```bash
   tail -20 /var/log/hms_update.log
   ```

2. Manually seed:
   ```bash
   cd /opt/hms
   php artisan db:seed --class=LicenseSeeder
   ```

3. Check database:
   ```bash
   mysql -u hms_user -p hms_db -e "SELECT COUNT(*) FROM licenses;"
   ```

### License Shows Expired

The seeded license has `expires_at = null` (perpetual). If it shows as expired:

1. Delete and re-seed:
   ```bash
   mysql -u hms_user -p hms_db -e "DELETE FROM licenses WHERE license_key = 'E7503BB1-99D9EBED-42568D93-E249B472';"
   php artisan db:seed --class=LicenseSeeder
   ```

2. Clear caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

### Cannot Seed (Permission Error)

Run with proper user:
```bash
sudo -u www-data php artisan db:seed --class=LicenseSeeder
```

## Benefits

✅ **No Manual Activation** - License is ready immediately after install/update
✅ **Offline Operation** - Works without external API validation
✅ **Idempotent** - Safe to seed multiple times
✅ **Development Friendly** - Useful for testing and development
✅ **No License Expiration** - Perpetual license (never expires)
✅ **Full Features Enabled** - All premium features included

## Security Notes

- The license is stored in the database, not in code
- It can be modified via database or UI
- For production, consider:
  - Using environment variables for sensitive data
  - Backing up the license regularly
  - Monitoring license usage
  - Validating against actual license server periodically

## Support

If you need to:
- **Modify the license**: Edit the seeder or update via database
- **Add new licenses**: Create a new seeder or use the UI
- **Validate the license**: Check the license check script

---

**Last Updated:** March 10, 2026
**License Key:** E7503BB1-99D9EBED-42568D93-E249B472
**Hotel:** Hotel Donzebe HD
