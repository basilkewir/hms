# URGENT: Duplicate Leave Requests Migration - Step 6/8 Fix

## Problem

Installation at Step 6/8 failed with:

```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'leave_requests' already exists
```

## Root Cause

Two migrations try to create the same `leave_requests` table:

1. ✅ **2024_01_01_000006_create_time_tracking_tables.php** - Creates table correctly (early)
2. ❌ **2026_03_02_000100_create_leave_requests_table.php** - Tries to create again (duplicate!)

When migration 2 runs, the table already exists, causing the error.

## Solution

### Step 1: On Your Local Machine (in VS Code)

Delete the duplicate migration file by running this in terminal:

```powershell
cd "C:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system"
rm "database\migrations\2026_03_02_000100_create_leave_requests_table.php"
git add database/migrations
git commit -m "fix: remove duplicate leave_requests migration"
git push origin master
```

### Step 2: On Server (192.168.20.85)

After you push from local machine, pull the latest fix on the server:

```bash
cd /root/hms
git pull origin master
```

### Step 3: Clean Install

Then run the cleanup and fresh install:

```bash
sudo bash cleanup.sh     # Complete cleanup (type YES when prompted)
sudo bash install.sh     # Fresh installation
```

## Expected Result

Step 6/8 will now complete without errors. The `leave_requests` table will be created exactly once by the original migration (2024_01_01_000006).

## Files Changed

- ❌ **Deleted**: `database/migrations/2026_03_02_000100_create_leave_requests_table.php`

## Why This Happens

During development, migrations sometimes get created without checking if the table already exists. The safest approach is to:
1. Always check if the table was created in an earlier migration
2. If yes, delete the duplicate
3. Only create once

---

**Status**: Ready for fix
**Action**: Delete file locally, push, pull on server, reinstall
**Expected Time**: 5 minutes total
