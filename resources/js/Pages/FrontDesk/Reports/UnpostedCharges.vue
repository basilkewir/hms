<template>
    <DashboardLayout title="Unposted Charges" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Unposted Charges</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        POS room-charges not yet transferred to guest folios — as of {{ asOfDate }}
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
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Unposted</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ summary.total_unposted }}</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Amount</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(summary.total_amount) }}</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{
                     backgroundColor: summary.urgent_count > 0 ? 'rgba(239,68,68,0.1)' : themeColors.card,
                     borderColor: summary.urgent_count > 0 ? '#ef4444' : themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Urgent (checking out today)</p>
                <p class="text-2xl font-bold" :style="{ color: summary.urgent_count > 0 ? '#ef4444' : themeColors.textPrimary }">
                    {{ summary.urgent_count }}
                </p>
            </div>
        </div>

        <!-- Charges table -->
        <div class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Sale #</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Guest</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Confirmation</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Sale Date</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Check-Out</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Amount</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in charges" :key="c.sale_id"
                        class="border-t"
                        :style="{
                            borderColor: themeColors.border,
                            backgroundColor: c.urgent ? 'rgba(239,68,68,0.05)' : 'transparent'
                        }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.primary }">
                            {{ c.sale_number }}
                            <span v-if="c.urgent" class="ml-1 text-xs px-1.5 py-0.5 rounded font-medium" style="background:#7f1d1d;color:#fca5a5">Urgent</span>
                        </td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ c.guest_name }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ c.confirmation || '—' }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ c.sale_date || '—' }}</td>
                        <td class="px-4 py-3" :style="{ color: c.urgent ? '#ef4444' : themeColors.textSecondary }">
                            {{ c.check_out_date || '—' }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(c.total) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium"
                                  :style="c.payment_status === 'pending'
                                    ? { background: '#78350f', color: '#fde68a' }
                                    : { background: '#14532d', color: '#86efac' }">
                                {{ c.payment_status }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="charges.length === 0">
                        <td colspan="7" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            All room charges have been posted to guest folios.
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
    charges: Array,
    summary: Object,
    asOfDate: String,
    currency: Object,
})

const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
