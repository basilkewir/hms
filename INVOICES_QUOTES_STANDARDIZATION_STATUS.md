# Invoices & Quotes Full Standardization - Implementation Status

**Date:** March 7, 2026

---

## Current Status Assessment

### ✅ COMPLETE - FrontDesk Pages (Reference Implementation)

**FrontDesk Invoices:**
- ✅ Index.vue - Professional design with theme, stats, filters, table
- ✅ Create.vue - Professional form with theme colors
- ✅ Show.vue - Professional detail view

**FrontDesk Quotes:**
- ✅ Index.vue - Professional design with theme, stats, filters, table
- ✅ Create.vue - Professional form with theme colors, quote items
- ✅ Edit.vue - Professional form with quote items
- ✅ Show.vue - Professional detail view

---

## Implementation Plan by Role

### Role 1: ADMIN (6 files)

**Status: IN REVIEW - Likely 50-70% Complete**

Files to check/update:
- [ ] Admin/Invoices/Index.vue - Check if has stats cards and filters
- [ ] Admin/Invoices/Create.vue - Check if has professional form
- [ ] Admin/Invoices/Show.vue - Check if has detail view styling
- [ ] Admin/Quotes/Index.vue - Check layout and stats
- [ ] Admin/Quotes/Create.vue - Check form styling
- [ ] Admin/Quotes/Show.vue - Check if exists/needs creation

**Actions:**
1. Review current implementations
2. Compare with FrontDesk reference
3. Update to match FrontDesk pattern
4. Ensure all route names are admin.* instead of front-desk.*

---

### Role 2: MANAGER (5 files)

**Status: IN REVIEW - Likely 50-70% Complete**

Files to check/update:
- [ ] Manager/Invoices/Index.vue
- [ ] Manager/Invoices/Create.vue
- [ ] Manager/Invoices/Show.vue
- [ ] Manager/Quotes/Index.vue
- [ ] Manager/Quotes/Create.vue

**Actions:**
1. Review current implementations
2. Update route names to manager.*
3. Align with FrontDesk design patterns
4. Ensure theme color integration

---

### Role 3: ACCOUNTANT (7 files)

**Status: IN REVIEW - Likely 40-60% Complete**

Files to check/update:
- [ ] Accountant/Invoices/Index.vue
- [ ] Accountant/Invoices/Create.vue
- [ ] Accountant/Invoices/Show.vue
- [ ] Accountant/Invoices/Paid.vue
- [ ] Accountant/Invoices/Overdue.vue
- [ ] Accountant/Quotes/Index.vue
- [ ] Accountant/Quotes/Create.vue

**Special Considerations:**
- Paid.vue and Overdue.vue are filtered views of invoices
- May have additional filtering/reporting features
- Should follow same design pattern as Index.vue

**Actions:**
1. Review all 7 files
2. Update to match FrontDesk patterns
3. Update route names to accountant.*
4. Ensure proper stat calculations for different views
5. Add appropriate filters for Paid/Overdue views

---

## Quick Checklist for Each Page

### For Index Pages:
- [ ] Proper header with description
- [ ] "New" button (Create) with primary color
- [ ] Export button (if applicable)
- [ ] Stat cards grid with icons and colors
- [ ] Filters section with proper styling
- [ ] Professional table with:
  - [ ] Hover effects (backgroundColor = themeColors.hover)
  - [ ] Theme-colored text and borders
  - [ ] Status badges with color coding
  - [ ] Action links (View, Edit, etc.)
- [ ] Pagination component
- [ ] useTheme composable imported and initialized
- [ ] All colors use CSS variables, not hardcoded
- [ ] Proper Heroicons imported

### For Create/Edit Pages:
- [ ] Professional header with title
- [ ] Back button to list view
- [ ] Form fields with proper styling
- [ ] Error messages displayed
- [ ] Submit/Cancel buttons
- [ ] useTheme composable initialized
- [ ] All form inputs styled with theme colors
- [ ] Validation feedback

### For Show Pages:
- [ ] Header with title and details
- [ ] Edit/Back buttons
- [ ] Detail sections with proper layout
- [ ] Status badge with color coding
- [ ] Print/Export functionality (if applicable)
- [ ] Action links or buttons
- [ ] Theme color integration

---

## Key Design Elements to Implement

### 1. Theme Color Setup
```javascript
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))
loadTheme()
```

### 2. Header Pattern
```vue
<div class="shadow rounded-lg p-6 mb-8"
     :style="{ 
         backgroundColor: themeColors.card,
         borderColor: themeColors.border 
     }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-2"
                :style="{ color: themeColors.textPrimary }">Title</h1>
            <p class="text-sm"
               :style="{ color: themeColors.textSecondary }">Description</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Buttons -->
        </div>
    </div>
</div>
```

### 3. Table Row Hover
```vue
<tr @mouseenter="$event.target.parentElement.style.backgroundColor = themeColors.hover"
    @mouseleave="$event.target.parentElement.style.backgroundColor = 'transparent'">
```

### 4. Status Badge Function
```javascript
const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        // ... add all status options
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
```

---

## Implementation Order

### Phase 1 - HIGH PRIORITY (Do First)
**All FrontDesk CRUD for both Invoices and Quotes**
- ✅ Already complete - just verify no regressions

### Phase 2 - HIGH PRIORITY (Do Second)
**Admin Pages - Invoices and Quotes**
- Check current state
- Update all 6 files to match FrontDesk pattern
- Route names: admin.*

### Phase 3 - MEDIUM PRIORITY
**Manager Pages - Invoices and Quotes**
- Check current state  
- Update all 5 files to match FrontDesk pattern
- Route names: manager.*

### Phase 4 - MEDIUM PRIORITY
**Accountant Pages - Main Invoices and Quotes**
- Check current state
- Update Invoices: Index, Create, Show
- Update Quotes: Index, Create
- Route names: accountant.*

### Phase 5 - MEDIUM PRIORITY
**Accountant Special Views**
- Invoices/Paid.vue
- Invoices/Overdue.vue
- These filtered views should maintain same design as Index.vue

---

## Commands to Execute (Reference)

Once each file is identified as needing updates:

```bash
# View current state
code resources/js/Pages/{role}/{module}/{page}.vue

# Compare with reference
# Look at FrontDesk/Invoices/Index.vue or FrontDesk/Quotes/Index.vue

# Update the file using replace_string_in_file tool
# Focus on:
# 1. Adding theme colors if missing
# 2. Updating route names to role-specific routes
# 3. Aligning header section
# 4. Adding/updating stat cards
# 5. Fixing table structure if needed
# 6. Updating button/link styling
```

---

## Validation Checklist

For each updated file:
- [ ] No compilation errors (`get_errors`)
- [ ] Correct route names for the role
- [ ] Theme colors integrated (not hardcoded colors)
- [ ] Header properly styled
- [ ] If Index page: has stats, filters, table
- [ ] If CRUD page: has proper form styling
- [ ] All icons properly imported
- [ ] Pagination component imported (if applicable)
- [ ] useTheme composable initialized
- [ ] Hover effects on table rows
- [ ] Status badges display correctly
- [ ] Responsive grid layout (grid-cols-1 md:... lg:... xl:...)

---

## Summary

**Total Files to Review/Update: 28**
- ✅ FrontDesk: 7 files (COMPLETE)
- ⏳ Admin: 6 files (PENDING REVIEW)
- ⏳ Manager: 5 files (PENDING REVIEW)
- ⏳ Accountant: 7 files (PENDING REVIEW)

**Current Completion: ~25%**
**Time Estimate: 2-3 hours for full implementation**

Next Step: Begin Phase 2 - Admin pages standardization
