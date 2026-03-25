<template>
    <DashboardLayout title="Folio Balance Report" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Folio Balance Report</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        All open guest folios as of {{ asOfDate }} — {{ summary.total_folios }} guests in-house
                    </p>
                </div>
                <button @click="printPage" class="px-4 py-2 rounded-md text-sm text-white"
                        :style="{ backgroundColor: themeColors.success }">
                    <PrinterIcon class="h-4 w-4 inline mr-1" /> Print
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Charges</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                    {{ currency.symbol }}{{ fmt(summary.total_charges) }}
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Paid</p>
                <p class="text-2xl font-bold" style="color:#22c55e">
                    {{ currency.symbol }}{{ fmt(summary.total_paid) }}
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Outstanding Balance</p>
                <p class="text-2xl font-bold" style="color:#f59e0b">
                    {{ currency.symbol }}{{ fmt(summary.total_balance) }}
                </p>
            </div>
        </div>

        <!-- Folio table -->
        <div class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Folio #</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Guest</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Check-In</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Check-Out</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Total Charges</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Paid</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="f in folios" :key="f.folio_id"
                        class="border-t"
                        :class="f.balance > 0 ? '' : ''"
                        :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.primary }">{{ f.folio_number }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ f.guest_name }}</td>
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ f.room_number }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ f.check_in_date || '—' }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ f.check_out_date || '—' }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">
                            {{ currency.symbol }}{{ fmt(f.total_charges) }}
                        </td>
                        <td class="text-right px-4 py-3" style="color:#22c55e">
                            {{ currency.symbol }}{{ fmt(f.paid_amount) }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: f.balance > 0 ? '#f59e0b' : '#22c55e' }">
                            {{ currency.symbol }}{{ fmt(f.balance) }}
                        </td>
                    </tr>
                    <tr v-if="folios.length === 0">
                        <td colspan="8" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No open folios at this time.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
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
    folios: Array,
    summary: Object,
    asOfDate: String,
    currency: Object,
})

const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
