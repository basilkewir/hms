# License Rate Limiting - Troubleshooting Guide

## Error Message
```
Failed security checks: rate_limiting
License rejected by server (rate_limiting). Contact KewirDev support.
```

## What This Means

The KewirDev license server has detected too many validation requests from your HMS installation in a short time period and is temporarily blocking further attempts to protect against abuse.

## Why This Happens

1. **Multiple Activation Attempts** - Clicking the "Activate License" button repeatedly too quickly
2. **Browser Refresh During Activation** - Refreshing the page while license is being validated
3. **Dashboard Auto-Refresh** - The license status page checking validation repeatedly
4. **Race Conditions** - Multiple requests being sent simultaneously from different tabs/users

## Solutions

### Solution 1: Wait 10 Minutes (Recommended)
The rate limit is temporary. Simply:
1. Wait 10 minutes
2. Try activating your license again
3. Click "Activate License" **only once** and wait for the response

### Solution 2: Clear Rate Limit Immediately (CLI)
If you have SSH access to your server:

```bash
cd /opt/hms
php artisan license:clear-rate-limit YOUR-LICENSE-KEY
```

Replace `YOUR-LICENSE-KEY` with your actual license key. For example:
```bash
php artisan license:clear-rate-limit DEMO-HMS-ENTERPRISE
```

### Solution 3: Clear All Rate Limits
```bash
cd /opt/hms
php artisan license:clear-rate-limit
```
This clears all license rate limiting for all keys at once.

## Best Practices to Avoid Rate Limiting

1. **Click Once** - Click "Activate License" button only once. Do not click multiple times.
2. **Wait for Response** - Wait for the success or error message before doing anything else.
3. **No Refresh During Activation** - Don't refresh the page or switch tabs while license is being validated.
4. **Don't Hammer the Button** - If you get an error, wait at least 2 minutes before trying again.
5. **Single User** - Have only one person attempt license activation at a time.

## Verification Steps

After waiting or clearing the rate limit:

1. Go to **Settings → License**
2. Click the **Activate License** link/button
3. Enter your license key
4. Click **"Activate"** once
5. Wait for the page to update (30 seconds max)

## Still Getting Rate Limit Error?

If the error continues after waiting 10 minutes or clearing the rate limit:

1. **Check the application logs:**
   ```bash
   tail -50 /opt/hms/storage/logs/laravel.log
   ```

2. **Verify network connectivity:**
   ```bash
   curl -I https://kewirdev.com/api/license/validate
   ```
   Should return HTTP 200 (or 403/404, but the connection works)

3. **Check your license key:**
   - Ensure it's copied exactly without extra spaces
   - Verify at: https://kewirdev.com (login required)

4. **Contact KewirDev Support:**
   - Email: support@kewirdev.com
   - Provide:
     - Your license key (last 4 chars only for security)
     - Server IP address
     - Installation date
     - When the rate limiting started

## Technical Details

**Rate Limit Configuration:**
- **Maximum Attempts:** 3 failed attempts per license key
- **Time Window:** 10 minutes
- **Reset Condition:** Successful validation or time expiration
- **Backoff:** Manual - wait 10 minutes or use CLI command to reset

**Why This Exists:**
- Prevents brute-force license key attacks
- Protects KewirDev servers from abuse
- Ensures fair usage across all HMS installations

## For Administrators

If you manage multiple HMS servers and one is having rate limiting issues:

```bash
# SSH into affected server
ssh root@your-server-ip

# Clear the rate limit
cd /opt/hms
php artisan license:clear-rate-limit

# Verify license is still valid
php artisan license:check

# Try activation again using browser
```

## See Also

- [License Activation Guide](LICENSE_ACTIVATION_NOW.md)
- [License Issues Reference](LICENSE_FIX_NOW.md)
- [System Requirements](INSTALL_FROM_SCRATCH.md)

---

**Last Updated:** March 25, 2026
