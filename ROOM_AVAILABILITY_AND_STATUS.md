# Room Availability and Status Management

## Overview

When a room is booked through the HMS booking API, it is automatically marked as **`reserved`** and is immediately removed from the public availability endpoints. This prevents double-booking and ensures guests only see rooms that are actually available.

---

## Room Statuses

The HMS system manages rooms using the following statuses defined in the `rooms` table:

| Status | Meaning | Displayed on Website | Description |
|--------|---------|----------------------|-------------|
| `available` | Ready to book | ✅ Yes | Room is clean, functional, and ready for guests |
| `reserved` | Booked but not checked in | ❌ No | Room has an active booking but guest hasn't arrived yet |
| `occupied` | Guest is currently in room | ❌ No | Room is in use; guest has checked in |
| `cleaning` | Housekeeping is cleaning | ❌ No | Housekeeping staff is actively cleaning the room |
| `maintenance` | Maintenance required | ❌ No | Room needs repair or technical work |
| `out_of_order` | Temporarily unavailable | ❌ No | Room is blocked and cannot be booked (e.g., due to damage) |

---

## Booking Flow and Status Changes

### When a Booking is Created (Online)

```
1. POST /api/booking/create
   ↓
2. Validate booking token
   ↓
3. Check room availability (must be status='available')
   ↓
4. Lock room for update (prevent race conditions)
   ↓
5. Create Guest record
   ↓
6. Create Reservation with status='confirmed'
   ↓
7. Update Room status: 'available' → 'reserved'  ← KEY CHANGE
   ↓
8. Return confirmation with reservation details
   ↓
9. Send confirmation email (queued)
```

### Room Status Timeline

```
Timeline:
---------
Before booking:     [Status: available]  ← shown on website
                    User can book

After booking:      [Status: reserved]   ← NOT shown on website
                    Booking confirmed

At check-in:        [Status: occupied]   ← NOT shown on website
                    Guest has arrived

At check-out:       [Status: cleaning]   ← NOT shown on website
                    Housekeeping cleans

After cleaning:     [Status: available]  ← shown on website again
                    Ready for next guest
```

---

## API Availability Filtering

### Endpoints That Filter by Room Status

#### 1. **GET `/api/booking/availability`** (OnlineBookingController)
**Query only returns room types that have available rooms**

```sql
SELECT room_types.*, COUNT(available_rooms) AS available_rooms
FROM room_types
LEFT JOIN rooms ON rooms.room_type_id = room_types.id
WHERE status = 'available'          ← Only 'available' status
  AND housekeeping_status = 'clean'
  AND is_active = 1
  AND rooms don't have overlapping reservations
```

**What this means:**
- Reserved rooms are NOT included (status ≠ 'available')
- Only rooms with status='available' are shown
- Same filtering applies to maintenance, cleaning, occupied, out_of_order

---

#### 2. **GET `/api/public/rooms`** (BookingController)
**Returns list of currently bookable rooms**

```sql
SELECT rooms.*
FROM rooms
WHERE status = 'available'          ← Only 'available' status
  AND housekeeping_status = 'clean'
  AND is_active = 1
```

**What this means:**
- After booking, room status changes to 'reserved'
- Reserved rooms immediately disappear from this list
- Website booking widget doesn't show already-booked rooms

---

#### 3. **GET `/api/public/room-types`** (BookingController)
**Returns room types with count of available rooms**

```sql
SELECT room_types.*,
       COUNT(rooms.id) AS available_rooms
FROM room_types
LEFT JOIN rooms ON rooms.room_type_id = room_types.id
WHERE rooms.status = 'available'    ← Only 'available' status
  AND rooms.housekeeping_status = 'clean'
  AND rooms.is_active = 1
```

**What this means:**
- If all rooms of a type are reserved/occupied/maintenance, `available_rooms: 0`
- Room type still appears in API but shows as unavailable

---

## Database Implementation

### Rooms Table Schema

```sql
CREATE TABLE rooms (
    id BIGINT UNSIGNED PRIMARY KEY,
    room_number VARCHAR(255) UNIQUE,
    room_type_id BIGINT UNSIGNED,
    status ENUM(
        'available',
        'occupied',
        'maintenance',
        'cleaning',
        'out_of_order',
        'reserved'         ← Added for online bookings
    ) DEFAULT 'available',
    
    housekeeping_status ENUM(
        'clean',
        'dirty',
        'inspected',
        'maintenance_required'
    ) DEFAULT 'clean',
    
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    ...
);
```

---

## Code Implementation

### Booking Creation - Room Status Update

**File:** `app/Http/Controllers/Api/OnlineBookingController.php` (lines ~404-405)

```php
// After reservation is created, mark room as reserved
$availableRoom->update(['status' => 'reserved']);

DB::commit(); // Transaction ends successfully
```

**Key Points:**
- Update happens AFTER reservation is created
- Happens inside database transaction (atomic)
- If any step fails, transaction rolls back and room stays 'available'

---

### Availability Check - Filter Query

**File:** `app/Http/Controllers/Api/OnlineBookingController.php` (lines ~60-62)

```php
$roomTypes = RoomType::where('is_active', true)
    ->where('max_adults', '>=', $adults)
    ->with(['rooms' => function($query) use ($checkIn, $checkOut) {
        $query->where('status', 'available')        ← FILTER HERE
            ->where('housekeeping_status', 'clean')
            ->where('is_active', true)
            ->whereDoesntHave('reservations', ...); ← ALSO check dates
    }])
    ->get();
```

**Two-layer filtering:**
1. **Room status check:** `where('status', 'available')`
2. **Date conflict check:** `whereDoesntHave('reservations', ...)` ensures no overlapping confirmed/checked-in reservations

---

## Verify Reservation Status

### Check Room Status After Booking

```bash
# Connect to HMS database
mysql -u hms_user -p donzebe_db

# Query room status
SELECT 
    rooms.room_number,
    rooms.status,
    reservations.reservation_number,
    reservations.check_in_date,
    reservations.status as reservation_status
FROM rooms
LEFT JOIN reservations ON rooms.id = reservations.room_id
WHERE rooms.id = 81;
```

**Expected output after booking:**
```
+--------------+----------+---------------------+---------------+--------------------+
| room_number  | status   | reservation_number  | check_in_date | reservation_status |
+--------------+----------+---------------------+---------------+--------------------+
| 101          | reserved | WEB20260320C08E0... | 2026-03-21    | confirmed          |
+--------------+----------+---------------------+---------------+--------------------+
```

---

## Testing

### Tests That Verify This Behavior

Run these tests to ensure room reservation works correctly:

```bash
cd /opt/hms

# Test 1: Booking sets room to reserved status
php artisan test tests/Feature/BookingApiTest.php \
    --filter "test_online_create_booking_sets_room_to_reserved"

# Test 2: Reserved rooms excluded from availability API
php artisan test tests/Feature/BookingApiTest.php \
    --filter "test_online_availability_excludes_reserved_rooms"

# Test 3: Reserved rooms excluded from public room listing
php artisan test tests/Feature/BookingApiTest.php \
    --filter "test_public_rooms_excludes_reserved_rooms"

# Test 4: All unavailable statuses are filtered
php artisan test tests/Feature/BookingApiTest.php \
    --filter "test_availability_excludes_unavailable_room_statuses"

# Run all booking tests
php artisan test tests/Feature/BookingApiTest.php
```

All tests should pass ✅

---

## Donzebe Integration (Booking Widget)

### Sequence Diagram

```
Browser                    Donzebe                   HMS
  │                          │                        │
  ├─ Load booking page ────────────────────────────────│
  │                                                     │
  ├─ Select dates & guest count                        │
  │                          │                         │
  ├─ Check availability ────→│                         │
  │                          ├─→ GET /api/booking/availability
  │                          │   (with check-in/out dates)
  │                          │←─ Return available room types
  │                          │   [Standard: 1 available]
  │                          │←─ Response with pricing
  │
  ├─ Display available rooms  │
  ├─ Fill booking form        │
  ├─ Submit ────────────────→│
  │                          ├─→ POST /api/booking/create
  │                          │   X-Booking-Token: [TOKEN]
  │                          │   Create guest & reservation
  │                          │   Update room status→'reserved'
  │                          │←─ Reservation confirmed
  │                          │←─ Send confirmation email
  │                          │
  │←─ Display confirmation     │
  │   Booking successful       │
  │
  ├─ Refresh booking page ────→│
  │                          ├─→ GET /api/booking/availability
  │                          │   (same dates)
  │                          │←─ Return room types
  │                          │   [Standard: 0 available]
  │                          │   ← Room now reserved!
  │
  │   "No more rooms available
  │    for these dates"
  │
```

---

## Frequently Asked Questions

### Q: Why does my booking fail with "Room already reserved"?
**A:** Two bookings attempted simultaneously for the same room/dates. The database uses row locking (`lockForUpdate()`) to prevent this. Both requests run the availability check, but only one acquires the lock and succeeds. The second gets a 409 Conflict error. Retry the booking.

---

### Q: Can I manually change room status?
**A:** Yes, but be careful:

```php
// Via Tinker
php artisan tinker
>>> \App\Models\Room::find(81)->update(['status' => 'available']);
```

**⚠️ Warning:** Only change status to 'available' if:
- The room has been cleaned (housekeeping_status = 'clean')
- There are no overlapping active reservations
- Housekeeping manager has approved release

---

### Q: What happens if connection fails during booking?
**A:** Database transaction ensures atomicity:
- If booking creation fails at ANY step (validation, reservation insert, etc.)
- The entire transaction rolls back
- Room status NEVER changes from 'available'
- Website can immediately retry the booking

---

### Q: How do reserved rooms become available again?
**A:** Manual status change via admin panel or API:

1. **After checkout:** Housekeeping marks room as 'cleaning'
2. **After inspection:** Changed to 'available' or 'reserved' (if next booking same day)
3. **Admin override:** Change status in Settings → Rooms → [Room] → Change Status

---

### Q: Are upcoming bookings shown to new guests?
**A:** No. The API only checks:
1. Room `status = 'available'`
2. No existing reservations that overlap the requested dates

Even if a reservation is confirmed for tomorrow, today's available dates can still be booked for today.

---

## Monitoring & Troubleshooting

### Check Room Status Distribution
```sql
SELECT status, COUNT(*) as room_count
FROM rooms
GROUP BY status;
```

Expected output (3 rooms, 1 booked):
```
Status          Count
available       2
reserved        1
```

### Monitor Booking API Errors
```bash
# Watch for errors in real-time
tail -f /opt/hms/storage/logs/laravel.log | grep "Online booking failed"

# Count booking failures by error type
grep "Online booking failed" /opt/hms/storage/logs/laravel.log | \
  grep -o "Field '[^']*'" | sort | uniq -c
```

---

##Summary

✅ **Rooms automatically change to 'reserved' when booked**
✅ **Reserved rooms are excluded from all availability APIs**
✅ **Treated same as maintenance/cleaning/occupied/out_of_order**
✅ **Atomic transactions prevent double-booking**
✅ **Comprehensive tests verify the behavior**

The system is designed to prevent overbooking while providing a seamless booking experience on your website.

