# 🚀 QUICK FIX: License Activation - On Your Server NOW

## What Happened

Installation completed successfully! ✅ But the license activation page is showing:

```
License validation failed: {"success":false,"error":"Security check failed","reason":"Failed security checks: request_signature"}
```

This means the `.env` file is missing the `LICENSE_SIGNATURE_SECRET` configuration.

---

## Quick Fix (2 minutes)

### Step 1: SSH to Server

```bash
ssh root@192.168.20.85
```

### Step 2: Check Current APP_KEY

```bash
grep "^APP_KEY=" /opt/hms/.env
```

Copy the value (it looks like: `base64:abc123def456...`)

### Step 3: Add License Secret to .env

```bash
sudo nano /opt/hms/.env
```

Find the line `LICENSE_SERVER_URL=...` and add this right after it:

```bash
LICENSE_SIGNATURE_SECRET=<PASTE_YOUR_APP_KEY_HERE>
```

Example - if your APP_KEY is `base64:xyz789`, then add:
```bash
LICENSE_SIGNATURE_SECRET=base64:xyz789
```

Save file: `Ctrl+X`, then `Y`, then `Enter`

### Step 4: Clear Laravel Cache

```bash
cd /opt/hms
php artisan config:clear
php artisan cache:clear
```

### Step 5: Refresh Browser

Go to: `http://192.168.20.85`

**License activation should now work!** ✅

---

## Manual Entry (If Nano Doesn't Work)

If you're not comfortable with nano, use sed:

```bash
# Get your APP_KEY
APP_KEY=$(grep "^APP_KEY=" /opt/hms/.env | cut -d'=' -f2)

# Add the license signature secret line
sudo sed -i "/^LICENSE_SERVER_URL=/a LICENSE_SIGNATURE_SECRET=${APP_KEY}" /opt/hms/.env

# Verify it was added
grep "LICENSE_SIGNATURE_SECRET" /opt/hms/.env
```

---

## Best Solution: Fresh Install with Latest Code

For future installations, use the updated install script which includes this fix:

```bash
# Pull latest code
cd /root/hms
git pull origin master

# Full reinstall
sudo bash cleanup.sh     # Complete cleanup
sudo bash install.sh     # Fresh install with fix included
```

The new installer will automatically set `LICENSE_SIGNATURE_SECRET` correctly.

---

## What Was Fixed

**Commit 3be5b45**: Modified `install.sh` to add:
```bash
LICENSE_SIGNATURE_SECRET=${APP_KEY}
```

This ensures the `.env` file has the correct signature secret for license validation.

**Commit fcc02d1**: Added comprehensive documentation

---

## After License Activation

Once activated:

1. ✅ System will verify license with kewirdev.com
2. ✅ Features will be unlocked based on your license type
3. ✅ Room limits will be set correctly
4. ✅ Dashboard will show license status

Default admin login:
- **Email**: admin@hotel.com
- **Password**: password

⚠️ **IMPORTANT**: Change admin password immediately after first login!

---

## If Still Having Issues

Check the logs:

```bash
# Application logs
tail -50 /opt/hms/storage/logs/laravel.log

# Check .env was updated
grep "LICENSE_SIGNATURE_SECRET" /opt/hms/.env

# Verify HTTP requests are being made
tail -50 /var/log/nginx/access.log
```

---

## What Signature Secret Does

The signature secret is used to create an HMAC-SHA256 hash of your license request:

```
Request → Hash(secret + payload) → Server verifies signature
```

This prevents:
- ❌ License key tampering
- ❌ Unauthorized installations
- ❌ Key cloning

Using the same secret for both ensures requests are authentic.

---

**Status**: ✅ FIXED in code, needs manual `.env` update on server
**Time**: 2 minutes
**Risk**: Zero - just adding one config line
**Benefit**: Full license validation working

Go ahead and fix it on your server now! 🚀
