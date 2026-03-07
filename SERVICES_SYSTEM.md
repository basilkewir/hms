# Hotel Services & Breakfast Management System

## Overview
A comprehensive system for managing additional hotel services and breakfast menus that can be controlled by admin and accessed by both the local hotel management system and online reservation website.

## Database Structure

### Tables Created

#### 1. `hotel_services`
Manages all additional services offered by the hotel.

**Columns:**
- `name` - Service name
- `category` - Service category (breakfast, spa, laundry, transport, room, etc.)
- `description` - Detailed description
- `price` - Service price
- `pricing_type` - How service is priced (per_service, per_person, per_night)
- `is_active` - Admin can enable/disable
- `available_online` - Show on online booking website
- `requires_advance_booking` - If advance booking required
- `advance_hours` - Hours needed for advance booking
- `availability_schedule` - JSON field for days/times available
- `max_quantity` - Maximum quantity allowed
- `icon` - Icon identifier
- `sort_order` - Display order

#### 2. `breakfast_menus`
Manages breakfast menu options for guests.

**Columns:**
- `name` - Menu name (e.g., "Continental Breakfast")
- `type` - Menu type (continental, american, buffet, a_la_carte)
- `description` - Menu description
- `price` - Price per person
- `items` - JSON array of menu items
- `serving_time_start` - Start time (e.g., "07:00")
- `serving_time_end` - End time (e.g., "10:30")
- `is_active` - Admin can enable/disable
- `available_online` - Show on online booking
- `image` - Menu image path
- `sort_order` - Display order

#### 3. `reservation_services`
Pivot table linking reservations with services and breakfast menus.

**Columns:**
- `reservation_id` - Foreign key to reservations
- `hotel_service_id` - Foreign key to hotel_services (nullable)
- `breakfast_menu_id` - Foreign key to breakfast_menus (nullable)
- `quantity` - Number of services/persons
- `unit_price` - Price per unit at time of booking
- `total_price` - Total calculated price
- `service_date` - Date service will be provided
- `status` - Service status (pending, confirmed, completed, cancelled)
- `notes` - Additional notes

## Seeded Data

### Hotel Services (5 services)
1. **Airport Transfer** - $50.00 (per_service, requires 24h advance)
2. **Laundry Service** - $15.00 (per_service)
3. **Spa Treatment** - $80.00 (per_person, requires 12h advance)
4. **Extra Bed** - $25.00 (per_night, requires 24h advance)
5. **Late Checkout** - $30.00 (per_service)

### Breakfast Menus (4 options)
1. **Continental Breakfast** - $12.00 (07:00-10:00)
   - Pastries, fruits, yogurt, cereals, beverages

2. **American Breakfast** - $18.00 (07:00-10:30)
   - Eggs, bacon, sausages, hash browns, toast

3. **Breakfast Buffet** - $25.00 (06:30-11:00)
   - All-you-can-eat with international cuisine

4. **Healthy Start** - $15.00 (07:00-10:00)
   - Fruit bowl, Greek yogurt, whole grain toast, smoothie

## API Endpoints (For Online Website)

### Get Available Services
```
GET /api/reservation/services
```
Returns all active services available for online booking.

### Get Breakfast Menus
```
GET /api/reservation/breakfast-menus
```
Returns all active breakfast menus available for online booking.

### Get All Available Options
```
GET /api/reservation/available-options
```
Returns both services (grouped by category) and breakfast menus in one call.

**Response Format:**
```json
{
  "success": true,
  "data": {
    "services": {
      "transport": [...],
      "spa": [...],
      "laundry": [...],
      "room": [...]
    },
    "breakfast_menus": [...]
  }
}
```

## Admin Controllers

### HotelServiceController
**Location:** `app/Http/Controllers/Admin/HotelServiceController.php`

**Methods:**
- `index()` - List all services
- `store()` - Create new service
- `update()` - Update existing service
- `destroy()` - Delete service

**Admin Page:** `Admin/Services/Index.vue` (to be created)

### BreakfastMenuController
**Location:** `app/Http/Controllers/Admin/BreakfastMenuController.php`

**Methods:**
- `index()` - List all breakfast menus
- `store()` - Create new menu
- `update()` - Update existing menu
- `destroy()` - Delete menu

**Admin Page:** `Admin/Breakfast/Index.vue` (to be created)

## Models

### HotelService
**Location:** `app/Models/HotelService.php`
- Relationship: `belongsToMany(Reservation::class)`
- Casts: price, booleans, availability_schedule (array)

### BreakfastMenu
**Location:** `app/Models/BreakfastMenu.php`
- Relationship: `belongsToMany(Reservation::class)`
- Casts: price, items (array), booleans

### ReservationService
**Location:** `app/Models/ReservationService.php`
- Relationships: `belongsTo(Reservation, HotelService, BreakfastMenu)`
- Casts: prices, service_date

## Integration with Reservation System

### Local System (Front Desk/Manager)
When creating a reservation, staff can:
1. Select breakfast menu option for guests
2. Add additional services (spa, transport, etc.)
3. Specify quantity and dates
4. System calculates total price automatically

### Online Website
When guests book online:
1. API provides list of available services and breakfast menus
2. Guest selects desired options during booking
3. Prices are locked at booking time (stored in unit_price)
4. Reservation is created with selected services
5. Local system receives complete reservation with services

## Admin Control Features

### Service Management
- Enable/disable services globally (`is_active`)
- Control online availability (`available_online`)
- Set advance booking requirements
- Configure pricing and categories
- Set display order

### Breakfast Menu Management
- Enable/disable menus
- Control online availability
- Set serving times
- Update menu items and prices
- Upload menu images

## Usage Example

### Adding Services to Reservation (Code Example)
```php
$reservation = Reservation::create([...]);

// Add breakfast menu
$reservation->breakfastMenus()->attach($breakfastMenuId, [
    'quantity' => 2, // 2 persons
    'unit_price' => 18.00,
    'total_price' => 36.00,
    'service_date' => $checkInDate,
    'status' => 'confirmed'
]);

// Add additional service
$reservation->hotelServices()->attach($serviceId, [
    'quantity' => 1,
    'unit_price' => 50.00,
    'total_price' => 50.00,
    'service_date' => $checkInDate,
    'status' => 'pending'
]);
```

## Next Steps

1. **Create Admin UI Pages:**
   - `resources/js/Pages/Admin/Services/Index.vue`
   - `resources/js/Pages/Admin/Breakfast/Index.vue`

2. **Add Routes:**
   ```php
   Route::middleware(['auth', 'role:admin'])->group(function () {
       Route::resource('services', HotelServiceController::class);
       Route::resource('breakfast', BreakfastMenuController::class);
   });
   
   Route::prefix('api/reservation')->group(function () {
       Route::get('services', [ReservationServiceController::class, 'getAvailableServices']);
       Route::get('breakfast-menus', [ReservationServiceController::class, 'getBreakfastMenus']);
       Route::get('available-options', [ReservationServiceController::class, 'getAllAvailableOptions']);
   });
   ```

3. **Update Reservation Forms:**
   - Add service selection to reservation create/edit forms
   - Display breakfast menu options with images
   - Show total price calculation including services

4. **Online Website Integration:**
   - Consume API endpoints
   - Display services during booking flow
   - Calculate and show total price with services

## Security Notes

- Only admin role can enable/disable services
- API endpoints are public (for online website) but only return active services
- Prices are locked at booking time to prevent price manipulation
- Service availability can be controlled per service
- Advance booking requirements are enforced

---

**Status:** ✅ Database migrations completed and seeded
**Migrations:** 3 new tables created
**Models:** 3 new models created
**Controllers:** 3 new controllers created
**Seeded Data:** 5 services + 4 breakfast menus
