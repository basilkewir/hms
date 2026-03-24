<template>
    <DashboardLayout title="My Transactions" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Transactions</h1>
                    <p class="text-sm text-gray-500">Payments you have processed</p>
                </div>
                <div>
                    <a :href="route('front-desk.payments.process')"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        + Process Payment
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-xl">💳</div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Total Transactions</p>
                        <p class="text-lg font-bold text-indigo-600">{{ stats.total_count }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-xl">✅</div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Completed</p>
                        <p class="text-lg font-bold text-green-600">{{ stats.completed }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center text-xl">⏳</div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Pending</p>
                        <p class="text-lg font-bold text-yellow-600">{{ stats.pending }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-xl">💰</div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Total Amount</p>
                        <p class="text-lg font-bold text-blue-600">{{ formatCurrency(stats.total_amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Guest name, payment #..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select v-model="filters.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option v-for="type in transactionTypeOptions" :key="type" :value="type">
                                {{ formatType(type) }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Revenue Type</label>
                        <select v-model="filters.revenue_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Revenue Types</option>
                            <option v-for="option in revenueTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select v-model="filters.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">From Date</label>
                        <input v-model="filters.start_date" type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">To Date</label>
                        <input v-model="filters.end_date" type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="clearFilters"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        🔄 Clear
                    </button>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="10" class="px-6 py-10 text-center text-gray-500">
                                    No transactions found.
                                </td>
                            </tr>
                            <tr v-for="txn in filteredTransactions" :key="txn.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-gray-900">
                                    {{ txn.payment_number || txn.transaction_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ txn.guest_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ txn.room_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ txn.reference }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ txn.source_label || formatType(txn.type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ formatCurrency(txn.amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ formatMethod(txn.payment_method) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                                          :class="getStatusClass(txn.status)">
                                        {{ formatStatus(txn.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(txn.date || txn.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button @click="viewTransaction(txn)"
                                            class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-xs font-medium rounded text-indigo-700 bg-white hover:bg-indigo-50 transition-colors">
                                        👁 View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Transaction Detail Modal -->
        <div v-if="selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Transaction Details</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 block text-xs">Transaction ID</span>
                            <span class="font-mono font-medium text-gray-900">{{ selectedTransaction.payment_number || selectedTransaction.transaction_id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Status</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                  :class="getStatusClass(selectedTransaction.status)">
                                {{ formatStatus(selectedTransaction.status) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Guest</span>
                            <span class="font-medium text-gray-900">{{ selectedTransaction.guest_name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Room</span>
                            <span class="font-medium text-gray-900">{{ selectedTransaction.room_number }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Reference</span>
                            <span class="font-medium text-gray-900">{{ selectedTransaction.reference }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Amount</span>
                            <span class="font-bold text-lg text-green-600">{{ formatCurrency(selectedTransaction.amount) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Payment Method</span>
                            <span class="font-medium text-gray-900">{{ formatMethod(selectedTransaction.payment_method) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Date</span>
                            <span class="font-medium text-gray-900">{{ formatDate(selectedTransaction.date || selectedTransaction.created_at) }}</span>
                        </div>
                    </div>
                    <div v-if="selectedTransaction.notes" class="mt-2">
                        <span class="text-gray-500 block text-xs mb-1">Notes</span>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded p-2">{{ selectedTransaction.notes }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
                    <button @click="printReceipt(selectedTransaction)"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                        🖨 Print Receipt
                    </button>
                    <button @click="closeModal"
                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    navigation: Array,
    transactions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({ total_count: 0, total_amount: 0, completed: 0, pending: 0 })
    },
})

// Filters
const filters = ref({
    search: '',
    type: '',
    revenue_type: '',
    status: '',
    start_date: '',
    end_date: '',
})

const transactionTypeOptions = computed(() => {
    return [...new Set(props.transactions.map(transaction => transaction.type).filter(Boolean))].sort()
})

const revenueTypeOptions = computed(() => {
    return [...new Map(props.transactions
        .filter(transaction => transaction.source_key || transaction.source_label)
        .map(transaction => [
            transaction.source_key || transaction.source_label,
            {
                value: transaction.source_key || transaction.source_label,
                label: transaction.source_label || formatType(transaction.type),
            }
        ])).values()].sort((left, right) => left.label.localeCompare(right.label))
})

// Modal
const selectedTransaction = ref(null)

const viewTransaction = (txn) => {
    selectedTransaction.value = txn
}

const closeModal = () => {
    selectedTransaction.value = null
}

const printReceipt = (txn) => {
    const html = `<!DOCTYPE html><html><head><title>Receipt - ${txn.payment_number || txn.transaction_id}</title>
    <style>body{font-family:Arial,sans-serif;font-size:13px;margin:20px;}h2{text-align:center;}.row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee;font-size:12px;}.label{color:#666;}.val{font-weight:500;}.grand{font-weight:bold;font-size:15px;border-top:2px solid #333;margin-top:8px;padding-top:6px;}</style>
    </head><body>
    <h2>Payment Receipt</h2>
    <p style="text-align:center;margin:2px 0;"><strong>${txn.payment_number || txn.transaction_id}</strong></p>
    <p style="text-align:center;margin:2px 0;color:#555;font-size:12px;">${formatDate(txn.date || txn.created_at)}</p>
    <hr style="margin:12px 0;"/>
    <div class="row"><span class="label">Guest</span><span class="val">${txn.guest_name}</span></div>
    <div class="row"><span class="label">Room</span><span class="val">${txn.room_number || 'N/A'}</span></div>
    <div class="row"><span class="label">Reference</span><span class="val">${txn.reference || 'N/A'}</span></div>
    <div class="row"><span class="label">Payment Method</span><span class="val">${formatMethod(txn.payment_method)}</span></div>
    <div class="row"><span class="label">Status</span><span class="val">${formatStatus(txn.status)}</span></div>
    ${txn.notes ? `<div class="row"><span class="label">Notes</span><span class="val">${txn.notes}</span></div>` : ''}
    <div class="row grand"><span>TOTAL PAID</span><span>${formatCurrency(txn.amount)}</span></div>
    <p style="text-align:center;margin-top:24px;font-size:11px;color:#999;">Thank you for your payment!</p>
    </body></html>`

    const w = window.open('', '_blank', 'width=400,height=500')
    w.document.write(html)
    w.document.close()
    w.focus()
    w.print()
}

// Filtered list (client-side)
const filteredTransactions = computed(() => {
    return props.transactions.filter(txn => {
        const search = filters.value.search.toLowerCase()
        if (search) {
            const haystack = [
                txn.guest_name,
                txn.payment_number,
                txn.transaction_id,
                txn.reference,
            ].join(' ').toLowerCase()
            if (!haystack.includes(search)) return false
        }
        if (filters.value.type && txn.type !== filters.value.type) return false
        if (filters.value.revenue_type && (txn.source_key || txn.source_label) !== filters.value.revenue_type) return false
        if (filters.value.status && txn.status !== filters.value.status) return false
        if (filters.value.start_date) {
            const txnDate = new Date(txn.date || txn.created_at).toISOString().slice(0, 10)
            if (txnDate < filters.value.start_date) return false
        }
        if (filters.value.end_date) {
            const txnDate = new Date(txn.date || txn.created_at).toISOString().slice(0, 10)
            if (txnDate > filters.value.end_date) return false
        }
        return true
    })
})

const clearFilters = () => {
    filters.value = { search: '', type: '', revenue_type: '', status: '', start_date: '', end_date: '' }
}

const formatType = (type) => {
    if (!type) return 'N/A'
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Formatters
const formatMethod = (method) => {
    if (!method) return 'N/A'
    return method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleString()
}

const getStatusClass = (status) => {
    const classes = {
        completed: 'bg-green-600 text-white',
        pending:   'bg-yellow-500 text-white',
        failed:    'bg-red-600 text-white',
        refunded:  'bg-gray-500 text-white',
    }
    return classes[status?.toLowerCase()] || 'bg-gray-400 text-white'
}
</script>
