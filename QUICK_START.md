# Quick Start Guide - LAN Server Access

## 🚀 Start Server for Mobile App Access

### Option 1: Use the Script (Recommended)

**Windows:**
```bash
start-server.bat
```

**Linux/Ubuntu/macOS:**
```bash
chmod +x start-server.sh
./start-server.sh
```

### Option 2: Manual Command

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Option 3: Using Composer

```bash
composer run serve-lan
```

## 📱 Configure Mobile App

1. **Find your IP address:**
   - Windows: `ipconfig | findstr IPv4`
   - Linux: `hostname -I`
   - macOS: `ipconfig getifaddr en0`

2. **Your current IP addresses:**
   - `192.168.93.1` (Virtual adapter)
   - `192.168.91.1` (Virtual adapter)
   - `192.168.1.70` ← **Use this one for LAN access**

3. **Enter in mobile app:**
   ```
   http://192.168.1.70:8000
   ```

## ✅ Verify Server is Running

After starting the server, you should see:
```
Laravel development server started: http://0.0.0.0:8000
```

Test from another device:
- Open browser on phone/tablet
- Go to: `http://192.168.1.70:8000`
- You should see the Laravel welcome page or login screen

## 🔥 Firewall (If Connection Fails)

**Windows:**
```powershell
# Run PowerShell as Administrator
New-NetFirewallRule -DisplayName "Laravel Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
```

**Linux:**
```bash
sudo ufw allow 8000/tcp
```

## 📝 Important Notes

- ✅ Server must be running with `--host=0.0.0.0` (not just `localhost`)
- ✅ Phone and computer must be on the same Wi-Fi network
- ✅ Use the IP address shown above (192.168.1.70)
- ⚠️ This is for development only - use proper web server for production
