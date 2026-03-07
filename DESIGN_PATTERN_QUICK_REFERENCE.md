# 🎨 Employee Pages Design Pattern - Quick Reference

## 📋 Standard Page Structure

Every employee page should follow this exact structure:

```
1. Header Section (Title + Description + Action Button)
2. Stats Cards (4-6 cards with icons and numbers)
3. Data Table (Themed header + Hover rows + Action buttons)
4. Pagination (if needed)
```

---

## 🎨 Theme Colors Reference

### Available Theme Colors:
```javascript
themeColors = {
    background: 'var(--kotel-background)',      // Page background
    card: 'var(--kotel-card)',                  // Card backgrounds
    border: 'var(--kotel-border)',              // Borders
    textPrimary: 'var(--kotel-text-primary)',   // Main text
    textSecondary: 'var(--kotel-text-secondary)', // Secondary text
    textTertiary: 'var(--kotel-text-tertiary)', // Tertiary text
    primary: 'var(--kotel-primary)',            // Primary actions (yellow)
    success: 'var(--kotel-success)',            // Success states (green)
    warning: 'var(--kotel-warning)',            // Warning states (orange)
    danger: 'var(--kotel-danger)',              // Danger actions (red)
    hover: 'rgba(255, 255, 255, 0.05)'         // Hover effect (dark mode)
}
```

### Color Usage Guide:
- **Primary (Yellow)**: Main actions, primary buttons, active states
- **Success (Green)**: Completed, approved, active status
- **Warning (Orange)**: Pending, in-progress, caution
- **Danger (Red)**: Delete, cancel, error states
- **Background**: Table headers, secondary sections
- **Card**: Main content areas, cards
- **Border**: All borders and dividers

---

## 📝 Code Templates

### 1. Setup Script (Copy this to every page)
```vue
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    data: Array,
    stats: Object,
})

const { currentTheme } = useTheme()
const hoveredRow = ref(null)

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))
</script>
```

### 2. Header Section Template
```vue
<div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
     class="shadow rounded-lg p-6 mb-8 border">
    <div class="flex items-center justify-between">
        <div>
            <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">
                Page Title
            </h1>
            <p :style="{ color: themeColors.textSecondary }" class="mt-2">
                Page description goes here
            </p>
        </div>
        <button @click="handleAction"
                :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
            <PlusIcon class="h-4 w-4 mr-2" />
            Add New
        </button>
    </div>
</div>
```

### 3. Stats Card Template
```vue
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Card 1 -->
    <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
         class="rounded-lg shadow p-6 border">
        <div class="flex items-center">
            <IconComponent :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
            <div>
                <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">
                    Label
                </p>
                <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">
                    {{ stats.value }}
                </p>
            </div>
        </div>
    </div>
    
    <!-- Repeat for 3-5 more cards -->
</div>
```

### 4. Data Table Template
```vue
<div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
     class="shadow rounded-lg overflow-hidden border">
    <div :style="{ borderColor: themeColors.border }" class="px-6 py-4 border-b">
        <h3 :style="{ color: themeColors.textPrimary }" class="text-lg font-medium">
            Table Title
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead :style="{ backgroundColor: themeColors.background }">
                <tr>
                    <th :style="{ color: themeColors.textSecondary }" 
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                        Column 1
                    </th>
                    <!-- More columns -->
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in data" :key="item.id"
                    :style="hoveredRow === item.id ? { backgroundColor: themeColors.hover } : {}"
                    @mouseenter="hoveredRow = item.id"
                    @mouseleave="hoveredRow = null"
                    class="transition-colors">
                    <td :style="{ color: themeColors.textPrimary }" 
                        class="px-6 py-4 whitespace-nowrap text-sm">
                        {{ item.value }}
                    </td>
                    <!-- More columns -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button @click="view(item)" 
                                    :style="{ color: themeColors.primary }" 
                                    class="hover:opacity-80">View</button>
                            <button @click="edit(item)" 
                                    :style="{ color: themeColors.success }" 
                                    class="hover:opacity-80">Edit</button>
                            <button @click="remove(item)" 
                                    :style="{ color: themeColors.danger }" 
                                    class="hover:opacity-80">Delete</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### 5. Status Badge Template
```vue
<!-- Use fixed Tailwind classes for status badges -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      :class="{
          'bg-green-100 text-green-800': status === 'active',
          'bg-yellow-100 text-yellow-800': status === 'pending',
          'bg-blue-100 text-blue-800': status === 'in_progress',
          'bg-red-100 text-red-800': status === 'cancelled'
      }">
    {{ formatStatus(status) }}
</span>
```

---

## 🎯 Common Patterns

### Icon Colors by Purpose:
```vue
<!-- Primary action icon -->
<ClockIcon :style="{ color: themeColors.primary }" class="h-8 w-8" />

<!-- Success/Active icon -->
<CheckCircleIcon :style="{ color: themeColors.success }" class="h-8 w-8" />

<!-- Warning/Pending icon -->
<ExclamationTriangleIcon :style="{ color: themeColors.warning }" class="h-8 w-8" />

<!-- Danger/Error icon -->
<XCircleIcon :style="{ color: themeColors.danger }" class="h-8 w-8" />
```

### Button Patterns:
```vue
<!-- Primary action button -->
<button :style="{ backgroundColor: themeColors.primary, color: '#000' }"
        class="px-4 py-2 rounded-md hover:opacity-90">
    Primary Action
</button>

<!-- Text button (View) -->
<button :style="{ color: themeColors.primary }" 
        class="hover:opacity-80">
    View
</button>

<!-- Text button (Edit) -->
<button :style="{ color: themeColors.success }" 
        class="hover:opacity-80">
    Edit
</button>

<!-- Text button (Delete) -->
<button :style="{ color: themeColors.danger }" 
        class="hover:opacity-80">
    Delete
</button>
```

### Avatar/Initial Circle:
```vue
<div :style="{ backgroundColor: themeColors.primary, color: '#000' }" 
     class="w-10 h-10 rounded-full flex items-center justify-center">
    <span class="text-sm font-medium">{{ getInitials(name) }}</span>
</div>
```

---

## 📊 Controller Pattern (Laravel)

### Standard Controller Method:
```php
public function index()
{
    // Calculate stats
    $stats = [
        'total' => Model::count(),
        'active' => Model::where('status', 'active')->count(),
        'pending' => Model::where('status', 'pending')->count(),
        'completed' => Model::where('status', 'completed')->count(),
    ];

    // Get paginated data with relationships
    $data = Model::with(['user', 'department'])
        ->orderByDesc('created_at')
        ->paginate(20);

    return Inertia::render('Admin/PageName/Index', [
        'user' => auth()->user()->load('roles'),
        'data' => $data,
        'stats' => $stats,
    ]);
}
```

---

## ✅ Checklist for New Pages

When creating a new employee page, ensure:

- [ ] Import useTheme composable
- [ ] Create hoveredRow ref
- [ ] Create themeColors computed property
- [ ] Add header section with theme colors
- [ ] Add 4-6 stats cards with icons
- [ ] Add data table with themed header
- [ ] Add hover effect to table rows
- [ ] Add action buttons with theme colors
- [ ] Use real database data (no dummy data)
- [ ] Add pagination if needed
- [ ] Test in dark and light mode
- [ ] Test hover effects
- [ ] Test on mobile devices

---

## 🚫 Common Mistakes to Avoid

1. ❌ **Don't use hardcoded colors**: Always use themeColors
2. ❌ **Don't use inline hover styles**: Use hoveredRow ref pattern
3. ❌ **Don't use dummy data**: Always use real database queries
4. ❌ **Don't skip stats cards**: Every page needs 4-6 stats
5. ❌ **Don't forget mobile**: Test responsive design
6. ❌ **Don't use bg-white**: Use themeColors.card
7. ❌ **Don't use text-gray-900**: Use themeColors.textPrimary
8. ❌ **Don't use hover:bg-gray-50**: Use hoveredRow pattern

---

## 📱 Responsive Design

### Grid Breakpoints:
```vue
<!-- Stats cards: 1 column mobile, 4 columns desktop -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

<!-- Stats cards: 1 column mobile, 5 columns desktop -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4">

<!-- Table: Always scrollable on mobile -->
<div class="overflow-x-auto">
    <table class="min-w-full">
```

---

## 🎓 Examples

### Complete Working Examples:
1. `/admin/time-tracking` - Time Tracking page
2. `/admin/work-shifts` - Work Shifts page
3. `/admin/schedules` - Schedules page
4. `/admin/housekeeping-tasks` - Housekeeping Tasks page
5. `/admin/housekeeping/schedules` - Housekeeping Schedules page
6. `/admin/maintenance-requests` - Maintenance Requests page

**Reference File**: `resources/js/Pages/Admin/Reservations/Index.vue` (Original design pattern)

---

## 📞 Need Help?

1. Check existing pages for examples
2. Review EMPLOYEE_PAGES_STANDARDIZATION_COMPLETE.md
3. Test in both dark and light mode
4. Verify database queries return real data
5. Check browser console for errors

---

**Last Updated**: January 2024
**Version**: 1.0
**Status**: ✅ Production Ready
