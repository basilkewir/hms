# Room Reservation Status - Quick Reference

## Quick Answer

✅ **Already Implemented**
- When a room is booked, it changes to `reserved` status
- Reserved rooms DO NOT appear on the website booking API
- Same treatment as maintenance/cleaning/occupied/out_of_order rooms

---

## Check Room Status

```bash
cd /opt/hms
mysql -u hms_user -p donzebe_db

SELECT room_number, status, is_active  
FROM rooms 
ORDER BY status;
```

---

## Verify Booking Works

```bash
# Submit a test booking via the Donzebe form
# Then check the room status

mysql -u root donzebe_db -e "
SELECT 
  r.room_number,
  r.status,
  res.reservation_number,
  res.check_in_date,
  res.status as reservation_status
FROM rooms r
LEFT JOIN reservations res ON r.id = res.room_id
WHERE r.id = 81;
"
```

Expected after booking:
```
room_number: 101
status: reserved           ← Changed from 'available'
reservation_number: WEB...
reservation_status: confirmed
```

---

## API Endpoints (All Filter by Status)

| Endpoint | Filters | Shows |
|----------|---------|-------|
| `GET /api/booking/availability` | `status = 'available'` | Room types with availability count |
| `GET /api/public/rooms` | `status = 'available'` | Individual rooms |
| `GET /api/public/room-types` | `status = 'available'` | Room types with availability |

Reserved rooms excluded from all three.

---

## Test Everything

```bash
cd /opt/hms

# Run all booking tests
php artisan test tests/Feature/BookingApiTest.php

# Expected: Tests: 25 passed
```

---

## Troubleshooting

### Booking fails: "Room already reserved"
→ Someone else booked it simultaneously. Retry.

### Still seeing reserved room on booking form
→ Clear browser cache: Ctrl+Shift+Delete (Chrome) or Cmd+Shift+Delete (Mac)

### Room stuck as reserved
→ Manual fix in Tinker:
```bash
php artisan tinker
>>> \App\Models\Room::find(81)->update(['status' => 'available']);
>>> exit
```

---

## Key Code References

- **Booking update:** `app/Http/Controllers/Api/OnlineBookingController.php:404`
- **Availability filter:** `app/Http/Controllers/Api/OnlineBookingController.php:60`
- **Tests:** `tests/Feature/BookingApiTest.php` (25 tests)

---

## Files

- `ROOM_AVAILABILITY_AND_STATUS.md` - Complete guide
- `ROOM_RESERVATION_STATUS_VERIFIED.md` - Verification report
- `tests/Feature/BookingApiTest.php` - Test suite

