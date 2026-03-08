<template>
    <DashboardLayout title="Reports Overview" :user="user" :navigation="navigation">

        <!-- Header -->
        <div class="rounded-xl p-6 mb-6 border border-kotel-yellow/20 bg-kotel-card">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-yellow">Reports Overview</h1>
                    <p class="text-kotel-text-secondary mt-1 text-sm">Live operational summary across all hotel departments</p>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <a :href="route('manager.reports.revenue')"
                       class="px-4 py-2 bg-kotel-yellow text-kotel-black rounded-lg text-sm font-semibold hover:bg-yellow-400 transition">
                        Revenue Report →
                    </a>
                    <a :href="route('manager.reports.occupancy')"
                       class="px-4 py-2 border border-kotel-yellow/40 text-kotel-yellow rounded-lg text-sm font-semibold hover:bg-kotel-yellow/10 transition">
                        Occupancy Report →
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Top KPI Row ── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5 flex flex-col gap-1">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider">💰 Revenue This Month</p>
                <p class="text-2xl font-bold text-kotel-yellow">{{ fmt(stats.revenue_this_month) }}</p>
                <p class="text-xs text-kotel-text-secondary">Today: {{ fmt(stats.revenue_today) }}</p>
            </div>
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5 flex flex-col gap-1">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider">📈 Net Profit (All Time)</p>
                <p class="text-2xl font-bold" :class="stats.net_profit >= 0 ? 'text-green-400' : 'text-red-400'">{{ fmt(stats.net_profit) }}</p>
                <p class="text-xs text-kotel-text-secondary">This month: {{ fmt(stats.net_profit_this_month) }}</p>
            </div>
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5 flex flex-col gap-1">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider">🏨 Occupancy Rate</p>
                <p class="text-2xl font-bold text-blue-400">{{ stats.occupancy_rate }}%</p>
                <p class="text-xs text-kotel-text-secondary">{{ stats.occupied_rooms }} of {{ stats.total_rooms }} rooms</p>
            </div>
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5 flex flex-col gap-1">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider">👥 Total Guests</p>
                <p class="text-2xl font-bold text-purple-400">{{ stats.total_guests }}</p>
                <p class="text-xs text-kotel-text-secondary">+{{ stats.new_guests_this_month }} this month</p>
            </div>
        </div>

        <!-- ── Revenue + Expenses + Reservations ── -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider mb-1">Total Revenue (All Time)</p>
                <p class="text-3xl font-bold text-kotel-yellow">{{ fmt(stats.total_revenue) }}</p>
                <p class="text-sm text-kotel-text-secondary mt-1">{{ fmt(stats.revenue_this_year) }} this year</p>
            </div>
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider mb-1">Total Expenses (All Time)</p>
                <p class="text-3xl font-bold text-red-400">{{ fmt(stats.total_expenses) }}</p>
                <p class="text-sm text-kotel-text-secondary mt-1">{{ fmt(stats.expenses_this_month) }} this month</p>
            </div>
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <p class="text-xs text-kotel-text-secondary uppercase tracking-wider mb-2">Reservations ({{ stats.total_reservations }} total)</p>
                <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs">
                    <span class="text-green-400">✓ Checked Out: {{ stats.checked_out }}</span>
                    <span class="text-blue-400">↑ Arriving Today: {{ stats.arriving_today }}</span>
                    <span class="text-orange-400">⏳ Confirmed/Pending: {{ stats.confirmed_pending }}</span>
                    <span class="text-yellow-400">↓ Departing Today: {{ stats.departing_today }}</span>
                    <span class="text-red-400">✗ Cancelled: {{ stats.cancelled }}</span>
                    <span class="text-purple-400">🔑 Checked In: {{ stats.checked_in }}</span>
                </div>
            </div>
        </div>

        <!-- ── Room Status + Housekeeping + Maintenance ── -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Room Status -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-kotel-yellow mb-3 uppercase tracking-wider">🛏 Room Status</h3>
                <div class="space-y-2.5">
                    <div v-for="(item, i) in roomStatusBars" :key="i">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-kotel-text-secondary">{{ item.label }}</span>
                            <span class="text-white font-medium">{{ item.value }} <span class="text-kotel-text-secondary">({{ pct(item.value, stats.total_rooms) }}%)</span></span>
                        </div>
                        <div class="h-1.5 bg-kotel-black/50 rounded-full overflow-hidden">
                            <div :class="['h-1.5 rounded-full transition-all', item.color]" :style="{ width: pct(item.value, stats.total_rooms) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-kotel-yellow/10 grid grid-cols-2 gap-2 text-xs">
                    <span class="text-green-400">🧹 Clean: {{ stats.clean_rooms }}</span>
                    <span class="text-red-400">🪣 Dirty: {{ stats.dirty_rooms }}</span>
                </div>
            </div>

            <!-- Housekeeping -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-kotel-yellow mb-3 uppercase tracking-wider">🧹 Housekeeping Tasks</h3>
                <div class="space-y-2.5">
                    <div v-for="(item, i) in hkBars" :key="i">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-kotel-text-secondary">{{ item.label }}</span>
                            <span class="text-white font-medium">{{ item.value }} <span class="text-kotel-text-secondary">({{ pct(item.value, stats.hk_total) }}%)</span></span>
                        </div>
                        <div class="h-1.5 bg-kotel-black/50 rounded-full overflow-hidden">
                            <div :class="['h-1.5 rounded-full transition-all', item.color]" :style="{ width: pct(item.value, stats.hk_total) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-kotel-text-secondary">{{ stats.hk_today }} tasks today · {{ stats.hk_total }} total</p>
            </div>

            <!-- Maintenance -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-kotel-yellow mb-3 uppercase tracking-wider">🔧 Maintenance</h3>
                <div class="space-y-2.5">
                    <div v-for="(item, i) in maintBars" :key="i">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-kotel-text-secondary">{{ item.label }}</span>
                            <span class="text-white font-medium">{{ item.value }} <span class="text-kotel-text-secondary">({{ pct(item.value, stats.maint_total) }}%)</span></span>
                        </div>
                        <div class="h-1.5 bg-kotel-black/50 rounded-full overflow-hidden">
                            <div :class="['h-1.5 rounded-full transition-all', item.color]" :style="{ width: pct(item.value, stats.maint_total) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-kotel-text-secondary">{{ stats.maint_total }} total requests</p>
            </div>
        </div>

        <!-- ── Monthly Trend Table ── -->
        <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5 mb-6">
            <h3 class="text-sm font-semibold text-kotel-yellow mb-4 uppercase tracking-wider">📊 Monthly Revenue vs Expenses (Last 6 Months)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-kotel-yellow/20">
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium">Month</th>
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium text-right">Revenue</th>
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium text-right">Expenses</th>
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium text-right">Net Profit</th>
                            <th class="pb-2 text-kotel-text-secondary font-medium w-32">Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in monthlyRevenue" :key="row.month"
                            class="border-b border-kotel-yellow/10 hover:bg-kotel-yellow/5 transition">
                            <td class="py-2 pr-4 text-white font-medium">{{ row.month }}</td>
                            <td class="py-2 pr-4 text-green-400 text-right font-mono">{{ fmt(row.revenue) }}</td>
                            <td class="py-2 pr-4 text-red-400 text-right font-mono">{{ fmt(row.expenses) }}</td>
                            <td class="py-2 pr-4 text-right font-mono font-semibold"
                                :class="row.profit >= 0 ? 'text-kotel-yellow' : 'text-red-400'">
                                {{ fmt(row.profit) }}
                            </td>
                            <td class="py-2 pl-1">
                                <div class="h-2 bg-kotel-black/50 rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full"
                                         :class="row.profit >= 0 ? 'bg-green-500' : 'bg-red-500'"
                                         :style="{ width: monthBarWidth(row) + '%' }"></div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!monthlyRevenue.length">
                            <td colspan="5" class="py-4 text-center text-kotel-text-secondary text-sm">No monthly data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Payments + Expenses By Category ── -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Payment Methods -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-kotel-yellow mb-4 uppercase tracking-wider">💳 Payments by Method</h3>
                <div v-if="paymentByMethod.length" class="space-y-3">
                    <div v-for="p in paymentByMethod" :key="p.method" class="flex items-center gap-3">
                        <div class="flex-1">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-white capitalize">{{ p.method.replace(/_/g, ' ') }}</span>
                                <span class="text-kotel-yellow font-mono">{{ fmt(p.total) }}</span>
                            </div>
                            <div class="h-2 bg-kotel-black/50 rounded-full overflow-hidden">
                                <div class="h-2 bg-kotel-yellow rounded-full"
                                     :style="{ width: paymentBarWidth(p) + '%' }"></div>
                            </div>
                        </div>
                        <span class="text-xs text-kotel-text-secondary w-12 text-right">{{ p.count }}x</span>
                    </div>
                </div>
                <p v-else class="text-kotel-text-secondary text-sm">No payment data</p>
            </div>

            <!-- Expenses by Category -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-kotel-yellow mb-4 uppercase tracking-wider">📂 Top Expense Categories</h3>
                <div v-if="expenseByCategory.length" class="space-y-3">
                    <div v-for="e in expenseByCategory" :key="e.category">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-white">{{ e.category }}</span>
                            <span class="text-red-400 font-mono">{{ fmt(e.total) }}</span>
                        </div>
                        <div class="h-2 bg-kotel-black/50 rounded-full overflow-hidden">
                            <div class="h-2 bg-red-500 rounded-full"
                                 :style="{ width: expenseBarWidth(e) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-kotel-text-secondary text-sm">No expense data</p>
            </div>
        </div>

        <!-- ── Staff by Role ── -->
        <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5 mb-6">
            <h3 class="text-sm font-semibold text-kotel-yellow mb-4 uppercase tracking-wider">👨‍💼 Staff by Role ({{ stats.total_staff }} total)</h3>
            <div class="flex flex-wrap gap-3">
                <div v-for="s in staffByRole" :key="s.role"
                     class="flex items-center gap-2 bg-kotel-black/40 border border-kotel-yellow/20 rounded-lg px-4 py-2">
                    <span class="text-kotel-yellow font-bold text-lg">{{ s.count }}</span>
                    <span class="text-kotel-text-secondary text-sm capitalize">{{ s.role.replace(/_/g, ' ') }}</span>
                </div>
                <div v-if="!staffByRole.length" class="text-kotel-text-secondary text-sm">No staff data</div>
            </div>
        </div>

        <!-- ── Recent Tables ── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <!-- Recent Reservations -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-kotel-yellow uppercase tracking-wider">🗓 Recent Reservations</h3>
                    <a :href="route('manager.reservations.index')" class="text-xs text-kotel-yellow hover:underline">View all →</a>
                </div>
                <div v-if="recentReservations.length" class="space-y-2">
                    <div v-for="r in recentReservations" :key="r.id"
                         class="flex items-center justify-between py-2 border-b border-kotel-yellow/10 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-white font-medium truncate">{{ r.guest_name }}</p>
                            <p class="text-xs text-kotel-text-secondary">{{ r.reservation_number }} · Rm {{ r.room_number }} · {{ r.nights ?? '?' }}n</p>
                        </div>
                        <div class="text-right ml-3 flex-shrink-0">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusColor(r.status)">{{ r.status }}</span>
                            <p class="text-xs text-kotel-yellow font-mono mt-0.5">{{ fmt(r.total_amount) }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-kotel-text-secondary text-sm">No recent reservations</p>
            </div>

            <!-- Recent Payments -->
            <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-kotel-yellow uppercase tracking-wider mb-4">💵 Recent Payments</h3>
                <div v-if="recentPayments.length" class="space-y-2">
                    <div v-for="p in recentPayments" :key="p.id"
                         class="flex items-center justify-between py-2 border-b border-kotel-yellow/10 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-white font-medium">{{ p.payment_number }}</p>
                            <p class="text-xs text-kotel-text-secondary capitalize">{{ p.method.replace(/_/g, ' ') }} · {{ p.date }}</p>
                        </div>
                        <div class="text-right ml-3 flex-shrink-0">
                            <p class="text-sm font-bold text-green-400 font-mono">{{ fmt(p.amount) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="p.status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-orange-500/20 text-orange-400'">
                                {{ p.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-kotel-text-secondary text-sm">No recent payments</p>
            </div>
        </div>

        <!-- Recent Guests -->
        <div class="bg-kotel-card border border-kotel-yellow/20 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-kotel-yellow uppercase tracking-wider">🧳 Recent Guests</h3>
                <a :href="route('manager.guests.index')" class="text-xs text-kotel-yellow hover:underline">View all →</a>
            </div>
            <div v-if="recentGuests.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-kotel-yellow/20">
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium">Name</th>
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium">Email</th>
                            <th class="pb-2 pr-4 text-kotel-text-secondary font-medium">Nationality</th>
                            <th class="pb-2 text-kotel-text-secondary font-medium text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="g in recentGuests" :key="g.id"
                            class="border-b border-kotel-yellow/10 hover:bg-kotel-yellow/5 transition">
                            <td class="py-2 pr-4 text-white font-medium">{{ g.name }}</td>
                            <td class="py-2 pr-4 text-kotel-text-secondary">{{ g.email || '—' }}</td>
                            <td class="py-2 pr-4 text-kotel-text-secondary">{{ g.nationality || '—' }}</td>
                            <td class="py-2 text-kotel-text-secondary text-right">{{ g.joined }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-kotel-text-secondary text-sm">No guest data</p>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency as moneyFormat, initializeCurrencySettings } from '@/Utils/currency.js'

const props = defineProps({
    user:               { type: Object },
    navigation:         { type: Array,  default: () => [] },
    stats:              { type: Object, default: () => ({
        revenue_this_month: 0, revenue_today: 0, net_profit: 0, net_profit_this_month: 0,
        occupancy_rate: 0, occupied_rooms: 0, total_rooms: 0, total_guests: 0, new_guests_this_month: 0,
        total_revenue: 0, revenue_this_year: 0, total_expenses: 0, expenses_this_month: 0,
        total_reservations: 0, checked_out: 0, checked_in: 0, cancelled: 0, confirmed_pending: 0,
        arriving_today: 0, departing_today: 0,
        available_rooms: 0, cleaning_rooms: 0, maintenance_rooms: 0, dirty_rooms: 0, clean_rooms: 0,
        hk_total: 0, hk_pending: 0, hk_in_progress: 0, hk_completed: 0, hk_today: 0,
        maint_total: 0, maint_open: 0, maint_in_progress: 0, maint_done: 0, total_staff: 0,
    }) },
    monthlyRevenue:     { type: Array,  default: () => [] },
    paymentByMethod:    { type: Array,  default: () => [] },
    expenseByCategory:  { type: Array,  default: () => [] },
    staffByRole:        { type: Array,  default: () => [] },
    recentReservations: { type: Array,  default: () => [] },
    recentPayments:     { type: Array,  default: () => [] },
    recentGuests:       { type: Array,  default: () => [] },
})

onMounted(() => initializeCurrencySettings())

const fmt = (v) => moneyFormat(v || 0)
const pct = (val, total) => total > 0 ? Math.min(100, Math.round((val / total) * 100)) : 0

// Status bars data
const roomStatusBars = computed(() => [
    { label: 'Available',    value: props.stats.available_rooms,    color: 'bg-green-500' },
    { label: 'Occupied',     value: props.stats.occupied_rooms,     color: 'bg-blue-500' },
    { label: 'Cleaning',     value: props.stats.cleaning_rooms,     color: 'bg-yellow-500' },
    { label: 'Maintenance',  value: props.stats.maintenance_rooms,  color: 'bg-red-500' },
])

const hkBars = computed(() => [
    { label: 'Pending / Assigned', value: props.stats.hk_pending,     color: 'bg-orange-500' },
    { label: 'In Progress',        value: props.stats.hk_in_progress,  color: 'bg-blue-500' },
    { label: 'Completed',          value: props.stats.hk_completed,    color: 'bg-green-500' },
])

const maintBars = computed(() => [
    { label: 'Open / Pending', value: props.stats.maint_open,        color: 'bg-red-500' },
    { label: 'In Progress',    value: props.stats.maint_in_progress,  color: 'bg-yellow-500' },
    { label: 'Completed',      value: props.stats.maint_done,         color: 'bg-green-500' },
])

// Bar widths
const maxPayment = computed(() => Math.max(...props.paymentByMethod.map(p => p.total), 1))
const maxExpense = computed(() => Math.max(...props.expenseByCategory.map(e => e.total), 1))
const maxMonthly = computed(() => Math.max(...props.monthlyRevenue.map(r => Math.max(r.revenue, r.expenses)), 1))

const paymentBarWidth = (p) => Math.round((p.total / maxPayment.value) * 100)
const expenseBarWidth = (e) => Math.round((e.total / maxExpense.value) * 100)
const monthBarWidth   = (row) => Math.round((Math.max(row.revenue, row.expenses) / maxMonthly.value) * 100)

const statusColor = (status) => {
    const map = {
        checked_in:  'bg-blue-500/20 text-blue-400',
        checked_out: 'bg-green-500/20 text-green-400',
        confirmed:   'bg-kotel-yellow/20 text-kotel-yellow',
        pending:     'bg-orange-500/20 text-orange-400',
        cancelled:   'bg-red-500/20 text-red-400',
        canceled:    'bg-red-500/20 text-red-400',
        no_show:     'bg-gray-500/20 text-gray-400',
    }
    return map[status] ?? 'bg-gray-500/20 text-gray-400'
}
</script>
