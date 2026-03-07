# Create Quote Form - Quick Reference

## 🎯 What Changed?

The Create Quote form now has advanced features for professional quote generation.

## ✨ New Features

### 1. Auto-Calculating Total
- **Subtotal**: Automatically sums all items
- **Tax %**: Enter tax percentage (0-100)
- **Tax Amount**: Automatically calculated
- **Total**: Automatically calculated (Subtotal + Tax)
- **Updates**: All calculations happen in real-time

### 2. Item Display
Each item now shows a **Total** column that auto-calculates:
```
Item Total = Quantity × Unit Price
```

### 3. Print Quote
Click **"🖨️ Print"** to:
- Open print preview
- Send to printer
- Save as PDF from print dialog
- Show professional quote format

### 4. Export to PDF
Click **"📄 Export PDF"** to:
- Download quote as PDF file
- File named: `quote-2026-03-07.pdf` (with today's date)
- Ready to email to customer
- Professional formatting

### 5. Preview Quote
Click **"👁 Preview"** to:
- See how quote looks before creating
- View in new window
- No changes saved
- Can preview multiple times

---

## 🚀 How to Use

### Step 1: Select Quote Type
```
◉ Checked-in Guest    ◯ Outsider
```

### Step 2: Enter Customer Info
- If Guest: Select from dropdown
- If Outsider: Enter name, email, phone

### Step 3: Add Items
```
Description: [Text input]
Quantity:    [Number input]
Unit Price:  [Number input]
Total:       $XXX.XX (auto-calculated)
```

Click **"+ Add Item"** to add more items

### Step 4: Set Tax (Optional)
```
Tax (%):     [0-100 number]
Tax Amount:  $XX.XX (auto-calculated)
```

### Step 5: Review & Action
- **Subtotal**: Auto-shown
- **Total Amount**: Auto-calculated in green

Then choose:
- 👁 **Preview** - See formatted quote
- 🖨️ **Print** - Send to printer
- 📄 **Export PDF** - Save as file
- **Create Quote** - Save to database

---

## 📊 Example Calculation

### Your Input:
```
Item 1: Room Service   Qty: 3   Unit Price: $50
Item 2: Beverages      Qty: 2   Unit Price: $75
Tax: 10%
```

### Auto-Calculated Values:
```
Item 1 Total: $150 (3 × $50)
Item 2 Total: $150 (2 × $75)
Subtotal:     $300 (Sum of items)
Tax (10%):    $30  ($300 × 0.10)
Total:        $330 ($300 + $30)
```

---

## 🎨 New Form Layout

```
┌─────────────────────────────────────────────────┐
│  Create Quote                                   │
├─────────────────────────────────────────────────┤
│  Quote Type: [Guest] [Outsider]                │
│  Customer: [Dropdown/Input]                     │
│                                                  │
│  Valid Until: [Date]  Status: [Dropdown]       │
│  Subtotal:   $XXX.XX                           │
│  Tax (%):    [0-100]                           │
│  Tax Amount: $XX.XX                            │
│  Total:      $XXX.XX (in green)                │
│                                                  │
│  Quote Items:                                   │
│  ┌────────────┬───┬─────┬────────┬───────────┐ │
│  │Description │Qty│Price│Total   │Remove     │ │
│  ├────────────┼───┼─────┼────────┼───────────┤ │
│  │Room Svc    │ 3 │$50  │$150.00│[Remove]   │ │
│  │Beverages   │ 2 │$75  │$150.00│[Remove]   │ │
│  └────────────┴───┴─────┴────────┴───────────┘ │
│  [+ Add Item]                                   │
│                                                  │
│  Notes: [Textarea]                             │
│                                                  │
│  [Create] [Preview] [Print] [PDF] [Cancel]     │
└─────────────────────────────────────────────────┘
```

---

## ⚡ Real-Time Updates

### When You Change:
| Change | Result |
|--------|--------|
| Item Quantity | Item total updates immediately |
| Item Unit Price | Item total updates immediately |
| Add new item | Subtotal updates immediately |
| Remove item | Subtotal/Total update immediately |
| Tax percentage | Tax amount updates immediately |
| Tax amount | Total updates immediately |

**No manual recalculation needed!**

---

## 🖨️ Print/Export Options

### Print Button
```
Click [🖨️ Print]
  ↓
Browser print dialog opens
  ↓
Choose printer:
  • Physical printer (HP, Canon, etc.)
  • PDF Printer (Save as PDF)
  • Microsoft Print to PDF
  ↓
Click Print
```

### PDF Export Button
```
Click [📄 Export PDF]
  ↓
PDF automatically downloads
  ↓
Saved in Downloads folder
  ↓
File named: quote-2026-03-07.pdf
```

### Preview Button
```
Click [👁 Preview]
  ↓
New window opens with formatted quote
  ↓
Review the layout
  ↓
Close window to return to form
```

---

## 💾 Submission

### Before Creating Quote
1. Fill all required fields
2. Verify totals are correct
3. Check customer information
4. Review items and prices

### After Creating Quote
1. Quote saved to database
2. Redirected to quotes list
3. Appears with status "Draft"
4. Can be edited later

---

## ✅ Validation Rules

### Required Fields
- ✅ Quote Type (Guest or Outsider)
- ✅ Reservation (if Guest type)
- ✅ Customer Name (if Outsider type)
- ✅ Valid Until Date
- ✅ At least one item
- ✅ Total Amount > 0

### Optional Fields
- ◯ Customer Email
- ◯ Customer Phone
- ◯ Tax Percentage (defaults to 0)
- ◯ Notes

---

## 🔢 Calculation Examples

### Example 1: Simple Quote
```
1 item: 10 units × $25 = $250
No tax
Total: $250
```

### Example 2: Multiple Items with Tax
```
Item 1: 5 × $100 = $500
Item 2: 3 × $50 = $150
Item 3: 2 × $75 = $150
Subtotal: $800
Tax (15%): $120
Total: $920
```

### Example 3: Complex Quote
```
Item 1: Room Service (7 days × $150/day) = 7 × $150 = $1,050
Item 2: Meals (3 × $35) = $105
Item 3: Activities (2 × $75) = $150
Item 4: Spa (1 × $200) = $200
Subtotal: $1,505
Tax (12%): $180.60
Total: $1,685.60
```

---

## 🎓 Tips & Tricks

### Tip 1: Use Decimals for Quantities
```
Example: 0.5 units × $100 = $50
Useful for partial items or services
```

### Tip 2: Set Tax Once, It Sticks
```
Enter tax % once, it applies to all recalculations
Change it anytime, total updates instantly
```

### Tip 3: Preview Before Creating
```
Always click Preview first to check formatting
Especially important for professional quotes
```

### Tip 4: Export Multiple Formats
```
PDF for email to customers
Print for local records
Preview for screen review
All show same professional format
```

### Tip 5: Reorder Items Manually
```
Copy values from input
Delete old item
Add new item in desired order
Recalculate happens automatically
```

---

## 🆘 Common Questions

### Q: Will total update automatically?
**A**: Yes! Every time you change quantity, price, or tax percentage, the total updates instantly.

### Q: Can I edit total manually?
**A**: No, it's read-only (green field). Total is always calculated from items and tax.

### Q: What if I need to change total?
**A**: Adjust the items (quantities/prices) instead. Total will recalculate correctly.

### Q: Can I add/remove items later?
**A**: Yes, you can edit the quote after creating it.

### Q: Is tax optional?
**A**: Yes, leave it at 0 if no tax needed. Default is 0%.

### Q: Can I print without creating?
**A**: Yes! Preview and Print work before you click "Create Quote"

### Q: Can I export PDF without creating?
**A**: Yes! Export PDF works anytime before creation.

### Q: What format is the PDF?
**A**: Professional single-page format with:
- Customer info
- Itemized table
- Subtotal, tax, total
- Notes section

---

## 📱 Mobile Support

Form is fully responsive on:
- ✅ Desktop (full width)
- ✅ Tablet (2-column layout)
- ✅ Mobile (stacked layout)

All features work on mobile phones!

---

## 🔒 Security Notes

✅ All calculations done in browser (no server load)  
✅ Data not sent until you click "Create Quote"  
✅ Print/Preview/Export don't save anything  
✅ Safe to print/export to unsecured printer  

---

## 🚀 Quick Start (30 seconds)

```
1. Go to: http://localhost:8000/front-desk/quotes/create
2. Select Quote Type: [Outsider]
3. Enter Customer Name: "Test Company"
4. Click "+ Add Item"
5. Enter: Description: "Service", Qty: 2, Price: 50
   → Item Total auto-shows: $100.00
6. Enter Tax: 10
   → Tax Amount auto-shows: $10.00
   → Total auto-shows: $110.00
7. Click [👁 Preview] to see formatted quote
8. Click [📄 Export PDF] to download
9. Click [Create Quote] to save
```

Done! ✅

---

## 📞 Support

**Something not working?**
1. Refresh page (Ctrl+R or Cmd+R)
2. Check browser console (F12)
3. Verify all fields filled correctly
4. Try different browser
5. Check form for error messages (red text)

**PDF not downloading?**
1. Check download folder
2. Check browser download settings
3. Try print to PDF instead
4. Check if popup blocked

---

**Version**: 1.0  
**Released**: March 7, 2026  
**Status**: ✅ PRODUCTION READY  

Ready to use! 🎉
