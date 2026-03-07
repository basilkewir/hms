# Front-Desk Quotes View & Edit - Quick Testing Guide

## 🚀 Quick Start (5 minutes)

### Test 1: Navigate to Quotes List
```
1. Go to: http://127.0.0.1:8000/front-desk/quotes
2. Verify: You see the quote list table
3. Expected: Quotes with columns (Quote Number, Customer, Email, Amount, Valid Until, Date, Status, Actions)
```

### Test 2: View Single Quote
```
1. Click: "👁 View" button on any quote
2. Expected: Navigate to /front-desk/quotes/{id}
3. Verify: 
   - Quote number displays in header
   - All details visible (customer, email, phone, type, dates)
   - Quote items shown in table
   - Summary card with status and amount
   - Days until expiry showing
```

### Test 3: Edit Quote
```
1. Click: "✏️ Edit" button (from list or view page)
2. Expected: Navigate to /front-desk/quotes/{id}/edit
3. Verify:
   - Form pre-filled with quote data
   - All fields editable
   - Change a customer name
   - Click "Save Changes"
   - Redirected back to View page
   - Changes saved
```

### Test 4: Manage Items
```
1. On Edit page, scroll to "Quote Items"
2. Verify:
   - Items table shows all existing items
   - Can edit description, qty, unit price
   - Total auto-updates when changing qty or price
   - Can click "+ Add Item" to add new row
   - Can click "✕" to remove item
3. Change an item quantity
4. Verify: Total amount updates immediately
```

### Test 5: Send Quote (Draft Only)
```
1. Find a draft quote in the list
2. Verify: "📧 Send" button appears
3. Click: Send button
4. Confirm: Dialog asks "Send quote #XXX to email@example.com?"
5. Click: OK
6. Expected: Status changes from "draft" to "sent"
```

### Test 6: Convert to Invoice (Sent Only)
```
1. Find a sent quote in the list
2. Verify: "📄 Convert to Invoice" button appears
3. Click: Convert button
4. Confirm: Dialog asks "Convert quote #XXX to invoice?"
5. Click: OK
6. Expected: Success message "Invoice created successfully!"
7. Verify: New invoice created in /front-desk/invoices
```

### Test 7: Print Quote
```
1. Go to: /front-desk/quotes/{id} (View page)
2. Click: "🖨️ Print" button
3. Expected: Print preview opens
4. Verify: Professional layout with:
   - Quote header
   - Customer details
   - Items table
   - Subtotal, Tax, Total
   - Notes section
```

### Test 8: Export to PDF
```
1. Go to: /front-desk/quotes/{id}/edit (Edit page)
2. Click: "📄 Export PDF" button
3. Expected: PDF downloads as "quote-YYYY-MM-DD.pdf"
4. Verify: PDF opens with professional layout
```

---

## 📋 Detailed Testing Matrix

### Routes Test

| Route | Method | Status | Test |
|-------|--------|--------|------|
| `/front-desk/quotes` | GET | ✅ | List all quotes |
| `/front-desk/quotes/{id}` | GET | ✅ | View single quote |
| `/front-desk/quotes/{id}/edit` | GET | ✅ | Load edit form |
| `/front-desk/quotes/{id}` | PUT | ✅ | Save changes |

### Buttons Test

#### List Page Buttons
| Button | Status | Test Case |
|--------|--------|-----------|
| 👁 View | ✅ | Click on any quote |
| ✏️ Edit | ✅ | Click on any quote |
| 📧 Send | ✅ | Draft quotes only |
| 📄 Convert | ✅ | Sent quotes only |

#### View Page Buttons
| Button | Test |
|--------|------|
| Edit | Navigate to edit form |
| Send Quote | Draft quotes only |
| Convert to Invoice | Sent quotes only |
| Print | Open print dialog |
| Back to Quotes | Return to list |

#### Edit Page Buttons
| Button | Test |
|--------|------|
| Save Changes | Update quote |
| Print | Open print dialog |
| Export PDF | Download PDF file |
| Cancel | Return to view page |

---

## 🔍 Form Validation Tests

### Edit Page Validation

**Test Case 1: Required Fields**
```
1. Go to edit page
2. Clear "Valid Until" field
3. Try to save
4. Expected: Error message "Valid until is required"
```

**Test Case 2: Amount Validation**
```
1. Go to edit page
2. Remove all items
3. Try to save
4. Expected: Error or warning about minimum items
```

**Test Case 3: Quote Type - Guest**
```
1. Change quote type to "Checked-in Guest"
2. Don't select a reservation
3. Try to save
4. Expected: Error "Reservation is required"
```

**Test Case 4: Quote Type - Outsider**
```
1. Change quote type to "Outsider"
2. Clear customer name
3. Try to save
4. Expected: Error "Customer name is required"
```

---

## 💰 Calculation Tests

### Auto-Calculation Tests

**Test Case 1: Basic Calculation**
```
Item 1: Qty 5, Price $10
Item 2: Qty 3, Price $20
Tax: 10%

Expected:
- Item 1 Total: $50
- Item 2 Total: $60
- Subtotal: $110
- Tax Amount: $11
- Total: $121
```

**Test Case 2: Item Change Updates Total**
```
1. Edit an item quantity
2. Total should update immediately
3. Verify both on edit form and summary
```

**Test Case 3: Tax Percentage Change**
```
1. Change tax from 0% to 15%
2. Total should update
3. Verify tax amount calculated correctly
```

**Test Case 4: Add/Remove Items**
```
1. Add new item with $50
2. Verify total increases by $50 (plus tax)
3. Remove the item
4. Verify total decreases
```

---

## 🎨 UI/UX Tests

### Theme & Styling

- [ ] Colors match dashboard theme
- [ ] Responsive on desktop (1920px+)
- [ ] Responsive on tablet (768px - 1024px)
- [ ] Responsive on mobile (320px - 640px)
- [ ] Tables don't overflow on mobile
- [ ] Forms are readable on all sizes
- [ ] Buttons are clickable on mobile

### Layout Tests

- [ ] View page shows summary on right
- [ ] Edit page has proper spacing
- [ ] Items table is scrollable on small screens
- [ ] All fields visible without scrolling (desktop)
- [ ] Status badges have correct colors

---

## 🔄 Data Flow Tests

### Create → View → Edit → View Flow

```
1. Create a new quote
   ✓ Navigate to /front-desk/quotes/create
   ✓ Fill in all details
   ✓ Add items
   ✓ Click "Create Quote"
   ✓ Redirected to list

2. View the quote
   ✓ Click View button
   ✓ See all details on View page

3. Edit the quote
   ✓ Click Edit button
   ✓ Form pre-filled with data
   ✓ Make changes
   ✓ Click Save
   ✓ Redirected to View page
   ✓ Changes visible

4. View updated quote
   ✓ All changes persisted
   ✓ Totals recalculated
```

---

## ⚠️ Error Handling Tests

### Network Error Tests

**Test Case 1: Save with Network Error**
```
1. Open edit page
2. Disconnect network
3. Try to save
4. Expected: Error message shown
5. Reconnect and retry
6. Expected: Save succeeds
```

**Test Case 2: Navigation Error**
```
1. Try to navigate to non-existent quote: /front-desk/quotes/9999
2. Expected: 404 error or redirect
```

---

## 📱 Mobile Tests

### Mobile Functionality (375px width)

- [ ] List page scrolls horizontally
- [ ] View page displays correctly
- [ ] Edit form is usable on mobile
- [ ] Buttons are touch-friendly (40px+ height)
- [ ] Date picker works on mobile
- [ ] Print dialog opens on mobile
- [ ] Table headers visible on mobile

---

## 🖨️ Print & PDF Tests

### Print Dialog Test

```
1. Go to View or Edit page
2. Click "Print" button
3. Verify print preview shows:
   - Proper quote header
   - All items
   - Correct totals
   - Notes if any
4. Print to paper/PDF
5. Verify output quality
```

### PDF Export Test

```
1. Go to Edit page
2. Click "Export PDF"
3. Verify:
   - PDF downloads with name "quote-YYYY-MM-DD.pdf"
   - File opens in PDF viewer
   - Layout looks professional
   - All data is readable
```

---

## 🎯 Status-Specific Tests

### Draft Quote Actions

```
Quote Status: Draft
Expected buttons:
- 👁 View ✓
- ✏️ Edit ✓
- 📧 Send ✓
- NO Convert button
```

### Sent Quote Actions

```
Quote Status: Sent
Expected buttons:
- 👁 View ✓
- ✏️ Edit ✓
- NO Send button
- 📄 Convert ✓
```

### Other Status Quotes

```
Quote Status: Accepted/Rejected/Expired
Expected buttons:
- 👁 View ✓
- ✏️ Edit ✓
- NO Send button
- NO Convert button
```

---

## ✅ Final Verification Checklist

- [ ] All routes working (5 routes total)
- [ ] Show page displays correctly
- [ ] Edit page displays correctly
- [ ] Index page buttons functional
- [ ] Auto-calculation working
- [ ] Print dialog opens
- [ ] PDF export downloads
- [ ] Form validation working
- [ ] Status changes working
- [ ] Convert to invoice working
- [ ] Mobile responsive
- [ ] Theme colors applied
- [ ] No console errors
- [ ] No broken links

---

## 🆘 Troubleshooting

### Page Not Found (404)
```
Problem: /front-desk/quotes/{id} returns 404
Solution: 
1. Check routes/web.php has the show route
2. Verify QuoteController@show method exists
3. Check quote ID exists in database
```

### Button Not Working
```
Problem: View/Edit button doesn't work
Solution:
1. Open console (F12)
2. Check for JavaScript errors
3. Verify route helper is working
4. Check Inertia.js is loaded
```

### Total Not Updating
```
Problem: Auto-calculation not working
Solution:
1. Check computed properties in Edit.vue
2. Verify updateTotalAmount() is called on input
3. Check form data structure
4. Try page refresh
```

### Print Dialog Not Opening
```
Problem: Print button doesn't work
Solution:
1. Check browser allows popups
2. Verify window.print() is available
3. Check for JavaScript errors in console
4. Try using PDF export instead
```

### PDF Not Downloading
```
Problem: PDF export doesn't work
Solution:
1. Check html2pdf library is loaded
2. Verify window.html2pdf exists
3. Check browser download settings
4. Try print dialog as fallback
5. Check console for errors
```

---

## 📞 Support

If tests fail:

1. **Check Console Errors** (F12 → Console)
2. **Verify Routes** - `php artisan route:list | grep quotes`
3. **Check Database** - Quote records exist
4. **Clear Cache** - `php artisan cache:clear`
5. **Reload Page** - Ctrl+Shift+R (hard refresh)

---

**Last Updated:** March 7, 2026  
**Status:** ✅ Ready for Testing  
**Time to Complete:** ~30 minutes  
**Difficulty:** Easy (follow checklist)
