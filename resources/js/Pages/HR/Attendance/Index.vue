<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

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
    hover: `var(--kotel-primary-hover)`
}))

const attendances = computed(() => page.props.attendances)
const filters = computed(() => page.props.filters)
const employees = computed(() => page.props.employees)

const searchParams = ref({
    date_from: filters.value.date_from || '',
    date_to: filters.value.date_to || '',
    employee_id: filters.value.employee_id || ''
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatTime = (time) => {
    if (!time) return '-'
    return new Date(`2000-01-01 ${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const calculateHours = (checkIn, checkOut) => {
    if (!checkIn || !checkOut) return '-'
    
    const checkInTime = new Date(`2000-01-01 ${checkIn}`)
    const checkOutTime = new Date(`2000-01-01 ${checkOut}`)
    
    const diffMs = checkOutTime - checkInTime
    const diffHours = diffMs / (1000 * 60 * 60)
    
    return diffHours.toFixed(2) + ' hrs'
}

const getStatusBadge = (status) => {
    const statusConfig = {
        present: { text: 'Present', color: themeColors.value.success },
        absent: { text: 'Absent', color: themeColors.value.danger },
        late: { text: 'Late', color: themeColors.value.warning },
        half_day: { text: 'Half Day', color: themeColors.value.secondary }
    }
    
    const config = statusConfig[status] || { text: status, color: themeColors.value.textTertiary }
    
    return {
        text: config.text,
        style: {
            backgroundColor: config.color + '20',
            color: config.color
        }
    }
}

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const checkIn = () => {
    router.post(route('hr.attendance.check-in'), {}, {
        onSuccess: () => {
            // Refresh the page to show updated attendance
            router.reload()
        }
    })
}

const checkOut = () => {
    router.post(route('hr.attendance.check-out'), {}, {
        onSuccess: () => {
            // Refresh the page to show updated attendance
            router.reload()
        }
    })
}

// Watch for changes and update URL
watch(searchParams, debounce((newParams) => {
    router.get(route('hr.attendance.index'), newParams, {
        preserveState: true,
        replace: true
    })
}, 300), { deep: true })

const resetFilters = () => {
    searchParams.value = {
        date_from: '',
        date_to: '',
        employee_id: ''
    }
}

const getTodayAttendance = () => {
    const today = new Date().toISOString().split('T')[0]
    return attendances.value.data.find(a => a.date === today)
}

const canCheckIn = computed(() => {
    const todayAttendance = getTodayAttendance()
    return !todayAttendance || !todayAttendance.check_in_time
})

const canCheckOut = computed(() => {
    const todayAttendance = getTodayAttendance()
    return todayAttendance && todayAttendance.check_in_time && !todayAttendance.check_out_time
})
</script>

<template>
    <DashboardLayout title="Attendance" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Attendance Management</h1>
                    <p :style="{ color: themeColors.textSecondary }">Track employee attendance and working hours</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button 
                        v-if="canCheckIn"
                        @click="checkIn"
                        class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                        :style="{ backgroundColor: themeColors.success }">
                        Check In
                    </button>
                    <button 
                        v-if="canCheckOut"
                        @click="checkOut"
                        class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                        :style="{ backgroundColor: themeColors.warning }">
                        Check Out
                    </button>
                </div>
            </div>
        </div>

        <!-- Today's Status Card -->
        <div class="rounded-lg p-6 mb-6 border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Today's Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Check In</p>
                    <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">
                        {{ getTodayAttendance()?.check_in_time ? formatTime(getTodayAttendance().check_in_time) : 'Not checked in' }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Check Out</p>
                    <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">
                        {{ getTodayAttendance()?.check_out_time ? formatTime(getTodayAttendance().check_out_time) : 'Not checked out' }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Hours Worked</p>
                    <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">
                        {{ calculateHours(getTodayAttendance()?.check_in_time, getTodayAttendance()?.check_out_time) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg p-6 mb-6 border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Date From</label>
                    <DatePicker v-model="searchParams.date_from" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Date To</label>
                    <DatePicker v-model="searchParams.date_to" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Employee</label>
                    <select 
                        v-model="searchParams.employee_id"
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                        <option value="">All Employees</option>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                            {{ employee.name }}
                        </option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button 
                        @click="resetFilters"
                        class="w-full px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            color: themeColors.textSecondary,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Employee</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Date</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Check In</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Check Out</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Hours</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attendance in attendances.data" :key="attendance.id"
                            class="border-t transition-colors hover:opacity-80"
                            :style="{ 
                                borderColor: themeColors.border,
                                borderStyle: 'solid',
                                borderWidth: '1px'
                            }">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm"
                                         :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                                        {{ getInitials(attendance.user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ attendance.user.name }}</p>
                                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                                            {{ attendance.user.employee_id || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(attendance.date) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatTime(attendance.check_in_time) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatTime(attendance.check_out_time) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ calculateHours(attendance.check_in_time, attendance.check_out_time) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :style="getStatusBadge(attendance.status).style">
                                    {{ getStatusBadge(attendance.status).text }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-sm" :style="{ color: themeColors.textSecondary }">
                                    {{ attendance.notes || '-' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="attendances.links" class="p-4 border-t" :style="{ borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Showing {{ attendances.from || 0 }} to {{ attendances.to || 0 }} of {{ attendances.total }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link v-if="attendances.prev_page_url" :href="attendances.prev_page_url"
                              class="px-3 py-1 rounded border text-sm transition-colors"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderStyle: 'solid',
                                  borderWidth: '1px'
                              }">
                            Previous
                        </Link>
                        <Link v-if="attendances.next_page_url" :href="attendances.next_page_url"
                              class="px-3 py-1 rounded border text-sm transition-colors"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderStyle: 'solid',
                                  borderWidth: '1px'
                              }">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="attendances.data.length === 0" class="text-center py-12">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                 :style="{ backgroundColor: themeColors.background, color: themeColors.textTertiary }">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No attendance records found</h3>
            <p :style="{ color: themeColors.textSecondary }">No attendance records match your filters.</p>
        </div>
    </DashboardLayout>
</template>
