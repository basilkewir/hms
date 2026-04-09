# Installation Guide — Ubuntu 20.04 / 22.04 / 24.04 LTS

## System Requirements

| | Minimum | Recommended |
|---|---|---|
| **OS** | Ubuntu 20.04 LTS | Ubuntu 22.04 / 24.04 LTS |
| **RAM** | 2 GB | 4 GB |
| **Storage** | 10 GB | 20 GB |
| **Network** | Internet connection required | — |

---

## Installation Modes

When you run the installer you are presented with **three choices**:

```
  1) IPTV Management only
  2) Hotel Management System (HMS) only
  3) Both — IPTV + HMS (full install)
```

| Mode | What is installed | Android TV API | Hotel PMS UI | Front-end build |
|---|---|---|---|---|
| **1 — IPTV only** | Laravel API, MySQL, Nginx, PHP | ✅ | ❌ | Skipped |
| **2 — HMS only** | Full hotel PMS, MySQL, Nginx, PHP, Node.js | ❌ | ✅ | Required |
| **3 — Both** | Everything above | ✅ | ✅ | Required |

> **IPTV-only** installs the API backend that Android TV boxes register against
> (ping, register, settings, heartbeat). No admin web UI is available in the
> browser, but the Android app Settings screen will connect and register just fine.

---

## Pre-Installation

```bash
# Update system
sudo apt-get update && sudo apt-get upgrade -y

# Install required tools
sudo apt-get install -y git curl wget

# Clone the repository
cd /root
git clone https://github.com/basilkewir/hms.git /root/hms
cd /root/hms
```

### Optional: Clean up a previous installation first

```bash
sudo systemctl stop nginx mysql php8.2-fpm 2>/dev/null || true
sudo rm -rf /opt/hms
sudo rm -f /root/hms_credentials.txt /var/log/hms_install.log
mysql -u root -e "DROP DATABASE IF EXISTS hms_db; DROP USER IF EXISTS 'hms_user'@'localhost';" 2>/dev/null || true
```

---

## Running the Installer

```bash
sudo bash install.sh
```

### Step 0 — Choose installation mode

```
  1) IPTV Management only
     Installs the IPTV panel (Xtream Codes management, Android TV
     device registration, channel/VOD push). No hotel PMS features.

  2) Hotel Management System (HMS) only
     Full hotel PMS: reservations, billing, housekeeping, staff,
     POS, reports. No IPTV panel.

  3) Both — IPTV + HMS (full install)
     Everything: hotel PMS + IPTV management + Android TV API.

Enter choice [1/2/3]:
```

### Step 1 — System packages

The installer detects what is already installed and only installs what is missing:
- **PHP 8.2** + extensions (mysql, mbstring, xml, zip, curl, gd, intl, dom)
- **Nginx**
- **MySQL Server**
- **Composer**
- **Node.js 20** *(HMS and Both modes only — skipped for IPTV-only)*

### Step 2 — Configuration prompts

```
Domain or IP [192.168.1.10]:          <- leave blank to use server IP
Use HTTPS? (y/n) [n]:
Cloudflare Tunnel / Public URL (optional, leave blank):
```

**HMS and Both modes only:**
```
Hotel Name [Grand Hotel]:
Hotel Email [info@hotel.com]:
Hotel Phone [+1234567890]:
Hotel Address [123 Hotel St]:
```

**All modes:**
```
Database name [hms_db]:
Database user [hms_user]:
Database password [auto, press Enter]:
License server [https://kewirdev.com/api/license]:
```

After answering the prompts, a summary is shown:

```
Summary:
  Mode    : IPTV Management only
  URL     : http://192.168.1.10
  DB      : hms_db

Proceed? (y/n) [y]:
```

### Steps 3 to 9 — Automated

| Step | Action |
|---|---|
| 3 | Create MySQL database and user |
| 4 | Deploy application files to `/opt/hms` |
| 5 | Write `.env` with all settings and mode flags |
| 6 | `composer install`; `npm run build` *(skipped for IPTV-only)* |
| 7 | `php artisan migrate:fresh --seed` + mode-specific seeders |
| 8 | Configure Nginx virtual host |
| 9 | Start queue worker systemd service + cron scheduler |

---

## After Installation

On success you will see:

```
   INSTALLATION COMPLETE!

  Mode:     IPTV Management only
  URL:      http://192.168.1.10

  Admin Login:
    Email    : admin@hotel.com
    Password : password

  Database:
    Name     : hms_db
    User     : hms_user
    Password : <generated>

  Change the admin password immediately after first login!
  Credentials saved to: /root/hms_credentials.txt
```

---

## Connecting the Android TV App

The Android TV client uses the **Settings screen** to register against the server.

### On the Android device:

1. Open the IPTV app and navigate to **Settings**
2. Enter the server URL in the URL field:
   ```
   http://192.168.1.10        <- your server IP or domain
   ```
3. Tap **Step 1 — Test Connection**
   - The app sends `GET /api/android/ping`
   - Expected result: `✅ Step 1 PASSED — Server is reachable!`
4. Tap **Step 2 — Register Device & Apply Settings**
   - The app sends `POST /api/android/register` with the device ID
   - The server creates a device record and returns a registration token
   - The app immediately fetches settings via `GET /api/android/settings`
   - Expected result: `✅ Step 2 COMPLETE`
5. *(Optional)* Tap **Step 3 — Sync All Settings** to manually re-pull settings.

### Troubleshooting registration

| Symptom | Cause | Fix |
|---|---|---|
| Step 1 FAILED — Host not found | Wrong IP or DNS | Use `http://IP` not a hostname |
| Step 1 FAILED — Connection refused | Server not running | `sudo systemctl status nginx php8.2-fpm` |
| Step 1 FAILED — Timed out | Firewall blocking port 80 | `sudo ufw allow 80/tcp` |
| Step 2 FAILED — Registration failed | API error | Check `/opt/hms/storage/logs/laravel.log` |
| Step 3 FAILED — Not registered yet | Token missing | Re-run Step 2 |

### Assigning a room to a device (HMS and Both modes)

After a device registers it appears in the HMS admin panel under
**IPTV → Devices**. From there you can assign it to a room number.
The room number is returned to the app in the next settings sync.

---

## After Installation — Admin Web UI (HMS and Both modes only)

1. Open a browser: `http://<your-server-ip>`
2. Login:
   - **Email:** `admin@hotel.com`
   - **Password:** `password`
3. **Change the password immediately** — Profile → Change Password

---

## Updating an Existing Installation

```bash
cd /root/hms
git pull
sudo bash update.sh
```

`update.sh` reads `INSTALL_MODE` from `/opt/hms/.env` and automatically:
- Skips the npm front-end build for IPTV-only installs
- Runs `php artisan migrate` (non-destructive, no data loss)
- Clears all caches and restarts services

---

## Uninstalling

```bash
sudo bash uninstall.sh
```

The uninstaller reads `INSTALL_MODE` from `/opt/hms/.env` and removes:
- Application files at `/opt/hms`
- Nginx virtual host
- Queue worker service and cron
- Database *(optional — you are asked)*

---

## System Services

| Service | Command |
|---|---|
| Nginx | `sudo systemctl status nginx` |
| MySQL | `sudo systemctl status mysql` |
| PHP-FPM | `sudo systemctl status php8.2-fpm` |
| Queue worker | `sudo systemctl status hms-queue` |
| Scheduler | Cron at `/etc/cron.d/hms-scheduler` |

---

## File Locations

| Item | Path |
|---|---|
| Application | `/opt/hms` |
| Environment config | `/opt/hms/.env` |
| Laravel logs | `/opt/hms/storage/logs/laravel.log` |
| Nginx logs | `/var/log/nginx/error.log` |
| Install log | `/var/log/hms_install.log` |
| Saved credentials | `/root/hms_credentials.txt` |
| Database | MySQL `hms_db` |

---

## Troubleshooting

### PHP / application errors
```bash
sudo tail -50 /opt/hms/storage/logs/laravel.log
```

### Nginx not starting
```bash
sudo nginx -t
sudo tail -20 /var/log/nginx/error.log
```

### App not accessible
```bash
sudo systemctl status nginx php8.2-fpm
sudo ufw status
sudo ufw allow 80/tcp
```

### Database connection error
```bash
sudo systemctl status mysql
mysql -u root -e "SHOW DATABASES;"
```

### Full reinstall from scratch
```bash
sudo systemctl stop nginx mysql php8.2-fpm
sudo rm -rf /opt/hms
sudo rm -f /root/hms_credentials.txt /var/log/hms_install.log
mysql -u root -e "DROP DATABASE IF EXISTS hms_db; DROP USER IF EXISTS 'hms_user'@'localhost';"
cd /root/hms
sudo bash install.sh
```
