<template>
    <DashboardLayout title="Transaction Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-kotel-bg-card shadow-kotel-card rounded-lg p-6 mb-8 border border-kotel-border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-text-primary">Transaction Management</h1>
                    <p class="text-kotel-text-secondary mt-2">Monitor and manage all financial transactions.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportTransactions"
                            class="bg-kotel-purple text-white px-4 py-2 rounded-md hover:bg-kotel-purple-dark">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Revenue Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-kotel-bg-card rounded-lg shadow-kotel-card p-6 border border-kotel-border">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-kotel-green mr-4" />
                    <div>
                        <p class="text-sm font-medium text-kotel-text-secondary">Today's Revenue</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ formatCurrency(transactionStats.todayRevenue) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-kotel-bg-card rounded-lg shadow-kotel-card p-6 border border-kotel-border">
                <div class="flex items-center">
                    <CreditCardIcon class="h-8 w-8 text-kotel-sky-blue mr-4" />
                    <div>
                        <p class="text-sm font-medium text-kotel-text-secondary">Total Revenue</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ formatCurrency(transactionStats.totalRevenue) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-kotel-bg-card rounded-lg shadow-kotel-card p-6 border border-kotel-border">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-kotel-orange mr-4" />
                    <div>
                        <p class="text-sm font-medium text-kotel-text-secondary">Pending</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ transactionStats.pending }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-kotel-bg-card rounded-lg shadow-kotel-card p-6 border border-kotel-border">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 text-kotel-red mr-4" />
                    <div>
                        <p class="text-sm font-medium text-kotel-text-secondary">Failed</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ transactionStats.failed }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Center Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Today's Revenue by Center -->
            <div class="bg-kotel-bg-card rounded-lg shadow-kotel-card p-6 border border-kotel-border">
                <h3 class="text-lg font-medium text-kotel-text-primary mb-4">Today's Revenue by Center</h3>
                <div class="space-y-3">
                    <div v-for="(data, key) in transactionStats.todayByRevenueCenter" :key="key"
                         class="flex items-center justify-between p-3 rounded-md bg-kotel-gray">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-3"
                                  :class="{
                                    'bg-kotel-purple': key === 'room',
                                    'bg-kotel-green': key === 'food_beverage' || key === 'restaurant',
                                    'bg-kotel-blue': key === 'bar',
                                    'bg-kotel-orange': key === 'room_service',
                                    'bg-kotel-yellow': key === 'laundry',
                                    'bg-kotel-cyan': key === 'parking',
                                    'bg-kotel-pink': key === 'spa',
                                    'bg-kotel-indigo': key === 'iptv',
                                    'bg-kotel-gray': key === 'minibar' || key === 'other'
                                  }"></span>
                            <span class="text-sm text-kotel-text-primary">{{ data.label }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-kotel-text-primary">{{ formatCurrency(data.amount) }}</p>
                            <p class="text-xs text-kotel-text-tertiary">{{ data.count }} transactions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Time Revenue by Center -->
            <div class="bg-kotel-bg-card rounded-lg shadow-kotel-card p-6 border border-kotel-border">
                <h3 class="text-lg font-medium text-kotel-text-primary mb-4">All Time Revenue by Center</h3>
                <div class="space-y-3">
                    <div v-for="(data, key) in transactionStats.allTimeByRevenueCenter" :key="key"
                         class="flex items-center justify-between p-3 rounded-md bg-kotel-gray">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-3"
                                  :class="{
                                    'bg-kotel-purple': key === 'room',
                                    'bg-kotel-green': key === 'food_beverage' || key === 'restaurant',
                                    'bg-kotel-blue': key === 'bar',
                                    'bg-kotel-orange': key === 'room_service',
                                    'bg-kotel-yellow': key === 'laundry',
                                    'bg-kotel-cyan': key === 'parking',
                                    'bg-kotel-pink': key === 'spa',
                                    'bg-kotel-indigo': key === 'iptv',
                                    'bg-kotel-gray': key === 'minibar' || key === 'other'
                                  }"></span>
                            <span class="text-sm text-kotel-text-primary">{{ data.label }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-kotel-text-primary">{{ formatCurrency(data.amount) }}</p>
                            <p class="text-xs text-kotel-text-tertiary">{{ data.count }} transactions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-kotel-bg-card shadow-kotel-card rounded-lg p-6 mb-8 border border-kotel-border">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Search</label>
                    <input type="text" v-model="searchQuery" placeholder="Search transactions..."
                           class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                </div>
                <div>
                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Type</label>
                    <select v-model="selectedType"
                            class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        <option value="">All Types</option>
                        <option value="payment">Payment</option>
                        <option value="refund">Refund</option>
                        <option value="deposit">Deposit</option>
                        <option value="fee">Fee</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Status</label>
                    <select v-model="selectedStatus"
                            class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Payment Method</label>
                    <select v-model="selectedPaymentMethod"
                            class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        <option value="">All Methods</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters"
                            class="w-full bg-kotel-darker text-kotel-text-secondary px-4 py-2 rounded-md hover:bg-kotel-dark hover:text-kotel-yellow">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-kotel-bg-card shadow-kotel-card rounded-lg overflow-hidden border border-kotel-border">
            <div class="px-6 py-4 border-b border-kotel-border">
                <h3 class="text-lg font-medium text-kotel-text-primary">All Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-kotel-border">
                    <thead class="bg-kotel-gray">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Transaction ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Guest/Reference
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Payment Method
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-secondary uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-kotel-bg-card divide-y divide-kotel-border">
                        <tr v-for="transaction in filteredTransactions" :key="transaction.id" class="hover:bg-kotel-gray">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-kotel-text-primary">
                                {{ transaction.transaction_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-kotel-text-primary">{{ transaction.guest_name }}</div>
                                <div class="text-sm text-kotel-text-tertiary">{{ transaction.reference }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-text-primary">
                                {{ transaction.user_name || '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeColor(transaction.type)">
                                    {{ formatType(transaction.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :class="transaction.type === 'refund' ? 'text-kotel-red' : 'text-kotel-green'">
                                {{ transaction.type === 'refund' ? '-' : '+' }}{{ formatCurrency(transaction.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-text-primary">
                                {{ formatPaymentMethod(transaction.payment_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(transaction.status)">
                                    {{ formatStatus(transaction.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-text-tertiary">
                                {{ formatDateTime(transaction.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewTransaction(transaction)" class="text-kotel-sky-blue hover:text-kotel-yellow">View</button>
                                    <button v-if="transaction.type === 'payment' && transaction.status === 'pending'"
                                            @click="processTransaction(transaction)"
                                            class="text-kotel-green hover:text-kotel-yellow">Process</button>
                                    <button v-if="transaction.type === 'payment' && transaction.status === 'completed'"
                                            @click="refundTransaction(transaction)"
                                            class="text-kotel-red hover:text-kotel-yellow">Refund</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transaction Detail Modal -->
        <Teleport to="body">
            <div v-if="selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @click.self="closeModal">
                <div class="absolute inset-0 bg-black bg-opacity-60" @click="closeModal"></div>
                <div class="relative w-full max-w-lg rounded-xl shadow-2xl z-10 bg-kotel-bg-card border border-kotel-border">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-kotel-border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg bg-kotel-purple bg-opacity-20 text-kotel-purple font-bold">💳</div>
                            <div>
                                <h2 class="text-lg font-bold text-kotel-text-primary">Transaction Details</h2>
                                <p class="text-xs font-mono text-kotel-text-tertiary">{{ selectedTransaction.transaction_id }}</p>
                            </div>
                        </div>
                        <button @click="closeModal"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xl font-bold text-kotel-text-secondary hover:bg-kotel-red hover:text-white transition-colors">×</button>
                    </div>
                    <!-- Body -->
                    <div class="px-6 py-5 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg p-3 bg-kotel-gray">
                                <p class="text-xs font-medium mb-1 text-kotel-text-secondary">Guest / Reference</p>
                                <p class="text-sm font-semibold text-kotel-text-primary">{{ selectedTransaction.guest_name || '—' }}</p>
                                <p class="text-xs text-kotel-text-tertiary">{{ selectedTransaction.reference || '—' }}</p>
                            </div>
                            <div class="rounded-lg p-3 bg-kotel-gray">
                                <p class="text-xs font-medium mb-1 text-kotel-text-secondary">Amount</p>
                                <p class="text-lg font-bold"
                                   :class="selectedTransaction.type === 'refund' ? 'text-kotel-red' : 'text-kotel-green'">
                                    {{ selectedTransaction.type === 'refund' ? '-' : '+' }}{{ formatCurrency(selectedTransaction.amount || 0) }}
                                </p>
                            </div>
                            <div class="rounded-lg p-3 bg-kotel-gray">
                                <p class="text-xs font-medium mb-1 text-kotel-text-secondary">Type</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeColor(selectedTransaction.type)">
                                    {{ formatType(selectedTransaction.type) }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3 bg-kotel-gray">
                                <p class="text-xs font-medium mb-1 text-kotel-text-secondary">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(selectedTransaction.status)">
                                    {{ formatStatus(selectedTransaction.status) }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3 bg-kotel-gray">
                                <p class="text-xs font-medium mb-1 text-kotel-text-secondary">Payment Method</p>
                                <p class="text-sm font-semibold text-kotel-text-primary">{{ formatPaymentMethod(selectedTransaction.payment_method) }}</p>
                            </div>
                            <div class="rounded-lg p-3 bg-kotel-gray">
                                <p class="text-xs font-medium mb-1 text-kotel-text-secondary">Date & Time</p>
                                <p class="text-sm text-kotel-text-primary">{{ formatDateTime(selectedTransaction.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-kotel-border">
                        <button @click="closeModal"
                                class="px-4 py-2 rounded-lg text-sm font-medium bg-kotel-gray text-kotel-text-secondary border border-kotel-border hover:text-kotel-text-primary transition-colors">
                            Close
                        </button>
                        <button v-if="selectedTransaction.type === 'payment' && selectedTransaction.status === 'pending'"
                                @click="processTransaction(selectedTransaction)"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-kotel-green hover:opacity-90 transition-opacity">
                            ✓ Mark as Processed
                        </button>
                        <button v-if="selectedTransaction.type === 'payment' && selectedTransaction.status === 'completed'"
                                @click="refundTransaction(selectedTransaction)"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-kotel-red hover:opacity-90 transition-opacity">
                            ↩ Refund
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
    ClockIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    settings: Object,
    transactions: Array,
    transactionStats: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const formatCurrency = (amount) => {
    const currency = props.settings?.currency || 'USD'
    return formatCurrencyUtil(amount, currency)
}

const searchQuery = ref('')
const selectedType = ref('')
const selectedStatus = ref('')
const selectedPaymentMethod = ref('')
const selectedTransaction = ref(null)

const closeModal = () => { selectedTransaction.value = null }

const filteredTransactions = computed(() => {
    if (!props.transactions) return []
    return props.transactions.filter(transaction => {
        const matchesSearch = !searchQuery.value ||
            transaction.guest_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            transaction.transaction_id?.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesType = !selectedType.value || transaction.type === selectedType.value
        const matchesStatus = !selectedStatus.value || transaction.status === selectedStatus.value
        const matchesPaymentMethod = !selectedPaymentMethod.value || transaction.payment_method === selectedPaymentMethod.value

        return matchesSearch && matchesType && matchesStatus && matchesPaymentMethod
    })
})

const getTypeColor = (type) => {
    const colors = {
        payment:      'bg-green-600 text-white',
        refund:       'bg-red-600 text-white',
        deposit:      'bg-blue-600 text-white',
        fee:          'bg-yellow-500 text-white',
        folio_charge: 'bg-purple-600 text-white',
        room_charge:  'bg-indigo-600 text-white',
        charge:       'bg-indigo-600 text-white',
        sale:         'bg-teal-600 text-white',
        expense:      'bg-orange-600 text-white',
        transfer:     'bg-cyan-600 text-white',
        adjustment:   'bg-pink-600 text-white',
    }
    return colors[type?.toLowerCase()] || 'bg-slate-500 text-white'
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-600 text-white',
        complete:  'bg-green-600 text-white',
        paid:      'bg-green-600 text-white',
        active:    'bg-blue-600 text-white',
        pending:   'bg-yellow-500 text-white',
        failed:    'bg-red-600 text-white',
        cancelled: 'bg-slate-500 text-white',
        canceled:  'bg-slate-500 text-white',
        refunded:  'bg-red-500 text-white',
        voided:    'bg-slate-500 text-white',
        draft:     'bg-slate-500 text-white',
        overdue:   'bg-orange-600 text-white',
    }
    return colors[status?.toLowerCase()] || 'bg-slate-500 text-white'
}

const formatType = (type) => {
    if (!type) return 'Unknown'
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatPaymentMethod = (method) => {
    if (!method) return '—'
    return method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString()
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedType.value = ''
    selectedStatus.value = ''
    selectedPaymentMethod.value = ''
}

const exportTransactions = () => {
    window.location.href = route('admin.transactions.export', { format: 'csv' })
}

const viewTransaction = (transaction) => {
    selectedTransaction.value = transaction
}

const processTransaction = (transaction) => {
    if (!confirm(`Mark transaction ${transaction.transaction_id} as processed?`)) return
    router.post(route('admin.transactions.process', { payment: transaction.source_id ?? transaction.id }), {}, {
        preserveScroll: true,
        onSuccess: () => { closeModal() }
    })
}

const refundTransaction = (transaction) => {
    if (!confirm(`Initiate refund for transaction ${transaction.transaction_id}?`)) return
    router.post(route('admin.transactions.refund', { payment: transaction.source_id ?? transaction.id }), {}, {
        preserveScroll: true,
        onSuccess: () => { closeModal() }
    })
}
</script>
