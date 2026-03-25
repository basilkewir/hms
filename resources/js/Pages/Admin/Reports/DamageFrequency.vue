<template>
    <DashboardLayout title="Damage Frequency Report" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Damage Frequency Report</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Guest damage incidents — {{ startDate }} to {{ endDate }}
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
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Incidents</p>
                <p class="text-3xl font-bold" style="color:#ef4444">{{ totals.count }}</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Total Damage Recovered</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(totals.amount) }}</p>
            </div>
        </div>

        <!-- By room -->
        <div v-if="byRoom.length > 0" class="rounded-lg border shadow-sm overflow-x-auto mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">By Room (Most Frequent)</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Incidents</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Total Recovered</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Last Incident</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in byRoom" :key="r.room_number" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">Room {{ r.room_number }}</td>
                        <td class="text-right px-4 py-3 font-bold" style="color:#ef4444">{{ r.incident_count }}</td>
                        <td class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(r.total_amount) }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ r.last_incident }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- All cases -->
        <div class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">All Incidents</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Date</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Guest</th>
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Description</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in cases" :key="c.charge_date + c.room_number + c.description"
                        class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ c.charge_date }}</td>
                        <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">{{ c.room_number }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textSecondary }">{{ c.guest_name }}</td>
                        <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ c.description || '—' }}</td>
                        <td class="text-right px-4 py-3 font-bold" style="color:#ef4444">{{ currency.symbol }}{{ fmt(c.amount) }}</td>
                    </tr>
                    <tr v-if="cases.length === 0">
                        <td colspan="5" class="text-center px-4 py-8" :style="{ color: themeColors.textSecondary }">
                            No damage incidents found for this period.
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
    cases: Array,
    byRoom: Array,
    totals: Object,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')

const routeName = computed(() => {
    const role = props.user?.roles?.[0]?.name ?? 'manager'
    if (role === 'accountant') return 'accountant.reports.damage-frequency'
    return 'manager.reports.damage-frequency'
})

const reload = () => router.get(route(routeName.value), { period: selectedPeriod.value }, { preserveState: true })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const printPage = () => window.print()
</script>
