<template>
    <DashboardLayout title="POS Reports" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">POS Reports</h1>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Sales performance, revenue breakdown, and product analytics.</p>
                    </div>
                    <button @click="exportCSV"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2"
                            style="background-color: #8b5cf6;"
                            @mouseenter="$event.target.style.backgroundColor='#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor='#8b5cf6'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Today's Revenue</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.success }">{{ formatCurrency(kpis.today_revenue) }}</p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">{{ kpis.today_sales }} sales today</p>
                </div>
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Month Revenue</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(kpis.month_revenue) }}</p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">{{ kpis.month_sales }} sales this month</p>
                </div>
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(kpis.total_revenue) }}</p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">{{ kpis.total_sales }} total sales</p>
                </div>
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Avg Order Value</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.warning }">{{ formatCurrency(kpis.avg_order) }}</p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">per transaction</p>
                </div>
            </div>

            <!-- Revenue by Payment Method + Monthly Trend -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Payment Method Breakdown -->
                <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card }">
                    <h2 class="text-base font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Revenue by Payment Method</h2>
                    <div v-if="byMethod.length === 0" class="text-sm text-center py-6" :style="{ color: themeColors.textTertiary }">No data</div>
                    <div v-else class="space-y-3">
                        <div v-for="m in byMethod" :key="m.method" class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0"
                                 :style="{ backgroundColor: methodColor(m.method) + '22', color: methodColor(m.method) }">
                                {{ methodIcon(m.method) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium capitalize" :style="{ color: themeColors.textPrimary }">{{ formatMethod(m.method) }}</span>
                                    <span class="text-sm font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(m.revenue) }}</span>
                                </div>
                                <div class="w-full rounded-full h-1.5" :style="{ backgroundColor: themeColors.border }">
                                    <div class="h-1.5 rounded-full transition-all"
                                         :style="{ width: methodPct(m.revenue) + '%', backgroundColor: methodColor(m.method) }"></div>
                                </div>
                                <p class="text-xs mt-0.5" :style="{ color: themeColors.textTertiary }">{{ m.count }} transactions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Revenue Trend -->
                <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card }">
                    <h2 class="text-base font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Monthly Revenue (Last 12 Months)</h2>
                    <div v-if="monthly.length === 0" class="text-sm text-center py-6" :style="{ color: themeColors.textTertiary }">No data</div>
                    <div v-else class="space-y-2">
                        <div v-for="m in monthly" :key="m.month" class="flex items-center gap-3">
                            <span class="text-xs w-16 flex-shrink-0" :style="{ color: themeColors.textSecondary }">{{ m.month }}</span>
                            <div class="flex-1 rounded-full h-2" :style="{ backgroundColor: themeColors.border }">
                                <div class="h-2 rounded-full"
                                     :style="{ width: monthlyPct(m.revenue) + '%', backgroundColor: themeColors.primary }"></div>
                            </div>
                            <span class="text-xs w-20 text-right font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(m.revenue) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue (30 days) -->
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card }">
                <h2 class="text-base font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Daily Revenue — Last 30 Days</h2>
                <div v-if="daily.length === 0" class="text-sm text-center py-6" :style="{ color: themeColors.textTertiary }">No data</div>
                <div v-else class="flex items-end gap-1 h-24">
                    <div v-for="d in daily" :key="d.day"
                         class="flex-1 rounded-sm transition-all cursor-default relative group"
                         :style="{ height: dailyPct(d.revenue) + '%', minHeight: '2px', backgroundColor: themeColors.primary + 'bb' }">
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block z-10 whitespace-nowrap rounded px-2 py-1 text-xs shadow-lg"
                             :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                            {{ d.day }}: {{ formatCurrency(d.revenue) }}
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-xs" :style="{ color: themeColors.textTertiary }">{{ daily[0]?.day }}</span>
                    <span class="text-xs" :style="{ color: themeColors.textTertiary }">{{ daily[daily.length-1]?.day }}</span>
                </div>
            </div>

            <!-- Top Products + Recent Sales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Top Products -->
                <div class="rounded-lg shadow" :style="{ backgroundColor: themeColors.card }">
                    <div class="p-5 border-b" :style="{ borderColor: themeColors.border }">
                        <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Top 10 Products by Revenue</h2>
                    </div>
                    <div v-if="topProducts.length === 0" class="p-6 text-sm text-center" :style="{ color: themeColors.textTertiary }">No product data</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr :style="{ backgroundColor: themeColors.background }">
                                    <th class="px-4 py-2 text-left text-xs font-medium" :style="{ color: themeColors.textSecondary }">#</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium" :style="{ color: themeColors.textSecondary }">Product</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium" :style="{ color: themeColors.textSecondary }">Qty</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium" :style="{ color: themeColors.textSecondary }">Revenue</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium" :style="{ color: themeColors.textSecondary }">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(p, i) in topProducts" :key="p.id"
                                    class="border-t transition-colors hover:opacity-80"
                                    :style="{ borderColor: themeColors.border }">
                                    <td class="px-4 py-2.5 text-xs font-bold" :style="{ color: themeColors.textTertiary }">{{ i + 1 }}</td>
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium truncate max-w-[140px]" :style="{ color: themeColors.textPrimary }">{{ p.name }}</p>
                                        <p class="text-xs" :style="{ color: themeColors.textTertiary }">{{ p.code }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-right" :style="{ color: themeColors.textSecondary }">{{ p.qty }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold" :style="{ color: themeColors.success }">{{ formatCurrency(p.revenue) }}</td>
                                    <td class="px-4 py-2.5 text-right text-xs" :style="{ color: p.profit >= 0 ? themeColors.success : themeColors.danger }">{{ formatCurrency(p.profit) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="rounded-lg shadow" :style="{ backgroundColor: themeColors.card }">
                    <div class="p-5 border-b" :style="{ borderColor: themeColors.border }">
                        <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Recent Sales</h2>
                    </div>
                    <div v-if="recent.length === 0" class="p-6 text-sm text-center" :style="{ color: themeColors.textTertiary }">No recent sales</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr :style="{ backgroundColor: themeColors.background }">
                                    <th class="px-4 py-2 text-left text-xs font-medium" :style="{ color: themeColors.textSecondary }">Sale #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium" :style="{ color: themeColors.textSecondary }">Method</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium" :style="{ color: themeColors.textSecondary }">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium" :style="{ color: themeColors.textSecondary }">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in recent" :key="s.id"
                                    class="border-t transition-colors hover:opacity-80"
                                    :style="{ borderColor: themeColors.border }">
                                    <td class="px-4 py-2.5 font-medium text-xs" :style="{ color: themeColors.textPrimary }">{{ s.sale_number || '#' + s.id }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                              :style="{ backgroundColor: methodColor(s.payment_method) + '22', color: methodColor(s.payment_method) }">
                                            {{ formatMethod(s.payment_method) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-xs" :style="{ color: themeColors.success }">{{ formatCurrency(s.total_amount) }}</td>
                                    <td class="px-4 py-2.5 text-xs" :style="{ color: themeColors.textTertiary }">{{ formatDate(s.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:   `var(--kotel-background)`,
    card:         `var(--kotel-card)`,
    border:       `var(--kotel-border)`,
    textPrimary:  `var(--kotel-text-primary)`,
    textSecondary:`var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary:      `var(--kotel-primary)`,
    success:      `var(--kotel-success)`,
    danger:       `var(--kotel-danger)`,
    warning:      `var(--kotel-warning)`,
}))
loadTheme()

const props = defineProps({
    user:        Object,
    navigation:  Array,
    kpis:        Object,
    byMethod:    Array,
    daily:       Array,
    monthly:     Array,
    topProducts: Array,
    recent:      Array,
})

const formatDate = (d) => {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatMethod = (m) => {
    const map = { cash: 'Cash', card: 'Card', bank_transfer: 'Bank Transfer', mobile: 'Mobile', room_charge: 'Room Charge' }
    return map[m] || (m || 'Unknown')
}

const METHOD_COLORS = {
    cash: '#22c55e', card: '#3b82f6', bank_transfer: '#8b5cf6',
    mobile: '#f59e0b', room_charge: '#ec4899',
}
const methodColor = (m) => METHOD_COLORS[m] || '#6b7280'
const methodIcon  = (m) => ({ cash: '💵', card: '💳', bank_transfer: '🏦', mobile: '📱', room_charge: '🏨' }[m] || '💰')

const maxMethod  = computed(() => Math.max(...(props.byMethod ?? []).map(m => m.revenue), 1))
const methodPct  = (v) => Math.max((v / maxMethod.value) * 100, 2)

const maxMonthly = computed(() => Math.max(...(props.monthly ?? []).map(m => m.revenue), 1))
const monthlyPct = (v) => Math.max((v / maxMonthly.value) * 100, 2)

const maxDaily   = computed(() => Math.max(...(props.daily ?? []).map(d => d.revenue), 1))
const dailyPct   = (v) => Math.max((v / maxDaily.value) * 100, 2)

const exportCSV = () => {
    const rows = [
        ['Sale #', 'Payment Method', 'Amount', 'Status', 'Cashier', 'Date'],
        ...(props.recent ?? []).map(s => [
            s.sale_number || s.id,
            formatMethod(s.payment_method),
            s.total_amount,
            s.payment_status,
            s.cashier,
            s.created_at,
        ])
    ]
    const csv = rows.map(r => r.map(c => `"${c ?? ''}"`).join(',')).join('\n')
    const a   = document.createElement('a')
    a.href    = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv)
    a.download = `pos-reports-${new Date().toISOString().slice(0,10)}.csv`
    a.click()
}
</script>