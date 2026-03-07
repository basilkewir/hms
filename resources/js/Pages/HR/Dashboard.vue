<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/Utils/currency.js'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

const props = defineProps({
    user: Object,
    navigation: [Array, Object],
    stats: Object,
    recent_employees: Array,
    upcoming_birthdays: Array,
})

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
    hover: `var(--kotel-primary-hover)`
}))

loadTheme()

const stats = computed(() => props.stats || {
    total_employees: 0,
    active_employees: 0,
    pending_leave_requests: 0,
    open_positions: 0,
})

const recentEmployees = computed(() => props.recent_employees || [])
const upcomingBirthdays = computed(() => props.upcoming_birthdays || [])

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}
</script>

<template>
    <DashboardLayout title="HR Dashboard" :user="props.user" :navigation="props.navigation">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">HR Dashboard</h1>
            <p :style="{ color: themeColors.textSecondary }">Manage employees, attendance, payroll and more</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Employees</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_employees }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Active Employees</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.active_employees }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.success + '20', color: themeColors.success }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Pending Leave Requests</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.pending_leave_requests }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.warning + '20', color: themeColors.warning }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Open Positions</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.open_positions }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.danger + '20', color: themeColors.danger }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Employees -->
            <div class="rounded-lg border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="p-6 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Recent Employees</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="employee in recentEmployees" :key="employee.id" 
                             class="flex items-center justify-between p-3 rounded-lg"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm"
                                     :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                                    {{ getInitials(employee.name) }}
                                </div>
                                <div>
                                    <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</p>
                                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                                        {{ employee.roles?.[0]?.name || 'No role' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm" :style="{ color: themeColors.textTertiary }">Joined</p>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(employee.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Link :href="route('hr.employees.index')" 
                              class="text-sm font-medium hover:opacity-80 transition-opacity"
                              :style="{ color: themeColors.primary }">
                            View all employees →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Upcoming Birthdays -->
            <div class="rounded-lg border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="p-6 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Upcoming Birthdays</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="employee in upcomingBirthdays" :key="employee.id" 
                             class="flex items-center justify-between p-3 rounded-lg"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm"
                                     :style="{ backgroundColor: themeColors.success, color: 'white' }">
                                    🎂
                                </div>
                                <div>
                                    <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</p>
                                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                                        {{ employee.roles?.[0]?.name || 'No role' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm" :style="{ color: themeColors.textTertiary }">Birthday</p>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(employee.date_of_birth) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-if="upcomingBirthdays.length === 0" class="text-center py-8">
                        <p :style="{ color: themeColors.textSecondary }">No upcoming birthdays this month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link :href="route('hr.employees.create')"
                      class="p-4 rounded-lg border text-center hover:opacity-80 transition-opacity"
                      :style="{
                          backgroundColor: themeColors.card,
                          borderColor: themeColors.border,
                          borderStyle: 'solid',
                          borderWidth: '1px',
                          color: themeColors.textPrimary
                      }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
                         :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="font-medium">Add Employee</p>
                </Link>

                <Link :href="route('hr.attendance.index')"
                      class="p-4 rounded-lg border text-center hover:opacity-80 transition-opacity"
                      :style="{
                          backgroundColor: themeColors.card,
                          borderColor: themeColors.border,
                          borderStyle: 'solid',
                          borderWidth: '1px',
                          color: themeColors.textPrimary
                      }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
                         :style="{ backgroundColor: themeColors.success + '20', color: themeColors.success }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="font-medium">Attendance</p>
                </Link>

                <Link :href="route('hr.payroll.index')"
                      class="p-4 rounded-lg border text-center hover:opacity-80 transition-opacity"
                      :style="{
                          backgroundColor: themeColors.card,
                          borderColor: themeColors.border,
                          borderStyle: 'solid',
                          borderWidth: '1px',
                          color: themeColors.textPrimary
                      }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
                         :style="{ backgroundColor: themeColors.warning + '20', color: themeColors.warning }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="font-medium">Payroll</p>
                </Link>

                <Link :href="route('hr.reports.index')"
                      class="p-4 rounded-lg border text-center hover:opacity-80 transition-opacity"
                      :style="{
                          backgroundColor: themeColors.card,
                          borderColor: themeColors.border,
                          borderStyle: 'solid',
                          borderWidth: '1px',
                          color: themeColors.textPrimary
                      }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
                         :style="{ backgroundColor: themeColors.danger + '20', color: themeColors.danger }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="font-medium">Reports</p>
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>
