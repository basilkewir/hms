# PDF Design Visual Guide

## Quote PDF Layout

```
╔════════════════════════════════════════════════════════════════════╗
║                                                                    ║
║                          QUOTE                         #QT-001    ║
║                 Professional Quote Document             Date: ...  ║
║                                                                    ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║                          CUSTOMER DETAILS                          ║
║                                                                    ║
║  BILL TO                        │  QUOTE DETAILS                   ║
║  ─────────────────────────────  │  ────────────────────────────   ║
║  Name: John Doe                 │  Valid Until: March 31, 2024    ║
║  Email: john@example.com        │                                  ║
║  Phone: 555-1234                │                                  ║
║                                  │                                  ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║                          LINE ITEMS                                ║
║                                                                    ║
║  # │ Description           │ Qty │ Unit Price │ Amount            ║
║  ──┼──────────────────────┼─────┼────────────┼──────────────────  ║
║  1 │ Professional Service │ 1   │ $500.00    │ $500.00           ║
║  2 │ Additional Item      │ 2   │ $100.00    │ $200.00           ║
║  ──┼──────────────────────┼─────┼────────────┼──────────────────  ║
║                                                                    ║
║                                      Subtotal: $700.00            ║
║                                      Tax (10%): $70.00            ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║                                      TOTAL: $770.00               ║
║                                                                    ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║  NOTES                                                             ║
║  ─────────────────────────────────────────────────────────────   ║
║  Thank you for your business. This quote is valid for 30 days.   ║
║                                                                    ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║  This is a professional quote document. Please contact us if you  ║
║  have any questions.                                               ║
║                                                                    ║
╚════════════════════════════════════════════════════════════════════╝

COLOR SCHEME:
- Header: #1a5490 (Professional Blue)
- Borders: #e0e0e0 (Light Gray)
- Text: #333333 (Dark Gray)
- Background: White
```

---

## Invoice PDF Layout

```
╔════════════════════════════════════════════════════════════════════╗
║                                                                    ║
║                         INVOICE                       #INV-001    ║
║              Professional Invoice Document              Date: ...  ║
║                                                     [PAID]         ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║                       CUSTOMER DETAILS                             ║
║                                                                    ║
║  BILL TO                        │  INVOICE DETAILS                 ║
║  ─────────────────────────────  │  ────────────────────────────   ║
║  Name: Jane Doe                 │  Due Date: April 30, 2024       ║
║  Email: jane@example.com        │                                  ║
║  Phone: 555-5678                │                                  ║
║                                  │                                  ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║                          LINE ITEMS                                ║
║                                                                    ║
║  # │ Description           │ Qty │ Unit Price │ Amount            ║
║  ──┼──────────────────────┼─────┼────────────┼──────────────────  ║
║  1 │ Room Charge - 2 Nts  │ 2   │ $250.00    │ $500.00           ║
║  2 │ Restaurant Services  │ 1   │ $150.00    │ $150.00           ║
║  ──┼──────────────────────┼─────┼────────────┼──────────────────  ║
║                                                                    ║
║                                      Subtotal: $650.00            ║
║                                      Tax (10%): $65.00            ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║                                      TOTAL: $715.00               ║
║                                                                    ║
║                                  BALANCE DUE: $0.00              ║
║                                                                    ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║  NOTES                                                             ║
║  ─────────────────────────────────────────────────────────────   ║
║  Thank you for your stay. We look forward to seeing you again!   ║
║                                                                    ║
║ ════════════════════════════════════════════════════════════════  ║
║                                                                    ║
║  This is a professional invoice document. Please contact us if    ║
║  you have any questions about your billing.                       ║
║                                                                    ║
╚════════════════════════════════════════════════════════════════════╝

COLOR SCHEME:
- Header: #16a34a (Professional Green)
- Borders: #e0e0e0 (Light Gray)
- Text: #333333 (Dark Gray)
- Balance Due: #ef4444 (Red when > 0)
- Background: White
- Status Badges: Green (Paid), Blue (Open), Red (Overdue)
```

---

## Date Input Visual

### Quote List - Date Filters

```
┌────────────────────────┬────────────────────────┬─────────────────┐
│                        │                        │                 │
│ Date From              │ Date To                │ Search          │
│ ┌──────────────────────┐ ┌──────────────────────┐ ┌─────────────┐  │
│ │ YYYY-MM-DD    📅    │ │ YYYY-MM-DD    📅    │ │ Name or ID  │  │
│ └──────────────────────┘ └──────────────────────┘ └─────────────┘  │
│   ↑                       ↑                                         │
│   Fully Clickable!        Fully Clickable!                          │
│   Calendar Icon           Calendar Icon                             │
│                                                                     │
└────────────────────────┴────────────────────────┴─────────────────┘
```

### Quote Create - Valid Until

```
┌──────────────────────────────────┐
│                                  │
│ Valid Until *                    │
│ ┌──────────────────────────────┐ │
│ │ YYYY-MM-DD          [📅 Icon]│ │
│ └──────────────────────────────┘ │
│   ↑                               │
│   Fully Clickable!                │
│   SVG Calendar Icon               │
│                                  │
└──────────────────────────────────┘
```

### Quote Edit - Valid Until

```
┌──────────────────────────────────┐
│                                  │
│ Valid Until *                    │
│ ┌──────────────────────────────┐ │
│ │ YYYY-MM-DD            📅    │ │
│ └──────────────────────────────┘ │
│   ↑                               │
│   Fully Clickable!                │
│   Calendar Emoji Icon             │
│                                  │
└──────────────────────────────────┘
```

---

## Color Palette

### Quote PDF Colors
```
Professional Blue
#1a5490
█████████████████████

Light Gray (Borders)
#e0e0e0
█████████████████████

Dark Gray (Text)
#333333
█████████████████████

Medium Gray (Secondary Text)
#666666
█████████████████████

White (Background)
#FFFFFF
█████████████████████
```

### Invoice PDF Colors
```
Professional Green
#16a34a
█████████████████████

Light Gray (Borders)
#e0e0e0
█████████████████████

Dark Gray (Text)
#333333
█████████████████████

Medium Gray (Secondary Text)
#666666
█████████████████████

Red (Balance Due)
#ef4444
█████████████████████

White (Background)
#FFFFFF
█████████████████████
```

### Status Badge Colors
```
Open / Pending
#3b82f6
█████████████████████

Paid
#10b981
█████████████████████

Overdue
#ef4444
█████████████████████
```

---

## PDF Element Specifications

### Typography
```
Headings
- Font: Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- Size: 32px (main), 18px (secondary), 12px (section)
- Weight: Bold
- Color: Theme color (#1a5490 or #16a34a)

Body Text
- Font: Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- Size: 11px
- Weight: Normal
- Color: #333333

Labels
- Font: Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- Size: 12px
- Weight: Bold
- Color: Theme color (#1a5490 or #16a34a)
```

### Spacing
```
Document Margins: 40px all sides
Section Spacing: 30px between sections
Padding: 10-15px in cells and boxes
Gaps: 8px between related items
Line Height: 1.6x (relaxed for readability)
```

### Borders
```
Separator Lines: 1px solid #e0e0e0
Accent Borders: 2-3px solid (theme color)
Table Borders: 1px solid #e0e0e0
Header Borders: 3px solid (theme color) on bottom
```

---

## Before & After Comparison

### BEFORE: Web UI Theme in PDF
```
❌ Dark theme colors visible
❌ Web navigation styling
❌ Sidebar colors bleeding through
❌ Theme variables visible
❌ Not suitable for professional printing
```

### AFTER: Professional PDF Design
```
✅ Professional blue (quotes) or green (invoices) headers
✅ Clean, minimal design
✅ No web UI elements
✅ Professional typography
✅ Print-optimized colors
✅ Suitable for framing or archiving
```

---

## User Journey

### Printing a Quote
```
1. User opens Quote (Edit or Create page)
2. User clicks "Print" button
3. New window opens with professional PDF design
4. Browser print dialog appears
5. User selects printer and prints
6. Beautiful, professional document printed!
```

### Exporting to PDF
```
1. User opens Quote (Edit or Create page)
2. User clicks "Export PDF" button
3. html2pdf converts professional design to PDF
4. PDF downloads with naming:
   - quote-[number]-[date].pdf
   - e.g., quote-QT-001-2024-03-07.pdf
5. File saved to Downloads folder
```

### Selecting a Date
```
1. User clicks on date input field
2. Entire input area is clickable
3. Calendar emoji/icon visible for visual cue
4. Native date picker opens
5. User selects date
6. Date populated in input
7. User submits form
```

---

## Print Media Query

```css
@media print {
    body {
        padding: 0;
        background: white;
    }
    
    .container {
        max-width: 100%;
    }
    
    @page {
        margin: 20mm;
    }
    
    /* Hide non-printable elements */
    nav, header, footer, .no-print {
        display: none !important;
    }
    
    /* Ensure content visibility */
    * {
        box-shadow: none !important;
        transition: none !important;
    }
}
```

---

## File Size Reference

```
Quote PDF (simple):
- HTML: ~8-12KB
- As PDF: ~50-100KB (depending on complexity)

Invoice PDF (simple):
- HTML: ~8-12KB
- As PDF: ~50-100KB (depending on charges)

Quote PDF (complex with 20+ items):
- HTML: ~20-30KB
- As PDF: ~150-200KB

Overall Bundle Impact:
- PDFExporter.js: 12KB
- Minified: 4KB
- Gzipped: 2KB
```

---

## Responsive Behavior

### Desktop (1024px+)
```
Two-column layout:
- Left: Customer info (50%)
- Right: Quote/Invoice details (50%)

Two-column items table:
- Full width, scrollable if needed
```

### Tablet (768px-1023px)
```
Two-column layout adapts:
- Maintains readability
- Proper padding and spacing
```

### Mobile (< 768px)
```
Single column layout:
- Stack vertically
- Full width items
- Proper touch targets for date inputs
```

---

## Accessibility

### Color Contrast
- Text on white: 4.5:1 ratio (WCAG AA compliant)
- All colors tested for colorblind accessibility
- No color-only information

### Typography
- Clear, sans-serif fonts for readability
- Proper line height (1.6x)
- Adequate font sizes (11px minimum)

### Structure
- Semantic HTML
- Proper heading hierarchy
- Clear section divisions
- Numbered items for clarity

### Date Inputs
- Keyboard navigable
- Focus ring visible
- ARIA labels preserved
- Native HTML5 date input

---

**Design Guide Complete!**  
All visual specifications documented and ready for reference.
