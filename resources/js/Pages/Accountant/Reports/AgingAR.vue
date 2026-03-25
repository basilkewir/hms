<template>
    <DashboardLayout title="Aging Accounts Receivable" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Aging Accounts Receivable</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Open folio balances as of {{ asOfDate }} — {{ currency.symbol }}{{ fmt(total) }} outstanding
                    </p>
                </div>
                <button @click="printPage" class="px-4 py-2 rounded-md text-sm text-white"
                        :style="{ backgroundColor: themeColors.success }">
                    <PrinterIcon class="h-4 w-4 inline mr-1" /> Print
                </button>
            </div>
        </div>

        <!-- Aging buckets -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div v-for="bucket in summary" :key="bucket.label"
                 class="rounded-lg p-4 border text-center"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">{{ bucket.label }}</p>
                <p class="text-xl font-bold" :style="{ color: bucketColor(bucket.label) }">
                    {{ currency.symbol }}{{ fmt(bucket.amount) }}
                </p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">{{ bucket.count }} folio{{ bucket.count !== 1 ? 's' : '' }}</p>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="flex gap-2 mb-4 flex-wrap">
            <button v-for="opt in filterOpts" :key="opt.value"
                    @click="activeFilter = opt.value"
                    class="px-3 py-1.5 rounded-md text-xs font-medium border"
                    :style="activeFilter === opt.value
                        ? { backgroundColor: themeColors.primary, color: '#fff', borderColor: themeColors.primary }
                        : { backgroundColor: themeColors.card, color: themeColors.textSecondary, borderColor: themeColors.border }">
                {{ opt.label }}
            </button>
        </div>

        <!-- Folio table -->
        <div class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Folio #</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Guest</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Confirmation</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Due Date</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Days Overdue</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Balance</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Aging</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="f in filteredFolios" :key="f.folio_id"
                        class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.primary }">{{ f.folio_number }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ f.guest_name }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ f.confirmation || '—' }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ f.due_date }}</td>
                        <td class="text-right px-4 py-3 font-medium" :style="{ color: f.days_overdue > 0 ? '#ef4444' : themeColors.textPrimary }">
                            {{ f.days_overdue > 0 ? f.days_overdue + 'd' : 'Current' }}
                        </td>
                        <td class="text-right px-4 py-3 font-bold" :style="{ color: f.days_overdue > 60 ? '#ef4444' : themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(f.balance) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium" :style="bucketBadge(f.bucket)">
                                {{ bucketLabel(f.bucket) }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="filteredFolios.length === 0">
                        <td colspan="7" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No open balances found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
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
    summary: Array,
    total: Number,
    asOfDate: String,
    currency: Object,
})

const activeFilter = ref('all')

const filterOpts = [
    { value: 'all', label: 'All' },
    { value: 'current', label: 'Current' },
    { value: '1_30', label: '1–30 days' },
    { value: '31_60', label: '31–60 days' },
    { value: '61_90', label: '61–90 days' },
    { value: '90_plus', label: '90+ days' },
]

const filteredFolios = computed(() =>
    activeFilter.value === 'all' ? props.folios : props.folios.filter(f => f.bucket === activeFilter.value)
)

const bucketLabel = (b) => ({ current: 'Current', '1_30': '1–30d', '31_60': '31–60d', '61_90': '61–90d', '90_plus': '90+d' }[b] || b)
const bucketColor = (label) => {
    if (label === 'Current') return '#22c55e'
    if (label === '1–30 days') return '#f59e0b'
    if (label === '31–60 days') return '#f97316'
    return '#ef4444'
}
const bucketBadge = (b) => {
    const colors = {
        current: { background: '#14532d', color: '#86efac' },
        '1_30': { background: '#78350f', color: '#fde68a' },
        '31_60': { background: '#7c2d12', color: '#fdba74' },
        '61_90': { background: '#7f1d1d', color: '#fca5a5' },
        '90_plus': { background: '#450a0a', color: '#f87171' },
    }
    return colors[b] || {}
}

const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
