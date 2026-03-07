<template>
    <DashboardLayout title="Orders" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Orders</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">All POS orders — filter by payment status or type</p>
                </div>
                <button @click="exportOrders"
                        class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                        :style="{ backgroundColor: themeColors.success }">
                    📥 Export
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="stat in stats" :key="stat.label"
                     class="rounded-lg p-5 border shadow-sm flex items-center gap-4"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl flex-shrink-0"
                         :style="{ backgroundColor: stat.color + '20' }">{{ stat.icon }}</div>
                    <div>
                        <p class="text-xs font-medium" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-xl font-bold mt-0.5" :style="{ color: stat.color }">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border p-4 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Sale #, customer, room..."
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Status</label>
                        <select v-model="filters.payment_status"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Statuses</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partially Paid</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Order Type</label>
                        <select v-model="filters.type"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Types</option>
                            <option value="room">Room Charges</option>
                            <option value="walk_in">Walk-in</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date</label>
                        <DatePicker v-model="filters.date" />
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs" :style="{ color: themeColors.textSecondary }">
                        Showing {{ filteredOrders.length }} of {{ orders.length }} orders
                    </span>
                    <button @click="clearFilters" class="text-xs px-3 py-1 rounded"
                            :style="{ color: themeColors.textSecondary, backgroundColor: themeColors.border + '60' }">
                        Clear filters
                    </button>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="rounded-lg border shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }">
                                <th class="px-4 py-3 w-8"></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Order #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Customer / Room</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Items</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Payment</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Cashier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="order in paginatedOrders" :key="order.id">
                                <tr class="transition-colors cursor-pointer"
                                    :style="{ borderBottomWidth: expandedId === order.id ? '0' : '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }"
                                    @click="toggleExpand(order.id)">
                                    <td class="px-4 py-3 text-center text-xs" :style="{ color: themeColors.textTertiary }">
                                        {{ expandedId === order.id ? '▲' : '▼' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm font-mono font-medium" :style="{ color: themeColors.primary }">
                                            {{ order.sale_number || '#' + order.id }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ order.customer_name || 'Walk-in' }}
                                        </div>
                                        <div v-if="order.room_number" class="text-xs" :style="{ color: themeColors.textTertiary }">
                                            🚪 Room {{ order.room_number }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              :style="{ backgroundColor: themeColors.primary + '18', color: themeColors.primary }">
                                            {{ order.items_count }} item{{ order.items_count !== 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm font-bold" :style="{ color: themeColors.textPrimary }">
                                            {{ formatCurrency(order.total_amount) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :style="paymentStatusStyle(order.payment_status)">
                                            {{ paymentStatusLabel(order.payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span v-if="order.is_charged_to_room"
                                              class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium"
                                              :style="{ backgroundColor: themeColors.warning + '20', color: themeColors.warning }">
                                            🏨 Room Charge
                                        </span>
                                        <span v-else class="text-xs" :style="{ color: themeColors.textTertiary }">Walk-in</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs" :style="{ color: themeColors.textSecondary }">
                                        <div>{{ formatDate(order.sale_date || order.created_at) }}</div>
                                        <div :style="{ color: themeColors.textTertiary }">{{ formatTime(order.sale_date || order.created_at) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs" :style="{ color: themeColors.textSecondary }">
                                        {{ order.user || 'System' }}
                                    </td>
                                </tr>
                                <!-- Expanded items -->
                                <tr v-if="expandedId === order.id" :key="'exp-' + order.id">
                                    <td colspan="9" class="px-6 pb-4"
                                        :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }">
                                        <div class="rounded-lg p-4 mt-1" :style="{ backgroundColor: themeColors.background }">
                                            <p class="text-xs font-semibold mb-3 uppercase tracking-wider"
                                               :style="{ color: themeColors.textTertiary }">Order Items</p>
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }">
                                                        <th class="text-left pb-2 text-xs font-medium" :style="{ color: themeColors.textSecondary }">Product</th>
                                                        <th class="text-center pb-2 text-xs font-medium" :style="{ color: themeColors.textSecondary }">Qty</th>
                                                        <th class="text-right pb-2 text-xs font-medium" :style="{ color: themeColors.textSecondary }">Unit Price</th>
                                                        <th class="text-right pb-2 text-xs font-medium" :style="{ color: themeColors.textSecondary }">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="item in order.items" :key="item.id"
                                                        :style="{ borderTopWidth: '1px', borderTopStyle: 'solid', borderColor: themeColors.border }">
                                                        <td class="py-1.5" :style="{ color: themeColors.textPrimary }">{{ item.name }}</td>
                                                        <td class="py-1.5 text-center" :style="{ color: themeColors.textSecondary }">{{ item.quantity }}</td>
                                                        <td class="py-1.5 text-right" :style="{ color: themeColors.textSecondary }">{{ formatCurrency(item.unit_price) }}</td>
                                                        <td class="py-1.5 text-right font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.total) }}</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr :style="{ borderTopWidth: '2px', borderTopStyle: 'solid', borderColor: themeColors.border }">
                                                        <td colspan="3" class="pt-2 text-right text-xs font-semibold pr-4"
                                                            :style="{ color: themeColors.textSecondary }">Total</td>
                                                        <td class="pt-2 text-right font-bold" :style="{ color: themeColors.textPrimary }">
                                                            {{ formatCurrency(order.total_amount) }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                            <p v-if="order.notes" class="mt-3 text-xs italic" :style="{ color: themeColors.textTertiary }">
                                                📝 {{ order.notes }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="9" class="px-4 py-12 text-center">
                                    <div class="text-3xl mb-2">📋</div>
                                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">No orders match your filters</p>
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
    user:       Object,
    navigation: Array,
    orders:     { type: Array, default: () => [] },
})

const filters    = ref({ search: '', payment_status: '', type: '', date: '' })
const currentPage = ref(1)
const perPage    = 20
const expandedId = ref(null)

watch(filters, () => { currentPage.value = 1 }, { deep: true })

const clearFilters = () => {
    filters.value = { search: '', payment_status: '', type: '', date: '' }
}

const filteredOrders = computed(() => {
    let list = props.orders

    if (filters.value.search) {
        const q = filters.value.search.toLowerCase()
        list = list.filter(o =>
            (o.sale_number || '').toLowerCase().includes(q) ||
            (o.customer_name || '').toLowerCase().includes(q) ||
            String(o.room_number || '').toLowerCase().includes(q)
        )
    }

    if (filters.value.payment_status) {
        list = list.filter(o => (o.payment_status || 'unpaid') === filters.value.payment_status)
    }

    if (filters.value.type === 'room') {
        list = list.filter(o => o.is_charged_to_room)
    } else if (filters.value.type === 'walk_in') {
        list = list.filter(o => !o.is_charged_to_room)
    }

    if (filters.value.date) {
        list = list.filter(o => {
            const d = new Date(o.sale_date || o.created_at)
            return d.toISOString().slice(0, 10) === filters.value.date
        })
    }

    return list
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredOrders.value.length / perPage)))

const paginatedOrders = computed(() => {
    const start = (currentPage.value - 1) * perPage
    return filteredOrders.value.slice(start, start + perPage)
})

const stats = computed(() => {
    const all     = props.orders
    const unpaid  = all.filter(o => (o.payment_status || 'unpaid') === 'unpaid')
    const partial = all.filter(o => o.payment_status === 'partial')
    const room    = all.filter(o => o.is_charged_to_room)
    return [
        { label: 'Total Orders', value: all.length,     icon: '📋', color: `var(--kotel-primary)` },
        { label: 'Unpaid',       value: unpaid.length,  icon: '⏳', color: `var(--kotel-danger)`  },
        { label: 'Partial',      value: partial.length, icon: '💰', color: `var(--kotel-warning)` },
        { label: 'Room Charges', value: room.length,    icon: '🏨', color: `var(--kotel-info)`    },
    ]
})

const toggleExpand = (id) => {
    expandedId.value = expandedId.value === id ? null : id
}

const paymentStatusLabel = (status) => ({ paid: 'Paid', partial: 'Partial', unpaid: 'Unpaid' }[status] || 'Unpaid')

const paymentStatusStyle = (status) => ({
    paid:    { backgroundColor: 'var(--kotel-success)20', color: 'var(--kotel-success)' },
    partial: { backgroundColor: 'var(--kotel-warning)20', color: 'var(--kotel-warning)' },
    unpaid:  { backgroundColor: 'var(--kotel-danger)20',  color: 'var(--kotel-danger)'  },
}[status] || { backgroundColor: 'var(--kotel-danger)20', color: 'var(--kotel-danger)' })

const formatCurrency = (value) =>
    Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—'
const formatTime = (d) => d ? new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''

const exportOrders = () => {
    const headers = ['Order #', 'Customer', 'Room', 'Items', 'Total', 'Payment Status', 'Type', 'Date', 'Cashier']
    const rows = filteredOrders.value.map(o => [
        o.sale_number || o.id,
        o.customer_name || 'Walk-in',
        o.room_number || '',
        o.items_count,
        o.total_amount,
        paymentStatusLabel(o.payment_status),
        o.is_charged_to_room ? 'Room Charge' : 'Walk-in',
        formatDate(o.sale_date || o.created_at),
        o.user || 'System',
    ])
    const csv = [headers, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n')
    const link = document.createElement('a')
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }))
    link.download = `orders-${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
}
</script>
