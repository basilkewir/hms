# ✅ Installation Complete - License Activation Steps

## Current Status

Your HMS (Hotel Management System) is **fully installed and running** on 192.168.20.85! 🎉

But it's showing a license activation screen because the `.env` file is missing one configuration line.

---

## What You See

```
License Activation
Enter your license key to unlock the system.

License validation failed: {"success":false,"error":"Security check failed","reason":"Failed security checks: request_signature"}
```

---

## Quick Fix (Choose One Option)

### Option A: Use Updated Installer (Recommended for Fresh Installs)

If you want to reinstall with the latest code that includes the fix:

```bash
cd /root/hms
git pull origin master              # Get latest code with fix
sudo bash cleanup.sh                 # Complete cleanup
sudo bash install.sh                 # Fresh install
```

The new installer will automatically configure LICENSE_SIGNATURE_SECRET.

**Time**: ~25 minutes

---

### Option B: Manual .env Update (Fastest - 2 Minutes)

If your system is already set up and you just want to activate license:

```bash
# 1. SSH into server
ssh root@192.168.20.85

# 2. Get your current APP_KEY
grep "^APP_KEY=" /opt/hms/.env

# 3. Copy that value (looks like: base64:xyz123abc...)

# 4. Add to .env using sed (automatic)
sudo sed -i "/^LICENSE_SERVER_URL=/a LICENSE_SIGNATURE_SECRET=base64:xyz123abc..." /opt/hms/.env

# Replace base64:xyz123abc... with actual value from step 2

# 5. Clear caches
cd /opt/hms
php artisan config:clear
php artisan cache:clear

# 6. Refresh browser - try activation again
```

**Time**: 2 minutes

---

### Option C: Manual Edit (If You Prefer Nano)

```bash
ssh root@192.168.20.85
sudo nano /opt/hms/.env

# Find this line:
# LICENSE_SERVER_URL=https://kewirdev.com/api/license

# Add right after it:
# LICENSE_SIGNATURE_SECRET=base64:xyz123abc...
# (Use same value as your APP_KEY)

# Save: Ctrl+X, Y, Enter

# Then:
cd /opt/hms
php artisan config:clear
php artisan cache:clear
```

---

## After Adding LICENSE_SIGNATURE_SECRET

1. Refresh browser: `http://192.168.20.85`
2. You should see the License Activation form
3. Enter your license key and click "Activate License"
4. If you don't have a license key, use: `DEMO-HMS-ENTERPRISE` for demo mode
5. After activation, login with:
   - **Email**: admin@hotel.com
   - **Password**: password

---

## What Changed in Code

The `install.sh` script was updated to automatically set `LICENSE_SIGNATURE_SECRET` during installation.

**Latest commits**:
- `248bf8b` - Comprehensive post-installation guide
- `0bd5f2b` - Quick license activation fix guide  
- `fcc02d1` - License activation documentation
- `3be5b45` - install.sh updated to include LICENSE_SIGNATURE_SECRET

---

## Why This Was Needed

The HMS system communicates with kewirdev.com's license server to validate your license key. For security, this communication is cryptographically signed using a shared secret (your APP_KEY).

Without the `LICENSE_SIGNATURE_SECRET` in `.env`, the signature verification fails with:
```
"Failed security checks: request_signature"
```

With it added, everything works perfectly.

---

## What LICENSE_SIGNATURE_SECRET Does

It creates a secure signature of your license request:

```
Request Data → Hash(APP_KEY + data) → Signature
              ↓
      License Server Verifies
              ↓
         ✅ Signature Valid? → Allow
         ❌ Signature Invalid? → Reject
```

This prevents:
- License key tampering
- Unauthorized installations
- Key cloning

---

## Next Actions

**RIGHT NOW** (Choose Option A, B, or C above):
1. Add LICENSE_SIGNATURE_SECRET to .env
2. Clear caches
3. Activate your license

**AFTER ACTIVATION** (First Login):
1. Go to: http://192.168.20.85
2. Enter your license key
3. Login as: admin@hotel.com / password
4. **CHANGE THE ADMIN PASSWORD IMMEDIATELY**
5. Configure hotel settings in Settings page

---

## Troubleshooting

### License activation still failing?

1. **Verify the line was added**:
   ```bash
   grep "LICENSE_SIGNATURE_SECRET" /opt/hms/.env
   ```
   Should show: `LICENSE_SIGNATURE_SECRET=base64:...`

2. **Check caches were cleared**:
   ```bash
   cd /opt/hms && php artisan config:show | grep LICENSE_SIGNATURE_SECRET
   ```
   Should show your APP_KEY value

3. **Check application logs**:
   ```bash
   tail -50 /opt/hms/storage/logs/laravel.log
   ```

### Still not working?

Check network connectivity to license server:
```bash
curl -I https://kewirdev.com/api/license
```

Should return a response (might be 404 but should connect).

If no connection, you may need to allow outbound HTTPS:
```bash
sudo ufw allow out 443/tcp
sudo ufw allow out 80/tcp
```

---

## Support

All documentation is in the repository:

1. **LICENSE_FIX_QUICK.md** - This quick guide
2. **LICENSE_ACTIVATION_FIX.md** - Technical details
3. **INSTALLATION_COMPLETE_POST_LICENSE.md** - Full summary
4. **INSTALLATION_GUIDE.md** - Complete installation reference

---

## System Is Ready! 🚀

Your HMS system is:
- ✅ Fully installed
- ✅ Running on http://192.168.20.85
- ✅ Database with all tables
- ✅ Services active
- ⏳ Just needs license activation (2 minutes)

You're very close! Just add that one line to `.env` and you're done.

---

**Questions?** Check the documentation files above or contact support@kewirdev.com

**Ready?** Follow Option A, B, or C above and activate your license now! 🎉
