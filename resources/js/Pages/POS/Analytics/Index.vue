<template>
    <DashboardLayout title="POS Analytics" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">POS Analytics</h1>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Revenue trends, product performance, and sales patterns.</p>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full font-medium"
                          :style="{ backgroundColor: growthPositive ? '#22c55e22' : '#ef444422',
                                    color: growthPositive ? '#22c55e' : '#ef4444' }">
                        {{ growthPositive ? 'Up' : 'Down' }} {{ Math.abs(kpis.revenue_growth) }}% vs last month
                    </span>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-2" :style="{ color: themeColors.textSecondary }">This Month Revenue</p>
                    <p class="text-2xl font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(kpis.this_month_revenue) }}</p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">vs {{ formatCurrency(kpis.last_month_revenue) }} last month</p>
                    <span class="inline-block mt-2 text-xs font-semibold px-2 py-0.5 rounded"
                          :style="{ backgroundColor: growthPositive ? '#22c55e22' : '#ef444422', color: growthPositive ? '#22c55e' : '#ef4444' }">
                        {{ growthPositive ? '+' : '' }}{{ kpis.revenue_growth }}%
                    </span>
                </div>
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-2" :style="{ color: themeColors.textSecondary }">This Month Transactions</p>
                    <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ kpis.this_month_count }}</p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">{{ kpis.last_month_count }} transactions last month</p>
                    <span class="inline-block mt-2 text-xs font-semibold px-2 py-0.5 rounded"
                          :style="{ backgroundColor: salesGrowthPositive ? '#22c55e22' : '#ef444422', color: salesGrowthPositive ? '#22c55e' : '#ef4444' }">
                        {{ salesGrowthPositive ? '+' : '' }}{{ salesGrowthPct }}%
                    </span>
                </div>
                <div class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs font-medium mb-2" :style="{ color: themeColors.textSecondary }">Total All-Time Profit</p>
                    <p class="text-2xl font-bold" :style="{ color: kpis.total_profit >= 0 ? '#22c55e' : '#ef4444' }">
                        {{ formatCurrency(kpis.total_profit) }}
                    </p>
                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">gross profit from all sales</p>
                </div>
            </div>

            <!-- 12-Month Revenue Trend -->
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card }">
                <h2 class="text-base font-semibold mb-5" :style="{ color: themeColors.textPrimary }">12-Month Revenue Trend</h2>
                <div v-if="!months || months.length === 0" class="text-sm text-center py-8" :style="{ color: themeColors.textTertiary }">No data available</div>
                <div v-else>
                    <div class="flex items-end gap-1.5 h-40 mb-2">
                        <div v-for="(m, i) in months" :key="i"
                             class="flex-1 rounded-t-sm relative group cursor-default transition-opacity hover:opacity-80"
                             :style="{ height: trendPct(m.revenue) + '%', minHeight: '3px', backgroundColor: m.revenue === maxTrendValue ? themeColors.primary : themeColors.primary + '66' }">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block z-10 whitespace-nowrap rounded px-2 py-1 text-xs shadow-lg"
                                 :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                                {{ m.label }}: {{ formatCurrency(m.revenue) }} ({{ m.count }} sales)
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-1.5">
                        <div v-for="(m, i) in months" :key="i" class="flex-1 text-center">
                            <span class="text-xs" :style="{ color: themeColors.textTertiary }">{{ m.label.slice(0, 3) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Breakdown + Hourly Heatmap -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Payment method breakdown (this month) -->
                <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card }">
                    <h2 class="text-base font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Payment Mix — This Month</h2>
                    <div v-if="!methodBreakdown || methodBreakdown.length === 0" class="text-sm text-center py-6" :style="{ color: themeColors.textTertiary }">No transactions this month</div>
                    <div v-else class="space-y-4">
                        <div v-for="m in methodBreakdown" :key="m.method">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: methodColor(m.method) }"></div>
                                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatMethod(m.method) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(m.revenue) }}</span>
                                    <span class="text-xs ml-2" :style="{ color: themeColors.textTertiary }">{{ m.count }} txn</span>
                                </div>
                            </div>
                            <div class="w-full rounded-full h-2" :style="{ backgroundColor: themeColors.border }">
                                <div class="h-2 rounded-full"
                                     :style="{ width: mBreakdownPct(m.revenue) + '%', backgroundColor: methodColor(m.method) }"></div>
                            </div>
                            <p class="text-xs mt-0.5 text-right" :style="{ color: themeColors.textTertiary }">{{ mBreakdownPct(m.revenue).toFixed(1) }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Hourly distribution -->
                <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card }">
                    <h2 class="text-base font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Peak Hours (Last 30 Days)</h2>
                    <div v-if="!hourly || hourly.length === 0" class="text-sm text-center py-6" :style="{ color: themeColors.textTertiary }">No data</div>
                    <div v-else>
                        <div class="flex items-end gap-0.5 h-24 mb-2">
                            <div v-for="h in 24" :key="h"
                                 class="flex-1 rounded-t-sm relative group cursor-default"
                                 :style="{ height: hourlyBarPct(h - 1) + '%', minHeight: '2px', backgroundColor: getHourlyCount(h-1) > 0 ? '#f59e0bcc' : themeColors.border }">
                                <div v-if="getHourlyCount(h-1) > 0"
                                     class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block z-10 whitespace-nowrap rounded px-2 py-1 text-xs shadow-lg"
                                     :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                                    {{ String(h-1).padStart(2,'0') }}:00 — {{ getHourlyCount(h-1) }} sales
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-0.5">
                            <div v-for="h in 24" :key="h" class="flex-1">
                                <span v-if="[1,7,13,19,24].includes(h)" class="text-xs" :style="{ color: themeColors.textTertiary }">{{ String(h-1).padStart(2,'0') }}</span>
                            </div>
                        </div>
                        <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">
                            Busiest hour: <strong>{{ busiestHourLabel }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Top Products Table -->
            <div class="rounded-lg shadow" :style="{ backgroundColor: themeColors.card }">
                <div class="p-5 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Top Products Performance</h2>
                </div>
                <div v-if="!topItems || topItems.length === 0" class="p-6 text-sm text-center" :style="{ color: themeColors.textTertiary }">No product data</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Product</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Units</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Revenue</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Profit</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Margin</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Revenue Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in topItems" :key="i"
                                class="border-t hover:opacity-80 transition-opacity"
                                :style="{ borderColor: themeColors.border }">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                              :style="{ backgroundColor: themeColors.primary + '22', color: themeColors.primary }">{{ i + 1 }}</span>
                                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ p.name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right" :style="{ color: themeColors.textSecondary }">{{ p.qty }}</td>
                                <td class="px-5 py-3 text-right font-semibold" style="color: #22c55e;">{{ formatCurrency(p.revenue) }}</td>
                                <td class="px-5 py-3 text-right font-semibold"
                                    :style="{ color: p.profit >= 0 ? '#22c55e' : '#ef4444' }">{{ formatCurrency(p.profit) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                          :style="{ backgroundColor: p.margin >= 30 ? '#22c55e22' : p.margin >= 10 ? '#f59e0b22' : '#ef444422',
                                                    color:            p.margin >= 30 ? '#22c55e'   : p.margin >= 10 ? '#f59e0b'   : '#ef4444' }">
                                        {{ p.margin }}%
                                    </span>
                                </td>
                                <td class="px-5 py-3 min-w-[140px]">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 rounded-full h-1.5" :style="{ backgroundColor: themeColors.border }">
                                            <div class="h-1.5 rounded-full"
                                                 :style="{ width: topItemsPct(p.revenue) + '%', backgroundColor: themeColors.primary }"></div>
                                        </div>
                                        <span class="text-xs w-8 text-right" :style="{ color: themeColors.textTertiary }">{{ topItemsPct(p.revenue).toFixed(0) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary:  `var(--kotel-text-tertiary)`,
    primary:       `var(--kotel-primary)`,
    success:       `var(--kotel-success)`,
    danger:        `var(--kotel-danger)`,
    warning:       `var(--kotel-warning)`,
}))
loadTheme()

const props = defineProps({
    user:            Object,
    navigation:      Array,
    months:          Array,
    methodBreakdown: Array,
    topItems:        Array,
    hourly:          Array,
    kpis:            Object,
})

const formatMethod = (m) => {
    const map = { cash: 'Cash', card: 'Card', bank_transfer: 'Bank Transfer', mobile: 'Mobile', room_charge: 'Room Charge' }
    return map[m] || (m || 'Unknown')
}

const METHOD_COLORS = {
    cash: '#22c55e', card: '#3b82f6', bank_transfer: '#8b5cf6',
    mobile: '#f59e0b', room_charge: '#ec4899',
}
const methodColor = (m) => METHOD_COLORS[m] || '#6b7280'

const growthPositive      = computed(() => (props.kpis?.revenue_growth ?? 0) >= 0)
const salesGrowthPct      = computed(() => {
    const last = props.kpis?.last_month_count ?? 0
    const cur  = props.kpis?.this_month_count ?? 0
    return last > 0 ? ((cur - last) / last * 100).toFixed(1) : 0
})
const salesGrowthPositive = computed(() => Number(salesGrowthPct.value) >= 0)

const maxTrendValue = computed(() => Math.max(...(props.months ?? []).map(m => m.revenue), 1))
const trendPct      = (v) => Math.max((v / maxTrendValue.value) * 100, 3)

const maxMBreakdownValue = computed(() => Math.max(...(props.methodBreakdown ?? []).map(m => m.revenue), 1))
const mBreakdownPct      = (v) => Math.max((v / maxMBreakdownValue.value) * 100, 1)

const hourlyMap = computed(() => {
    const map = {}
    ;(props.hourly ?? []).forEach(h => { map[h.hour] = h })
    return map
})
const getHourlyCount = (h) => hourlyMap.value[h]?.count ?? 0
const maxHourlyCount = computed(() => Math.max(...Object.values(hourlyMap.value).map(h => h.count), 1))
const hourlyBarPct   = (h) => Math.max((getHourlyCount(h) / maxHourlyCount.value) * 100, 2)

const busiestHourLabel = computed(() => {
    const entry = (props.hourly ?? []).reduce((a, b) => b.count > a.count ? b : a, { count: 0, hour: null })
    return entry.hour !== null ? String(entry.hour).padStart(2, '0') + ':00' : '—'
})

const maxTopItem  = computed(() => Math.max(...(props.topItems ?? []).map(t => t.revenue), 1))
const topItemsPct = (v) => Math.max((v / maxTopItem.value) * 100, 1)
</script>
