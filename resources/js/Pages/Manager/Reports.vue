<template>
    <DashboardLayout title="Manager Reports" :user="user">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Manager Reports</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Open operational reports by category.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Link :href="route('manager.reports.occupancy')" class="rounded-lg shadow p-6 border hover:opacity-95 transition-opacity"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center mb-4">
                    <HomeIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Occupancy</h3>
                </div>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Room occupancy rates and booking trends.</p>
            </Link>

            <Link :href="route('manager.reports.revenue')" class="rounded-lg shadow p-6 border hover:opacity-95 transition-opacity"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center mb-4">
                    <CurrencyDollarIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Revenue</h3>
                </div>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Revenue performance and department breakdown.</p>
            </Link>

            <Link :href="route('manager.reports.satisfaction')" class="rounded-lg shadow p-6 border hover:opacity-95 transition-opacity"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center mb-4">
                    <ChartBarIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.warning }" />
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Satisfaction</h3>
                </div>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Guest satisfaction and service quality insights.</p>
            </Link>

            <Link :href="route('manager.reports.staff')" class="rounded-lg shadow p-6 border hover:opacity-95 transition-opacity"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center mb-4">
                    <UserGroupIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.secondary }" />
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Staff</h3>
                </div>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Attendance, performance, and staffing reports.</p>
            </Link>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'
import {
    ChartBarIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    HomeIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    user: Object,
})

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
</script>
