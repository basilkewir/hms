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
                                    <button v-if="transaction.status === 'pending'"
                                            @click="processTransaction(transaction)"
                                            class="text-kotel-green hover:text-kotel-yellow">Process</button>
                                    <button v-if="transaction.status === 'completed' && transaction.type === 'payment'"
                                            @click="refundTransaction(transaction)"
                                            class="text-kotel-red hover:text-kotel-yellow">Refund</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
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
        payment: 'bg-green-100 text-green-800',
        refund: 'bg-red-100 text-red-800',
        deposit: 'bg-blue-100 text-blue-800',
        fee: 'bg-yellow-100 text-yellow-800'
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1)
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatPaymentMethod = (method) => {
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
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
    alert('Exporting transactions...')
}

const viewTransaction = (transaction) => {
    alert(`Viewing transaction: ${transaction.transaction_id}`)
}

const processTransaction = (transaction) => {
    if (confirm(`Process transaction ${transaction.transaction_id}?`)) {
        transaction.status = 'completed'
        alert('Transaction processed successfully!')
    }
}

const refundTransaction = (transaction) => {
    if (confirm(`Refund transaction ${transaction.transaction_id}?`)) {
        alert('Refund initiated successfully!')
    }
}
</script>
