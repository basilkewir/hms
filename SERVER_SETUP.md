# Server Setup for LAN Access

This guide explains how to start the Laravel server so it can be accessed from other devices on your local network (LAN), including mobile devices.

## Quick Start

### Windows
```bash
# Double-click or run:
start-server.bat

# Or manually:
php artisan serve --host=0.0.0.0 --port=8000
```

### Linux/Ubuntu/macOS
```bash
# Make script executable (first time only)
chmod +x start-server.sh

# Run the script:
./start-server.sh

# Or manually:
php artisan serve --host=0.0.0.0 --port=8000
```

### Using Composer
```bash
composer run serve-lan
```

## Finding Your Local IP Address

### Windows
```bash
# Method 1: Using ipconfig
ipconfig | findstr IPv4

# Method 2: Using PowerShell
Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.InterfaceAlias -notlike "*Loopback*"} | Select-Object IPAddress
```

### Linux/Ubuntu
```bash
# Method 1: Using hostname
hostname -I

# Method 2: Using ip command
ip addr show | grep "inet " | grep -v 127.0.0.1

# Method 3: Using ifconfig
ifconfig | grep "inet " | grep -v 127.0.0.1
```

### macOS
```bash
# Method 1: Using ipconfig
ipconfig getifaddr en0

# Method 2: Using ifconfig
ifconfig | grep "inet " | grep -v 127.0.0.1
```

## Server Access URLs

Once the server is running with `--host=0.0.0.0`, it will be accessible at:

- **Local access**: `http://localhost:8000`
- **LAN access**: `http://YOUR_IP_ADDRESS:8000` (e.g., `http://192.168.1.100:8000`)

## Mobile App Configuration

When setting up the cleaner mobile app:

1. Find your computer's local IP address (see above)
2. Enter in the app: `http://YOUR_IP_ADDRESS:8000`
   - Example: `http://192.168.1.100:8000`
3. Make sure your phone and computer are on the same Wi-Fi network

## Firewall Configuration

### Windows Firewall
1. Open Windows Defender Firewall
2. Click "Allow an app or feature through Windows Firewall"
3. Click "Change Settings" → "Allow another app"
4. Browse to `php.exe` (usually in `C:\php\` or where XAMPP/WAMP installed it)
5. Check both "Private" and "Public" networks
6. Click "Add"

Or allow port 8000:
```powershell
# Run as Administrator
New-NetFirewallRule -DisplayName "Laravel Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
```

### Linux Firewall (UFW)
```bash
# Allow port 8000
sudo ufw allow 8000/tcp

# Check status
sudo ufw status
```

### macOS Firewall
1. System Preferences → Security & Privacy → Firewall
2. Click "Firewall Options"
3. Click "+" and add PHP or allow incoming connections for Terminal

## Troubleshooting

### Cannot Access from Mobile Device

1. **Check Network**: Ensure phone and computer are on the same Wi-Fi network
2. **Check IP Address**: Verify you're using the correct local IP (not 127.0.0.1 or localhost)
3. **Check Firewall**: Ensure firewall allows connections on port 8000
4. **Check Server**: Verify server is running with `--host=0.0.0.0`
5. **Test Connection**: Try accessing `http://YOUR_IP:8000` from a browser on another device

### Server Not Starting

```bash
# Check if port 8000 is already in use
# Windows:
netstat -ano | findstr :8000

# Linux/macOS:
lsof -i :8000

# Use a different port if needed:
php artisan serve --host=0.0.0.0 --port=8001
```

### Connection Timeout

- Ensure both devices are on the same network
- Check router settings (some routers block device-to-device communication)
- Try disabling VPN if active
- Check if your network is set to "Public" (Windows) - change to "Private"

## Production Deployment

For production, use a proper web server like:
- **Nginx** with PHP-FPM
- **Apache** with mod_php
- **Docker** with proper networking

The `php artisan serve` command is for development only and should not be used in production.

## Environment Variables

Make sure your `.env` file has:
```env
APP_URL=http://YOUR_IP_ADDRESS:8000
# Or for production:
# APP_URL=https://yourdomain.com
```

## Security Notes

⚠️ **Important**: Running with `--host=0.0.0.0` makes your server accessible to anyone on your local network. 

- Only use this in a trusted local network
- Never expose this to the internet without proper security
- Use HTTPS in production
- Keep Laravel and dependencies updated
- Use strong passwords for all user accounts
