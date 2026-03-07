# Invoices & Quotes Pages Standardization Plan

**Objective:** Make all invoices and quotes pages across all user roles (Admin, Manager, FrontDesk, Accountant) use the same professional design pattern as FrontDesk Invoices and FrontDesk Quotes pages.

**Date:** March 7, 2026

---

## Reference Implementation

### FrontDesk Invoices: `/front-desk/invoices`
**Location:** `resources/js/Pages/FrontDesk/Invoices/Index.vue`

**Design Features:**
- ✅ Theme colors (card, border, text colors)
- ✅ Professional header with description
- ✅ "New Invoice" button (primary color)
- ✅ "Export" button (purple color)
- ✅ Multiple stat cards with icons and colors
- ✅ Filter section with date pickers (SVG icons, pointer-events-none)
- ✅ Professional table with hover effects
- ✅ Status badges with color coding
- ✅ Action links with proper styling
- ✅ Pagination support

### FrontDesk Quotes: `/front-desk/quotes`
**Location:** `resources/js/Pages/FrontDesk/Quotes/Index.vue`

**Design Features:**
- ✅ Same professional header pattern
- ✅ "New Quote" button
- ✅ "Export" button
- ✅ Stat cards with icons
- ✅ Date filters (Date From, Date To)
- ✅ Professional table layout
- ✅ Status badges
- ✅ Action links

---

## Pages to Update

### INVOICES (14 files total)

#### By Role:

**FrontDesk - 3 files (REFERENCE - NO CHANGES NEEDED)**
- [✅] `resources/js/Pages/FrontDesk/Invoices/Index.vue` - Reference implementation
- [ ] `resources/js/Pages/FrontDesk/Invoices/Create.vue`
- [ ] `resources/js/Pages/FrontDesk/Invoices/Show.vue`

**Manager - 3 files**
- [ ] `resources/js/Pages/Manager/Invoices/Index.vue` 
- [ ] `resources/js/Pages/Manager/Invoices/Create.vue`
- [ ] `resources/js/Pages/Manager/Invoices/Show.vue`

**Admin - 3 files**
- [ ] `resources/js/Pages/Admin/Invoices/Index.vue`
- [ ] `resources/js/Pages/Admin/Invoices/Create.vue`
- [ ] `resources/js/Pages/Admin/Invoices/Show.vue`

**Accountant - 5 files**
- [ ] `resources/js/Pages/Accountant/Invoices/Index.vue`
- [ ] `resources/js/Pages/Accountant/Invoices/Create.vue`
- [ ] `resources/js/Pages/Accountant/Invoices/Show.vue`
- [ ] `resources/js/Pages/Accountant/Invoices/Paid.vue`
- [ ] `resources/js/Pages/Accountant/Invoices/Overdue.vue`

---

### QUOTES (14 files total)

**FrontDesk - 4 files (REFERENCE - NO CHANGES NEEDED)**
- [✅] `resources/js/Pages/FrontDesk/Quotes/Index.vue` - Reference implementation
- [ ] `resources/js/Pages/FrontDesk/Quotes/Create.vue`
- [ ] `resources/js/Pages/FrontDesk/Quotes/Edit.vue`
- [ ] `resources/js/Pages/FrontDesk/Quotes/Show.vue`

**Manager - 2 files**
- [ ] `resources/js/Pages/Manager/Quotes/Index.vue`
- [ ] `resources/js/Pages/Manager/Quotes/Create.vue`

**Admin - 2 files**
- [ ] `resources/js/Pages/Admin/Quotes/Index.vue`
- [ ] `resources/js/Pages/Admin/Quotes/Create.vue`

**Accountant - 2 files**
- [ ] `resources/js/Pages/Accountant/Quotes/Index.vue`
- [ ] `resources/js/Pages/Accountant/Quotes/Create.vue`

---

## Design Pattern Standard

### Header Section
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
            <!-- Action Buttons -->
        </div>
    </div>
</div>
```

### Stats Cards
```vue
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <div class="rounded-lg p-6 border shadow-sm"
         :style="{ 
             backgroundColor: themeColors.card,
             borderColor: themeColors.border,
             borderStyle: 'solid',
             borderWidth: '1px'
         }">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                 :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                <Icon class="h-6 w-6" :style="{ color: themeColors.primary }" />
            </div>
            <div>
                <p class="text-sm font-medium mb-1"
                   :style="{ color: themeColors.textSecondary }">Label</p>
                <p class="text-2xl font-bold"
                   :style="{ color: themeColors.textPrimary }">Value</p>
            </div>
        </div>
    </div>
</div>
```

### Table Header
```vue
<thead :style="{ backgroundColor: themeColors.background }">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
            :style="{ color: themeColors.textTertiary }">
            Column Name
        </th>
    </tr>
</thead>
```

### Table Rows
```vue
<tr v-for="item in items" :key="item.id" 
    class="transition-colors"
    :style="{ 
        borderBottomStyle: 'solid',
        borderBottomWidth: '1px',
        borderColor: themeColors.border
    }"
    @mouseenter="$event.target.parentElement.style.backgroundColor = themeColors.hover"
    @mouseleave="$event.target.parentElement.style.backgroundColor = 'transparent'">
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
        :style="{ color: themeColors.textPrimary }">
        {{ item.name }}
    </td>
</tr>
```

### Status Badges
```vue
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      :class="getStatusBadgeClass(item.status)">
    {{ formatStatus(item.status) }}
</span>
```

---

## Script Setup Requirements

### Imports
```javascript
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    PlusIcon,
    DocumentArrowDownIcon,
    // ... other icons as needed
} from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'
```

### Theme Initialization
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
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

loadTheme()
```

---

## Implementation Priority

### Phase 1 - FrontDesk Quotes (CRUD) - HIGH PRIORITY
- Create.vue
- Edit.vue
- Show.vue
- **Status:** Already partially done, needs final review

### Phase 2 - FrontDesk Invoices (CRUD) - HIGH PRIORITY
- Create.vue
- Show.vue

### Phase 3 - Manager Pages - MEDIUM PRIORITY
- Manager/Invoices: Index, Create, Show
- Manager/Quotes: Index, Create

### Phase 4 - Admin Pages - MEDIUM PRIORITY
- Admin/Invoices: Index, Create, Show
- Admin/Quotes: Index, Create

### Phase 5 - Accountant Pages - MEDIUM PRIORITY
- Accountant/Invoices: Index, Create, Show, Paid, Overdue
- Accountant/Quotes: Index, Create

---

## Success Criteria

✅ All pages use theme colors (no hardcoded colors)
✅ All pages have professional header with description
✅ All Index pages have stat cards
✅ All Index pages have filters section
✅ All Index pages have professional tables with hover effects
✅ All CRUD pages have consistent styling
✅ All pages support pagination
✅ All pages have proper error handling
✅ All pages compile without errors
✅ All date inputs match invoices pattern
✅ All status badges use color coding

---

## Notes

- Route names vary by role (admin.invoices, manager.invoices, front-desk.invoices, etc.)
- Status options may vary slightly between roles
- Filter options should match the specific module
- Icons should be consistent across all pages
- Color schemes should be consistent
