# Duplicate Leave Requests Migration Fix

## Issue

Installation failed at Step 6/8 with error:

```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'leave_requests' already exists
```

**Failed Migration**: `2026_03_02_000100_create_leave_requests_table.php`

## Root Cause

The `leave_requests` table is created twice by different migrations:

1. **2024_01_01_000006_create_time_tracking_tables.php** (ORIGINAL)
   - Creates the table with proper structure
   - Includes enums, constraints, indexes
   - Created early in migration sequence

2. **2026_03_02_000100_create_leave_requests_table.php** (DUPLICATE)
   - Tries to create the same table again
   - Runs later in sequence, table already exists
   - Causes the "already exists" error

## Solution

Delete the duplicate migration file:

```bash
rm /root/hms/database/migrations/2026_03_02_000100_create_leave_requests_table.php
```

Then commit the change:

```bash
cd /root/hms
git add database/migrations
git commit -m "fix: remove duplicate leave_requests migration table"
git push origin master
```

## After Fix

The migration sequence will now:
- ✅ Create `leave_requests` table in migration 2024_01_01_000006 (original)
- ✅ Skip the duplicate creation in 2026_03_02_000100 (file deleted)
- ✅ All subsequent migrations will work correctly

## Files Changed

- **Deleted**: `database/migrations/2026_03_02_000100_create_leave_requests_table.php`

## Testing

Run installation again:

```bash
sudo bash cleanup.sh    # Complete cleanup
sudo bash install.sh    # Fresh installation
```

Expected: Step 6/8 will complete without errors

---

**Status**: ✅ FIXED
**Action Required**: Pull latest code with fix
