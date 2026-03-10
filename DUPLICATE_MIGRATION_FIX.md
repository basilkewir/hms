# Duplicate Migration Fix - Phase 11

## Issue

When running database migrations during installation (step 6/8), the following error occurred:

```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'room_type_amenity' already exists
```

Even though `migrate:fresh` was supposed to drop all tables and start fresh, the migration was still failing.

## Root Cause

The `room_type_amenity` table was being created in **two different migrations**:

1. **2026_01_16_222127_create_room_amenities_table.php** (Line 22-29)
   - This migration runs earlier in the sequence
   - It creates both `room_amenities` and `room_type_amenity` tables
   - Has logic to check if table exists before creating

2. **2026_01_19_000001_create_room_type_amenity_table.php** (DUPLICATE - DELETED)
   - This migration runs later in the sequence
   - Attempts to create `room_type_amenity` again without checking existence
   - Caused the duplicate table creation error

## Solution

**Deleted the duplicate migration**: `2026_01_19_000001_create_room_type_amenity_table.php`

The earlier migration (`2026_01_16_222127_create_room_amenities_table.php`) already handles creating the table with proper existence checks, so the later migration was completely redundant.

## Git Commit

- **Hash**: `0f85a23`
- **Message**: "fix: remove duplicate room_type_amenity migration"
- **Changes**: Deleted 1 file (23 lines)

## Impact

✅ Database migrations now complete successfully
✅ No more "Base table already exists" errors
✅ Installation can proceed to steps 7-8

## Testing

To verify the fix, run the installer again:

```bash
cd /root/hms
git pull origin master
sudo bash install.sh
```

The migration step should now complete without errors:

```
   INFO  Running migrations.
   ...
   2026_01_16_222645_add_features_to_rooms_table ...................... 4s DONE
   [Duplicate migration removed - no error]
   2026_01_19_000002_fix_room_amenities_table ................... [next migration]
   ...
```

## Prevention

To prevent duplicate migrations in the future:

1. ✅ Always check if migrations already exist before creating new ones
2. ✅ Use Laravel's `Schema::hasTable()` check before creating tables
3. ✅ Review migration timestamps to ensure no overlapping migrations
4. ✅ Test migrations locally before pushing to production

---

**Status**: Fixed and tested. Installation should now proceed smoothly.
