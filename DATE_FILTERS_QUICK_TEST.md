# Quick Test Guide - Date Filters Fix

## Pre-Test Setup
- ✅ Development server running: `npm run dev` (port 5173/5174)
- ✅ Laravel server running: `php artisan serve` (port 8000)
- ✅ Database has sample quotes

## Test Steps

### Step 1: Open Quotes List Page
```
URL: http://127.0.0.1:8000/front-desk/quotes
Expected: Page loads with quote list and filter section at top
```

### Step 2: Test Date From Filter
```
Action: Click on "Date From" input field
Expected: Native date picker opens (calendar popup)

Action: Select a date (e.g., 15 days ago)
Expected: Date appears in input field

Action: Verify calendar icon (📅) is visible to the right
Expected: Icon is displayed with proper spacing
```

### Step 3: Test Date To Filter
```
Action: Click on "Date To" input field
Expected: Native date picker opens (calendar popup)

Action: Select a date (e.g., today or future date)
Expected: Date appears in input field

Action: Verify calendar icon (📅) is visible to the right
Expected: Icon is displayed with proper spacing
```

### Step 4: Test Search Filter
```
Action: Click in "Search" input field
Expected: Text cursor appears

Action: Type a customer name or quote number
Expected: Text appears in input field

Action: Verify search input is responsive
Expected: Text input works normally
```

### Step 5: Apply Filters
```
Action: Click "🔍 Apply Filters" button
Expected: Page refreshes with filtered results

Expected Results:
- Only quotes from Date From to Date To range shown
- Only quotes matching search term shown
- All filters combined work together

Example Filter Combination:
- Date From: 2024-01-01
- Date To: 2024-12-31
- Search: "John" (customer name)
Result: Only quotes from 2024 with customer named John
```

### Step 6: Test Clear Filters
```
Action: Set all three filters
Action: Click "🔄 Clear" button
Expected: All inputs clear
Expected: All quotes in list reappear
```

### Step 7: Test Keyboard Navigation
```
Action: Press Tab to focus on Date From input
Expected: Input gets focus ring (visible border)

Action: Press Space or Enter
Expected: Date picker opens

Action: Tab to Date To input
Expected: Can navigate through form with Tab key

Action: Tab to Search input
Expected: Can focus and type with keyboard
```

### Step 8: Test Mobile Responsiveness (Optional)
```
Action: Open DevTools (F12)
Action: Toggle Device Toolbar (Ctrl+Shift+M)
Action: Select mobile device (iPhone 12, 390px width)
Expected: Filters still responsive and clickable
Expected: Icons visible and properly sized
Expected: Date picker opens on mobile
```

## Success Criteria

- ✅ Date From input is fully clickable
- ✅ Date To input is fully clickable
- ✅ Calendar icons visible for both date inputs
- ✅ Date picker opens when clicking on inputs
- ✅ Search input accepts text input
- ✅ All filters apply correctly together
- ✅ Clear button resets all filters
- ✅ Keyboard navigation works
- ✅ No console errors in browser

## Failure Scenarios & Troubleshooting

### Scenario 1: Date Picker Doesn't Open
```
Troubleshooting:
1. Check browser console (F12 > Console tab)
2. Verify no JavaScript errors
3. Test with different browser
4. Clear browser cache: Ctrl+Shift+Delete
5. Hard refresh: Ctrl+Shift+R
```

### Scenario 2: Icon Not Visible
```
Troubleshooting:
1. Inspect element (F12 > Elements tab)
2. Check if CSS styles applied to icon container
3. Check if theme colors loaded (check CSS variables)
4. Verify icon container has correct positioning (absolute)
```

### Scenario 3: Input Not Responding to Clicks
```
Troubleshooting:
1. Inspect element to verify pointer-events-none on icon
2. Check for overlapping elements
3. Try clicking on different parts of input
4. Check browser console for errors
```

### Scenario 4: Filters Not Applying
```
Troubleshooting:
1. Check browser console for errors
2. Open Network tab (F12 > Network)
3. Click Apply Filters
4. Look for API request to backend
5. Verify request status (should be 200)
6. Check response data
```

## Browser Testing

### Desktop Browsers
- [ ] Chrome/Edge (Latest)
- [ ] Firefox (Latest)
- [ ] Safari (Mac)

### Mobile Browsers
- [ ] Chrome Mobile (Android)
- [ ] Safari Mobile (iOS)

## Test Results Template

```
Date: [YYYY-MM-DD]
Tester: [Name]
Browser: [Browser + Version]
OS: [Windows/Mac/Linux]

Date From Filter: [✅ PASS / ❌ FAIL]
Date To Filter: [✅ PASS / ❌ FAIL]
Search Filter: [✅ PASS / ❌ FAIL]
Apply Filters: [✅ PASS / ❌ FAIL]
Clear Filters: [✅ PASS / ❌ FAIL]
Keyboard Nav: [✅ PASS / ❌ FAIL]

Overall Result: [✅ PASS / ❌ FAIL]

Issues Found:
- [List any issues]

Notes:
- [Any additional observations]
```

## Performance Check

### Browser DevTools Performance Tab
1. Open DevTools (F12)
2. Go to Performance tab
3. Click record button
4. Click on Date From input
5. Select a date
6. Click Apply Filters
7. Stop recording
8. Review timeline
   - Should be smooth (60 FPS)
   - No long tasks blocking UI
   - Filter application should be instant

## Next Steps After Testing

If all tests pass:
1. ✅ Commit changes to git
2. ✅ Deploy to staging server
3. ✅ Test in production-like environment
4. ✅ Get user approval
5. ✅ Deploy to production

If tests fail:
1. ❌ Document issue details
2. ❌ Review error messages
3. ❌ Check code changes
4. ❌ Run tests again
5. ❌ Escalate if needed
