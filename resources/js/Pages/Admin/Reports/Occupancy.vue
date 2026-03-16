<template>
    <DashboardLayout title="Occupancy Reports" :user="user" :navigation="navigation">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">Occupancy Reports</h1>
                    <p class="mt-1" :style="{ color: themeColors.textSecondary }">Comprehensive hotel occupancy analytics — room utilization, trends, and reservation activity.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a :href="route('admin.reports.occupancy.export', { format: 'csv' })"
                       class="px-4 py-2 rounded-md border hover:opacity-80 text-sm"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        ⬇ Export CSV
                    </a>
                    <a :href="route('admin.reports.occupancy.export', { format: 'json' })"
                       class="px-4 py-2 rounded-md hover:opacity-90 text-sm font-medium"
                       :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        ⬇ Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Primary KPI Cards - Row 1 -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Total Rooms</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.textPrimary }">{{ stats.total_rooms }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Inventory</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Occupied</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.danger }">{{ stats.occupied_today }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Right now</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Available</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.success }">{{ stats.available_today }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Ready to book</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Cleaning</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.warning }">{{ stats.cleaning_rooms ?? 0 }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Housekeeping</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Today %</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.primary }">{{ stats.occupancy_today_pct }}%</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Occupancy rate</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Month %</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.primary }">{{ stats.occupancy_month_pct }}%</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">This month</p>
            </div>
        </div>

        <!-- Secondary KPI Cards - Row 2 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">🛬 Arrivals Today</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.success }">{{ stats.arrivals_today ?? 0 }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Expected check-ins</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">🛫 Departures Today</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.warning }">{{ stats.departures_today ?? 0 }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Expected check-outs</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">💵 Avg Daily Rate</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.adr ?? 0) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">ADR this month</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">🌙 Avg Stay Length</p>
                <p class="text-2xl font-bold mt-2" :style="{ color: themeColors.textPrimary }">{{ (stats.avg_nights ?? 0).toFixed(1) }}<span class="text-sm font-normal ml-1" :style="{ color: themeColors.textSecondary }">nights</span></p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Average LOS</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Occupancy by Room Type with Progress Bars -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Occupancy by Room Type</h2>
                <div v-if="byType && byType.length" class="space-y-4">
                    <div v-for="r in byType" :key="r.type">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ r.type }}</span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs" :style="{ color: themeColors.textSecondary }">{{ r.occupied ?? 0 }}/{{ r.total }}</span>
                                <span class="text-sm font-semibold" :style="{ color: (r.occupancy_pct ?? 0) >= 80 ? themeColors.danger : (r.occupancy_pct ?? 0) >= 50 ? themeColors.warning : themeColors.success }">
                                    {{ r.occupancy_pct ?? 0 }}%
                                </span>
                            </div>
                        </div>
                        <div class="w-full rounded-full h-2" :style="{ backgroundColor: themeColors.border }">
                            <div class="h-2 rounded-full transition-all"
                                 :style="{ width: Math.min(r.occupancy_pct ?? 0, 100) + '%', backgroundColor: (r.occupancy_pct ?? 0) >= 80 ? themeColors.danger : (r.occupancy_pct ?? 0) >= 50 ? themeColors.warning : themeColors.success }">
                            </div>
                        </div>
                    </div>
                    <!-- Summary table -->
                    <div class="mt-4 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr>
                                    <th class="py-1 text-left font-medium" :style="{ color: themeColors.textSecondary }">Type</th>
                                    <th class="py-1 text-right font-medium" :style="{ color: themeColors.textSecondary }">Total</th>
                                    <th class="py-1 text-right font-medium" :style="{ color: themeColors.textSecondary }">Occupied</th>
                                    <th class="py-1 text-right font-medium" :style="{ color: themeColors.textSecondary }">Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in byType" :key="'tbl-' + r.type" class="border-t" :style="{ borderColor: themeColors.border }">
                                    <td class="py-1.5" :style="{ color: themeColors.textPrimary }">{{ r.type }}</td>
                                    <td class="py-1.5 text-right" :style="{ color: themeColors.textPrimary }">{{ r.total }}</td>
                                    <td class="py-1.5 text-right font-medium" :style="{ color: themeColors.danger }">{{ r.occupied ?? 0 }}</td>
                                    <td class="py-1.5 text-right font-medium" :style="{ color: themeColors.success }">{{ (r.total - (r.occupied ?? 0)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No room type data available</p>
            </div>

            <!-- Monthly Trend -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Monthly Occupancy Trend</h2>
                <div v-if="monthlyTrend && monthlyTrend.length" class="space-y-3">
                    <div v-for="m in monthlyTrend" :key="m.month">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium w-24" :style="{ color: themeColors.textPrimary }">{{ m.month }}</span>
                            <span class="text-xs mr-2" :style="{ color: themeColors.textSecondary }">{{ m.booked }} reservations</span>
                            <span class="text-sm font-semibold w-12 text-right" :style="{ color: themeColors.primary }">{{ m.pct }}%</span>
                        </div>
                        <div class="w-full rounded-full h-2" :style="{ backgroundColor: themeColors.border }">
                            <div class="h-2 rounded-full" :style="{ width: Math.min(m.pct, 100) + '%', backgroundColor: themeColors.primary, minWidth: m.pct > 0 ? '4px' : '0' }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No trend data available</p>
            </div>
        </div>

        <!-- Reservation Status + Recent Reservations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Status Breakdown -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Reservation Status Breakdown</h2>
                <div v-if="statusBreakdown && statusBreakdown.length" class="space-y-3">
                    <div v-for="s in statusBreakdown" :key="s.status" class="flex items-center justify-between py-2 border-b last:border-b-0" :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center gap-3">
                            <span class="text-xs px-2 py-1 rounded-full font-medium" :class="badgeClass(s.status)">{{ s.status.replace(/_/g, ' ').toUpperCase() }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-24 rounded-full h-1.5" :style="{ backgroundColor: themeColors.border }">
                                <div class="h-1.5 rounded-full" :style="{ width: (totalReservations > 0 ? Math.min((s.count / totalReservations) * 100, 100) : 0) + '%', backgroundColor: statusColor(s.status) }"></div>
                            </div>
                            <span class="text-sm font-semibold w-8 text-right" :style="{ color: themeColors.textPrimary }">{{ s.count }}</span>
                            <span class="text-xs w-10 text-right" :style="{ color: themeColors.textSecondary }">{{ totalReservations > 0 ? ((s.count/totalReservations)*100).toFixed(0) : 0 }}%</span>
                        </div>
                    </div>
                    <div class="pt-2 flex items-center justify-between text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                        <span>Total Reservations</span>
                        <span>{{ totalReservations }}</span>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No reservation data</p>
            </div>

            <!-- Recent Reservations -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Reservations</h2>
                <div v-if="recentReservations && recentReservations.length" class="space-y-3">
                    <div v-for="res in recentReservations" :key="res.id" class="pb-3 border-b last:border-b-0" :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ res.reservation_number }}</p>
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="badgeClass(res.status)">{{ (res.status || 'booked').replace(/_/g,' ').toUpperCase() }}</span>
                                </div>
                                <p class="text-xs mt-0.5" :style="{ color: themeColors.textSecondary }">{{ res.guest_name }} · Room {{ res.room_number }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textTertiary }">{{ formatDate(res.check_in_date) }} → {{ formatDate(res.check_out_date) }} ({{ res.nights ?? 0 }} nights)</p>
                            </div>
                            <div class="text-right ml-3">
                                <p class="text-sm font-semibold" :style="{ color: themeColors.success }">{{ formatCurrency(res.total_amount ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent reservations</p>
            </div>
        </div>

        <!-- RevPAR & Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">RevPAR</p>
                <p class="text-xl font-bold mt-2" :style="{ color: themeColors.primary }">{{ formatCurrency(stats.revpar ?? 0) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Revenue per available room</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Monthly Revenue</p>
                <p class="text-xl font-bold mt-2" :style="{ color: themeColors.success }">{{ formatCurrency(stats.monthly_revenue ?? 0) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">From reservations this month</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Rooms Dirty</p>
                <p class="text-xl font-bold mt-2" :style="{ color: themeColors.warning }">{{ stats.dirty_rooms ?? 0 }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Need housekeeping</p>
            </div>
            <div class="rounded-lg shadow p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Rooms Clean</p>
                <p class="text-xl font-bold mt-2" :style="{ color: themeColors.success }">{{ stats.clean_rooms ?? 0 }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Ready for guests</p>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useTheme } from '@/Composables/useTheme'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    navigation: Object,
    stats: {
        type: Object,
        default: () => ({
            total_rooms: 0, occupied_today: 0, available_today: 0, cleaning_rooms: 0,
            occupancy_today_pct: 0, occupancy_month_pct: 0, arrivals_today: 0, departures_today: 0,
            adr: 0, revpar: 0, avg_nights: 0, monthly_revenue: 0, dirty_rooms: 0, clean_rooms: 0,
        })
    },
    byType: { type: Array, default: () => [] },
    monthlyTrend: { type: Array, default: () => [] },
    statusBreakdown: { type: Array, default: () => [] },
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

const totalReservations = computed(() => (props.statusBreakdown || []).reduce((sum, s) => sum + s.count, 0))

const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val ?? 0)
}

const badgeClass = (status) => {
    const s = (status || '').toLowerCase()
    if (s.includes('checked_in') || s === 'checked_in') return 'bg-blue-100 text-blue-800'
    if (s.includes('checked_out') || s === 'checked_out') return 'bg-gray-100 text-gray-700'
    if (s.includes('confirm')) return 'bg-green-100 text-green-800'
    if (s.includes('cancel')) return 'bg-red-100 text-red-800'
    if (s.includes('pending')) return 'bg-yellow-100 text-yellow-800'
    return 'bg-gray-100 text-gray-800'
}

const statusColor = (status) => {
    const tc = themeColors.value
    const s = (status || '').toLowerCase()
    if (s.includes('checked_in')) return tc.primary
    if (s.includes('checked_out')) return tc.textSecondary
    if (s.includes('confirm')) return tc.success
    if (s.includes('cancel')) return tc.danger
    return tc.warning
}
</script>
