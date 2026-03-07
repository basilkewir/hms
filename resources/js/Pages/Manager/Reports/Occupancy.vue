<template>
    <DashboardLayout title="Occupancy Reports" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Occupancy Reports</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Daily and monthly occupancy with recent reservations.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a :href="route('admin.reports.occupancy.export', { format: 'csv' })"
                       class="px-4 py-2 rounded-md border hover:opacity-80"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        Export CSV
                    </a>
                    <a :href="route('admin.reports.occupancy.export', { format: 'json' })"
                       class="px-4 py-2 rounded-md hover:opacity-90"
                       :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Rooms</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_rooms }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Occupied Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.occupied_today }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Available Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.available_today }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Occupancy Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.occupancy_today_pct }}%</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Occupancy This Month</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.occupancy_month_pct }}%</p>
            </div>
        </div>

        <!-- Body -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- By Room Type -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Occupancy by Room Type</h2>
                <div v-if="byType && byType.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Total</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Occupied</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Occupancy %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in byType" :key="r.type" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.type }}</td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ r.total }}</td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ r.occupied ?? 0 }}</td>
                                <td class="px-4 py-2 text-sm text-right font-medium"
                                    :style="{ color: (r.occupancy_pct ?? 0) > 50 ? themeColors.success : themeColors.textPrimary }">
                                    {{ r.occupancy_pct ?? 0 }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No room type data</p>
            </div>

            <!-- Recent Reservations -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Reservations</h2>
                <div v-if="recentReservations && recentReservations.length" class="space-y-3">
                    <div v-for="res in recentReservations" :key="res.id" class="flex items-center justify-between pb-3 border-b" :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ res.reservation_code }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">Room #{{ res.room_id }} • {{ formatDate(res.check_in_date) }} → {{ formatDate(res.check_out_date) }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full" :class="badgeClass(res.status)">{{ (res.status || 'booked').toUpperCase() }}</span>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent reservations</p>
            </div>
        </div>
    </DashboardLayout>
    
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    navigation: Object,
    stats: { type: Object, default: () => ({ total_rooms: 0, occupied_today: 0, available_today: 0, occupancy_today_pct: 0, occupancy_month_pct: 0 }) },
    byType: { type: Array, default: () => [] },
    recentReservations: { type: Array, default: () => [] },
})

const navigation = computed(() => {
    if (props.navigation) return props.navigation
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

const badgeClass = (status) => {
    const s = (status || 'booked').toLowerCase()
    if (s.includes('confirm')) return 'bg-green-100 text-green-800'
    if (s.includes('check')) return 'bg-blue-100 text-blue-800'
    if (s.includes('cancel')) return 'bg-red-100 text-red-800'
    return 'bg-gray-100 text-gray-800'
}

onMounted(() => {
    // could trigger any client-side initializations if needed
})
</script>
