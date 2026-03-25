<template>
    <DashboardLayout title="Night Audit" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Night Audit — Trial Balance</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Daily summary for {{ auditDate }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="date" v-model="selectedDate" @change="reload"
                           class="rounded-md px-3 py-2 text-sm border focus:outline-none"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    <button @click="printPage" class="px-4 py-2 rounded-md text-sm text-white"
                            :style="{ backgroundColor: themeColors.success }">
                        <PrinterIcon class="h-4 w-4 inline mr-1" /> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Key metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg p-4 border text-center" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">In-House Guests</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.primary }">{{ trialBalance.in_house_count }}</p>
            </div>
            <div class="rounded-lg p-4 border text-center" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Arrivals</p>
                <p class="text-3xl font-bold" style="color:#22c55e">{{ trialBalance.arrivals }}</p>
            </div>
            <div class="rounded-lg p-4 border text-center" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Departures</p>
                <p class="text-3xl font-bold" style="color:#f59e0b">{{ trialBalance.departures }}</p>
            </div>
            <div class="rounded-lg p-4 border text-center" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Open Folios</p>
                <p class="text-3xl font-bold" style="color:#a78bfa">{{ trialBalance.open_folio_count }}</p>
            </div>
        </div>

        <!-- Trial Balance -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- Revenue & Payments -->
            <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Revenue Posted Today</h2>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Room Revenue</span>
                        <span class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(trialBalance.room_revenue) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2" :style="{ borderColor: themeColors.border }">
                        <span :style="{ color: themeColors.textSecondary }">Net Charges (ex-tax)</span>
                        <span class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(trialBalance.total_net_charges) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Tax Posted</span>
                        <span class="font-semibold" style="color:#f59e0b">{{ currency.symbol }}{{ fmt(trialBalance.total_tax_charges) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 font-bold" :style="{ borderColor: themeColors.border }">
                        <span :style="{ color: themeColors.textPrimary }">Gross Charges</span>
                        <span :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(trialBalance.total_charges) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payments & Balance -->
            <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Payments & Outstanding</h2>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Payments Received</span>
                        <span class="font-semibold" style="color:#22c55e">{{ currency.symbol }}{{ fmt(trialBalance.payments_received) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Refunds Issued</span>
                        <span class="font-semibold" style="color:#ef4444">{{ currency.symbol }}{{ fmt(trialBalance.payments_refunded) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2" :style="{ borderColor: themeColors.border }">
                        <span :style="{ color: themeColors.textSecondary }">Net Payments</span>
                        <span class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(trialBalance.net_payments) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 font-bold" :style="{ borderColor: themeColors.border }">
                        <span :style="{ color: themeColors.textPrimary }">Outstanding Balance</span>
                        <span style="color:#f59e0b">{{ currency.symbol }}{{ fmt(trialBalance.open_balance) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charges by type -->
        <div v-if="chargeByType.length > 0" class="rounded-lg border shadow-sm overflow-x-auto mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Charge Breakdown by Type</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Type</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Net</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tax</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Total</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in chargeByType" :key="row.type" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ row.label }}</td>
                        <td class="text-right px-4 py-2" :style="{ color: themeColors.textSecondary }">{{ currency.symbol }}{{ fmt(row.net_amount) }}</td>
                        <td class="text-right px-4 py-2" style="color:#f59e0b">{{ currency.symbol }}{{ fmt(row.tax_amount) }}</td>
                        <td class="text-right px-4 py-2 font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(row.total_amount) }}</td>
                        <td class="text-right px-4 py-2" :style="{ color: themeColors.textSecondary }">{{ row.count }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Payment methods -->
        <div v-if="paymentByMethod.length > 0" class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Payments by Method</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Method</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Amount</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in paymentByMethod" :key="p.method" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium capitalize" :style="{ color: themeColors.textPrimary }">{{ p.method }}</td>
                        <td class="text-right px-4 py-3 font-semibold" style="color:#22c55e">{{ currency.symbol }}{{ fmt(p.amount) }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ p.count }}</td>
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
    auditDate: String,
    trialBalance: Object,
    chargeByType: Array,
    paymentByMethod: Array,
    currency: Object,
})

const selectedDate = ref(props.auditDate)
const reload = () => router.get(route('accountant.reports.night-audit'), { audit_date: selectedDate.value }, { preserveState: false })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
