# 🚀 QUICK FIX - License Signature Issue on Your Server

## The Problem

Your server's `.env` file is **missing** `LICENSE_SIGNATURE_SECRET`, which is why license validation keeps failing with:
```
License validation failed: {"success":false,"error":"Security check failed","reason":"Failed security checks: request_signature"}
```

## The Solution (Choose One)

### ✅ EASIEST: Run the Fix Script

```bash
# From your server (via PuTTY):
cd /root/hms
bash license_fix.sh
```

This automatically:
1. Gets your APP_KEY
2. Adds LICENSE_SIGNATURE_SECRET with the same value
3. Clears caches
4. Shows verification

**Time: 30 seconds**

---

### ✅ MANUAL: One-Command Fix

Copy and paste this entire block into PuTTY:

```bash
APP_KEY=$(grep "^APP_KEY=" /opt/hms/.env | cut -d'=' -f2); \
sudo sed -i "/^LICENSE_TOKEN_EXPIRATION=/a LICENSE_SIGNATURE_SECRET=$APP_KEY" /opt/hms/.env; \
cd /opt/hms; \
php artisan config:clear; \
php artisan cache:clear; \
echo "✓ Fixed! LICENSE_SIGNATURE_SECRET is now:"; \
grep "^LICENSE_SIGNATURE_SECRET=" /opt/hms/.env
```

**Time: 1 minute**

---

### ✅ MANUAL: Step by Step

```bash
# 1. SSH to server
ssh root@192.168.20.85

# 2. Get your APP_KEY
grep "^APP_KEY=" /opt/hms/.env

# 3. Copy the value (looks like: base64:MFmq9j+RvX79...)

# 4. Edit .env
sudo nano /opt/hms/.env

# 5. Find this line:
#    LICENSE_TOKEN_EXPIRATION=3600

# 6. Add this line right after it:
#    LICENSE_SIGNATURE_SECRET=base64:MFmq9j+RvX79...
#    (Paste your APP_KEY value)

# 7. Save file: Ctrl+X, then Y, then Enter

# 8. Clear caches
cd /opt/hms
php artisan config:clear
php artisan cache:clear
```

**Time: 2 minutes**

---

## After Applying Fix

1. **Refresh browser**: https://192.168.20.85 (clear cache with Ctrl+Shift+Delete)
2. **Try license activation**: Enter your license key
3. **Should work now!** ✅

---

## What Changed

**Added to server's `/opt/hms/.env`:**
```bash
LICENSE_SIGNATURE_SECRET=base64:MFmq9j+RvX79/pkAXSIfG5zUrno8b+zSreyL7gE8EXg=
```
(The same value as your APP_KEY)

---

## Why It Works

The license server uses this secret to verify that requests are authentic. Without it, all signatures are computed with an empty/wrong secret, so the server rejects them.

Now both sides have the same secret, so signatures will match ✅

---

## Verify It's Fixed

```bash
# Check the line was added
grep "^LICENSE_SIGNATURE_SECRET=" /opt/hms/.env

# Should output something like:
# LICENSE_SIGNATURE_SECRET=base64:MFmq9j+RvX79...
```

---

## If Still Not Working

Check the logs:
```bash
tail -50 /opt/hms/storage/logs/laravel.log
```

Or manually test the configuration:
```bash
cd /opt/hms
php artisan tinker
>>> config('services.license.signature_secret')
# Should show your base64 key
```

---

**Choose one method above and run it now!** 🚀

Time: 30 seconds to 2 minutes
Result: License will activate successfully
