<template>
    <DashboardLayout title="System Reports" :user="user">
        <!-- Header Section (match Reservations style) -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">System Reports</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Open live reports by category.</p>
                </div>
            </div>
        </div>

        <!-- Report Category Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Admin context: system-wide reports -->
            <template v-if="!isHrContext">
                <Link
                    :href="getRoute('reports.occupancy')"
                    class="rounded-lg shadow p-6 transition-shadow border"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.2)';"
                    @mouseleave="$event.target.style.boxShadow = ''">
                    <div class="flex items-center mb-4">
                        <HomeIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Occupancy Reports</h3>
                    </div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Room occupancy rates, availability trends, and booking patterns.
                    </p>
                </Link>

                <Link
                    :href="getRoute('reports.revenue')"
                    class="rounded-lg shadow p-6 transition-shadow border"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.2)';"
                    @mouseleave="$event.target.style.boxShadow = ''">
                    <div class="flex items-center mb-4">
                        <CurrencyDollarIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Revenue Reports</h3>
                    </div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Revenue summaries, service income, and trend analysis.
                    </p>
                </Link>

                <Link
                    :href="getRoute('reports.staff')"
                    class="rounded-lg shadow p-6 transition-shadow border"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.2)';"
                    @mouseleave="$event.target.style.boxShadow = ''">
                    <div class="flex items-center mb-4">
                        <UserGroupIcon class="h-8 w-8 mr-3" :style="{ color: '#8b5cf6' }" />
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Staff Reports</h3>
                    </div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Attendance, productivity, and staff performance insights.
                    </p>
                </Link>
            </template>

            <!-- HR context: HR-focused reports -->
            <template v-else>
                <Link
                    href="/hr/attendance"
                    class="rounded-lg shadow p-6 transition-shadow border"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.2)';"
                    @mouseleave="$event.target.style.boxShadow = ''">
                    <div class="flex items-center mb-4">
                        <UserGroupIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Attendance & Leave Reports</h3>
                    </div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Daily attendance, leave status, and on-leave summaries for staff.
                    </p>
                </Link>

                <Link
                    href="/hr/payroll/history"
                    class="rounded-lg shadow p-6 transition-shadow border"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.2)';"
                    @mouseleave="$event.target.style.boxShadow = ''">
                    <div class="flex items-center mb-4">
                        <CurrencyDollarIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Payroll Reports</h3>
                    </div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Processed payroll history, approvals, and tax information.
                    </p>
                </Link>

                <Link
                    href="/hr/performance"
                    class="rounded-lg shadow p-6 transition-shadow border"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.2)';"
                    @mouseleave="$event.target.style.boxShadow = ''">
                    <div class="flex items-center mb-4">
                        <HomeIcon class="h-8 w-8 mr-3" :style="{ color: '#8b5cf6' }" />
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Performance Reports</h3>
                    </div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        HR-focused performance metrics, productivity, and scores by employee.
                    </p>
                </Link>
            </template>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    HomeIcon,
    CurrencyDollarIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline'

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
}))

const isHrContext = computed(() => window.location.pathname.startsWith('/hr'))

loadTheme()

defineProps({
    user: Object,
})

const getRoute = (name) => {
    const prefix = window.location.pathname.startsWith('/hr') ? 'hr.' : 'admin.'
    return route(prefix + name)
}
</script>
