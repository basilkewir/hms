<template>
    <DashboardLayout title="Reports" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Reports</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">View analytics and export operational reports.</p>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Customers</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_customers }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Sales</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_sales }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Revenue Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.revenue_today) }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Revenue This Month</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.revenue_month) }}</p>
            </div>
        </div>

        <!-- Recent Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Sales</h2>
                <div v-if="recentSales && recentSales.length" class="space-y-3">
                    <div v-for="s in recentSales" :key="s.id" class="flex items-center justify-between pb-3 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ s.sale_number }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ formatDate(s.sale_date) }}</p>
                        </div>
                        <p class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(s.total_amount) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent sales</p>
            </div>

            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">New Customers</h2>
                <div v-if="recentCustomers && recentCustomers.length" class="space-y-3">
                    <div v-for="c in recentCustomers" :key="c.id" class="flex items-center justify-between pb-3 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ c.first_name }} {{ c.last_name }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ c.customer_code }}</p>
                        </div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ formatDate(c.created_at) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No new customers</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { formatCurrency as moneyFormat, initializeCurrencySettings } from '@/Utils/currency.js'
import { onMounted } from 'vue'

const props = defineProps({
    user: Object,
    stats: { type: Object, default: () => ({ total_customers: 0, total_sales: 0, revenue_today: 0, revenue_month: 0 }) },
    recentSales: { type: Array, default: () => [] },
    recentCustomers: { type: Array, default: () => [] },
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

// Currency/date helpers
onMounted(() => {
    initializeCurrencySettings()
})
const formatCurrency = (v) => moneyFormat(v || 0)
const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}
</script>
