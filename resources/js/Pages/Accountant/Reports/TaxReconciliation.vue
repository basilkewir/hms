<template>
    <DashboardLayout title="Tax Reconciliation" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Tax Reconciliation</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Collected taxes by rate — {{ startDate }} to {{ endDate }}
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

        <!-- Summary totals -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Folio Tax Collected</p>
                <p class="text-2xl font-bold" style="color:#f59e0b">{{ currency.symbol }}{{ fmt(totals.folio_tax) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">on {{ currency.symbol }}{{ fmt(totals.folio_taxable) }} taxable base</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">POS Tax Collected</p>
                <p class="text-2xl font-bold" style="color:#f59e0b">{{ currency.symbol }}{{ fmt(totals.pos_tax) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">on {{ currency.symbol }}{{ fmt(totals.pos_taxable) }} taxable base</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Combined Tax Total</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(totals.combined_tax) }}</p>
            </div>
        </div>

        <!-- Folio charges by tax rate -->
        <div class="rounded-lg border shadow-sm overflow-x-auto mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Guest Folio Charges — by Tax Rate</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tax Rate</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Taxable Base</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tax Collected</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Gross Total</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.rate" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ row.rate }}%</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">
                            {{ currency.symbol }}{{ fmt(row.taxable_base) }}
                        </td>
                        <td class="text-right px-4 py-3 font-semibold" style="color:#f59e0b">
                            {{ currency.symbol }}{{ fmt(row.tax_collected) }}
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(row.gross) }}
                        </td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.tx_count }}</td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="5" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No tax data found for this period.
                        </td>
                    </tr>
                </tbody>
                <tfoot v-if="rows.length > 0">
                    <tr class="border-t-2" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">Folio Total</td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(totals.folio_taxable) }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" style="color:#f59e0b">
                            {{ currency.symbol }}{{ fmt(totals.folio_tax) }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(totals.folio_gross) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- POS Tax summary -->
        <div v-if="posTax" class="rounded-lg border shadow-sm p-4"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h2 class="font-semibold text-sm mb-3" :style="{ color: themeColors.textPrimary }">POS Sales Tax</h2>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <p :style="{ color: themeColors.textSecondary }">Taxable Base</p>
                    <p class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(posTax.taxable_base) }}</p>
                </div>
                <div>
                    <p :style="{ color: themeColors.textSecondary }">Tax Collected</p>
                    <p class="font-semibold" style="color:#f59e0b">{{ currency.symbol }}{{ fmt(posTax.tax_collected) }}</p>
                </div>
                <div>
                    <p :style="{ color: themeColors.textSecondary }">Gross Total</p>
                    <p class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(posTax.gross) }}</p>
                </div>
            </div>
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
    posTax: Object,
    totals: Object,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')
const reload = () => router.get(route('accountant.reports.tax-reconciliation'), { period: selectedPeriod.value }, { preserveState: true })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
