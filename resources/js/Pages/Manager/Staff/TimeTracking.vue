<template>
    <DashboardLayout title="Time Tracking" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Time Tracking</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Monitor employee attendance and working hours.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportTimesheet"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.warning, color: '#000' }">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Select Date</label>
                    <div class="relative">
                        <input
                            ref="dateInput"
                            type="date"
                            id="date"
                            v-model="selectedDate"
                            @change="applyFilters"
                            class="w-full rounded-lg border shadow-sm focus:outline-none cursor-pointer"
                            :style="inputStyle"
                        >
                        <div class="absolute inset-0 cursor-pointer" @click="dateInput?.showPicker ? dateInput.showPicker() : dateInput?.focus()"></div>
                    </div>
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Department</label>
                    <select
                        id="department"
                        v-model="filterDepartment"
                        @change="applyFilters"
                        class="w-full rounded-lg border shadow-sm focus:outline-none"
                        :style="inputStyle"
                    >
                        <option value="">All Departments</option>
                        <option v-for="dept in departments" :key="dept" :value="dept">
                            {{ formatDepartment(dept) }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                    <select
                        id="status"
                        v-model="filterStatus"
                        @change="applyFilters"
                        class="w-full rounded-lg border shadow-sm focus:outline-none"
                        :style="inputStyle"
                    >
                        <option value="">All Statuses</option>
                        <option value="working">Working</option>
                        <option value="on_break">On Break</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Time Tracking Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Hours</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ timeStats.totalHoursToday }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <UsersIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Employees Present</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ timeStats.employeesPresent }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Late Arrivals</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ timeStats.lateArrivals }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.secondary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Overtime Hours</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ timeStats.overtimeHours }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Current Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold" :style="{ color: themeColors.success }">{{ currentStatus.clockedIn }}</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Clocked In</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold" :style="{ color: themeColors.warning }">{{ currentStatus.onBreak }}</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">On Break</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold" :style="{ color: themeColors.textSecondary }">{{ currentStatus.clockedOut }}</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Clocked Out</div>
                </div>
            </div>
        </div>

        <!-- Employee Time Records -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b flex items-center justify-between" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Time Records - {{ formatDate(selectedDate) }}</h3>
                <span class="text-sm" :style="{ color: themeColors.textSecondary }">{{ timeRecords.length }} record(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Clock In
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Clock Out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Hours Worked
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in timeRecords" :key="record.id" class="transition-colors" :style="hoveredRow === record.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = record.id" @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 border"
                                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ getInitials(record.employee_name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ record.employee_name }}</div>
                                        <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ record.employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatDepartment(record.department) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ record.clock_in || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ record.clock_out || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ record.hours_worked || '0.0' }}h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusPillStyle(record.status)">
                                    {{ formatStatus(record.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewTimesheet(record)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.primary }">View</button>
                                    <button @click="editTime(record)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.success }">Edit</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import {
    DocumentArrowDownIcon,
    ClockIcon,
    UsersIcon,
    ExclamationTriangleIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    timeStats: Object,
    currentStatus: Object,
    timeRecords: Array,
    selectedDate: String,
    departments: Array,
    filters: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

const hoveredRow = ref(null)

const { currentTheme } = useTheme()

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
}))

// Filter state
const selectedDate = ref(props.selectedDate || new Date().toISOString().split('T')[0])
const filterDepartment = ref(props.filters?.department || '')
const filterStatus = ref(props.filters?.status || '')

const getInitials = (name) => {
    if (!name) return '';
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDepartment = (department) => {
    if (!department) return 'General'
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getStatusPillStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'working') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'on_break') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'completed') return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (key === 'absent') return { backgroundColor: themeColors.value.danger, color: '#000' }
    if (key === 'late') return { backgroundColor: themeColors.value.warning, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const applyFilters = () => {
    const params = {
        date: selectedDate.value,
    }
    
    if (filterDepartment.value) {
        params.department = filterDepartment.value
    }
    
    if (filterStatus.value) {
        params.status = filterStatus.value
    }

    router.get(route('manager.staff.time-tracking'), params, {
        preserveState: true,
        preserveScroll: true,
    })
}

const exportTimesheet = () => {
    const params = {
        date: selectedDate.value,
    }
    
    if (filterDepartment.value) {
        params.department = filterDepartment.value
    }
    
    if (filterStatus.value) {
        params.status = filterStatus.value
    }

    window.location.href = route('manager.staff.time-tracking.export', params)
}

const viewTimesheet = (record) => {
    router.get(route('manager.staff.time-tracking.show', { timeEntry: record.id }))
}

const editTime = (record) => {
    router.get(route('manager.staff.time-tracking.show', { timeEntry: record.id }))
}
</script>
