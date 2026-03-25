<template>
    <DashboardLayout title="Gratuity & Tips Report" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Gratuity &amp; Tips Report</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Tips collected from POS sales — {{ startDate }} to {{ endDate }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <select v-model="selectedPeriod" @change="reload"
                            class="rounded-md px-3 py-2 text-sm border focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <button @click="printPage" class="px-4 py-2 rounded-md text-sm text-white"
                            :style="{ backgroundColor: themeColors.success }">
                        <PrinterIcon class="h-4 w-4 inline mr-1" /> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Tips Collected</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.primary }">
                    {{ currency.symbol }}{{ fmt(totals.tip_total) }}
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Transactions with Tips</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">{{ totals.tx_count }}</p>
            </div>
        </div>

        <!-- Per staff -->
        <div class="rounded-lg border shadow-sm overflow-x-auto mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">By Staff Member</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Staff</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Transactions</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Sales Base</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tips Total</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tip %</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.staff_name" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ row.staff_name }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.tx_count }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ currency.symbol }}{{ fmt(row.sales_base) }}</td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.primary }">{{ currency.symbol }}{{ fmt(row.tip_total) }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">
                            {{ row.sales_base > 0 ? pct(row.tip_total / row.sales_base) : '—' }}
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="5" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No tips recorded for this period. (Tip amounts are entered via POS sales.)
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Daily trend -->
        <div v-if="daily.length > 0" class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Daily Trend</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Date</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tips</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in daily" :key="d.day" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-2" :style="{ color: themeColors.textPrimary }">{{ d.day }}</td>
                        <td class="text-right px-4 py-2 font-medium" :style="{ color: themeColors.primary }">{{ currency.symbol }}{{ fmt(d.tip_total) }}</td>
                        <td class="text-right px-4 py-2" :style="{ color: themeColors.textSecondary }">{{ d.tx_count }}</td>
                    </tr>
                </tbody>
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
    daily: Array,
    totals: Object,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')
const reload = () => router.get(route('accountant.reports.gratuity'), { period: selectedPeriod.value }, { preserveState: true })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const pct = (v) => (v * 100).toFixed(1) + '%'
const printPage = () => window.print()
</script>
