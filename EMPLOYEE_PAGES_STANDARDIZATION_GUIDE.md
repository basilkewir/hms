# 🎨 Employee Pages Design Standardization Guide

## Objective
Update all employee-related pages (tasks, time tracking, schedules, shifts, housekeeping schedules) to match the design, style, and theme of `/admin/reservations` page and use real database data.

## Design Pattern from Reservations Page

### 1. **Theme System**
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

### 2. **Header Section Pattern**
```vue
<div class="shadow rounded-lg p-6 mb-8"
     :style="{ 
         backgroundColor: themeColors.card,
         borderColor: themeColors.border 
     }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-2"
                :style="{ color: themeColors.textPrimary }">Page Title</h1>
            <p class="text-sm"
               :style="{ color: themeColors.textSecondary }">Description</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Action buttons -->
        </div>
    </div>
</div>
```

### 3. **Stats Cards Pattern**
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
                   :style="{ color: themeColors.textPrimary }">{{ value }}</p>
            </div>
        </div>
    </div>
</div>
```

### 4. **Table Pattern**
```vue
<div class="rounded-lg overflow-hidden shadow"
     :style="{ 
         backgroundColor: themeColors.card,
         borderColor: themeColors.border,
         borderStyle: 'solid',
         borderWidth: '1px'
     }">
    <div class="px-6 py-4 border-b"
         :style="{ 
             borderColor: themeColors.border,
             borderBottomWidth: '1px'
         }">
        <h3 class="text-lg font-medium"
            :style="{ color: themeColors.textPrimary }">Table Title</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr :style="{ backgroundColor: themeColors.background }">
                    <th :style="{ color: themeColors.textTertiary }">Header</th>
                </tr>
            </thead>
            <tbody>
                <tr :style="{ borderColor: themeColors.border }"
                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                    @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                    <td :style="{ color: themeColors.textPrimary }">Data</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

---

## Pages to Update

### 1. Time Tracking (`/admin/time-tracking`)

**Controller**: `App\Http\Controllers\Admin\TimeTrackingController@index`

**Required Data**:
```php
return Inertia::render('Admin/TimeTracking/Index', [
    'user' => $request->user()->load('roles'),
    'timeEntries' => TimeEntry::with(['user.department', 'user.position'])
        ->whereDate('clock_in', '>=', Carbon::today()->subDays(30))
        ->orderByDesc('clock_in')
        ->paginate(20),
    'stats' => [
        'total_hours_today' => TimeEntry::whereDate('clock_in', Carbon::today())->sum('hours_worked'),
        'clocked_in' => TimeEntry::whereNull('clock_out')->count(),
        'total_employees' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['housekeeping', 'maintenance', 'staff']))->count(),
        'late_clock_ins' => TimeEntry::whereDate('clock_in', Carbon::today())->where('is_late', true)->count(),
    ]
]);
```

**Design Elements**:
- Header with "Time Tracking Overview"
- 4 stat cards: Total Hours Today, Clocked In, Total Employees, Late Clock-ins
- Table with columns: Employee, Department, Clock In, Clock Out, Hours, Status, Actions
- Export button
- Real-time clock display

---

### 2. Schedules (`/admin/schedules`)

**Controller**: `App\Http\Controllers\Admin\ScheduleController@index`

**Required Data**:
```php
return Inertia::render('Admin/Schedules/Index', [
    'user' => $request->user()->load('roles'),
    'schedules' => EmployeeShift::with(['user', 'workShift'])
        ->whereBetween('shift_date', [Carbon::today(), Carbon::today()->addDays(7)])
        ->orderBy('shift_date')
        ->orderBy('start_time')
        ->get(),
    'workShifts' => WorkShift::where('is_active', true)->get(),
    'employees' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['housekeeping', 'maintenance', 'staff']))->get(),
    'stats' => [
        'total_shifts_week' => EmployeeShift::whereBetween('shift_date', [Carbon::today(), Carbon::today()->addWeek()])->count(),
        'unassigned_shifts' => WorkShift::whereDoesntHave('employeeShifts', fn($q) => $q->whereDate('shift_date', '>=', Carbon::today()))->count(),
        'total_employees' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['housekeeping', 'maintenance', 'staff']))->count(),
    ]
]);
```

**Design Elements**:
- Header with "Staff Schedules"
- 3 stat cards: Total Shifts This Week, Unassigned Shifts, Total Employees
- Calendar view or table view toggle
- Table with columns: Employee, Shift, Date, Start Time, End Time, Status, Actions
- "Generate Schedule" button
- Export button

---

### 3. Work Shifts (`/admin/work-shifts`)

**Controller**: `App\Http\Controllers\Admin\WorkShiftController@index`

**Required Data**:
```php
return Inertia::render('Admin/WorkShifts/Index', [
    'user' => $request->user()->load('roles'),
    'workShifts' => WorkShift::withCount('employeeShifts')->orderBy('start_time')->get(),
    'stats' => [
        'total_shifts' => WorkShift::count(),
        'active_shifts' => WorkShift::where('is_active', true)->count(),
        'morning_shifts' => WorkShift::where('shift_type', 'morning')->count(),
        'evening_shifts' => WorkShift::where('shift_type', 'evening')->count(),
        'night_shifts' => WorkShift::where('shift_type', 'night')->count(),
    ]
]);
```

**Design Elements**:
- Header with "Work Shifts Management"
- 5 stat cards: Total Shifts, Active, Morning, Evening, Night
- Table with columns: Shift Name, Type, Start Time, End Time, Duration, Assigned Employees, Status, Actions
- "Create Shift" button

---

### 4. Housekeeping Tasks (`/admin/housekeeping-tasks`)

**Controller**: `App\Http\Controllers\Admin\HousekeepingTaskController@index`

**Required Data**:
```php
return Inertia::render('Admin/Housekeeping/Tasks/Index', [
    'user' => $request->user()->load('roles'),
    'tasks' => HousekeepingTask::with(['room', 'assignedTo'])
        ->whereDate('created_at', '>=', Carbon::today()->subDays(7))
        ->orderByDesc('created_at')
        ->paginate(20),
    'stats' => [
        'total_tasks' => HousekeepingTask::whereDate('created_at', Carbon::today())->count(),
        'pending' => HousekeepingTask::where('status', 'pending')->count(),
        'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
        'completed_today' => HousekeepingTask::where('status', 'completed')->whereDate('completed_at', Carbon::today())->count(),
    ]
]);
```

**Design Elements**:
- Header with "Housekeeping Tasks"
- 4 stat cards: Total Tasks Today, Pending, In Progress, Completed Today
- Table with columns: Room, Task Type, Assigned To, Priority, Status, Created, Actions
- Filter by status, priority, room
- "Create Task" button

---

### 5. Housekeeping Schedules (`/admin/housekeeping/schedules`)

**Controller**: `App\Http\Controllers\Admin\HousekeepingScheduleController@index`

**Required Data**:
```php
return Inertia::render('Admin/Housekeeping/Schedules/Index', [
    'user' => $request->user()->load('roles'),
    'schedules' => HousekeepingSchedule::with(['user', 'rooms'])
        ->whereDate('schedule_date', '>=', Carbon::today())
        ->orderBy('schedule_date')
        ->get(),
    'housekeepers' => User::whereHas('roles', fn($q) => $q->where('name', 'housekeeping'))->get(),
    'rooms' => Room::where('is_active', true)->orderBy('room_number')->get(),
    'stats' => [
        'total_schedules_week' => HousekeepingSchedule::whereBetween('schedule_date', [Carbon::today(), Carbon::today()->addWeek()])->count(),
        'rooms_assigned_today' => HousekeepingSchedule::whereDate('schedule_date', Carbon::today())->sum('rooms_count'),
        'housekeepers_scheduled' => HousekeepingSchedule::whereDate('schedule_date', Carbon::today())->distinct('user_id')->count(),
    ]
]);
```

**Design Elements**:
- Header with "Housekeeping Schedules"
- 3 stat cards: Schedules This Week, Rooms Assigned Today, Housekeepers Scheduled
- Table with columns: Housekeeper, Date, Rooms Assigned, Status, Actions
- "Create Schedule" button
- Calendar view option

---

### 6. Maintenance Requests (`/admin/maintenance-requests`)

**Controller**: `App\Http\Controllers\Admin\MaintenanceRequestController@index`

**Required Data**:
```php
return Inertia::render('Admin/Maintenance/Requests/Index', [
    'user' => $request->user()->load('roles'),
    'requests' => MaintenanceRequest::with(['room', 'assignedTo', 'reportedBy'])
        ->orderByDesc('created_at')
        ->paginate(20),
    'stats' => [
        'total_requests' => MaintenanceRequest::count(),
        'pending' => MaintenanceRequest::where('status', 'pending')->count(),
        'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
        'completed_today' => MaintenanceRequest::where('status', 'resolved')->whereDate('updated_at', Carbon::today())->count(),
    ]
]);
```

**Design Elements**:
- Header with "Maintenance Requests"
- 4 stat cards: Total Requests, Pending, In Progress, Completed Today
- Table with columns: Request ID, Room, Category, Priority, Assigned To, Status, Created, Actions
- Filter by status, priority, category
- "Create Request" button

---

## Implementation Steps

### Step 1: Update Controllers
For each controller, ensure it returns:
1. Real database data (no dummy data)
2. Statistics for stat cards
3. Paginated results where appropriate
4. Related models loaded with `with()`

### Step 2: Update Vue Components
For each page:
1. Import `useTheme` composable
2. Add `themeColors` computed property
3. Call `loadTheme()` on mount
4. Apply theme colors to all elements using `:style` bindings
5. Remove all dummy/hardcoded data
6. Use props data from controller

### Step 3: Standardize Structure
Every page should have:
1. **Header Section** - Title, description, action buttons
2. **Stats Cards** - 3-6 cards with key metrics
3. **Main Content** - Table or list view
4. **Pagination** - If data is paginated
5. **Export Button** - For data export

### Step 4: Apply Consistent Styling
- Use `shadow rounded-lg` for cards
- Use `border` with `themeColors.border`
- Use `themeColors.card` for backgrounds
- Use `themeColors.textPrimary/Secondary/Tertiary` for text
- Add hover effects with `@mouseenter/@mouseleave`
- Use consistent spacing: `p-6`, `mb-8`, `gap-6`

### Step 5: Status Badges
Use consistent status badge styling:
```vue
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      :class="getStatusBadgeClass(item.status)">
    {{ formatStatus(item.status) }}
</span>
```

---

## Code Templates

### Complete Page Template
```vue
<template>
    <DashboardLayout :title="pageTitle" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">
                        {{ pageTitle }}
                    </h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        {{ pageDescription }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="createNew" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ backgroundColor: themeColors.primary }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Create New
                    </button>
                    <button @click="exportData" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ backgroundColor: '#8b5cf6' }">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in stats" :key="stat.label"
                 class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: stat.bgColor }">
                        <component :is="stat.icon" class="h-6 w-6" :style="{ color: stat.iconColor }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                            {{ stat.label }}
                        </p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ stat.value }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                    {{ tableTitle }}
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th v-for="header in headers" :key="header"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                {{ header }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id"
                            :style="{ borderColor: themeColors.border }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <!-- Table cells -->
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="pagination" class="px-6 py-4 border-t" :style="{ borderColor: themeColors.border }">
                <Pagination :links="pagination.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { PlusIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

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

const props = defineProps({
    user: Object,
    items: Object,
    stats: Object,
})
</script>
```

---

## Database Queries Best Practices

### 1. Always Use Eager Loading
```php
// Bad
$tasks = HousekeepingTask::all();

// Good
$tasks = HousekeepingTask::with(['room', 'assignedTo', 'reportedBy'])->get();
```

### 2. Use Pagination
```php
$items = Model::with('relations')->paginate(20);
```

### 3. Calculate Stats Efficiently
```php
$stats = [
    'total' => Model::count(),
    'active' => Model::where('status', 'active')->count(),
    'today' => Model::whereDate('created_at', Carbon::today())->count(),
];
```

### 4. Order Results
```php
$items = Model::orderByDesc('created_at')->get();
```

---

## Testing Checklist

For each updated page:
- [ ] Theme colors apply correctly
- [ ] No dummy data visible
- [ ] Stats cards show real numbers
- [ ] Table displays database records
- [ ] Pagination works
- [ ] Export button functions
- [ ] Hover effects work
- [ ] Status badges display correctly
- [ ] Actions (View/Edit/Delete) work
- [ ] Responsive on mobile
- [ ] Loading states handled
- [ ] Empty states handled

---

## Priority Order

1. **Time Tracking** - Most used by staff
2. **Schedules** - Critical for operations
3. **Housekeeping Tasks** - Daily operations
4. **Work Shifts** - Staff management
5. **Housekeeping Schedules** - Planning
6. **Maintenance Requests** - Facility management

---

**Status**: Ready for Implementation
**Estimated Time**: 2-3 hours per page
**Total Pages**: 6 pages
