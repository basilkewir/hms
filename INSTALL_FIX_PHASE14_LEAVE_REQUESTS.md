# INSTALL FIX: Remove Duplicate Leave Requests Migration

## Current Error

```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'leave_requests' already exists
At migration: 2026_03_02_000100_create_leave_requests_table
```

## Problem Analysis

| Migration | Timestamp | Action | Status |
|-----------|-----------|--------|--------|
| 2024_01_01_000006 | Early | Creates `leave_requests` | ✅ SUCCESS |
| 2026_03_02_000100 | Later | Creates `leave_requests` AGAIN | ❌ FAILS - Table exists |

## Solution in 3 Steps

### Step 1️⃣: Delete Duplicate Migration File (LOCAL)

In VS Code, **delete this file**:
```
database/migrations/2026_03_02_000100_create_leave_requests_table.php
```

**DO NOT delete any other files!** This is the ONLY duplicate.

### Step 2️⃣: Commit & Push Changes (LOCAL)

In terminal at workspace root:

```bash
cd /root/hms
git add database/migrations
git commit -m "fix: remove duplicate leave_requests migration"
git push origin master
```

### Step 3️⃣: Pull & Reinstall (SERVER)

SSH into server and run:

```bash
cd /root/hms
git pull origin master

# Clean install
sudo bash cleanup.sh     # Answer 'YES' when prompted
sudo bash install.sh     # Full installation

# Expected: Step 6/8 completes without errors
```

## Verification

After installation completes, check the migration ran once:

```bash
mysql -u hms_user -phms_password hms_db -e "SELECT * FROM migrations WHERE migration LIKE '%leave%';"
```

Should show exactly ONE row for the leave_requests table.

## Root Cause (FYI)

Migration `2026_03_02_000100` was accidentally created without checking if `leave_requests` table already existed in earlier migration `2024_01_01_000006`. Now removing the duplicate.

## Prevention

✅ Always check existing migrations before creating new ones
✅ Search for table names across all migrations
✅ Use `Schema::hasTable()` checks if uncertainty exists

---

**Time to Fix:** 5 minutes
**Risk Level:** Very Low (just removing 1 duplicate file)
**Impact:** Installation will proceed past Step 6/8 ✅
