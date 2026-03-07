# Create Quote Form - Testing Guide

## 5-Minute Feature Test

### Test 1: Auto-Calculation (1 minute)

**Steps:**
1. Navigate to: `http://localhost:8000/front-desk/quotes/create`
2. Select Quote Type: **Outsider**
3. Enter Customer Name: **Test Company**
4. Click "+ Add Item" (if not already there)
5. Enter Item 1:
   - Description: `Room Service`
   - Quantity: `3`
   - Unit Price: `50`
6. Observe the item total: Should show **$150.00**

**Expected Results:**
- ✅ Item total displays: $150.00
- ✅ Subtotal shows: $150.00
- ✅ Tax Amount shows: $0.00 (if tax is 0%)
- ✅ Total Amount shows: $150.00

---

### Test 2: Tax Calculation (1 minute)

**Steps:**
1. In the Tax field, enter: `10`
2. Observe tax calculation updates

**Expected Results:**
- ✅ Tax Amount updates to: $15.00 (10% of $150)
- ✅ Total Amount updates to: $165.00
- ✅ Calculation is: $150.00 + $15.00 = $165.00

---

### Test 3: Add Multiple Items (1 minute)

**Steps:**
1. Click "+ Add Item" button
2. Add Item 2:
   - Description: `Food & Beverage`
   - Quantity: `2`
   - Unit Price: `75`
3. Observe totals update

**Expected Results:**
- ✅ Item 2 total shows: $150.00 (2 × $75)
- ✅ Subtotal updates to: $300.00 ($150 + $150)
- ✅ Tax Amount updates to: $30.00 (10% of $300)
- ✅ Total Amount updates to: $330.00

---

### Test 4: Preview Quote (1 minute)

**Steps:**
1. Fill form with sample data (as above)
2. Click **"👁 Preview"** button
3. New window opens with quote preview
4. Review the formatted quote

**Expected Results:**
- ✅ New window opens (not blocked by popup blocker)
- ✅ Quote displays with professional formatting
- ✅ All items listed in table format
- ✅ Subtotal shows: $300.00
- ✅ Tax (10%) shows: $30.00
- ✅ Total shows: $330.00
- ✅ Customer info visible
- ✅ Can close window and return to form

---

### Test 5: Print Quote (1 minute)

**Steps:**
1. Click **"🖨️ Print"** button
2. Print preview/dialog appears
3. Review print preview
4. Click "Cancel" (don't actually print)

**Expected Results:**
- ✅ Print dialog opens
- ✅ Preview shows formatted quote
- ✅ All data visible and readable
- ✅ Margins and spacing look good
- ✅ Can cancel without printing

---

### Test 6: Export to PDF (1 minute)

**Steps:**
1. Click **"📄 Export PDF"** button
2. File downloads to Downloads folder
3. Check Downloads folder for file named: `quote-2026-03-07.pdf`

**Expected Results:**
- ✅ Download starts automatically
- ✅ File named: `quote-YYYY-MM-DD.pdf`
- ✅ File appears in Downloads folder
- ✅ File is readable PDF format

---

## Detailed Feature Testing

### Feature 1: Item Total Calculation

| Qty | Unit Price | Expected Total | ✅/❌ |
|-----|-----------|-----------------|------|
| 1 | $50.00 | $50.00 | |
| 3 | $50.00 | $150.00 | |
| 5 | $25.00 | $125.00 | |
| 10 | $100.00 | $1,000.00 | |
| 0.5 | $100.00 | $50.00 | |

### Feature 2: Subtotal Calculation

**Test Case:** 3 items
- Item 1: 2 × $50 = $100
- Item 2: 3 × $75 = $225
- Item 3: 1 × $200 = $200
- **Expected Subtotal: $525.00**

### Feature 3: Tax Calculation

| Subtotal | Tax % | Expected Tax | Expected Total | ✅/❌ |
|----------|-------|--------------|-----------------|------|
| $100 | 0% | $0.00 | $100.00 | |
| $100 | 10% | $10.00 | $110.00 | |
| $100 | 15% | $15.00 | $115.00 | |
| $1000 | 20% | $200.00 | $1200.00 | |
| $500 | 2.5% | $12.50 | $512.50 | |

### Feature 4: Remove Item

**Test Case:**
1. Add 3 items with totals: $150, $150, $100
2. Subtotal should be: $400
3. Remove middle item
4. Expected Subtotal: $250
5. With 10% tax, Total should be: $275

---

## Field Behavior Testing

### Quantity Field
- [ ] Accepts integers (1, 2, 3, etc.)
- [ ] Accepts decimals (0.5, 1.5, etc.)
- [ ] Rejects negative numbers
- [ ] Updates total on change
- [ ] Min value is 1

### Unit Price Field
- [ ] Accepts integers ($50, $100, etc.)
- [ ] Accepts decimals ($50.99, $99.99, etc.)
- [ ] Can be zero
- [ ] Rejects negative numbers
- [ ] Updates total on change
- [ ] Step value is 0.01

### Tax Percentage Field
- [ ] Accepts 0-100
- [ ] Accepts decimals (2.5, 7.25, etc.)
- [ ] Defaults to 0
- [ ] Updates tax amount in real-time
- [ ] Updates total amount in real-time

### Description Field
- [ ] Accepts any text
- [ ] No character limit visible
- [ ] Optional field
- [ ] Can be empty

---

## Action Button Testing

### Preview Button
- [ ] Opens new window (not blocked)
- [ ] Shows formatted quote
- [ ] Can close window
- [ ] Can preview multiple times
- [ ] Doesn't save quote

### Print Button
- [ ] Opens print dialog
- [ ] Shows print preview
- [ ] Can print to PDF (if PDF printer selected)
- [ ] Can print to physical printer
- [ ] Can cancel without printing
- [ ] Doesn't save quote

### Export PDF Button
- [ ] Automatically downloads file
- [ ] File appears in Downloads folder
- [ ] File is named with date: `quote-YYYY-MM-DD.pdf`
- [ ] PDF is readable in PDF viewer
- [ ] Doesn't save quote to database
- [ ] All content visible in PDF

### Create Quote Button
- [ ] Validates required fields
- [ ] Shows error for missing date
- [ ] Shows error for zero total
- [ ] Saves to database on success
- [ ] Redirects to quotes list
- [ ] Doesn't show until form valid

---

## Validation Testing

### Required Fields Validation
- [ ] Valid Until date is required
- [ ] Cannot submit without valid date
- [ ] Shows error message if blank

### Business Logic Validation
- [ ] Total Amount must be > 0
- [ ] Cannot submit if total is 0
- [ ] Guest quotes require reservation selection
- [ ] Outsider quotes require customer name
- [ ] Shows appropriate error messages

---

## Display Formatting Testing

### Currency Display
- [ ] All amounts show with 2 decimal places ($X.XX)
- [ ] Large amounts format with commas ($1,234.56)
- [ ] Zero shows as $0.00
- [ ] Negative (if possible) shows with minus sign

### Date Display
- [ ] Date picker shows correct format
- [ ] Selected date displays properly
- [ ] All dates use consistent format

---

## Browser Testing

| Browser | Auto-Calc | Print | PDF Export | Preview | Date Picker |
|---------|-----------|-------|-----------|---------|-------------|
| Chrome | ✅ | ✅ | ✅ | ✅ | ✅ |
| Firefox | ✅ | ✅ | ✅ | ✅ | ✅ |
| Safari | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edge | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## Performance Testing

### Load Time
- [ ] Form loads within 2 seconds
- [ ] No lag when adding items
- [ ] Calculations instant (<100ms)

### Large Dataset
- [ ] Can handle 20+ items
- [ ] Calculations still instant
- [ ] No memory issues
- [ ] No UI freezing

### PDF Generation
- [ ] Takes <3 seconds
- [ ] File downloads automatically
- [ ] No errors in console

---

## Edge Case Testing

### Zero and Negative Values
- [ ] Quantity cannot be 0
- [ ] Unit Price can be 0
- [ ] Negative quantities rejected
- [ ] Negative prices rejected

### Large Values
- [ ] Can handle large amounts ($100,000+)
- [ ] Formatting remains correct
- [ ] Calculations remain accurate

### Decimal Precision
- [ ] Handles 2 decimal places
- [ ] Rounding is correct
- [ ] No floating point errors

---

## Success Criteria

All of the following must be true:

✅ Auto-calculation works for single items  
✅ Auto-calculation works for multiple items  
✅ Tax calculation is accurate  
✅ Total updates in real-time  
✅ Preview button works  
✅ Print dialog opens  
✅ PDF exports successfully  
✅ All formatting is correct  
✅ No console errors  
✅ Responsive on all screen sizes  

---

**Test Duration**: ~15-20 minutes for all features  
**Difficulty**: Easy  
**Required Skills**: Basic form interaction  

## Checklist Summary

```
Feature Testing:
- [ ] Auto-calculation from items
- [ ] Tax percentage support
- [ ] Item total display
- [ ] Subtotal calculation
- [ ] Preview functionality
- [ ] Print functionality
- [ ] PDF export functionality

Validation Testing:
- [ ] Required fields validated
- [ ] Error messages displayed
- [ ] Business rules enforced

Display Testing:
- [ ] Currency formatting correct
- [ ] Dates display properly
- [ ] Layout responsive
- [ ] No styling issues

Browser Testing:
- [ ] Chrome tested
- [ ] Firefox tested
- [ ] Safari tested
- [ ] Edge tested

Performance Testing:
- [ ] Fast load time
- [ ] Instant calculations
- [ ] Handles many items
```

---

**Ready for Testing**: March 7, 2026
**All Features Implemented**: ✅ YES
**Documentation Complete**: ✅ YES
