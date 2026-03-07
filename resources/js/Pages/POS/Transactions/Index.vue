<template>
    <DashboardLayout title="Transactions" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Transactions</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">POS payment transactions from cash drawer sessions</p>
                </div>
                <button @click="exportTransactions"
                        class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                        :style="{ backgroundColor: themeColors.success }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'cc'"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                    📥 Export
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div v-for="stat in stats" :key="stat.label"
                     class="rounded-lg p-4 border shadow-sm flex items-center gap-3"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0"
                         :style="{ backgroundColor: stat.color + '20' }">{{ stat.icon }}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium truncate" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-lg font-bold mt-0.5" :style="{ color: stat.color }">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border p-4 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Sale #, description..."
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</label>
                        <select v-model="filters.payment_method"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile">Mobile</option>
                            <option value="room_charge">Room Charge</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Type</label>
                        <select v-model="filters.type"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Types</option>
                            <option v-for="t in uniqueTypes" :key="t" :value="t">{{ formatType(t) }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">From Date</label>
                        <DatePicker v-model="filters.date_from" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">To Date</label>
                        <DatePicker v-model="filters.date_to" />
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs" :style="{ color: themeColors.textSecondary }">
                        Showing {{ filteredTransactions.length }} of {{ transactions.length }} transactions
                        <span class="ml-2 font-semibold" :style="{ color: themeColors.textPrimary }">
                            · Total: {{ formatCurrency(filteredTotal) }}
                        </span>
                    </span>
                    <button @click="clearFilters" class="text-xs px-3 py-1 rounded"
                            :style="{ color: themeColors.textSecondary, backgroundColor: themeColors.border + '60' }">
                        Clear filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg border shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Sale</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Method</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Cashier</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="txn in paginatedTransactions" :key="txn.id"
                                :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }">
                                <!-- ID -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs font-mono" :style="{ color: themeColors.textTertiary }">#{{ txn.id }}</span>
                                </td>
                                <!-- Sale -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span v-if="txn.sale_number" class="text-sm font-mono font-medium"
                                          :style="{ color: themeColors.primary }">
                                        {{ txn.sale_number }}
                                    </span>
                                    <span v-else class="text-xs" :style="{ color: themeColors.textTertiary }">—</span>
                                </td>
                                <!-- Type -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium"
                                          :style="typeStyle(txn.type)">
                                        {{ formatType(txn.type) }}
                                    </span>
                                </td>
                                <!-- Method -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm"
                                          :style="{ color: themeColors.textPrimary }">
                                        <span>{{ methodIcon(txn.payment_method) }}</span>
                                        <span>{{ formatMethod(txn.payment_method) }}</span>
                                    </span>
                                </td>
                                <!-- Amount -->
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold"
                                          :style="{ color: txn.amount >= 0 ? themeColors.success : themeColors.danger }">
                                        {{ txn.amount >= 0 ? '+' : '' }}{{ formatCurrency(txn.amount) }}
                                    </span>
                                </td>
                                <!-- Description -->
                                <td class="px-4 py-3">
                                    <span class="text-sm" :style="{ color: themeColors.textSecondary }">
                                        {{ txn.description || '—' }}
                                    </span>
                                </td>
                                <!-- Cashier -->
                                <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                    {{ txn.cashier || 'System' }}
                                </td>
                                <!-- Date -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ formatDate(txn.created_at) }}</div>
                                    <div class="text-xs" :style="{ color: themeColors.textTertiary }">{{ formatTime(txn.created_at) }}</div>
                                </td>
                            </tr>
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="text-3xl mb-2">💳</div>
                                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">No transactions found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t"
                     :style="{ borderColor: themeColors.border }">
                    <span class="text-xs" :style="{ color: themeColors.textSecondary }">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <div class="flex gap-2">
                        <button :disabled="currentPage === 1" @click="currentPage--"
                                class="px-3 py-1 rounded text-xs disabled:opacity-40"
                                :style="{ backgroundColor: themeColors.border + '80', color: themeColors.textPrimary }">
                            ← Prev
                        </button>
                        <button :disabled="currentPage === totalPages" @click="currentPage++"
                                class="px-3 py-1 rounded text-xs disabled:opacity-40"
                                :style="{ backgroundColor: themeColors.border + '80', color: themeColors.textPrimary }">
                            Next →
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary:  `var(--kotel-text-tertiary)`,
    primary:       `var(--kotel-primary)`,
    secondary:     `var(--kotel-secondary)`,
    success:       `var(--kotel-success)`,
    warning:       `var(--kotel-warning)`,
    danger:        `var(--kotel-danger)`,
    info:          `var(--kotel-info)`,
}))
loadTheme()

const props = defineProps({
    user:         Object,
    navigation:   Array,
    transactions: { type: Array, default: () => [] },
})

const filters     = ref({ search: '', payment_method: '', type: '', date_from: '', date_to: '' })
const currentPage = ref(1)
const perPage     = 25

watch(filters, () => { currentPage.value = 1 }, { deep: true })

const clearFilters = () => {
    filters.value = { search: '', payment_method: '', type: '', date_from: '', date_to: '' }
}

const uniqueTypes = computed(() => [...new Set(props.transactions.map(t => t.type).filter(Boolean))])

const filteredTransactions = computed(() => {
    let list = props.transactions

    if (filters.value.search) {
        const q = filters.value.search.toLowerCase()
        list = list.filter(t =>
            (t.sale_number || '').toLowerCase().includes(q) ||
            (t.description || '').toLowerCase().includes(q) ||
            (t.cashier || '').toLowerCase().includes(q)
        )
    }

    if (filters.value.payment_method) {
        list = list.filter(t => t.payment_method === filters.value.payment_method)
    }

    if (filters.value.type) {
        list = list.filter(t => t.type === filters.value.type)
    }

    if (filters.value.date_from) {
        const from = new Date(filters.value.date_from)
        list = list.filter(t => new Date(t.created_at) >= from)
    }

    if (filters.value.date_to) {
        const to = new Date(filters.value.date_to)
        to.setHours(23, 59, 59, 999)
        list = list.filter(t => new Date(t.created_at) <= to)
    }

    return list
})

const filteredTotal = computed(() =>
    filteredTransactions.value.reduce((sum, t) => sum + (t.amount || 0), 0)
)

const totalPages = computed(() => Math.max(1, Math.ceil(filteredTransactions.value.length / perPage)))

const paginatedTransactions = computed(() => {
    const start = (currentPage.value - 1) * perPage
    return filteredTransactions.value.slice(start, start + perPage)
})

// Stats
const stats = computed(() => {
    const all    = props.transactions
    const cash   = all.filter(t => t.payment_method === 'cash').reduce((s, t) => s + t.amount, 0)
    const card   = all.filter(t => t.payment_method === 'card').reduce((s, t) => s + t.amount, 0)
    const mobile = all.filter(t => t.payment_method === 'mobile').reduce((s, t) => s + t.amount, 0)
    const room   = all.filter(t => t.payment_method === 'room_charge').reduce((s, t) => s + t.amount, 0)
    const total  = all.reduce((s, t) => s + t.amount, 0)

    return [
        { label: 'Total',        value: formatCurrency(total),  icon: '💰', color: `var(--kotel-primary)` },
        { label: 'Cash',         value: formatCurrency(cash),   icon: '💵', color: `var(--kotel-success)` },
        { label: 'Card',         value: formatCurrency(card),   icon: '💳', color: `var(--kotel-info)`    },
        { label: 'Mobile',       value: formatCurrency(mobile), icon: '📱', color: `var(--kotel-warning)` },
        { label: 'Room Charges', value: formatCurrency(room),   icon: '🏨', color: `var(--kotel-secondary)` },
    ]
})

// Formatters
const formatCurrency = (v) =>
    Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—'
const formatTime = (d) => d ? new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''

const formatMethod = (m) => ({
    cash:          'Cash',
    card:          'Card',
    bank_transfer: 'Bank Transfer',
    mobile:        'Mobile',
    room_charge:   'Room Charge',
}[m] || m || '—')

const methodIcon = (m) => ({
    cash:          '💵',
    card:          '💳',
    bank_transfer: '🏦',
    mobile:        '📱',
    room_charge:   '🏨',
}[m] || '💰')

const formatType = (t) => {
    if (!t) return '—'
    return t.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const typeStyle = (type) => {
    const map = {
        sale:       { backgroundColor: 'var(--kotel-success)20', color: 'var(--kotel-success)' },
        refund:     { backgroundColor: 'var(--kotel-danger)20',  color: 'var(--kotel-danger)'  },
        void:       { backgroundColor: 'var(--kotel-warning)20', color: 'var(--kotel-warning)' },
        adjustment: { backgroundColor: 'var(--kotel-info)20',    color: 'var(--kotel-info)'    },
    }
    return map[type] || { backgroundColor: 'var(--kotel-border)', color: 'var(--kotel-text-secondary)' }
}

const exportTransactions = () => {
    const headers = ['ID', 'Sale #', 'Type', 'Payment Method', 'Amount', 'Description', 'Cashier', 'Date', 'Time']
    const rows = filteredTransactions.value.map(t => [
        t.id,
        t.sale_number || '',
        formatType(t.type),
        formatMethod(t.payment_method),
        t.amount,
        t.description || '',
        t.cashier || 'System',
        formatDate(t.created_at),
        formatTime(t.created_at),
    ])
    const csv = [headers, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n')
    const link = document.createElement('a')
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }))
    link.download = `transactions-${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
}
</script>
