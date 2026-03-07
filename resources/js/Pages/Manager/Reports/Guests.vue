<template>
    <DashboardLayout title="Guests Report" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Guests Report</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Overview of guest totals, new signups, and groups.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a :href="route('admin.reports.guests.export', { format: 'csv' })"
                       class="px-4 py-2 rounded-md border hover:opacity-80"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        Export CSV
                    </a>
                    <a :href="route('admin.reports.guests.export', { format: 'json' })"
                       class="px-4 py-2 rounded-md hover:opacity-90"
                       :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Guests</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_guests }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">New Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.new_today }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">New This Month</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.new_month }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- By Group -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Guests by Group</h2>
                <div v-if="byGroup && byGroup.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Group</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="g in byGroup" :key="g.id" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ g.name }}</td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ g.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No group data</p>
            </div>

            <!-- Recent Guests -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Guests</h2>
                <div v-if="recentGuests && recentGuests.length" class="space-y-3">
                    <div v-for="c in recentGuests" :key="c.id" class="flex items-center justify-between pb-3 border-b" :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ c.first_name }} {{ c.last_name }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ c.customer_code }} • {{ c.email || c.phone }}</p>
                        </div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ formatDate(c.created_at) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent guests</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    stats: { type: Object, default: () => ({ total_guests: 0, new_today: 0, new_month: 0 }) },
    byGroup: { type: Array, default: () => [] },
    recentGuests: { type: Array, default: () => [] },
})

const navigation = computed(() => {
    const role = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(role)
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.02)'
}))

const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}
</script>
