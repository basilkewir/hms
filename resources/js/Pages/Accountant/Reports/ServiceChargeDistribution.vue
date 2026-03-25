<template>
    <DashboardLayout title="Service Charge Distribution" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Service Charge Distribution</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        All service charges collected — {{ startDate }} to {{ endDate }}
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
                    <button @click="printPage" class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.success }">
                        <PrinterIcon class="h-4 w-4 inline mr-1" /> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Service Charges</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">
                    {{ currency.symbol }}{{ fmt(total) }}
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Charge Codes Active</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.primary }">{{ byCode.length }}</p>
            </div>
        </div>

        <!-- By charge code -->
        <div class="rounded-lg border shadow-sm overflow-x-auto mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">By Charge Code</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Charge Code</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Department</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Amount</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Transactions</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in byCode" :key="row.charge_code"
                        class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ row.charge_code }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.department || '—' }}</td>
                        <td class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(row.amount) }}
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.tx_count }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">
                            {{ total > 0 ? pct(row.amount / total) : '0%' }}
                        </td>
                    </tr>
                    <tr v-if="byCode.length === 0">
                        <td colspan="5" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No service charges found for this period.
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
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Amount</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in daily" :key="d.day" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-2" :style="{ color: themeColors.textPrimary }">{{ d.day }}</td>
                        <td class="text-right px-4 py-2 font-medium" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(d.amount) }}
                        </td>
                        <td class="text-right px-4 py-2" :style="{ color: themeColors.textSecondary }">{{ d.count }}</td>
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
    daily: Array,
    byCode: Array,
    total: Number,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')
const reload = () => router.get(route('accountant.reports.service-charge-distribution'), { period: selectedPeriod.value }, { preserveState: true })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const pct = (v) => (v * 100).toFixed(1) + '%'
const printPage = () => window.print()
</script>
