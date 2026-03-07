<template>
    <DashboardLayout title="Staff Management" :user="user" :navigation="navigation">
        <!-- Staff Management Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Staff Management</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Manage hotel staff, schedules, and performance.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.users.create')" 
                          class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        <UserPlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Staff
                    </Link>
                    <Link :href="route('manager.staff.schedules')" 
                          class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.success, color: '#000' }">
                        <CalendarIcon class="h-4 w-4 mr-2 inline" />
                        Manage Schedules
                    </Link>
                </div>
            </div>
        </div>

        <!-- Staff Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <UserGroupIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Staff</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ staffStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">On Duty</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ staffStats.onDuty }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">On Break</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ staffStats.onBreak }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.danger }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Absent</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ staffStats.absent }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Filters and Search -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                    <div>
                        <input type="text" v-model="searchQuery" placeholder="Search staff..."
                               @input="applyFilters"
                               class="border rounded-md px-3 py-2 focus:outline-none"
                               :style="inputStyle">
                    </div>
                    <div>
                        <select v-model="selectedDepartment"
                                @change="applyFilters"
                                class="border rounded-md px-3 py-2 focus:outline-none"
                                :style="inputStyle">
                            <option value="">All Departments</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.name">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <select v-model="selectedStatus"
                                @change="applyFilters"
                                class="border rounded-md px-3 py-2 focus:outline-none"
                                :style="inputStyle">
                            <option value="">All Status</option>
                            <option value="on_duty">On Duty</option>
                            <option value="off_duty">Off Duty</option>
                            <option value="on_break">On Break</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button @click="clearFilters" class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Staff List -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Staff Members</h3>
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
                                Position
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Roles
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="staff in displayStaff" :key="staff.id" class="transition-colors" :style="hoveredRow === staff.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = staff.id" @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 border"
                                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ getInitials(staff.first_name, staff.last_name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ staff.full_name || `${staff.first_name} ${staff.last_name}` }}
                                        </div>
                                        <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ staff.email }}</div>
                                        <div v-if="staff.employee_id" class="text-xs" :style="{ color: themeColors.textTertiary }">
                                            ID: {{ staff.employee_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="staff.department" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getDepartmentPillStyle(staff.department)">
                                    {{ staff.department }}
                                </span>
                                <span v-else class="text-sm" :style="{ color: themeColors.textTertiary }">N/A</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ staff.position || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusPillStyle(staff.current_status)">
                                    {{ formatStatus(staff.current_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="role in staff.roles" :key="role.id"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                          :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, border: `1px solid ${themeColors.border}` }">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.users.show', staff.id)" 
                                          class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.primary }">View</Link>
                                    <Link :href="route('admin.users.edit', staff.id)" 
                                          class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.success }">Edit</Link>
                                    <Link :href="route('manager.staff.schedules')" 
                                          class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.warning }">Schedule</Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="staffMembers.links" class="px-6 py-4 border-t" :style="{ borderColor: themeColors.border }">
                <Pagination :links="staffMembers.links" />
            </div>
            <div v-if="displayStaff.length === 0" class="px-6 py-8 text-center">
                <p :style="{ color: themeColors.textSecondary }">No staff members found</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import {
    UserGroupIcon,
    UserPlusIcon,
    CalendarIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    staffMembers: {
        type: [Object, Array],
        default: () => []
    },
    staffStats: {
        type: Object,
        default: () => ({
            total: 0,
            onDuty: 0,
            onBreak: 0,
            absent: 0,
        })
    },
    departments: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
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

const searchQuery = ref(props.filters?.search || '')
const selectedDepartment = ref(props.filters?.department || '')
const selectedStatus = ref(props.filters?.status || '')

const displayStaff = computed(() => {
    const staff = Array.isArray(props.staffMembers) 
        ? props.staffMembers 
        : (props.staffMembers.data || [])
    return staff
})

const getInitials = (firstName, lastName) => {
    const first = firstName?.charAt(0)?.toUpperCase() || ''
    const last = lastName?.charAt(0)?.toUpperCase() || ''
    return first + last
}

const getDepartmentPillStyle = (department) => {
    const dept = (department || '').toLowerCase()
    if (dept.includes('front')) return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (dept.includes('housekeeping')) return { backgroundColor: themeColors.value.success, color: '#000' }
    if (dept.includes('maintenance')) return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (dept.includes('account')) return { backgroundColor: themeColors.value.secondary, color: '#000' }
    if (dept.includes('security')) return { backgroundColor: themeColors.value.danger, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const getStatusPillStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'on_duty') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'on_break') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'absent') return { backgroundColor: themeColors.value.danger, color: '#000' }
    if (key === 'off_duty') return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const applyFilters = () => {
    router.get('/manager/staff', {
        search: searchQuery.value || null,
        department: selectedDepartment.value || null,
        status: selectedStatus.value || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedDepartment.value = ''
    selectedStatus.value = ''
    router.get('/manager/staff')
}
</script>
