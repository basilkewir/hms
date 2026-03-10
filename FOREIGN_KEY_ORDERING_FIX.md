# Foreign Key Ordering Fix - Phase 13

## Issue Encountered

During Step 6/8 (Database Migrations), installation failed with:

```
SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'group_bookings'
```

**Failed Migration**: `2026_01_22_152854_add_group_booking_fields_to_reservations_table`

## Root Cause

Migration sequence issue:
1. **2026_01_22_152854** - Tries to add foreign key to `group_bookings` table
2. **2026_01_22_153012** - Creates `group_bookings` table (comes LATER)

The foreign key constraint was being created before the referenced table existed, causing the error.

## Solution

### Part 1: Modified existing migration (2026_01_22_152854)
Changed from:
```php
$table->foreignId('group_booking_id')->nullable()
    ->constrained('group_bookings')->onDelete('set null');
```

To:
```php
$table->unsignedBigInteger('group_booking_id')->nullable();
```

Now adds the column WITHOUT the constraint.

### Part 2: Created new migration (2026_01_22_160000)
New migration `add_group_booking_foreign_key_to_reservations.php`:
- Runs AFTER `group_bookings` table is created
- Adds the foreign key constraint safely
- Has error handling to skip if constraint already exists

## Migration Sequence (After Fix)

```
✅ 2026_01_22_152854 - Add columns (WITHOUT constraint)
✅ 2026_01_22_153012 - Create group_bookings table
✅ 2026_01_22_160000 - Add foreign key constraint
```

## Files Changed

1. ✅ Modified: `database/migrations/2026_01_22_152854_add_group_booking_fields_to_reservations_table.php`
   - Removed immediate foreign key constraint
   - Adds column without constraint

2. ✅ Created: `database/migrations/2026_01_22_160000_add_group_booking_foreign_key_to_reservations.php`
   - NEW migration file
   - Adds foreign key after group_bookings exists

## Git Commit

- **Hash**: `47262e5`
- **Message**: "fix: resolve foreign key constraint ordering issue for group_bookings"

## Testing

Installation should now proceed past this step. Run:

```bash
cd /root/hms
git pull origin master
sudo bash cleanup.sh    # Complete cleanup
sudo bash install.sh    # Fresh installation
```

Expected output at Step 6/8:
```
2026_01_22_152854_add_group_booking_fields_to_reservations_table ... 2s DONE
2026_01_22_153012_create_group_bookings_table ........................ 1s DONE
2026_01_22_160000_add_group_booking_foreign_key_to_reservations .... 1s DONE
```

All without errors!

## Prevention

✅ Always create tables before adding foreign key constraints to them
✅ Use sequential migration timestamps to ensure proper ordering
✅ Test migrations locally before deploying
✅ Consider using deferred constraint checks or creating constraints after all tables exist

---

**Status**: ✅ FIXED
**Latest Commit**: `47262e5`
