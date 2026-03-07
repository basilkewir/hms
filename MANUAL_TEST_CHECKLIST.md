# Quick Actions, Quotes & Invoices - Browser Testing Checklist

## Manual Testing Instructions

Follow these steps to test the implementation on `http://127.0.0.1:8000`

---

## Part 1: Admin Dashboard Quick Actions

### Test 1.1: Navigate to Admin Dashboard
```
URL: http://127.0.0.1:8000/admin/dashboard
Expected: Dashboard loads with 4 quick action buttons visible
Buttons: "Add New User", "Manage Rooms", "View Reports", "System Settings"
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 1.2: Click "Add New User"
```
Button: Add New User
Expected: Navigate to http://127.0.0.1:8000/admin/users/create
Form Fields: first_name, last_name, email, password, department, position, role
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 1.3: Click "Manage Rooms"
```
Button: Manage Rooms
Expected: Navigate to http://127.0.0.1:8000/admin/rooms
Page Content: List of rooms with room numbers, types, status
CRUD Available: [Create] [Edit] [Delete] [View]
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 1.4: Click "View Reports"
```
Button: View Reports
Expected: Navigate to http://127.0.0.1:8000/admin/reports
Page Content: Report selection or dashboard with various reports
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

---

## Part 2: Admin Quotes Management

### Test 2.1: View Quotes List
```
URL: http://127.0.0.1:8000/admin/quotes
Expected: List of quotes displayed with columns:
  - Quote Number (QT-2024-001, QT-2024-002, etc.)
  - Customer Name
  - Total Amount
  - Status (pending, accepted, rejected)
  - Issue Date
  - Valid Until
  - Actions (View, Edit, Delete)
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 2.2: Create New Quote
```
URL: http://127.0.0.1:8000/admin/quotes/create
Expected: Form with fields:
  - Quote Type selector (Guest / Outsider)
  - Guest/Reservation dropdown (if Guest selected)
  - Customer Name (if Outsider selected)
  - Customer Email
  - Customer Phone
  - Quote Items (Description, Amount, +Add Row)
  - Total auto-calculated
  - Validity Period (Issue Date, Valid Until)
  - Notes field
  - [Save Quote] button
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 2.3: Create Quote - Fill and Submit
```
Steps:
  1. Select Quote Type: "Outsider"
  2. Enter Customer Name: "Test Customer"
  3. Enter Email: "test@example.com"
  4. Enter Phone: "+1234567890"
  5. Add Quote Item: Description="Room charges", Amount=1000
  6. Click [Save Quote]
Expected: Success message, redirect to quote detail page
Quote Number: Auto-generated (QT-2024-XXX)
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 2.4: View Quote Details
```
Click on quote number from list
Expected: Quote detail page showing:
  - Quote number, customer info, items, total
  - Issue date, valid until date
  - Status: pending/accepted/rejected
  - Actions available:
    [Edit] [Convert to Invoice] [Print] [Email] [Delete]
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 2.5: Edit Quote
```
URL: http://127.0.0.1:8000/admin/quotes/{id}/edit
Expected: Form pre-populated with quote data
Changes: Modify customer name or amount
Click [Update Quote]
Expected: Success message, updated quote shown on detail page
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 2.6: Delete Quote
```
From Quote Detail page
Click [Delete] button
Expected: Confirmation dialog
Confirm deletion
Expected: Quote removed from list, success message
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

---

## Part 3: Admin Invoices Management

### Test 3.1: View Invoices List
```
URL: http://127.0.0.1:8000/admin/invoices
Expected: List of invoices displayed with columns:
  - Invoice Number
  - Customer Name
  - Customer Email (optional)
  - Total Amount
  - Issue Date
  - Due Date
  - Status (open, paid, overdue)
  - Actions (View, Edit, Mark Paid, Delete)
Filters: Status, Date Range, Search
Stats: Total, Pending, Overdue, Paid count
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.2: Create New Invoice
```
URL: http://127.0.0.1:8000/admin/invoices/create
Expected: Form with fields:
  - Guest/Folio selector dropdown
  - Invoice Charges (if from guest folio)
  - Subtotal field
  - Tax percentage field
  - Total auto-calculated
  - Payment Terms selector
  - Due Date
  - Notes field
  - [Save Invoice] button
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.3: Create Invoice - Fill and Submit
```
Steps:
  1. Select Guest/Folio from dropdown
  2. System shows folio charges
  3. Verify subtotal and total
  4. Set Payment Terms (e.g., Net 30)
  5. Click [Save Invoice]
Expected: Success message
Invoice created with auto-generated number
Redirected to invoice detail page
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.4: View Invoice Details
```
Click on invoice number from list
Expected: Invoice detail page showing:
  - Invoice header (number, date, due date)
  - Customer information
  - Itemized charges (room, services, etc.)
  - Subtotal, taxes, total amount
  - Payment status
  - Payment history (if any)
  - Actions:
    [Edit] [Mark Paid] [Print] [Email] [Delete]
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.5: Mark Invoice as Paid
```
From Invoice Detail page
Click [Mark Paid] button
Expected: Confirmation or direct update
Invoice Status changes: open → paid
Balance Amount: becomes 0
Success message shown
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.6: Edit Invoice
```
URL: http://127.0.0.1:8000/admin/invoices/{id}/edit
Expected: Form pre-populated with invoice data
Changes: Modify customer email, due date, notes
Click [Update Invoice]
Expected: Success message, updated invoice shown
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.7: Delete Invoice
```
From Invoice Detail page
Click [Delete] button
Expected: Confirmation dialog
Confirm deletion
Expected: Invoice removed from list, success message
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.8: View Overdue Invoices
```
URL: http://127.0.0.1:8000/admin/invoices/overdue
Expected: List of invoices with:
  - Due date in the past
  - Status NOT paid
  - Sorted by due date (oldest first)
  - Days overdue calculation
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 3.9: View Paid Invoices
```
URL: http://127.0.0.1:8000/admin/invoices/paid
Expected: List of invoices with status="paid"
All invoices show balance=0
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

---

## Part 4: Role-Based Access Tests

### Test 4.1: Manager Quotes
```
Login as: User with Manager role
URL: http://127.0.0.1:8000/manager/quotes
Expected: Same quotes interface but filtered to manager's data
Can Create/Edit/Delete: Own quotes only
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 4.2: Manager Invoices
```
Login as: User with Manager role
URL: http://127.0.0.1:8000/manager/invoices
Expected: Same invoices interface but filtered to manager's data
Can Create/Edit/Delete: Own invoices only
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 4.3: FrontDesk Quotes
```
Login as: User with FrontDesk role
URL: http://127.0.0.1:8000/front-desk/quotes
Expected: Quotes interface with FrontDesk-specific view
Limited functionality based on permissions
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 4.4: FrontDesk Invoices
```
Login as: User with FrontDesk role
URL: http://127.0.0.1:8000/front-desk/invoices
Expected: Invoices interface with FrontDesk-specific view
Can see guest folios/invoices
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 4.5: Access Control - Admin Only Routes
```
Login as: Non-admin user (Manager, FrontDesk)
Try: http://127.0.0.1:8000/admin/quotes
Expected: 403 Forbidden error
Try: http://127.0.0.1:8000/admin/invoices
Expected: 403 Forbidden error
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

---

## Part 5: Chart Interactivity Tests (Dashboard)

### Test 5.1: Revenue Chart Hover
```
URL: http://127.0.0.1:8000/admin/dashboard
Action: Hover mouse over data points on Revenue Trend chart
Expected:
  - Data point circle enlarges
  - Tooltip appears showing: Date and exact Amount
  - Amount formatted as currency ($X,XXX.XX)
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 5.2: Occupancy Chart Hover
```
URL: http://127.0.0.1:8000/admin/dashboard
Action: Hover mouse over data points on Occupancy Rate chart
Expected:
  - Data point circle enlarges
  - Tooltip appears showing: Date and Occupancy % (XX.X%)
  - Percentage value with one decimal
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 5.3: Chart Y-Axis Labels
```
URL: http://127.0.0.1:8000/admin/dashboard
Check: Revenue chart Y-axis labels
Expected: Values formatted as K/M
  - Example: $1.2M for 1,200,000
  - Example: $45K for 45,000
Check: Occupancy chart Y-axis labels
Expected: Values in percentage (100%, 50%, 0%)
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

---

## Part 6: Form Validation Tests

### Test 6.1: Quote Form Validation
```
URL: http://127.0.0.1:8000/admin/quotes/create
Action: Click Save without filling required fields
Expected: Validation errors shown for:
  - Quote Type (required)
  - Customer Name (required if outsider)
  - Quote Items (required, at least 1)
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

### Test 6.2: Invoice Form Validation
```
URL: http://127.0.0.1:8000/admin/invoices/create
Action: Click Save with invalid data
Expected: Validation errors shown for:
  - Guest/Folio selection (required)
  - Total Amount (must be numeric, >= 0)
Status: [ ] PASS [ ] FAIL
Notes: _______________________________________________
```

---

## Test Summary

Total Tests: 24 major test cases

### Results:
- Passed: ___ / 24
- Failed: ___ / 24
- Issues Found: _______________________________________________
           _______________________________________________

### Overall Status: [ ] PASS [ ] FAIL [ ] PARTIAL

---

## Notes & Issues Found:

1. _______________________________________________
2. _______________________________________________
3. _______________________________________________
4. _______________________________________________
5. _______________________________________________

---

## Date Tested: ____________
## Tester Name: ____________
## Browser: ____________
## Browser Version: ____________
