# Quick Testing Guide - Quotes Page with Database

## 5-Minute Test Procedure

### Prerequisites
- Application running at `http://localhost:8000`
- Database migrations completed
- Sample data seeded

### Test Steps

#### Step 1: Navigate to Quotes Page (30 seconds)
```
1. Go to: http://localhost:8000/front-desk/quotes
2. Verify: Page loads with real quotes from database
3. Check: At least 7 quotes visible in table
```

**Expected Result**: ✅ Table shows quotes with:
- Quote Numbers: QT-2026-03-0001, QT-2026-03-0002, etc.
- Customer Names: John Smith, Jane Doe, ABC Corporation, etc.
- Total Amounts: Various currency values
- Statuses: draft, sent, accepted, rejected, expired

---

#### Step 2: Test Date Picker on "Date From" (1 minute)
```
1. Click on "Date From" input field
2. Observe: Native date picker opens
3. Select: Any date (e.g., March 1, 2026)
4. Verify: Input shows selected date
```

**Expected Result**: ✅
- Date picker appears on click (browser-native)
- Date is selectable
- Input updates with selected date in YYYY-MM-DD format

---

#### Step 3: Test Date Picker on "Date To" (1 minute)
```
1. Click on "Date To" input field
2. Select: A date after the "Date From" date
3. Click "Apply Filters" button
```

**Expected Result**: ✅
- Page refreshes
- URL shows query parameters: `?start_date=2026-03-01&end_date=2026-03-31`
- Table shows only quotes created within that date range

---

#### Step 4: Test Status Filter (1 minute)
```
1. Click "Status" dropdown
2. Select: "draft"
3. Click "Apply Filters"
```

**Expected Result**: ✅
- Only draft quotes shown
- Quotes with "sent", "accepted", "rejected", "expired" hidden
- URL shows: `?status=draft`

---

#### Step 5: Test Search Filter (1 minute)
```
1. Click "Search" input field
2. Type: "John" or "ABC"
3. Click "Apply Filters"
```

**Expected Result**: ✅
- Only quotes matching search term shown
- Works with: quote numbers, customer names, emails
- URL shows: `?search=John`

---

#### Step 6: Test Clear Filters (30 seconds)
```
1. Click "Clear" button
2. Verify: All filters reset
```

**Expected Result**: ✅
- All input fields cleared
- All 7 quotes visible again
- URL shows no query parameters

---

### Quick Validation Checklist

| Test | ✅/❌ | Notes |
|------|-------|-------|
| Quotes display from database | | Should see real data, not dummy |
| Date From input is clickable | | Click should open date picker |
| Date To input is clickable | | Click should open date picker |
| Date picker appears on click | | Browser-native date selector |
| Status filter works | | Filter by draft/sent/accepted/rejected/expired |
| Search filter works | | Find quotes by name, email, or number |
| Combined filters work | | Status + Date + Search together |
| Clear button resets all | | Returns to showing all quotes |
| Dates format correctly | | Shows as "Mar 04, 2026" in table |
| Currency format correctly | | Shows as "$835.00" or similar |

---

## Browser Console Check (F12)

**Open DevTools (F12) → Console tab**

**Look for**:
- ❌ No red errors
- ❌ No network failures
- ✅ Should be clean (or only framework warnings)

**If you see errors**:
1. Note the error message
2. Check if it's related to: quotes, filters, date picker
3. Refresh page (Ctrl+R or Cmd+R)
4. Try again

---

## Database Verification

### Check Sample Data Exists
```bash
# In terminal, go to project directory:
cd hotel-management-system

# Run this command:
php artisan tinker

# Then type:
Quote::count()   # Should show: 7
Quote::first()    # Should show first quote details
exit
```

### Check Quotes with Items
```bash
php artisan tinker
Quote::with('items')->limit(3)->get()
exit
```

---

## Common Issues & Solutions

### Issue 1: Page Shows "No quotes found"
**Solution**:
```bash
php artisan db:seed --class=QuoteSeeder
# Then refresh browser
```

### Issue 2: Date picker doesn't appear
**Solution**:
- Ensure browser supports HTML5 date input (all modern browsers do)
- Try different browser (Chrome, Firefox, Safari, Edge)
- Check browser developer tools for CSS errors

### Issue 3: Filters not working
**Solution**:
1. Open Developer Tools (F12)
2. Click Network tab
3. Click "Apply Filters"
4. Check if request was sent to: `/front-desk/quotes?status=...`
5. Verify response contains filtered quotes

### Issue 4: Dates showing as "Invalid Date"
**Solution**:
- This is fixed in latest code
- Dates should format using `formatDate()` helper
- Clear cache: Ctrl+Shift+Delete (hard refresh)

---

## Success Indicators ✅

Your implementation is working correctly if:

1. ✅ Page loads with real database quotes (not mock data)
2. ✅ Table shows 7+ quotes from database
3. ✅ Date input fields open native date picker on click
4. ✅ Date From and Date To inputs are fully clickable
5. ✅ All filters (status, date, search) work independently
6. ✅ Filters can be combined (status + date + search together)
7. ✅ Clear button removes all filters at once
8. ✅ Dates in table display in "Mar 04, 2026" format
9. ✅ Currency displays in "$X,XXX.XX" format
10. ✅ No console errors when filtering

---

## Additional Features to Try

### Create a New Quote
```
1. Click "Create Quote" button
2. Fill form with sample data
3. Click "Create Quote"
4. New quote should appear in list
```

### View Quote Details
```
1. Click "👁 View" button on any quote
2. Should show quote details and items
```

### Edit a Quote
```
1. Click "✏️ Edit" button on any quote
2. Modify fields
3. Click "Update" button
4. Changes should be saved
```

---

**Test Duration**: ~5 minutes  
**Difficulty**: Easy - Just clicks and observation  
**Skills Required**: None - Just use the UI
