# License Signature Secret Issue - Server Configuration Problem

## Problem Identified

Your server's `.env` file is missing the critical `LICENSE_SIGNATURE_SECRET` configuration.

**Current Server `.env` has:**
```bash
APP_KEY=base64:MFmq9j+RvX79/pkAXSIfG5zUrno8b+zSreyL7gE8EXg=
LICENSE_JWT_SECRET=QveLxwQnbNvAMwAouKnu0lYr0S3JrvDnXysG0cR
# ❌ MISSING: LICENSE_SIGNATURE_SECRET
```

**Result**: License validation fails because:
1. License validation service looks for `LICENSE_SIGNATURE_SECRET` in config
2. config/services.php returns empty string (not set)
3. Signature is computed with empty/wrong secret
4. kewirdev.com license server rejects request due to signature mismatch

---

## Quick Fix on Server

### Option 1: Automatic Script (Recommended)

Copy the `license_fix.sh` script to your server and run it:

```bash
# From your server (via PuTTY):
cd /root/hms
bash license_fix.sh
```

This script will:
1. Get your APP_KEY from .env
2. Add LICENSE_SIGNATURE_SECRET with the same value
3. Clear Laravel caches
4. Verify changes

### Option 2: Manual Fix via PuTTY

```bash
# SSH to server
ssh root@192.168.20.85

# Get your APP_KEY value
APP_KEY=$(grep "^APP_KEY=" /opt/hms/.env | cut -d'=' -f2)
echo "Your APP_KEY: $APP_KEY"

# Add LICENSE_SIGNATURE_SECRET to .env
sudo sed -i "/^LICENSE_TOKEN_EXPIRATION=/a LICENSE_SIGNATURE_SECRET=$APP_KEY" /opt/hms/.env

# Verify it was added
grep "LICENSE_SIGNATURE_SECRET" /opt/hms/.env

# Clear caches
cd /opt/hms
php artisan config:clear
php artisan cache:clear
```

### Option 3: Manual Edit with Nano

```bash
sudo nano /opt/hms/.env
```

Find the line with `LICENSE_TOKEN_EXPIRATION=3600` and add this right after it:

```
LICENSE_SIGNATURE_SECRET=base64:MFmq9j+RvX79/pkAXSIfG5zUrno8b+zSreyL7gE8EXg=
```

(Use the same value as your APP_KEY)

Save: `Ctrl+X`, then `Y`, then `Enter`

Then clear caches:
```bash
cd /opt/hms
php artisan config:clear
php artisan cache:clear
```

---

## Why This Happens

The license validation flow:

```
1. User enters license key and clicks "Activate"
   ↓
2. LicenseValidationService.validateLicense() is called
   ↓
3. computeSignature() method computes HMAC-SHA256:
   - secret = config('services.license.signature_secret')  ← THIS IS EMPTY!
   - signature = hash_hmac('sha256', json_encode($payload), $secret)
   ↓
4. Signature sent to kewirdev.com/api/license/validate
   ↓
5. License server verifies signature against its records
   ↓
6. Signature doesn't match → Rejects with "Failed security checks"
```

**Solution**: Set `LICENSE_SIGNATURE_SECRET` to match the `APP_KEY`, which is the shared secret both sides should use.

---

## Code References

**In `app/Services/LicenseValidationService.php`:**
```php
private function computeSignature(array $payload): string
{
    $secret = config('services.license.signature_secret', '');
    // ☝️ Returns empty string if not configured
    
    if (str_starts_with($secret, 'base64:')) {
        $secret = base64_decode(substr($secret, 7));
    }
    
    return hash_hmac('sha256', json_encode($payload), $secret);
}
```

**In `config/services.php`:**
```php
'license' => [
    'signature_secret' => env('LICENSE_SIGNATURE_SECRET', ''),
    // ☝️ Looks for LICENSE_SIGNATURE_SECRET env variable
],
```

---

## Verification After Fix

Run these commands to verify:

```bash
# Check LICENSE_SIGNATURE_SECRET is set
grep "^LICENSE_SIGNATURE_SECRET=" /opt/hms/.env

# Check it matches APP_KEY
echo "APP_KEY:"; grep "^APP_KEY=" /opt/hms/.env
echo "LICENSE_SIGNATURE_SECRET:"; grep "^LICENSE_SIGNATURE_SECRET=" /opt/hms/.env
# Both should show the same base64 value

# Check Laravel can read it correctly
cd /opt/hms
php artisan config:cache
php artisan tinker --execute="echo config('services.license.signature_secret');"
# Should output your base64 key (will be long)
```

---

## After Applying Fix

1. **Refresh browser**: Clear cache and reload https://192.168.20.85
2. **Try license activation**: Enter your license key again
3. **Should succeed**: License will validate and activate

If still failing, check:
```bash
tail -50 /opt/hms/storage/logs/laravel.log
```

---

## Files Involved

- `/opt/hms/.env` - ← **FIX THIS FILE** (add LICENSE_SIGNATURE_SECRET)
- `app/Services/LicenseValidationService.php` - Uses the secret for signing
- `config/services.php` - Reads LICENSE_SIGNATURE_SECRET from env
- `app/Http/Controllers/SystemLicenseController.php` - Calls validation service

---

## Summary

| Item | Status | Fix |
|------|--------|-----|
| APP_KEY exists | ✅ YES | None needed |
| LICENSE_SIGNATURE_SECRET exists | ❌ NO | Add it! |
| License server validation | ❌ FAILING | Will work after fix |

---

**Time to Fix**: 2 minutes
**Risk Level**: Zero (just adding config)
**Impact**: License activation will work immediately
