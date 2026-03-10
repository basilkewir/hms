# 🚀 HMS Installation - Quick Reference Card

## Installation in 3 Commands

### Command 1: Connect & Clone
```bash
ssh root@192.168.20.85
cd /root && rm -rf hms
git clone https://github.com/basilkewir/hms.git /root/hms
cd /root/hms
```

### Command 2: Run Installer
```bash
sudo bash install.sh
```

### Command 3: Just Wait (~25 minutes)
Follow the prompts:
- Domain/IP: **[blank]**
- HTTPS: **n**
- Hotel details: **[your info]**
- Database: **[all blank]**
- License: **[blank]**

---

## After Installation (5 minutes)

```bash
# 1. Open browser
http://192.168.20.85

# 2. Login
Email: admin@hotel.com
Password: password

# 3. CHANGE PASSWORD IMMEDIATELY

# 4. Configure hotel info in Settings

# 5. Activate license (if you have a key)
```

---

## If Something Goes Wrong

```bash
# Check what failed
tail -100 /var/log/hms_install.log

# License issues?
cd /root/hms && bash license_fix.sh

# Need to reinstall?
sudo bash cleanup.sh
sudo bash install.sh
```

---

## Key Locations

| Item | Location |
|------|----------|
| **Application** | `/opt/hms` |
| **Source Code** | `/root/hms` |
| **Config** | `/opt/hms/.env` |
| **Database** | `hms_db` |
| **Logs** | `/opt/hms/storage/logs/` |
| **Credentials** | `/root/hms_credentials.txt` |

---

## Verify It Works

```bash
# Check database
mysql -u hms_user -phms_password hms_db -e "SELECT COUNT(*) as tables FROM information_schema.tables WHERE table_schema='hms_db';"
# Should show: 100+

# Check services
sudo systemctl status nginx mysql php8.2-fpm hms-queue
# All should show: active (running)

# Test web access
curl -I http://192.168.20.85
# Should show: HTTP/1.1 200
```

---

## Full Documentation

- **Complete Guide**: `INSTALL_FROM_SCRATCH.md`
- **License Help**: `LICENSE_FIX_NOW.md`
- **Detailed Reference**: `INSTALLATION_GUIDE.md`

---

## 🎯 Timeline

- **Step 1 (Packages)**: 10 min
- **Step 2 (Config)**: 2 min (prompts)
- **Steps 3-8**: 15 min
- **Total**: ~30 minutes ⏱️

---

**Ready? Start with:**
```bash
sudo bash install.sh
```

**Full details in:**
```bash
cat INSTALL_FROM_SCRATCH.md
```
