<template>
    <DashboardLayout title="Time Tracking" :user="user">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Time Tracking</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Monitor employee attendance and working hours</p>
                </div>
                <button @click="exportTimesheet"
                        :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                        class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                    <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                    Export
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <ClockIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Hours Today</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ (timeStats && timeStats.totalHoursToday) || '0.0h' }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <UsersIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Employees Present</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ (timeStats && timeStats.employeesPresent) || 0 }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <ExclamationTriangleIcon :style="{ color: themeColors.warning }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Late Arrivals</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ (timeStats && timeStats.lateArrivals) || 0 }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <ChartBarIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Overtime Hours</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ (timeStats && timeStats.overtimeHours) || '0.0h' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Records Table -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div :style="{ borderColor: themeColors.border }" class="px-6 py-4 border-b">
                <h3 :style="{ color: themeColors.textPrimary }" class="text-lg font-medium">Today's Time Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Department</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Clock In</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Clock Out</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Hours Worked</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in timeRecords" :key="record.id"
                            :style="hoveredRow === record.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = record.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div :style="{ backgroundColor: themeColors.primary, color: '#000' }" 
                                         class="w-10 h-10 rounded-full flex items-center justify-center mr-4">
                                        <span class="text-sm font-medium">{{ getInitials(record.employee_name) }}</span>
                                    </div>
                                    <div>
                                        <div :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ record.employee_name }}</div>
                                        <div :style="{ color: themeColors.textTertiary }" class="text-sm">{{ record.employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ formatDepartment(record.department) }}
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ record.clock_in || '-' }}
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ record.clock_out || '-' }}
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ record.hours_worked || '0.0' }}h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(record.status)">
                                    {{ formatStatus(record.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewTimesheet(record)" 
                                            :style="{ color: themeColors.primary }" 
                                            class="hover:opacity-80">View</button>
                                    <button @click="editTime(record)" 
                                            :style="{ color: themeColors.success }" 
                                            class="hover:opacity-80">Edit</button>
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

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDepartment = (department) => {
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusColor = (status) => {
    const colors = {
        working: 'bg-green-100 text-green-800',
        on_break: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-blue-100 text-blue-800',
        absent: 'bg-red-100 text-red-800',
        late: 'bg-orange-100 text-orange-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const exportTimesheet = () => {
    window.location.href = route('admin.time-tracking.export')
}

const viewTimesheet = (record) => {
    router.get(route('admin.staff.time-tracking.show', record.id))
}

const editTime = (record) => {
    router.get(route('admin.staff.time-tracking.show', record.id), { edit: 1 })
}
</script>
