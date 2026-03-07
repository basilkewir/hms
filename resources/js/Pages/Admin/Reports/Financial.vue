<template>
    <DashboardLayout title="Financial Report" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Financial Report</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">High-level KPIs and latest financial activity.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a :href="route('admin.reports.financial.export', { format: 'csv' })"
                       class="px-4 py-2 rounded-md border hover:opacity-80"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        Export CSV
                    </a>
                    <a :href="route('admin.reports.financial.export', { format: 'json' })"
                       class="px-4 py-2 rounded-md hover:opacity-90"
                       :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.success }">{{ formatCurrency(kpis.total_revenue) }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Hall Bookings</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.primary }">{{ formatCurrency(kpis.hall_booking_revenue ?? 0) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">confirmed &amp; completed</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Expenses</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.danger }">{{ formatCurrency(kpis.total_expenses) }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Net Profit</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: kpis.net_profit >= 0 ? themeColors.success : themeColors.danger }">{{ formatCurrency(kpis.net_profit) }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Net Margin</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.primary }">{{ kpis.net_margin }}%</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Transactions</h2>
            <div v-if="recentTransactions && recentTransactions.length" class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reference</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in recentTransactions" :key="`${t.type}-${t.id}`" class="border-t" :style="{ borderColor: themeColors.border }">
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatType(t.type) }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ t.ref }}</td>
                            <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(t.amount) }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(t.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent transactions</p>
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
    kpis: { type: Object, default: () => ({ total_revenue: 0, total_expenses: 0, net_profit: 0, net_margin: 0 }) },
    recentTransactions: { type: Array, default: () => [] },
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

const formatCurrency = (v) => {
    const num = Number(v || 0)
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(num)
}
const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}
const formatType = (t) => {
    const s = (t || '').toString().toLowerCase()
    if (s === 'sale') return 'Sale'
    if (s === 'expense') return 'Expense'
    if (s === 'hall_booking') return 'Hall Booking'
    return s || 'N/A'
}
</script>
