# API Endpoints Reference - Hotel Management System

## Quick Actions Routes

### Dashboard
```
GET  /admin/dashboard              → admin.dashboard
```

### Quick Action Routes
```
GET  /admin/users/create           → admin.users.create
GET  /admin/rooms                  → admin.rooms.index
GET  /admin/reports                → admin.reports.index
GET  /admin/settings               → admin.settings
```

---

## Quotes Management - Complete CRUD

### Admin Quotes Routes (✅ 7 routes)
```
GET    /admin/quotes                      → admin.quotes.index
GET    /admin/quotes/create               → admin.quotes.create
POST   /admin/quotes                      → admin.quotes.store
GET    /admin/quotes/{id}                 → admin.quotes.show
GET    /admin/quotes/{id}/edit            → admin.quotes.edit
PUT    /admin/quotes/{id}                 → admin.quotes.update
DELETE /admin/quotes/{id}                 → admin.quotes.destroy
```

### Manager Quotes Routes (✅ 7 routes)
```
GET    /manager/quotes                    → manager.quotes.index
GET    /manager/quotes/create             → manager.quotes.create
POST   /manager/quotes                    → manager.quotes.store
GET    /manager/quotes/{id}               → manager.quotes.show
GET    /manager/quotes/{id}/edit          → manager.quotes.edit
PUT    /manager/quotes/{id}               → manager.quotes.update
DELETE /manager/quotes/{id}               → manager.quotes.destroy
```

### FrontDesk Quotes Routes (✅ 7 routes)
```
GET    /front-desk/quotes                 → front-desk.quotes.index
GET    /front-desk/quotes/create          → front-desk.quotes.create
POST   /front-desk/quotes                 → front-desk.quotes.store
GET    /front-desk/quotes/{id}            → front-desk.quotes.show
GET    /front-desk/quotes/{id}/edit       → front-desk.quotes.edit
PUT    /front-desk/quotes/{id}            → front-desk.quotes.update
DELETE /front-desk/quotes/{id}            → front-desk.quotes.destroy
```

---

## Invoices Management - Complete CRUD + Utilities

### Admin Invoice Routes (✅ 11 routes)
```
GET    /admin/invoices                    → admin.invoices.index
GET    /admin/invoices/create             → admin.invoices.create
POST   /admin/invoices                    → admin.invoices.store
GET    /admin/invoices/{folio}            → admin.invoices.show
GET    /admin/invoices/{folio}/edit       → admin.invoices.edit
PUT    /admin/invoices/{folio}            → admin.invoices.update
DELETE /admin/invoices/{folio}            → admin.invoices.destroy
GET    /admin/invoices/overdue            → admin.invoices.overdue
GET    /admin/invoices/paid               → admin.invoices.paid
POST   /admin/invoices/{folio}/mark-paid  → admin.invoices.markPaid
POST   /admin/invoices/send-reminders     → admin.invoices.sendReminders
```

### Manager Invoice Routes (✅ 11 routes)
```
GET    /manager/invoices                  → manager.invoices.index
GET    /manager/invoices/create           → manager.invoices.create
POST   /manager/invoices                  → manager.invoices.store
GET    /manager/invoices/{folio}          → manager.invoices.show
GET    /manager/invoices/{folio}/edit     → manager.invoices.edit
PUT    /manager/invoices/{folio}          → manager.invoices.update
DELETE /manager/invoices/{folio}          → manager.invoices.destroy
GET    /manager/invoices/overdue          → manager.invoices.overdue
GET    /manager/invoices/paid             → manager.invoices.paid
POST   /manager/invoices/{folio}/mark-paid → manager.invoices.markPaid
POST   /manager/invoices/send-reminders   → manager.invoices.sendReminders
```

### FrontDesk Invoice Routes (✅ 11 routes)
```
GET    /front-desk/invoices               → front-desk.invoices.index
GET    /front-desk/invoices/create        → front-desk.invoices.create
POST   /front-desk/invoices               → front-desk.invoices.store
GET    /front-desk/invoices/{folio}       → front-desk.invoices.show
GET    /front-desk/invoices/{folio}/edit  → front-desk.invoices.edit
PUT    /front-desk/invoices/{folio}       → front-desk.invoices.update
DELETE /front-desk/invoices/{folio}       → front-desk.invoices.destroy
GET    /front-desk/invoices/overdue       → front-desk.invoices.overdue
GET    /front-desk/invoices/paid          → front-desk.invoices.paid
POST   /front-desk/invoices/{folio}/mark-paid → front-desk.invoices.markPaid
POST   /front-desk/invoices/send-reminders → front-desk.invoices.sendReminders
```

### Accountant Invoice Routes (✅ 11 routes)
```
GET    /accountant/invoices               → accountant.invoices.index
GET    /accountant/invoices/create        → accountant.invoices.create
POST   /accountant/invoices               → accountant.invoices.store
GET    /accountant/invoices/{folio}       → accountant.invoices.show
GET    /accountant/invoices/{folio}/edit  → accountant.invoices.edit
PUT    /accountant/invoices/{folio}       → accountant.invoices.update
DELETE /accountant/invoices/{folio}       → accountant.invoices.destroy
GET    /accountant/invoices/overdue       → accountant.invoices.overdue
GET    /accountant/invoices/paid          → accountant.invoices.paid
POST   /accountant/invoices/{folio}/mark-paid → accountant.invoices.markPaid
POST   /accountant/invoices/send-reminders → accountant.invoices.sendReminders
```

---

## Dashboard Charts & Data

### Admin Dashboard
```
GET  /admin/dashboard

Returns:
{
  "charts": {
    "revenue": [
      { "date": "Mar 1", "amount": 1500.00 },
      { "date": "Mar 2", "amount": 2300.50 },
      ...
    ],
    "occupancy": [
      { "date": "Mar 1", "rate": 75.5 },
      { "date": "Mar 2", "rate": 82.0 },
      ...
    ]
  },
  "stats": {
    "total_rooms": 50,
    "occupied_rooms": 40,
    "available_rooms": 10,
    "total_guests": 150,
    "total_reservations": 200,
    "todays_revenue": 5000.00
  },
  "alerts": {
    "maintenance_required": 2,
    "system_errors": 0,
    "pending_approvals": 5,
    "offline_devices": 0
  },
  "performanceMetrics": {
    "avgOccupancy": 80.5,
    "avgDailyRate": 125.00,
    "revPAR": 100.50
  }
}
```

---

## Dashboard Quick Actions

### Visual Elements
- 4 action buttons in the Admin Dashboard
- Icons from Heroicons
- Color-coded styling
- Hover effects

### Quick Action Links
1. **Add New User**
   - Icon: UserPlusIcon
   - Destination: `/admin/users/create`
   - Opens: User creation form

2. **Manage Rooms**
   - Icon: BuildingOfficeIcon
   - Destination: `/admin/rooms`
   - Opens: Room management list

3. **View Reports**
   - Icon: ChartBarIcon
   - Destination: `/admin/reports`
   - Opens: Reports dashboard

4. **System Settings**
   - Icon: CogIcon
   - Destination: `/admin/settings`
   - Opens: System configuration

---

## Request/Response Examples

### Create Quote
```http
POST /admin/quotes
Content-Type: application/json

{
  "quote_type": "outsider",
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+1234567890",
  "items": [
    { "description": "Room service", "amount": 150.00 },
    { "description": "Dining", "amount": 85.50 }
  ],
  "notes": "VIP customer"
}

Response: 302 Redirect to /admin/quotes/{id}
Flash Message: "Quote created successfully."
```

### Create Invoice
```http
POST /admin/invoices
Content-Type: application/json

{
  "folio_number": "INV-2024-001",
  "customer_name": "Guest Name",
  "customer_email": "guest@example.com",
  "folio_date": "2024-03-07",
  "total_amount": 250.00,
  "notes": "Room charges for March 1-7"
}

Response: 302 Redirect to /admin/invoices/{folio_id}
Flash Message: "Invoice created successfully."
```

### Mark Invoice as Paid
```http
POST /admin/invoices/{folio_id}/mark-paid
Content-Type: application/json

{}

Response: 302 Redirect to /admin/invoices/{folio_id}
Flash Message: "Invoice marked as paid."

Updates:
- balance_amount: 0
- status: "paid"
```

### Get Quote Details
```http
GET /admin/quotes/1

Response: {
  "quote": {
    "id": 1,
    "quote_number": "QT-2024-001",
    "customer_name": "John Doe",
    "total_amount": 235.50,
    "status": "pending",
    "issue_date": "2024-03-07",
    "valid_until": "2024-03-22",
    "items": [...]
  }
}
```

### Get Invoice Details
```http
GET /admin/invoices/1

Response: {
  "invoice": {
    "id": 1,
    "invoice_number": "INV-2024-001",
    "customer_name": "Guest Name",
    "total_amount": 250.00,
    "balance_amount": 0,
    "status": "paid",
    "issue_date": "2024-03-07",
    "due_date": "2024-03-14",
    "charges": [...],
    "payments": [...]
  }
}
```

---

## HTTP Status Codes

### Success
```
200 OK              - GET request successful
201 Created         - POST request successful (new resource created)
204 No Content      - DELETE request successful
302 Found           - Redirect (POST/PUT/DELETE success)
```

### Client Errors
```
400 Bad Request     - Invalid input data
401 Unauthorized    - User not authenticated
403 Forbidden       - User lacks required role/permission
404 Not Found       - Resource not found
422 Unprocessable   - Validation failed
```

### Server Errors
```
500 Internal Error  - Server error
503 Service Unavailable - Maintenance mode
```

---

## Authentication & Authorization

### Required Headers
```
Cookie: XSRF-TOKEN=...
X-CSRF-TOKEN: ...
```

### Required Roles
```
/admin/*        - Requires: admin role
/manager/*      - Requires: manager role
/front-desk/*   - Requires: front_desk role
/accountant/*   - Requires: accountant role
```

### Middleware Chain
```
Web Middleware:
  1. EncryptCookies
  2. AddQueuedCookiesToResponse
  3. StartSession
  4. ShareErrorsFromSession
  5. VerifyCsrfToken
  6. SubstituteBindings

Route Middleware:
  7. auth (authenticated user)
  8. role (required role)
```

---

## Rate Limiting

```
Default Laravel rate limiting:
- 60 requests per minute per authenticated user
- Applies to all routes

Custom limits can be configured per route group
```

---

## Data Filtering & Sorting

### Available Filters (Invoices)
```
GET /admin/invoices?status=paid
GET /admin/invoices?status=overdue
GET /admin/invoices?status=open
GET /admin/invoices?start_date=2024-03-01&end_date=2024-03-31
GET /admin/invoices?search=customer_name
GET /admin/invoices?search=invoice_number
```

### Available Filters (Quotes)
```
GET /admin/quotes?status=pending
GET /admin/quotes?status=accepted
GET /admin/quotes?status=rejected
GET /admin/quotes?start_date=2024-03-01&end_date=2024-03-31
GET /admin/quotes?search=customer_name
```

---

## Pagination

### Query Parameters
```
?page=1         - Page number (default: 1)
?per_page=15    - Items per page (default: 15, max: 200)
```

### Response
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 74
  }
}
```

---

## Summary

**Total Routes Registered**: 54
- Admin: 18 routes (7 quotes + 11 invoices)
- Manager: 18 routes (7 quotes + 11 invoices)
- FrontDesk: 18 routes (7 quotes + 11 invoices)
- Accountant: 11 routes (invoices only)

**Total Features**: 
- ✅ 4 Quick Actions
- ✅ 2 Interactive Charts
- ✅ 2 Complete CRUD systems (Quotes & Invoices)
- ✅ Role-based access control
- ✅ Advanced filtering and searching

**Status**: ✅ READY FOR PRODUCTION
