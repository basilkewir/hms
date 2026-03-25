<template>
    <DashboardLayout title="Incidentals Analysis" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Incidentals Analysis</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Non-room revenue by category — {{ startDate }} to {{ endDate }}
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

        <!-- Summary by type -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div v-for="t in byType" :key="t.type"
                 class="rounded-lg p-4 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">{{ t.label }}</p>
                <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(t.amount) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">{{ t.count }} charges</p>
            </div>
        </div>

        <!-- Total -->
        <div class="rounded-lg p-4 border mb-6 flex items-center justify-between"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <span class="font-semibold" :style="{ color: themeColors.textSecondary }">Total Incidentals</span>
            <span class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(total) }}</span>
        </div>

        <!-- Detail table -->
        <div class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Charge Code</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Category</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Department</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Net Amount</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Tax</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.charge_code + row.type"
                        class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ row.charge_code }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.label }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.department || '—' }}</td>
                        <td class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textPrimary }">
                            {{ currency.symbol }}{{ fmt(row.amount) }}
                        </td>
                        <td class="text-right px-4 py-3" style="color:#f59e0b">{{ currency.symbol }}{{ fmt(row.tax) }}</td>
                        <td class="text-right px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ row.count }}</td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="6" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No incidental charges found for this period.
                        </td>
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
    byType: Array,
    total: Number,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')

// Detect role from user for correct route
const routeName = computed(() => {
    const role = props.user?.roles?.[0]?.name ?? 'manager'
    if (role === 'accountant') return 'accountant.reports.incidentals'
    return 'manager.reports.incidentals'
})

const reload = () => router.get(route(routeName.value), { period: selectedPeriod.value }, { preserveState: true })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
