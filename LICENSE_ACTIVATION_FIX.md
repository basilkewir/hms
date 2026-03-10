# License Activation - Signature Verification Fix

## Problem

License activation is failing with:
```
License validation failed: {"success":false,"error":"Security check failed","reason":"Failed security checks: request_signature"}
```

## Root Cause

The license validation service computes an HMAC-SHA256 signature of the request using a secret key (`LICENSE_SIGNATURE_SECRET`). This signature is sent to the license server (kewirdev.com) for verification.

**The signature verification is failing because:**
1. The `.env` file on the server doesn't have `LICENSE_SIGNATURE_SECRET` configured
2. Without this, the signature computation defaults to an empty secret
3. The license server rejects the request due to signature mismatch

## Solution

The `.env` file needs to include the license signature secret. This should be the same APP_KEY value used by kewirdev.com's license server.

### Step 1: Edit the .env file on your server

```bash
sudo nano /opt/hms/.env
```

### Step 2: Add the LICENSE_SIGNATURE_SECRET

Add this line to the `.env` file:

```bash
LICENSE_SIGNATURE_SECRET=base64:your_app_key_here
```

**Replace `your_app_key_here` with the same value as your current `APP_KEY`**

You can find your current APP_KEY by checking:
```bash
grep "^APP_KEY=" /opt/hms/.env
```

Example - if your APP_KEY is `base64:abc123def456...`, then add:
```
LICENSE_SIGNATURE_SECRET=base64:abc123def456...
```

### Step 3: Clear Laravel Cache

```bash
cd /opt/hms
php artisan config:clear
php artisan cache:clear
```

### Step 4: Retry License Activation

Go back to: `http://192.168.20.85`
Try activating the license again with your license key.

---

## Alternative: Offline Mode

If you don't have the correct license signature secret, the system has an offline fallback:

The application will automatically fall back to offline mode if:
1. License key contains "DEMO" 
2. Network connection to kewirdev.com fails
3. Connection timeout occurs

In offline mode:
- ✅ System works fully
- ✅ No feature restrictions
- ❌ But license validity isn't verified with the license server

**To enable offline mode:**
1. Use a license key like: `DEMO-12345-DEMO`
2. System will accept it and work in offline mode

Or simply disconnect from internet - system will fall back automatically.

---

## Technical Details

The license service uses HMAC-SHA256 for request signing:

```php
private function computeSignature(array $payload): string
{
    $secret = config('services.license.signature_secret', '');
    
    // Laravel APP_KEY is stored as "base64:<base64data>" — decode it
    if (str_starts_with($secret, 'base64:')) {
        $secret = base64_decode(substr($secret, 7));
    }
    
    return hash_hmac('sha256', json_encode($payload), $secret);
}
```

The signature is computed on:
- license_key
- device_id
- device_type
- device_name
- device_model
- device_os
- device_os_version
- app_version
- mac_address
- metadata

The license server verifies this signature to ensure requests are legitimate.

---

## Configuration in install.sh

The install script should be updated to prompt for or set `LICENSE_SIGNATURE_SECRET`. Currently it may not be setting this value.

To fix this permanently in `install.sh`:

```bash
# In install.sh, where other .env variables are set:
LICENSE_SIGNATURE_SECRET="${APP_KEY}"
```

This ensures the signature secret matches the application key.

---

## Files Related to License

- `app/Services/LicenseValidationService.php` - License validation logic
- `app/Http/Controllers/SystemLicenseController.php` - License activation endpoint
- `config/services.php` - License service configuration
- `/opt/hms/.env` - Environment variables (where LICENSE_SIGNATURE_SECRET belongs)

---

## Next Steps

1. ✅ **Check current APP_KEY**:
   ```bash
   grep "^APP_KEY=" /opt/hms/.env
   ```

2. ✅ **Add LICENSE_SIGNATURE_SECRET** to `.env` with same value

3. ✅ **Clear caches**:
   ```bash
   cd /opt/hms && php artisan config:clear && php artisan cache:clear
   ```

4. ✅ **Try license activation again**

---

**Status**: Needs `.env` configuration
**Impact**: License validation fails without this
**Urgency**: Medium - system works but won't activate properly
