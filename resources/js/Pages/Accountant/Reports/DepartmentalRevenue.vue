<template>
    <DashboardLayout title="Departmental Revenue" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Departmental Revenue</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Revenue breakdown by charge type — {{ startDate }} to {{ endDate }}
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <select v-model="selectedPeriod" @change="reload"
                            class="rounded-md px-3 py-2 text-sm border focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <button @click="printPage" class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.success }">
                        <PrinterIcon class="h-4 w-4 inline mr-1" /> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Gross Revenue</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                    {{ currency.symbol }}{{ fmt(totals.gross_revenue) }}
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Net Revenue (ex-tax)</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                    {{ currency.symbol }}{{ fmt(totals.net_revenue) }}
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Tax Collected</p>
                <p class="text-2xl font-bold" :style="{ color: '#f59e0b' }">
                    {{ currency.symbol }}{{ fmt(totals.tax_collected) }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Department</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Net Revenue</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tax</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Gross</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Transactions</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.type"
                        class="border-t"
                        :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: row.non_revenue > 0 ? '#f87171' : themeColors.textPrimary }">
                            {{ row.label }}
                            <span v-if="row.non_revenue > 0" class="ml-2 text-xs px-1.5 py-0.5 rounded" style="background:#7f1d1d;color:#fca5a5">non-revenue</span>
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(row.net_revenue) }}
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: '#f59e0b' }">
                            {{ currency.symbol }}{{ fmt(row.tax_collected) }}
                        </td>
                        <td class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(row.gross_revenue) }}
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">
                            {{ row.tx_count.toLocaleString() }}
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">
                            {{ totals.gross_revenue > 0 ? pct(row.gross_revenue / totals.gross_revenue) : '0%' }}
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="6" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No charges found for this period.
                        </td>
                    </tr>
                </tbody>
                <tfoot v-if="rows.length > 0">
                    <tr class="border-t-2" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">Total</td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(totals.net_revenue) }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" style="color:#f59e0b">
                            {{ currency.symbol }}{{ fmt(totals.tax_collected) }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(totals.gross_revenue) }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ totals.tx_count.toLocaleString() }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { PrinterIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    primary: 'var(--kotel-primary)',
    success: 'var(--kotel-success)',
}))
loadTheme()

const props = defineProps({
    user: Object,
    rows: Array,
    totals: Object,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')

const reload = () => router.get(route('accountant.reports.departmental-revenue'), { period: selectedPeriod.value }, { preserveState: true })

const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const pct = (v) => (v * 100).toFixed(1) + '%'
const printPage = () => window.print()
</script>
