<template>
    <DashboardLayout title="TRevPAR Analysis" :user="user">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">TRevPAR Analysis</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Total Revenue Per Available Room — {{ startDate }} to {{ endDate }}
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

        <!-- Key metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">TRevPAR</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.primary }">{{ currency.symbol }}{{ fmt(metrics.trevpar) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">Total rev / available room</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">RevPAR</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(metrics.revpar) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">Room rev / available room</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">ADR</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(metrics.adr) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">Avg daily rate</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Occupancy</p>
                <p class="text-2xl font-bold" style="color:#22c55e">{{ metrics.occupancy_pct }}%</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">{{ metrics.occupied_room_nights }} / {{ metrics.available_room_nights }} room-nights</p>
            </div>
        </div>

        <!-- Revenue split -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Room Revenue</p>
                <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(metrics.room_revenue) }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">
                    {{ metrics.total_revenue > 0 ? pct(metrics.room_revenue / metrics.total_revenue) : '0%' }} of total
                </p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">Folio Other Revenue</p>
                <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(metrics.folio_revenue - metrics.room_revenue) }}</p>
            </div>
            <div class="rounded-lg p-5 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs mb-1" :style="{ color: themeColors.textSecondary }">POS Revenue</p>
                <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(metrics.pos_revenue) }}</p>
            </div>
        </div>

        <!-- Total revenue banner -->
        <div class="rounded-lg p-4 border mb-6 flex items-center justify-between"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <span class="font-semibold" :style="{ color: themeColors.textSecondary }">Total Revenue ({{ metrics.total_rooms }} rooms × {{ dayCount }} nights)</span>
            <span class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(metrics.total_revenue) }}</span>
        </div>

        <!-- Daily breakdown -->
        <div v-if="daily.length > 0" class="rounded-lg border shadow-sm overflow-x-auto"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-4 py-3 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold text-sm" :style="{ color: themeColors.textPrimary }">Daily TRevPAR</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Date</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Folio Revenue</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">POS Revenue</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">Total</th>
                        <th class="text-right px-4 py-3 font-semibold" :style="{ color: themeColors.textSecondary }">TRevPAR</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in daily" :key="d.day" class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="px-4 py-2" :style="{ color: themeColors.textPrimary }">{{ d.day }}</td>
                        <td class="text-right px-4 py-2" :style="{ color: themeColors.textSecondary }">{{ currency.symbol }}{{ fmt(d.folio_revenue) }}</td>
                        <td class="text-right px-4 py-2" :style="{ color: themeColors.textSecondary }">{{ currency.symbol }}{{ fmt(d.pos_revenue) }}</td>
                        <td class="text-right px-4 py-2 font-semibold" :style="{ color: themeColors.textPrimary }">{{ currency.symbol }}{{ fmt(d.total_revenue) }}</td>
                        <td class="text-right px-4 py-2 font-bold" :style="{ color: themeColors.primary }">{{ currency.symbol }}{{ fmt(d.trevpar) }}</td>
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
    metrics: Object,
    daily: Array,
    startDate: String,
    endDate: String,
    period: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')
const dayCount = computed(() => props.daily?.length ?? 1)

const reload = () => router.get(route('manager.reports.trevpar'), { period: selectedPeriod.value }, { preserveState: true })
const fmt = (v) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const pct = (v) => (v * 100).toFixed(1) + '%'
const printPage = () => window.print()
</script>
